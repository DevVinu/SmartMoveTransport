

// Indexes
db.passenger_reviews.createIndex({ routeId: 1 }, { name: "idx_reviews_route" });
db.passenger_reviews.createIndex({ driverId: 1, rating: -1 }, { name: "idx_reviews_driver_rating" });
db.passenger_reviews.createIndex({ vehicleId: 1, rating: -1 }, { name: "idx_reviews_vehicle_rating" });
db.passenger_reviews.createIndex({ comment: "text", tags: "text" }, { name: "idx_reviews_text" });

db.vehicle_documents.createIndex({ vehicleId: 1 }, { unique: true, name: "idx_vehdocs_vehicleId" });
db.vehicle_documents.createIndex({ registrationNo: 1 }, { name: "idx_vehdocs_regNo" });

db.travel_announcements.createIndex({ active: 1, priority: 1 }, { name: "idx_announcements_active" });
db.travel_announcements.createIndex({ affectedRoutes: 1 }, { name: "idx_announcements_routes" });

// Query 1: Reviews by route
print("\nRetrieve all passenger reviews for Route ");
const query1Results = db.passenger_reviews.find(
  { routeId: 1 },
  { _id: 0, reviewId: 1, passengerName: 1, rating: 1, comment: 1, tags: 1 }
).sort({ createdAt: -1 }).toArray();
printjson(query1Results);

// Query 2: Top rated drivers and vehicles
print("\n Highest-Rated Drivers");
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

print("\n Highest-Rated Vehicles ");
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
print("\nSearch complaints for keyword");
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
print("\nQUERY 4: Retrieve vehicle documents for Vehicle");
const query4Results = db.vehicle_documents.find(
  { vehicleId: 1 },
  { _id: 0, registrationNo: 1, model: 1, "documents.title": 1, "documents.status": 1, gallery: 1 }
).toArray();
printjson(query4Results);



print("  Total Reviews Seeded: " + db.passenger_reviews.countDocuments());
print("  Total Vehicle Documents: " + db.vehicle_documents.countDocuments());
print("  Total Announcements: " + db.travel_announcements.countDocuments());

