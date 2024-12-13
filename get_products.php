<?php
// get_products.php (for fetching products)
//header('Content-Type: application/json');

// Database connection credentials
$servername = "localhost";
$username = "mpsm";
$password = "C1@$$@ctc0ders";
$dbname = "mpsm_ecommerce";

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Query to fetch all available products
$sql = "SELECT productName, Price FROM products WHERE availiabilityStatus = 'Available'";

$result = $conn->query($sql);

if ($result === FALSE) {
    // If the query fails, return an error response
    echo json_encode(["error" => "Failed to fetch products: " . $conn->error]);
    exit();
}

$products = [];

if ($result->num_rows > 0) {
    // Fetch each product and store it in the products array
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    // If no products are available, send an empty response
    echo json_encode(["message" => "No products available"]);
    exit();
}

// Return the products array as JSON
echo json_encode($products);

// Close the database connection
$conn->close();
?>
