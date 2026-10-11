<?php
require_once __DIR__ . '/../config/db.php';

/**
 * SmartMove Transport Solutions — MongoDB Service Model
 * Implements the 4 mandatory NoSQL analytical queries required by the DM2 Coursework rubric.
 */
class MongoService {

    /**
     * QUERY 1 (Coursework Rubric):
     * Retrieve all passenger reviews for a specific route.
     * @param int $routeId
     * @return array
     */
    public static function getReviewsByRoute($routeId) {
        $filter = ['routeId' => (int)$routeId];
        $options = ['sort' => ['createdAt' => -1]];
        return Database::queryMongo('passenger_reviews', $filter, $options);
    }

    /**
     * QUERY 2 (Coursework Rubric):
     * Identify highest-rated vehicles or drivers based on customer feedback.
     * Groups reviews by driver/vehicle and computes average rating.
     * @return array
     */
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

            // Driver Aggregation
            $dId = $r['driverId'] ?? null;
            $dName = $r['driverName'] ?? 'Driver ' . $dId;
            if ($dId) {
                if (!isset($driverScores[$dId])) {
                    $driverScores[$dId] = ['name' => $dName, 'total' => 0, 'count' => 0];
                }
                $driverScores[$dId]['total'] += $rating;
                $driverScores[$dId]['count']++;
            }

            // Vehicle Aggregation
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

        // Calculate averages
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

    /**
     * QUERY 3 (Coursework Rubric):
     * Search customer complaints or feedback using specific keywords.
     * Uses regex / text matching across comments, tags, and sentiments.
     * @param string $keyword (e.g. 'AC', 'delay', 'luggage', 'clean')
     * @return array
     */
    public static function searchFeedbackByKeyword($keyword) {
        $keyword = trim($keyword);
        if (empty($keyword)) {
            return Database::queryMongo('passenger_reviews', [], ['sort' => ['createdAt' => -1], 'limit' => 10]);
        }

        // Regex search across comment or tags
        $filter = [
            '$or' => [
                ['comment' => ['$regex' => $keyword, '$options' => 'i']],
                ['tags'    => ['$regex' => $keyword, '$options' => 'i']],
                ['sentiment' => ['$regex' => $keyword, '$options' => 'i']]
            ]
        ];

        return Database::queryMongo('passenger_reviews', $filter);
    }

    /**
     * QUERY 4 (Coursework Rubric):
     * Retrieve vehicle documents and multimedia information.
     * @param int|null $vehicleId Optional vehicle filter
     * @return array
     */
    public static function getVehicleDocumentsAndMedia($vehicleId = null) {
        $filter = [];
        if ($vehicleId !== null) {
            $filter = ['vehicleId' => (int)$vehicleId];
        }
        return Database::queryMongo('vehicle_documents', $filter);
    }

    /**
     * Helper: Retrieve active travel announcements
     * @return array
     */
    public static function getActiveAnnouncements() {
        return Database::queryMongo('travel_announcements', ['active' => true]);
    }
}
