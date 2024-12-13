<?php 
// Database connection
include 'db_connection.php';

// Enable error reporting for debugging (configure as needed)
error_reporting(E_ALL);
ini_set('display_errors', 1); // Use 0 for production

// Debugging function to log messages
function debugLog($message) {
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['item_id'])) {
        $itemId = filter_var($_GET['item_id'], FILTER_SANITIZE_NUMBER_INT);
        debugLog("Fetching product data for Item ID: $itemId");

        // Fetch product from database
        $query = "SELECT * FROM products WHERE ItemID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            debugLog("Statement preparation failed: " . $conn->error);
            echo json_encode(['error' => 'Database error']);
            exit;
        }

        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            debugLog("Product data fetched successfully: " . json_encode($product));
            echo json_encode(['status' => 'success', 'data' => $product]);
        } else {
            debugLog("No product found with Item ID: $itemId");
            echo json_encode(['status' => 'error', 'message' => 'No product found']);
        }

        $stmt->close();
    } else {
        debugLog("Item ID not set.");
        echo json_encode(['status' => 'error', 'message' => 'Item ID not set']);
    }
} else {
    debugLog("Invalid request method.");
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
