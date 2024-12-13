<?php
session_start();
include 'blueprint.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch order details
$order_query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch order items
$order_items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($order_items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>

<style>
/* General Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 50px;
    padding: 0;
}

.container {
    margin-left: 250px; /* Space for left navigation */
    padding-top: 20px; /* Space for top navigation */
    padding-right: 50px; /* Additional padding for aesthetics */
    max-width: 900px;
}

/* Header Styling */
h2, h3 {
    color: #333;
    border-bottom: 2px solid #ddd;
    padding-bottom: 8px;
    margin-bottom: 16px;
}

/* Section Styling */
section {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 20px;
}

/* Labels and Text */
p {
    font-size: 16px;
    color: #666;
}

strong {
    color: #333;
}

/* Buttons */
.action-button {
    background-color: #5a67d8;
    color: #fff;
    padding: 10px 20px;
    margin: 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    font-size: 14px;
}

.action-button i {
    margin-right: 5px;
}

.action-button:hover {
    background-color: #434190;
}

/* Order Details Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #5a67d8;
    color: #fff;
    font-weight: bold;
}

td {
    color: #333;
}

.table-container {
    overflow-x: auto; /* For responsive design */
}

/* Info Box Styling */
.info-box {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.info-box .info-item {
    flex: 1 1 45%;
    background-color: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 15px;
}

.info-box .info-item h4 {
    font-size: 16px;
    color: #5a67d8;
    margin-bottom: 10px;
}

.info-box .info-item p {
    font-size: 14px;
    color: #666;
}

/* Align content to the right */
.container {
    margin-left: 250px; /* Adjust for left navigation */
    padding: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        margin-left: 0;
        padding: 10px;
    }

    .info-box .info-item {
        flex: 1 1 100%;
    }

    .action-button {
        font-size: 12px;
        padding: 8px 16px;
    }
}

</style>

<div class="container">
    <h2>Order Details</h2>

    <!-- Order Information Section -->
    <section class="order-info">
        <h3>Order Information</h3>
        <?php if ($order): ?>
            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
            <p><strong>Total Amount:</strong> R<?php echo htmlspecialchars($order['total_amount']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <p><strong>Shipment Date:</strong> <?php echo htmlspecialchars($order['shipment_date']); ?></p>
            <p><strong>Order Type:</strong> <?php echo htmlspecialchars($order['order_type']); ?></p>
            <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
        <?php else: ?>
            <p>Order not found.</p>
        <?php endif; ?>
    </section>

    <!-- Order Items Section -->
    <section class="order-items">
        <h3>Order Items</h3>
        <?php if ($order_items): ?>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['sku']); ?></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>R<?php echo htmlspecialchars($item['price']); ?></td>
                            <td>R<?php echo htmlspecialchars($item['total_price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items found for this order.</p>
        <?php endif; ?>
    </section>

    <!-- Actions Section -->
    <section class="actions">
        <h3>Actions</h3>
        <a href="order_edit.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="action-button">
            <i class="fas fa-edit"></i> Edit Order
        </a>
        <a href="delete_order.php?id=<?php echo htmlspecialchars($order['id']); ?>" onclick="return confirm('Are you sure you want to delete this order?');" class="action-button delete-btn">
            <i class="fas fa-trash"></i> Delete Order
        </a>
    </section>

</div>

</body>
</html>
