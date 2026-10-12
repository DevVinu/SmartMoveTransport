<?php
require_once __DIR__ . '/../config/db.php';

// MongoDB analytical queries service
class MongoService {

    // Retrieve passenger reviews by route
    public static function getReviewsByRoute($routeId) {
        $filter = ['routeId' => (int)$routeId];
        $options = ['sort' => ['createdAt' => -1]];
        return Database::queryMongo('passenger_reviews', $filter, $options);
    }

    // Top-rated drivers and vehicles by feedback
    public static function getTopRatedDriversAndVehicles() {
        $allReviews = Database::queryMongo('passenger_reviews');
        if (empty($allReviews)) {
            return ['drivers' => [], 'vehicles' => []];
        }

        $driverScores = [];
        $vehicleScores = [];

        foreach ($allReviews as $rev) {
            $r = is_array($rev) ? $rev : (array)$rev;
            $rating = (float)($r['rating'] ?? 5);

            $dId = $r['driverId'] ?? null;
            $dName = $r['driverName'] ?? 'Driver ' . $dId;
            if ($dId) {
                if (!isset($driverScores[$dId])) {
                    $driverScores[$dId] = ['name' => $dName, 'total' => 0, 'count' => 0];
                }
                $driverScores[$dId]['total'] += $rating;
                $driverScores[$dId]['count']++;
            }

            $vId = $r['vehicleId'] ?? null;
            $vReg = $r['vehicleRegNo'] ?? 'Vehicle ' . $vId;
            $vType = $r['vehicleType'] ?? 'CAR';
            if ($vId) {
                if (!isset($vehicleScores[$vId])) {
                    $vehicleScores[$vId] = ['regNo' => $vReg, 'type' => $vType, 'total' => 0, 'count' => 0];
                }
                $vehicleScores[$vId]['total'] += $rating;
                $vehicleScores[$vId]['count']++;
            }
        }

        $topDrivers = [];
        foreach ($driverScores as $id => $d) {
            $topDrivers[] = [
                'driverId'  => $id,
                'name'      => $d['name'],
                'avgRating' => round($d['total'] / $d['count'], 2),
                'reviews'   => $d['count']
            ];
        }
        usort($topDrivers, fn($a, $b) => $b['avgRating'] <=> $a['avgRating']);

        $topVehicles = [];
        foreach ($vehicleScores as $id => $v) {
            $topVehicles[] = [
                'vehicleId' => $id,
                'regNo'     => $v['regNo'],
                'type'      => $v['type'],
                'avgRating' => round($v['total'] / $v['count'], 2),
                'reviews'   => $v['count']
            ];
        }
        usort($topVehicles, fn($a, $b) => $b['avgRating'] <=> $a['avgRating']);

        return [
            'drivers'  => $topDrivers,
            'vehicles' => $topVehicles
        ];
    }

    // Keyword search across complaints and feedback
    public static function searchFeedbackByKeyword($keyword) {
        $keyword = trim($keyword);
        if (empty($keyword)) {
            return Database::queryMongo('passenger_reviews', [], ['sort' => ['createdAt' => -1], 'limit' => 10]);
        }

        $filter = [
            '$or' => [
                ['comment' => ['$regex' => $keyword, '$options' => 'i']],
                ['tags'    => ['$regex' => $keyword, '$options' => 'i']],
                ['sentiment' => ['$regex' => $keyword, '$options' => 'i']]
            ]
        ];

        return Database::queryMongo('passenger_reviews', $filter);
    }

    // Vehicle documents and media attachments
    public static function getVehicleDocumentsAndMedia($vehicleId = null) {
        $filter = [];
        if ($vehicleId !== null) {
            $filter = ['vehicleId' => (int)$vehicleId];
        }
        return Database::queryMongo('vehicle_documents', $filter);
    }

    // Active travel announcements
    public static function getActiveAnnouncements() {
        return Database::queryMongo('travel_announcements', ['active' => true]);
    }
}
