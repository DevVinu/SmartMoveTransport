<?php
// This verifys the test connectivity between php and mongoDB and demonstrate analatical quaries

require_once __DIR__ . '/../Web/config/db.php';
require_once __DIR__ . '/../Web/models/MongoService.php';

header('Content-Type: text/plain; charset=utf-8');

$health = Database::checkHealth();
if (!$health['mongodb']['connected']) {
    echo "[-] MongoDB is not connected through PHP.\n";
    echo "    Details: " . $health['mongodb']['message'] . "\n";
    exit(1);
}

echo "[+] Connected to MongoDB successfully.\n";

// Route reviews
$q1 = MongoService::getReviewsByRoute(1);
echo "1. Reviews for Route #1: " . count($q1) . " reviews found.\n";

// Top rated drivers and vehicles
$q2 = MongoService::getTopRatedDriversAndVehicles();
echo "2. Top Rated Drivers: " . count($q2['drivers']) . " drivers ranked.\n";
if (!empty($q2['drivers'])) {
    echo "   Highest Driver: " . $q2['drivers'][0]['name'] . " (★ " . $q2['drivers'][0]['avgRating'] . ")\n";
}
echo "   Top Rated Vehicles: " . count($q2['vehicles']) . " fleet units ranked.\n";
if (!empty($q2['vehicles'])) {
    echo "   Highest Vehicle: " . $q2['vehicles'][0]['regNo'] . " (" . $q2['vehicles'][0]['type'] . " - ★ " . $q2['vehicles'][0]['avgRating'] . ")\n";
}

// Search feedback
$q3 = MongoService::searchFeedbackByKeyword('delay');
echo "3. Feedback matching keyword 'delay': " . count($q3) . " matches.\n";

// Vehicle documents
$q4 = MongoService::getVehicleDocumentsAndMedia(1);
echo "4. Documents for Vehicle #1: " . count($q4) . " vehicle document packages.\n";

// Active announcements
$qAnn = MongoService::getActiveAnnouncements();
echo "5. Active Travel Announcements: " . count($qAnn) . " advisories active.\n";

echo "\nMongoDB verification complete.\n";
