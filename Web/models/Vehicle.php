<?php
require_once __DIR__ . '/../config/db.php';

// Vehicle categories and rates
class Vehicle {
    /**
     * Get live vehicles from Oracle database, fallback to predefined fleet categories.
     */
    public static function getAllFromDb() {
        $sql = "SELECT vehicleId, registrationNo, vehicleType, model, manufacturer, capacity, status 
                FROM vehicle 
                ORDER BY vehicleId ASC";
        $rows = Database::queryOracle($sql);
        return !empty($rows) ? $rows : self::getAll();
    }

    public static function getAll() {
        return [
            [
                'id' => 'car',
                'type' => 'LUXURYCAR',
                'name' => 'Executive Ride',
                'description' => 'Go anywhere with SmartMove. Request a premium sedan ride, hop in, and go.',
                'seats' => '1-4 Seats',
                'base_fare' => 5.00
            ],
            [
                'id' => 'van',
                'type' => 'VAN',
                'name' => 'Reserve Chauffeur',
                'description' => 'Reserve your ride in advance so you can relax on the day of your trip.',
                'seats' => '6-12 Seats',
                'base_fare' => 8.00
            ],
            [
                'id' => 'airport',
                'type' => 'VAN',
                'name' => 'Airport Transfer',
                'description' => 'Request a ride to or from the airport with executive luggage capacity.',
                'seats' => '6-12 Seats',
                'base_fare' => 8.00
            ],
            [
                'id' => 'threewheel',
                'type' => 'THREEWHEEL',
                'name' => 'City Express',
                'description' => 'Get affordable three-wheeler and motorbike rides in minutes at your doorstep.',
                'seats' => '1-3 Seats',
                'base_fare' => 2.50
            ],
            [
                'id' => 'bike',
                'type' => 'BIKE',
                'name' => 'Parcel Courier',
                'description' => 'SmartMove makes same-day item delivery faster and more reliable than ever.',
                'seats' => 'Parcel Express',
                'base_fare' => 1.50
            ],
            [
                'id' => 'bus',
                'type' => 'BUS',
                'name' => 'Intercity & Bus',
                'description' => 'Get convenient, affordable outstation cabs and buses anytime at your door.',
                'seats' => '30-50 Seats',
                'base_fare' => 25.00
            ]
        ];
    }
}
