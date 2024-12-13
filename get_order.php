<?php  
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include('db_connection.php');

// Set the content type to JSON
header('Content-Type: application/json');

// Function to log debug messages
function logDebug($message) {
    $logFile = 'debug_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Function to fetch order details and items
function getOrderDetails($orderID, $conn) {
    $sql = "SELECT o.id, o.order_number, o.total_amount, o.status, o.shipment_date, o.order_type, 
    u.first_name AS customer_first_name, u.last_name AS customer_last_name, 
    u.email AS customer_email, u.phone AS customer_phone, 
    d.phone AS delivery_phone, 
    oi.sku, oi.product_name, oi.quantity, oi.price, oi.total_price, oi.image_url
FROM orders o
JOIN users u ON o.user_id = u.id
LEFT JOIN deliveries d ON o.id = d.order_id
JOIN order_items oi ON o.id = oi.order_id
WHERE o.id = ?";


    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $orderID);

        // Execute the query and fetch the result
        if ($stmt->execute()) {
            return $stmt->get_result();
        } else {
            logDebug("Failed to execute order query for Order ID $orderID.");
            return false;
        }
    } else {
        logDebug("Failed to prepare order query for Order ID $orderID.");
        return false;
    }
}

// Verify the orderID is set and valid
if (!isset($_GET['orderID']) || !is_numeric($_GET['orderID'])) {
    $error = 'Invalid or missing orderID parameter';
    logDebug("Error: $error");
    echo json_encode(['error' => $error]);
    exit;
}

$orderID = intval($_GET['orderID']);
logDebug("Valid Order ID received: $orderID");

// Fetch order and order items data
$result = getOrderDetails($orderID, $conn);

if ($result && $result->num_rows > 0) {
    $orderData = [];
    $orderItems = [];

    // Fetch data and separate order from items
    while ($row = $result->fetch_assoc()) {
        // Initialize order data on the first item
        if (empty($orderData)) {
            $orderData = [
                'id' => $row['id'],
                'order_number' => $row['order_number'],
                'total_amount' => $row['total_amount'],
                'status' => $row['status'],
                'shipment_date' => $row['shipment_date'],
                'order_type' => $row['order_type'],
                'customer_name' => $row['customer_first_name'] . ' ' . $row['customer_last_name'],
                'customer_email' => $row['customer_email'],
                'items' => []  // Initialize empty array for items
            ];
        }

        // Add each item to the order items array
        $orderItems[] = [
            'sku' => $row['sku'],
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'price' => $row['price'],
            'total_price' => $row['total_price'],
            'image_url' => $row['image_url']  // Include image URL here
        ];
    }

    // Append items to order data
    $orderData['items'] = $orderItems;

    // Check if delivery details are required based on order type
    if ($orderData['order_type'] === 'Delivery') {
        $deliverySql = "SELECT phone, street_address, suburb_address, city, province, notes, delivery_option 
                         FROM deliveries 
                         WHERE order_id = ?";
        if ($deliveryStmt = $conn->prepare($deliverySql)) {
            $deliveryStmt->bind_param("i", $orderID);

            if ($deliveryStmt->execute()) {
                $deliveryResult = $deliveryStmt->get_result();
                if ($deliveryResult->num_rows > 0) {
                    $orderData['delivery'] = $deliveryResult->fetch_assoc();
                } else {
                    logDebug("No delivery details found for Order ID $orderID.");
                    $orderData['delivery'] = ['message' => 'No delivery details found.'];
                }
            } else {
                logDebug("Error executing delivery query for Order ID $orderID.");
                $orderData['delivery'] = ['message' => 'Error fetching delivery details.'];
            }
            $deliveryStmt->close();
        }
    } else {
        // Set message for Collection order type
        $orderData['delivery'] = ['message' => 'This order is for collection. No delivery details available.'];
    }

    // Log and return the final order data as JSON
    logDebug("Fetched order details for Order ID $orderID: " . json_encode($orderData));
    echo json_encode($orderData);

} else {
    $error = 'Order not found or query failed';
    logDebug("Error for Order ID $orderID: $error");
    echo json_encode(['error' => $error]);
}

// Close the database connection
$conn->close();
?>
