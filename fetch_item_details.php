<?php
// Include your database connection
include 'db_connection.php';

// Get the item ID from the request
$itemId = $_GET['id'];

// Prepare and execute the query to fetch item details
$query = $conn->prepare("SELECT * FROM items WHERE ItemID = ?");
$query->bind_param("i", $itemId);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

// Output item details as form fields with titles for each attribute
echo "<h2>Item Details</h2>";
echo "<label>Item ID</label><input type='text' value='" . htmlspecialchars($row['ItemID']) . "' readonly>";
echo "<label>Product Name</label><input type='text' value='" . htmlspecialchars($row['productName']) . "'>";
echo "<label>SKU</label><input type='text' value='" . htmlspecialchars($row['SKU']) . "'>";
echo "<label>Price</label><input type='number' value='" . htmlspecialchars($row['Price']) . "'>";
echo "<label>Category</label><input type='text' value='" . htmlspecialchars($row['Category']) . "'>";
echo "<label>Stock Level</label><input type='number' value='" . htmlspecialchars($row['stockLevel']) . "'>";
echo "<label>Restock Level</label><input type='number' value='" . htmlspecialchars($row['restockLevel']) . "'>";
echo "<label>Availability Status</label><input type='text' value='" . htmlspecialchars($row['availabilityStatus']) . "'>";
echo "<label>Created At</label><input type='text' value='" . htmlspecialchars($row['created_at']) . "' readonly>";
echo "<label>Updated At</label><input type='text' value='" . htmlspecialchars($row['updated_at']) . "' readonly>";
echo "<label>Image URL</label><input type='text' value='" . htmlspecialchars($row['imageURL']) . "'>";
echo "<label>Volume</label><input type='text' value='" . htmlspecialchars($row['volume']) . "'>";
echo "<label>Expiration Date</label><input type='date' value='" . htmlspecialchars($row['expirationDate']) . "'>";
?>
