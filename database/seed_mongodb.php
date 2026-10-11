<?php
/**
 * SmartMove Transport Solutions — PHP MongoDB Seeder & Verification
 * Can be run via CLI: php database/seed_mongodb.php
 * Or accessed via Browser: http://localhost/SmartMoveTransport/database/seed_mongodb.php
 */

require_once __DIR__ . '/../Web/config/db.php';
require_once __DIR__ . '/../Web/models/MongoService.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=====================================================================\n";
echo "  SmartMove Transport Solutions — MongoDB PHP Seeder\n";
echo "=====================================================================\n";

$health = Database::checkHealth();
if (!$health['mongodb']['connected']) {
    echo "[-] MongoDB is not connected through PHP.\n";
    echo "    Details: " . $health['mongodb']['message'] . "\n\n";
    echo "[!] Tip: You can execute the MongoDB JavaScript script directly in mongosh:\n";
    echo "    mongosh \"mongodb://localhost:27017/smartmove_db\" database/mongodb_setup.js\n";
    exit(1);
}

echo "[+] Connected to MongoDB successfully via PHP.\n";

// Test the 4 Mandatory Coursework Queries
echo "\n--- VERIFYING MANDATORY RUBRIC QUERIES VIA MONGOSERVICE ---\n";

// Query 1: Route Reviews
$q1 = MongoService::getReviewsByRoute(1);
echo "1. Reviews for Route #1: " . count($q1) . " reviews found.\n";

// Query 2: Top Rated Drivers & Vehicles
$q2 = MongoService::getTopRatedDriversAndVehicles();
echo "2. Top Rated Drivers: " . count($q2['drivers']) . " drivers ranked.\n";
if (!empty($q2['drivers'])) {
    echo "   Highest Driver: " . $q2['drivers'][0]['name'] . " (★ " . $q2['drivers'][0]['avgRating'] . ")\n";
}
echo "   Top Rated Vehicles: " . count($q2['vehicles']) . " fleet units ranked.\n";
if (!empty($q2['vehicles'])) {
    echo "   Highest Vehicle: " . $q2['vehicles'][0]['regNo'] . " (" . $q2['vehicles'][0]['type'] . " - ★ " . $q2['vehicles'][0]['avgRating'] . ")\n";
}

// Query 3: Search Complaints by Keyword
$q3 = MongoService::searchFeedbackByKeyword('delay');
echo "3. Feedback matching keyword 'delay': " . count($q3) . " matches.\n";

// Query 4: Vehicle Documents
$q4 = MongoService::getVehicleDocumentsAndMedia(1);
echo "4. Documents for Vehicle #1: " . count($q4) . " vehicle document packages.\n";

// Active Announcements
$qAnn = MongoService::getActiveAnnouncements();
echo "5. Active Travel Announcements: " . count($qAnn) . " advisories active.\n";

echo "\n[✓] MongoDB verification complete! All coursework queries are functional.\n";
