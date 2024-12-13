<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', 'debug_log.txt'); // Specify the log file path

// Log the request method
error_log('Request method: ' . $_SERVER['REQUEST_METHOD'] . "\n", 3, 'debug_log.txt');

// Include database connection
include('db_connection.php');

// Enable MySQLi error reporting for better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set a default response array
$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and validate the form data
        if (!isset($_POST['order_id'], $_POST['customer_name'], $_POST['customer_email'], $_POST['total_amount'], $_POST['status'])) {
            throw new Exception("Missing required fields.");
        }

        $orderId = intval($_POST['order_id']);
        if ($orderId <= 0) throw new Exception("Invalid order ID.");

        $customerName = trim($_POST['customer_name']);
        if (empty($customerName)) throw new Exception("Customer name is required.");

        $customerEmail = filter_var($_POST['customer_email'], FILTER_VALIDATE_EMAIL);
        if (!$customerEmail) throw new Exception("Invalid email format.");

        $totalAmount = floatval($_POST['total_amount']);
        if ($totalAmount <= 0) throw new Exception("Total amount must be a positive number.");

        $status = $_POST['status'];
        $shipmentDate = $_POST['shipment_date'] ?? null; // Optional
        $promoCode = $_POST['promo_code'] ?? null;       // Optional
        $deliveryAddress = $_POST['delivery_address'] ?? '';
        $city = $_POST['city'] ?? ''; // Ensure city is optional or set to default empty string

        // Added fields for the deliveries table
        $phone = $_POST['phone'] ?? null;
        $streetAddress = $_POST['street_address'] ?? '';
        $suburbAddress = $_POST['suburb_address'] ?? '';
        $province = $_POST['province'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $deliveryOption = $_POST['delivery_option'] ?? '';

        // Assuming order items are passed as a JSON string
        $orderItems = json_decode($_POST['order_items'], true);
        if ($orderItems === null) throw new Exception("Invalid order items format.");

        // Log the received data for debugging
        error_log("\nReceived data: orderID=$orderId, customerName=$customerName, email=$customerEmail, totalAmount=$totalAmount, status=$status, shipmentDate=$shipmentDate, city=$city, phone=$phone, streetAddress=$streetAddress, suburbAddress=$suburbAddress, province=$province, deliveryOption=$deliveryOption, orderItems=" . print_r($orderItems, true) . "\n", 3, 'debug_log.txt');

        // Prepare the SQL update statement for the orders table
        $updateOrderSql = "UPDATE orders SET 
                            total_amount = ?, 
                            status = ?, 
                            shipment_date = ?, 
                            promo_code = ?, 
                            delivery_address = ? 
                          WHERE id = ?";

        if ($updateOrderStmt = $conn->prepare($updateOrderSql)) {
            $updateOrderStmt->bind_param("dssssi", $totalAmount, $status, $shipmentDate, $promoCode, $deliveryAddress, $orderId);

            if ($updateOrderStmt->execute()) {
                error_log("Order ID $orderId updated successfully.\n", 3, 'debug_log.txt');

                // Update the deliveries table with the new delivery fields
                $updateDeliverySql = "UPDATE deliveries SET 
                                        phone = ?, 
                                        street_address = ?, 
                                        suburb_address = ?, 
                                        city = ?, 
                                        province = ?, 
                                        notes = ?, 
                                        delivery_option = ? 
                                      WHERE order_id = ?";

                if ($updateDeliveryStmt = $conn->prepare($updateDeliverySql)) {
                    $updateDeliveryStmt->bind_param("sssssssi", $phone, $streetAddress, $suburbAddress, $city, $province, $notes, $deliveryOption, $orderId);
                    $updateDeliveryStmt->execute();
                    $updateDeliveryStmt->close();
                } else {
                    throw new Exception("Failed to prepare update statement for deliveries.");
                }

                // Update order items if they are provided
                if (!empty($orderItems)) {
                    foreach ($orderItems as $item) {
                        $itemId = intval($item['id']);
                        $quantity = intval($item['quantity']);
                        $price = floatval($item['price']);

                        if ($quantity <= 0 || $price <= 0) {
                            throw new Exception("Invalid quantity or price for order item.");
                        }

                        $updateItemSql = "UPDATE order_items SET 
                                            quantity = ?, 
                                            price = ? 
                                          WHERE id = ? AND order_id = ?";

                        if ($updateItemStmt = $conn->prepare($updateItemSql)) {
                            $updateItemStmt->bind_param("diii", $quantity, $price, $itemId, $orderId);
                            $updateItemStmt->execute();
                            $updateItemStmt->close();
                        }
                    }
                }

                // Update user details based on the email
                $updateUserSql = "UPDATE users SET 
                                    first_name = ?, 
                                    last_name = ?, 
                                    email = ?, 
                                    city = ? 
                                  WHERE email = ?";

                // Split customer name into first and last with validation
                $nameParts = explode(' ', $customerName, 2);
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

                if ($updateUserStmt = $conn->prepare($updateUserSql)) {
                    $updateUserStmt->bind_param("sssss", $firstName, $lastName, $customerEmail, $city, $customerEmail);
                    $updateUserStmt->execute();
                    $updateUserStmt->close();
                }

                $response['success'] = true;
                $response['message'] = 'Order, delivery, and related data updated successfully.';
            } else {
                throw new Exception("Failed to update order.");
            }
        } else {
            throw new Exception("Failed to prepare update statement for orders.");
        }
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage() . "\n", 3, 'debug_log.txt');
    $response['message'] = $e->getMessage();
} finally {
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
}
