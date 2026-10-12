// MongoDB setup and seed script for smartmove_db
db = db.getSiblingDB("smartmove_db");

// Reset collections
db.passenger_reviews.drop();
db.vehicle_documents.drop();
db.travel_announcements.drop();
db.multimedia_content.drop();

// Vehicle documents
db.vehicle_documents.insertMany([
  {
    documentId: "DOC-VH01",
    vehicleId: 1,
    registrationNo: "WP-ND-4521",
    vehicleType: "BUS",
    model: "Coaster Deluxe",
    manufacturer: "Toyota",
    yearOfManufacture: 2023,
    capacity: 32,
    documents: [
      {
        docType: "INSURANCE",
        title: "Comprehensive Commercial Fleet Insurance",
        documentNo: "SLIC-POL-2026-90412",
        issuer: "Sri Lanka Insurance Corporation",
        issueDate: "2026-01-15",
        expiryDate: "2027-01-14",
        status: "VALID",
        fileUrl: "uploads/documents/WP-ND-4521_insurance.pdf",
        fileSize: "2.4 MB"
      },
      {
        docType: "REVENUE_LICENSE",
        title: "Western Province Annual Revenue License",
        documentNo: "WPRL-2026-38192",
        issuer: "Department of Motor Traffic WP",
        issueDate: "2026-02-01",
        expiryDate: "2027-01-31",
        status: "VALID",
        fileUrl: "uploads/documents/WP-ND-4521_revenue_license.pdf",
        fileSize: "1.1 MB"
      },
      {
        docType: "FITNESS_CERTIFICATE",
        title: "Passenger Transport Roadworthiness Certificate",
        documentNo: "NTC-FIT-8841",
        issuer: "National Transport Commission",
        issueDate: "2026-03-10",
        expiryDate: "2026-09-09",
        status: "VALID",
        fileUrl: "uploads/documents/WP-ND-4521_fitness.pdf",
        fileSize: "850 KB"
      }
    ],
    gallery: [
      {
        view: "Front Exterior & Livery",
        url: "https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=800&q=80",
        resolution: "1920x1080",
        caption: "Luxury Express 32-seater Highway Coach"
      },
      {
        view: "Interior Passenger Cabin",
        url: "https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&w=800&q=80",
        resolution: "1920x1080",
        caption: "Air-conditioned reclining luxury high-back seating"
      }
    ],
    telemetry: {
      gpsTrackingImei: "864209043219012",
      fuelType: "DIESEL",
      lastOdometerKm: 48250,
      installedSensors: ["AC_TEMPERATURE", "SPEED_LIMITER", "PASSENGER_DOOR_SAFETY"]
    },
    uploadedAt: new Date("2026-01-20T08:30:00Z")
  },
  {
    documentId: "DOC-VH02",
    vehicleId: 2,
    registrationNo: "WP-CAB-8921",
    vehicleType: "VAN",
    model: "KDH Commuter GL",
    manufacturer: "Toyota",
    yearOfManufacture: 2024,
    capacity: 14,
    documents: [
      {
        docType: "INSURANCE",
        title: "Executive Chauffeur Full Comprehensive Cover",
        documentNo: "ALLZ-POL-77291",
        issuer: "Allianz Insurance Lanka",
        issueDate: "2026-01-10",
        expiryDate: "2027-01-09",
        status: "VALID",
        fileUrl: "uploads/documents/WP-CAB-8921_insurance.pdf",
        fileSize: "1.8 MB"
      },
      {
        docType: "REVENUE_LICENSE",
        title: "Western Province Commercial Permit",
        documentNo: "WPRL-2026-55102",
        issuer: "Department of Motor Traffic",
        issueDate: "2026-02-15",
        expiryDate: "2027-02-14",
        status: "VALID",
        fileUrl: "uploads/documents/WP-CAB-8921_revenue.pdf",
        fileSize: "980 KB"
      }
    ],
    gallery: [
      {
        view: "Front Exterior Side Angle",
        url: "https://images.unsplash.com/photo-1559297434-fae8a1916a79?auto=format&fit=crop&w=800&q=80",
        resolution: "1920x1080",
        caption: "Executive 14-passenger VIP Van"
      },
      {
        view: "Rear Luggage Compartment",
        url: "https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=800&q=80",
        resolution: "1920x1080",
        caption: "Spacious trunk suited for airport luggage transfers"
      }
    ],
    telemetry: {
      gpsTrackingImei: "864209043219555",
      fuelType: "DIESEL",
      lastOdometerKm: 26100,
      installedSensors: ["DUAL_ZONE_AC", "GPS_LOCATOR", "USB_CHARGING_PORTS"]
    },
    uploadedAt: new Date("2026-02-01T10:15:00Z")
  },
  {
    documentId: "DOC-VH03",
    vehicleId: 3,
    registrationNo: "WP-NB-8812",
    vehicleType: "BUS",
    model: "Rosa Super Custom",
    manufacturer: "Mitsubishi",
    yearOfManufacture: 2022,
    capacity: 29,
    documents: [
      {
        docType: "INSURANCE",
        title: "Comprehensive Commercial Fleet Cover",
        documentNo: "CEY-POL-44120",
        issuer: "Ceylinco General Insurance",
        issueDate: "2025-11-20",
        expiryDate: "2026-11-19",
        status: "VALID",
        fileUrl: "uploads/documents/WP-NB-8812_insurance.pdf",
        fileSize: "2.1 MB"
      },
      {
        docType: "EMISSION_REPORT",
        title: "Vehicle Emission Test (VET) Certificate",
        documentNo: "CLEANCO-VET-3391",
        issuer: "CleanCo Lanka / DriveGreen",
        issueDate: "2026-02-18",
        expiryDate: "2027-02-17",
        status: "VALID",
        fileUrl: "uploads/documents/WP-NB-8812_vet.pdf",
        fileSize: "620 KB"
      }
    ],
    gallery: [
      {
        view: "Highway Profile",
        url: "https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&w=800&q=80",
        resolution: "1920x1080",
        caption: "Intercity Highway Express Shuttle"
      }
    ],
    telemetry: {
      gpsTrackingImei: "864209043219888",
      fuelType: "DIESEL",
      lastOdometerKm: 78900,
      installedSensors: ["AIR_SUSPENSION_MONITOR", "AC_TEMPERATURE"]
    },
    uploadedAt: new Date("2026-02-20T11:45:00Z")
  },
  {
    documentId: "DOC-VH04",
    vehicleId: 4,
    registrationNo: "WP-CAB-1209",
    vehicleType: "LUXURYCAR",
    model: "Prius Touring Executive",
    manufacturer: "Toyota",
    yearOfManufacture: 2024,
    capacity: 4,
    documents: [
      {
        docType: "INSURANCE",
        title: "First Party Luxury Comprehensive Policy",
        documentNo: "HNB-ASS-19942",
        issuer: "HNB Assurance PLC",
        issueDate: "2026-01-05",
        expiryDate: "2027-01-04",
        status: "VALID",
        fileUrl: "uploads/documents/WP-CAB-1209_insurance.pdf",
        fileSize: "1.6 MB"
      }
    ],
    gallery: [
      {
        view: "Corporate Sedan Front View",
        url: "https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=800&q=80",
        resolution: "1920x1080",
        caption: "Corporate Executive Sedan for Business Commuters"
      }
    ],
    telemetry: {
      gpsTrackingImei: "864209043219999",
      fuelType: "HYBRID_PETROL",
      lastOdometerKm: 14200,
      installedSensors: ["ECO_DRIVE_TRACKER", "CLIMATE_CONTROL"]
    },
    uploadedAt: new Date("2026-01-08T09:00:00Z")
  }
]);

// Passenger reviews
db.passenger_reviews.insertMany([
  {
    reviewId: "REV-2026-001",
    bookingId: 101,
    passengerId: 10,
    passengerName: "Kasun Jayawardena",
    routeId: 1,
    routeName: "Colombo - Kandy Express (E04)",
    vehicleId: 1,
    vehicleRegNo: "WP-ND-4521",
    vehicleType: "BUS",
    driverId: 1,
    driverName: "Kamal Perera",
    rating: 5,
    comment: "Excellent highway journey! The AC was super chilled and Kamal drove with top notch discipline. Departed right on time.",
    tags: ["AC", "Punctual", "Comfortable", "Safe Driving"],
    sentiment: "POSITIVE",
    createdAt: new Date("2026-09-02T10:30:00Z")
  },
  {
    reviewId: "REV-2026-002",
    bookingId: 102,
    passengerId: 11,
    passengerName: "Dilani Wickramasinghe",
    routeId: 1,
    routeName: "Colombo - Kandy Express (E04)",
    vehicleId: 1,
    vehicleRegNo: "WP-ND-4521",
    vehicleType: "BUS",
    driverId: 1,
    driverName: "Kamal Perera",
    rating: 4.8,
    comment: "Very clean interior and clean seats. Online ticket scanning was fast. Highly recommend SmartMove for Kandy commuters.",
    tags: ["Clean", "Punctual", "Fast Booking"],
    sentiment: "POSITIVE",
    createdAt: new Date("2026-09-03T14:15:00Z")
  },
  {
    reviewId: "REV-2026-003",
    bookingId: 103,
    passengerId: 12,
    passengerName: "Rohan Samaraweera",
    routeId: 2,
    routeName: "Colombo - Galle Southern Highway (E01)",
    vehicleId: 2,
    vehicleRegNo: "WP-CAB-8921",
    vehicleType: "VAN",
    driverId: 2,
    driverName: "Sunil Shantha",
    rating: 4.9,
    comment: "Smooth executive van trip down to Galle. Very polite driver, plenty of luggage space, and steady cruising at 100 km/h.",
    tags: ["Luggage", "Executive", "Comfortable", "Polite Driver"],
    sentiment: "POSITIVE",
    createdAt: new Date("2026-09-04T09:45:00Z")
  },
  {
    reviewId: "REV-2026-004",
    bookingId: 104,
    passengerId: 13,
    passengerName: "Anoma Senanayake",
    routeId: 1,
    routeName: "Colombo - Kandy Express (E04)",
    vehicleId: 3,
    vehicleRegNo: "WP-NB-8812",
    vehicleType: "BUS",
    driverId: 3,
    driverName: "Nimal Fernando",
    rating: 2.5,
    comment: "Significant delay of 25 minutes at Kadawatha exit. The AC cooling was low during the hot afternoon sun.",
    tags: ["Delay", "AC", "Traffic"],
    sentiment: "NEGATIVE",
    createdAt: new Date("2026-09-05T17:00:00Z")
  },
  {
    reviewId: "REV-2026-005",
    bookingId: 105,
    passengerId: 14,
    passengerName: "Buddhika Alwis",
    routeId: 3,
    routeName: "Colombo - Katunayake Airport Express (E03)",
    vehicleId: 4,
    vehicleRegNo: "WP-CAB-1209",
    vehicleType: "LUXURYCAR",
    driverId: 4,
    driverName: "Anura Jayasinghe",
    rating: 5,
    comment: "Airport transfer went flawlessly. Reached BIA in 22 minutes, driver helped load heavy luggage. 5 stars!",
    tags: ["Airport", "Luggage", "Fast", "Punctual"],
    sentiment: "POSITIVE",
    createdAt: new Date("2026-09-06T06:15:00Z")
  },
  {
    reviewId: "REV-2026-006",
    bookingId: 106,
    passengerId: 15,
    passengerName: "Sanjeewa Perera",
    routeId: 2,
    routeName: "Colombo - Galle Southern Highway (E01)",
    vehicleId: 2,
    vehicleRegNo: "WP-CAB-8921",
    vehicleType: "VAN",
    driverId: 2,
    driverName: "Sunil Shantha",
    rating: 4.7,
    comment: "On-time departure from Makumbura Multimodal Center. Clean vehicle with smooth suspension over the Southern Expressway.",
    tags: ["Clean", "Punctual", "Smooth"],
    sentiment: "POSITIVE",
    createdAt: new Date("2026-09-07T11:20:00Z")
  },
  {
    reviewId: "REV-2026-007",
    bookingId: 107,
    passengerId: 16,
    passengerName: "Chathuri Mendis",
    routeId: 1,
    routeName: "Colombo - Kandy Express (E04)",
    vehicleId: 1,
    vehicleRegNo: "WP-ND-4521",
    vehicleType: "BUS",
    driverId: 1,
    driverName: "Kamal Perera",
    rating: 4.9,
    comment: "Comfortable seating for tall passengers. The driver Kamal is very courteous and maintained road safety throughout.",
    tags: ["Safe Driving", "Comfortable", "Courteous"],
    sentiment: "POSITIVE",
    createdAt: new Date("2026-09-08T18:40:00Z")
  },
  {
    reviewId: "REV-2026-008",
    bookingId: 108,
    passengerId: 17,
    passengerName: "Harsha De Silva",
    routeId: 3,
    routeName: "Colombo - Katunayake Airport Express (E03)",
    vehicleId: 4,
    vehicleRegNo: "WP-CAB-1209",
    vehicleType: "LUXURYCAR",
    driverId: 4,
    driverName: "Anura Jayasinghe",
    rating: 4.8,
    comment: "Clean sedan, fresh fragrance, quiet hybrid engine. Perfect executive commute to meeting in Negombo.",
    tags: ["Clean", "Luxury", "Quiet"],
    sentiment: "POSITIVE",
    createdAt: new Date("2026-09-09T08:05:00Z")
  },
  {
    reviewId: "REV-2026-009",
    bookingId: 109,
    passengerId: 18,
    passengerName: "Mahesh Gunatilleke",
    routeId: 1,
    routeName: "Colombo - Kandy Express (E04)",
    vehicleId: 3,
    vehicleRegNo: "WP-NB-8812",
    vehicleType: "BUS",
    driverId: 3,
    driverName: "Nimal Fernando",
    rating: 3.2,
    comment: "A minor delay due to roadworks near Ambepussa. Seating was fine but luggage racks were full quickly.",
    tags: ["Delay", "Luggage", "Roadworks"],
    sentiment: "NEUTRAL",
    createdAt: new Date("2026-09-10T15:50:00Z")
  }
]);

// Travel announcements
db.travel_announcements.insertMany([
  {
    announcementId: "ANN-2026-001",
    title: "Central Expressway (E04) Lane Maintenance Detour",
    category: "EXPRESSWAY_ALERT",
    priority: "HIGH",
    message: "Scheduled resurfacing work between Mirigama and Kurunegala interchange. Heavy vehicle delays expected of approximately 15 minutes. Plan departures accordingly.",
    affectedRoutes: [1],
    active: true,
    publishedAt: "2026-09-10 08:00:00",
    expiresAt: "2026-09-15 23:59:59",
    author: "National Highway Operations Center"
  },
  {
    announcementId: "ANN-2026-002",
    title: "Monsoon Heavy Rain & Fog Warning on Kadugannawa Pass",
    category: "WEATHER_NOTICE",
    priority: "MEDIUM",
    message: "Adverse weather and mist reported along Kandy route section. All SmartMove drivers instructed to observe reduced speed limits and maintain safe headway distances.",
    affectedRoutes: [1],
    active: true,
    publishedAt: "2026-09-11 06:30:00",
    expiresAt: "2026-09-14 18:00:00",
    author: "SmartMove Safety & Dispatch Desk"
  },
  {
    announcementId: "ANN-2026-003",
    title: "Additional Weekend Express Services on Southern Expressway (E01)",
    category: "SCHEDULE_CHANGE",
    priority: "LOW",
    message: "Due to high commuter demand for the Galle Literary Festival, 4 additional VIP Van shuttles have been scheduled for Friday afternoon and Sunday evening.",
    affectedRoutes: [2],
    active: true,
    publishedAt: "2026-09-12 12:00:00",
    expiresAt: "2026-09-18 20:00:00",
    author: "Fleet Operations Management"
  },
  {
    announcementId: "ANN-2026-004",
    title: "Katunayake Airport Expressway (E03) Toll Plaza Electronic Lane Upgrade",
    category: "EXPRESSWAY_ALERT",
    priority: "MEDIUM",
    message: "Peliyagoda entrance electronic ETC toll gate undergoing sensor calibration. Minor queuing possible during peak flight departure windows (18:00 - 21:00).",
    affectedRoutes: [3],
    active: true,
    publishedAt: "2026-09-13 14:00:00",
    expiresAt: "2026-09-16 23:59:59",
    author: "Airport Transit Logistics"
  }
]);

// Multimedia content
db.multimedia_content.insertMany([
  {
    mediaId: "MED-001",
    category: "STATION_MAP",
    routeId: 1,
    title: "Makumbura Multimodal Center Boarding Bay Diagram",
    description: "Detailed layout showing Platform 4 for Kandy expressway coaches and luggage check-in counter.",
    mediaType: "MAP",
    fileUrl: "https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=800&q=80",
    fileSize: "1.4 MB",
    uploadedBy: "Admin Staff",
    uploadedAt: new Date("2026-08-01T09:00:00Z")
  },
  {
    mediaId: "MED-002",
    category: "ROUTE_LANDMARK",
    routeId: 1,
    title: "Kadugannawa Rock Tunnel Scenic Highway View",
    description: "Iconic historical pass on the Colombo-Kandy corridor with commuter rest stop amenities.",
    mediaType: "IMAGE",
    fileUrl: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80",
    fileSize: "3.2 MB",
    uploadedBy: "Content Team",
    uploadedAt: new Date("2026-08-05T11:20:00Z")
  },
  {
    mediaId: "MED-003",
    category: "STATION_MAP",
    routeId: 2,
    title: "Pinnaduwa Galle Terminal Passenger Lounge Map",
    description: "Floor map of Galle expressway exit lounge featuring café, restrooms, and taxi pickup zone.",
    mediaType: "MAP",
    fileUrl: "https://images.unsplash.com/photo-1524813686514-a57563d77d4c?auto=format&fit=crop&w=800&q=80",
    fileSize: "2.1 MB",
    uploadedBy: "Admin Staff",
    uploadedAt: new Date("2026-08-10T14:40:00Z")
  }
]);

// Indexes
db.passenger_reviews.createIndex({ routeId: 1 }, { name: "idx_reviews_route" });
db.passenger_reviews.createIndex({ driverId: 1, rating: -1 }, { name: "idx_reviews_driver_rating" });
db.passenger_reviews.createIndex({ vehicleId: 1, rating: -1 }, { name: "idx_reviews_vehicle_rating" });
db.passenger_reviews.createIndex({ comment: "text", tags: "text" }, { name: "idx_reviews_text" });

db.vehicle_documents.createIndex({ vehicleId: 1 }, { unique: true, name: "idx_vehdocs_vehicleId" });
db.vehicle_documents.createIndex({ registrationNo: 1 }, { name: "idx_vehdocs_regNo" });

db.travel_announcements.createIndex({ active: 1, priority: 1 }, { name: "idx_announcements_active" });
db.travel_announcements.createIndex({ affectedRoutes: 1 }, { name: "idx_announcements_routes" });

// Coursework queries
// Query 1: Reviews by route
print("\n--- QUERY 1: Retrieve all passenger reviews for Route #1 (Colombo - Kandy) ---");
const query1Results = db.passenger_reviews.find(
  { routeId: 1 },
  { _id: 0, reviewId: 1, passengerName: 1, rating: 1, comment: 1, tags: 1 }
).sort({ createdAt: -1 }).toArray();
printjson(query1Results);

// Query 2: Top rated drivers and vehicles
print("\n--- QUERY 2A: Highest-Rated Drivers ---");
const topDrivers = db.passenger_reviews.aggregate([
  {
    $group: {
      _id: "$driverId",
      driverName: { $first: "$driverName" },
      avgRating: { $avg: "$rating" },
      reviewCount: { $sum: 1 }
    }
  },
  { $sort: { avgRating: -1 } },
  {
    $project: {
      _id: 0,
      driverId: "$_id",
      driverName: 1,
      avgRating: { $round: ["$avgRating", 2] },
      reviewCount: 1
    }
  }
]).toArray();
printjson(topDrivers);

print("\n--- QUERY 2B: Highest-Rated Vehicles (Aggregation Pipeline) ---");
const topVehicles = db.passenger_reviews.aggregate([
  {
    $group: {
      _id: "$vehicleId",
      vehicleRegNo: { $first: "$vehicleRegNo" },
      vehicleType: { $first: "$vehicleType" },
      avgRating: { $avg: "$rating" },
      reviewCount: { $sum: 1 }
    }
  },
  { $sort: { avgRating: -1 } },
  {
    $project: {
      _id: 0,
      vehicleId: "$_id",
      vehicleRegNo: 1,
      vehicleType: 1,
      avgRating: { $round: ["$avgRating", 2] },
      reviewCount: 1
    }
  }
]).toArray();
printjson(topVehicles);

// Query 3: Search reviews by keyword
print("\n--- QUERY 3: Search complaints for keyword: 'delay' ---");
const query3Results = db.passenger_reviews.find(
  {
    $or: [
      { comment: { $regex: "delay", $options: "i" } },
      { tags: { $regex: "delay", $options: "i" } }
    ]
  },
  { _id: 0, reviewId: 1, passengerName: 1, routeName: 1, rating: 1, comment: 1, tags: 1 }
).toArray();
printjson(query3Results);

// Query 4: Vehicle documents
print("\n--- QUERY 4: Retrieve vehicle documents for Vehicle #1 ---");
const query4Results = db.vehicle_documents.find(
  { vehicleId: 1 },
  { _id: 0, registrationNo: 1, model: 1, "documents.title": 1, "documents.status": 1, gallery: 1 }
).toArray();
printjson(query4Results);

print("\n=====================================================================");
print("  MongoDB 'smartmove_db' initialized successfully!");
print("  Total Collections Created: 4");
print("  Total Reviews Seeded: " + db.passenger_reviews.countDocuments());
print("  Total Vehicle Documents: " + db.vehicle_documents.countDocuments());
print("  Total Announcements: " + db.travel_announcements.countDocuments());
print("=====================================================================\n");
