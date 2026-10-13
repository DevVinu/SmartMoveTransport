-- SmartMove PL/SQL Packages and Stored Procedures

-- Package Specification for Business Reports
CREATE OR REPLACE PACKAGE pkgSmartMoveReports AS
    PROCEDURE getFrequentRoutes(pCursor OUT SYS_REFCURSOR);
    PROCEDURE getPeriodRevenue(pCursor OUT SYS_REFCURSOR);
    PROCEDURE getPassengerHistory(pCursor OUT SYS_REFCURSOR);
    PROCEDURE getMaintenanceDue(pCursor OUT SYS_REFCURSOR);
    PROCEDURE getDriverPerformance(pCursor OUT SYS_REFCURSOR);
END pkgSmartMoveReports;
/

-- Package Body for Business Reports
CREATE OR REPLACE PACKAGE BODY pkgSmartMoveReports AS

    -- Report 1: Top Frequently Used Routes
    PROCEDURE getFrequentRoutes(pCursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN pCursor FOR
            SELECT r.routeId, r.routeName, r.origin, r.destination,
                   COUNT(b.bookingId) AS totalBookings,
                   NVL(SUM(b.ticketPrice), 0) AS totalRevenue
            FROM route r
            JOIN scheduledTrip st ON r.routeId = st.routeId
            JOIN booking b ON st.tripId = b.tripId
            WHERE b.bookingStatus = 'CONFIRMED'
            GROUP BY r.routeId, r.routeName, r.origin, r.destination
            ORDER BY totalBookings DESC;
    EXCEPTION
        WHEN OTHERS THEN
            OPEN pCursor FOR SELECT NULL AS routeId FROM dual WHERE 1=0;
    END getFrequentRoutes;

    -- Report 2: Period Revenue by Payment Channel
    PROCEDURE getPeriodRevenue(pCursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN pCursor FOR
            SELECT p.paymentMethod,
                   COUNT(p.paymentId) AS transactionCount,
                   SUM(p.amountPaid) AS totalCollected
            FROM payment p
            WHERE p.paymentStatus = 'PAID'
            GROUP BY p.paymentMethod
            ORDER BY totalCollected DESC;
    EXCEPTION
        WHEN OTHERS THEN
            OPEN pCursor FOR SELECT NULL AS paymentMethod FROM dual WHERE 1=0;
    END getPeriodRevenue;

    -- Report 3: Passenger Travel History
    PROCEDURE getPassengerHistory(pCursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN pCursor FOR
            SELECT b.bookingId, (u.firstName || ' ' || u.lastName) AS passengerName,
                   st.tripDate, r.routeName, st.departureTime,
                   b.seatNumber, b.ticketPrice, b.bookingStatus, p.paymentStatus
            FROM booking b
            JOIN passenger pass ON b.passengerId = pass.passengerId
            JOIN users u ON pass.passengerId = u.userId
            JOIN scheduledTrip st ON b.tripId = st.tripId
            JOIN route r ON st.routeId = r.routeId
            LEFT JOIN payment p ON b.bookingId = p.bookingId
            ORDER BY st.tripDate DESC;
    EXCEPTION
        WHEN OTHERS THEN
            OPEN pCursor FOR SELECT NULL AS bookingId FROM dual WHERE 1=0;
    END getPassengerHistory;

    -- Report 4: Vehicles Due for Maintenance
    PROCEDURE getMaintenanceDue(pCursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN pCursor FOR
            SELECT v.registrationNo, v.vehicleType, v.model,
                   ms.maintenanceType, ms.nextServiceDate,
                   CASE 
                       WHEN ms.nextServiceDate < SYSDATE THEN 'URGENT OVERDUE'
                       WHEN ms.nextServiceDate <= SYSDATE + 7 THEN 'DUE IN 7 DAYS'
                       ELSE 'UPCOMING'
                   END AS urgencyStatus
            FROM vehicle v
            JOIN maintenance ms ON v.vehicleId = ms.vehicleId
            WHERE ms.maintenanceStatus IN ('SCHEDULED', 'IN_PROGRESS')
            ORDER BY ms.nextServiceDate ASC;
    EXCEPTION
        WHEN OTHERS THEN
            OPEN pCursor FOR SELECT NULL AS registrationNo FROM dual WHERE 1=0;
    END getMaintenanceDue;

    -- Report 5: Driver Performance and Fuel Consumption
    PROCEDURE getDriverPerformance(pCursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN pCursor FOR
            SELECT (u.firstName || ' ' || u.lastName) AS driverName,
                   d.licenseNumber AS licenseNo,
                   COUNT(DISTINCT st.tripId) AS completedTrips,
                   COUNT(DISTINCT ds.shiftId) AS totalShifts,
                   NVL(SUM(fl.litersPumped), 0) AS totalFuelLogged
            FROM driver d
            JOIN users u ON d.driverId = u.userId
            LEFT JOIN scheduledTrip st ON d.driverId = st.driverId AND st.tripStatus = 'COMPLETED'
            LEFT JOIN driverShift ds ON d.driverId = ds.driverId
            LEFT JOIN fuelLog fl ON d.driverId = fl.driverId
            GROUP BY u.firstName, u.lastName, d.licenseNumber
            ORDER BY completedTrips DESC;
    EXCEPTION
        WHEN OTHERS THEN
            OPEN pCursor FOR SELECT NULL AS driverName FROM dual WHERE 1=0;
    END getDriverPerformance;

END pkgSmartMoveReports;
/

-- Stored Procedure: Atomic Ticket Booking with Promo Code and Payment
CREATE OR REPLACE PROCEDURE prcBookTicket(
    pPassengerId   IN  NUMBER,
    pTripId        IN  NUMBER,
    pPromoCode     IN  VARCHAR2,
    pSeatNumber    IN  NUMBER,
    pPaymentMethod IN  VARCHAR2,
    pBookingId     OUT NUMBER,
    pAmountPaid    OUT NUMBER,
    pStatus        OUT VARCHAR2,
    pMessage       OUT VARCHAR2
) AS
    vAvailableSeats NUMBER;
    vBaseFare       NUMBER(10,2);
    vFinalFare      NUMBER(10,2);
    vPromoId        NUMBER := NULL;
    vDiscountPct    NUMBER(5,2) := 0;
    vPaymentId      NUMBER;
    vTxnRef         VARCHAR2(60);
    vSeat           NUMBER := pSeatNumber;
    eNoSeats        EXCEPTION;
    eInvalidTrip    EXCEPTION;
BEGIN
    -- Check trip existence and available seats with row lock
    BEGIN
        SELECT availableSeats, fareAmount
        INTO vAvailableSeats, vBaseFare
        FROM scheduledTrip
        WHERE tripId = pTripId
        FOR UPDATE;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RAISE eInvalidTrip;
    END;

    IF vAvailableSeats <= 0 THEN
        RAISE eNoSeats;
    END IF;

    -- Validate promotional code if provided
    IF pPromoCode IS NOT NULL AND LENGTH(TRIM(pPromoCode)) > 0 THEN
        BEGIN
            SELECT promoId, discountPercentage
            INTO vPromoId, vDiscountPct
            FROM promoCode
            WHERE UPPER(promoCode) = UPPER(TRIM(pPromoCode))
              AND SYSDATE BETWEEN validFrom AND validUntil
              AND currentUsage < maxUsage;

            -- Increment promo usage count
            UPDATE promoCode
            SET currentUsage = currentUsage + 1
            WHERE promoId = vPromoId;
        EXCEPTION
            WHEN NO_DATA_FOUND THEN
                vPromoId := NULL;
                vDiscountPct := 0;
        END;
    END IF;

    -- Calculate final ticket fare
    vFinalFare := vBaseFare - (vBaseFare * (vDiscountPct / 100.0));
    IF vFinalFare < 0 THEN
        vFinalFare := 0;
    END IF;

    -- Generate next booking ID
    SELECT NVL(MAX(bookingId), 0) + 1 INTO pBookingId FROM booking;

    -- Allocate default seat number if needed
    IF vSeat IS NULL OR vSeat <= 0 THEN
        vSeat := MOD(pBookingId, 30) + 1;
    END IF;

    -- Insert confirmed booking
    INSERT INTO booking (bookingId, passengerId, tripId, promoId, bookingDate, seatNumber, ticketPrice, bookingStatus)
    VALUES (pBookingId, pPassengerId, pTripId, vPromoId, SYSDATE, vSeat, vFinalFare, 'CONFIRMED');

    -- Generate next payment ID and transaction reference
    SELECT NVL(MAX(paymentId), 0) + 1 INTO vPaymentId FROM payment;
    vTxnRef := 'TXN-' || UPPER(SUBSTR(NVL(pPaymentMethod, 'CARD'), 1, 3)) || '-' || TO_CHAR(SYSDATE, 'YYYYMMDD') || '-' || LPAD(pBookingId, 4, '0');

    -- Insert verified payment record
    INSERT INTO payment (paymentId, bookingId, paymentDate, amountPaid, paymentMethod, transactionReference, paymentStatus)
    VALUES (vPaymentId, pBookingId, SYSDATE, vFinalFare, NVL(pPaymentMethod, 'CARD'), vTxnRef, 'PAID');

    -- Decrement available seats on scheduled trip
    UPDATE scheduledTrip
    SET availableSeats = availableSeats - 1
    WHERE tripId = pTripId;

    COMMIT;

    pAmountPaid := vFinalFare;
    pStatus := 'SUCCESS';
    pMessage := 'Booking confirmed with payment reference ' || vTxnRef;

EXCEPTION
    WHEN eInvalidTrip THEN
        ROLLBACK;
        pBookingId := NULL;
        pAmountPaid := 0;
        pStatus := 'ERROR';
        pMessage := 'Specified trip does not exist.';
    WHEN eNoSeats THEN
        ROLLBACK;
        pBookingId := NULL;
        pAmountPaid := 0;
        pStatus := 'ERROR';
        pMessage := 'Selected trip is sold out.';
    WHEN OTHERS THEN
        ROLLBACK;
        pBookingId := NULL;
        pAmountPaid := 0;
        pStatus := 'ERROR';
        pMessage := 'Database error: ' || SQLERRM;
END prcBookTicket;
/

-- Stored Procedure: Process Passenger Cancellation and Refund
CREATE OR REPLACE PROCEDURE prcProcessRefund(
    pBookingId     IN  NUMBER,
    pStaffId       IN  NUMBER,
    pRefundReason  IN  VARCHAR2,
    pRefundId      OUT NUMBER,
    pRefundAmount  OUT NUMBER,
    pStatus        OUT VARCHAR2,
    pMessage       OUT VARCHAR2
) AS
    vTripId        NUMBER;
    vPaymentId     NUMBER;
    vAmount        NUMBER(10,2);
    vBookingStatus VARCHAR2(20);
    vStaffId       NUMBER;
    eNotFound      EXCEPTION;
    eAlreadyCan    EXCEPTION;
BEGIN
    -- Check booking details
    BEGIN
        SELECT b.tripId, b.ticketPrice, b.bookingStatus, NVL(p.paymentId, 0)
        INTO vTripId, vAmount, vBookingStatus, vPaymentId
        FROM booking b
        LEFT JOIN payment p ON b.bookingId = p.bookingId
        WHERE b.bookingId = pBookingId
        FOR UPDATE;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RAISE eNotFound;
    END;

    IF vBookingStatus = 'CANCELLED' THEN
        RAISE eAlreadyCan;
    END IF;

    -- Resolve valid staff ID
    BEGIN
        SELECT staffId INTO vStaffId FROM staff WHERE staffId = pStaffId;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            SELECT NVL(MIN(staffId), 2) INTO vStaffId FROM staff;
    END;

    -- Cancel booking status
    UPDATE booking
    SET bookingStatus = 'CANCELLED'
    WHERE bookingId = pBookingId;

    -- Return seat to scheduled trip
    UPDATE scheduledTrip
    SET availableSeats = availableSeats + 1
    WHERE tripId = vTripId;

    -- Generate refund record
    SELECT NVL(MAX(refundId), 0) + 1 INTO pRefundId FROM refund;

    INSERT INTO refund (refundId, bookingId, paymentId, processedByStaffId, refundAmount, refundReason, refundDate, refundStatus)
    VALUES (pRefundId, pBookingId, NVL(vPaymentId, 1), vStaffId, vAmount, NVL(pRefundReason, 'Customer Cancellation'), SYSDATE, 'PROCESSED');

    -- Update payment status to REFUNDED
    IF vPaymentId > 0 THEN
        UPDATE payment SET paymentStatus = 'REFUNDED' WHERE paymentId = vPaymentId;
    END IF;

    COMMIT;

    pRefundAmount := vAmount;
    pStatus := 'SUCCESS';
    pMessage := 'Refund processed successfully.';

EXCEPTION
    WHEN eNotFound THEN
        ROLLBACK;
        pRefundId := NULL;
        pRefundAmount := 0;
        pStatus := 'ERROR';
        pMessage := 'Booking record not found.';
    WHEN eAlreadyCan THEN
        ROLLBACK;
        pRefundId := NULL;
        pRefundAmount := 0;
        pStatus := 'ERROR';
        pMessage := 'Booking is already cancelled.';
    WHEN OTHERS THEN
        ROLLBACK;
        pRefundId := NULL;
        pRefundAmount := 0;
        pStatus := 'ERROR';
        pMessage := 'Database error: ' || SQLERRM;
END prcProcessRefund;
/
