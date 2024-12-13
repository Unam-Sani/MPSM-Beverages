<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include('db_connection.php');

if (isset($_GET['orderID'])) {
    $orderID = intval($_GET['orderID']); // Ensure it’s an integer to prevent SQL injection
    error_log("Received orderID: $orderID\n", 3, 'debug_log.txt');

    // Query to get the order details by ID
    $sql = "SELECT id, user_id, order_number, total_amount, status, shipment_date, delivery_address 
            FROM orders 
            WHERE id = $orderID";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            error_log("Fetched Order with user_id: " . $row['user_id'] . "\n", 3, 'debug_log.txt');

            $userID = $row['user_id'];
            // Check if the user exists in the users table
            $userQuery = "SELECT first_name, last_name, email FROM users WHERE id = $userID";
            $userResult = $conn->query($userQuery);

            $customerName = 'Unknown';
            $email = 'N/A';

            if ($userResult && $userResult->num_rows > 0) {
                $userRow = $userResult->fetch_assoc();
                $customerName = $userRow['first_name'] . ' ' . $userRow['last_name'];
                $email = $userRow['email'];
                error_log("Fetched customer: $customerName, Email: $email\n", 3, 'debug_log.txt');
            } else {
                error_log("User ID not found: " . $userID . "\n", 3, 'debug_log.txt');
            }

            // Return order details
            $response = [
                'id' => $row['id'],
                'customerName' => $customerName,
                'email' => $email,
                'totalAmount' => $row['total_amount'],
                'status' => $row['status'],
                'shipmentDate' => $row['shipment_date']
            ];
            error_log("Returning order details: " . json_encode($response) . "\n", 3, 'debug_log.txt');
            echo json_encode($response);
        } else {
            error_log("Order ID not found: " . $orderID . "\n", 3, 'debug_log.txt');
            echo json_encode(['error' => 'Order not found']);
        }
    } else {
        error_log("SQL Error: " . $conn->error . "\n", 3, 'debug_log.txt');
        echo json_encode(['error' => 'SQL error']);
    }
} else {
    // Log missing orderID parameter
    error_log("Missing orderID parameter\n", 3, 'debug_log.txt');
    echo json_encode(['error' => 'Missing orderID parameter']);
}

$conn->close(); // Close the connection
