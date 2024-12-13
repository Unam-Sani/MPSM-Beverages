<?php 
// Enable error reporting and logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_log.txt');
error_reporting(E_ALL);
error_log('Category: ' . $_POST['Category']); // Log the category value
error_log('Script started.');  // Log script start
error_log("Request method: " . $_SERVER["REQUEST_METHOD"]); // Log the request method


require 'db_connection.php'; // Include your actual database connection file

// Check request method
if ($_SERVER["REQUEST_METHOD"] !== "GET" && $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

// Handle GET request to fetch the product details
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['ItemID'])) {
    $itemID = filter_var($_GET['ItemID'], FILTER_VALIDATE_INT);

    // Check if ItemID is valid
    if ($itemID === false) {
        error_log("Invalid ItemID provided in the request.");
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "message" => "Invalid product ID"]);
        exit();
    }

    // Prepare and execute the SQL query to fetch the product
    $stmt = $conn->prepare("SELECT * FROM products WHERE ItemID = ?");
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a product was found
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        error_log("Fetched product for ItemID: " . $itemID);
        header("Content-Type: application/json");
        echo json_encode(["success" => true, "product" => $product]);
        exit();
    } else {
        error_log("Product not found for ItemID: " . $itemID);
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit();
    }
}

// Handle POST request to update the product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ItemID'])) {
    try {
        $productId = filter_var($_POST['ItemID'], FILTER_VALIDATE_INT);
        
        if ($productId === false) {
            error_log("Invalid product ID in POST: " . $_POST['ItemID']);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "message" => "Invalid product ID."]);
            exit();
        }

        // Sanitize and validate the rest of the form data
        $productName = trim(htmlspecialchars($_POST['productName'] ?? ''));
        $SKU = trim(htmlspecialchars($_POST['SKU'] ?? ''));
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $Category = trim(htmlspecialchars($_POST['Category'] ?? ''));
        $stockLevel = filter_var($_POST['stockLevel'], FILTER_VALIDATE_INT);
        $restockLevel = filter_var($_POST['restockLevel'], FILTER_VALIDATE_INT);
        $availabilityStatus = $_POST['availabilityStatus'] ?? null;
        $volume = trim(htmlspecialchars($_POST['volume'] ?? ''));
        $expirationDate = $_POST['expirationDate'] ?? null;

        // Validate required fields
        if (empty($productName) || empty($SKU) || $price === false || $stockLevel === false || $restockLevel === false || empty($Category)) {
            error_log("Form validation failed. Input values: Product Name: $productName, SKU: $SKU, Price: $price, Stock Level: $stockLevel");
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "message" => "Please provide valid input values."]);
            exit();
        }

        // Validate Category against expected values
        $validCategories = ['Water', 'Juices', 'Customised Beverages', 'Distillation Equipment', 'Ice'];
        if (!in_array($Category, $validCategories)) {
            error_log("Invalid Category: " .$Category);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "message" => "Invalid Category selected."]);
            exit();
        }

        // Prepare the SQL query to update product details
        $sql = "UPDATE products SET productName = ?, SKU = ?, Price = ?, Category = ?, stockLevel = ?, restockLevel = ?, availabilityStatus = ?, volume = ?, expirationDate = ? WHERE ItemID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssiddssssi", $productName, $SKU, $price, $Category, $stockLevel, $restockLevel, $availabilityStatus, $volume, $expirationDate, $productId);

            if ($stmt->execute()) {
                error_log("Product updated successfully: " . $productId);
                header("Content-Type: application/json");
                echo json_encode(["success" => true, "message" => "Product updated successfully"]);
                exit();
            } else {
                error_log("Execution failed: " . $stmt->error);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "message" => "Failed to update product."]);
                exit();
            }
        } else {
            error_log("Prepare failed: " . $conn->error);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "message" => "Failed to prepare update statement."]);
            exit();
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit();
    }
}

// Close the database connection
$conn->close();
?>
