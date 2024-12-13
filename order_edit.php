<?php   
include 'db_connection.php'; // Database connection file

// Get the order ID from the URL
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $order_id = $_POST['order_id'];
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $total_amount = floatval($_POST['total_amount']);
    $status = trim($_POST['status']);
    $shipment_date = !empty($_POST['shipment_date']) ? $_POST['shipment_date'] : null;
    $order_type = trim($_POST['order_type']);
    $delivery_address = trim($_POST['delivery_address']);

    // Split customer name into first and last name
    list($first_name, $last_name) = explode(' ', $customer_name, 2);

    // Update the user details in the users table
    $update_user_query = "UPDATE users SET first_name = ?, last_name = ? WHERE id = (SELECT user_id FROM orders WHERE id = ?)";
    $stmt_user = $conn->prepare($update_user_query);
    $stmt_user->bind_param("ssi", $first_name, $last_name, $order_id);

    if ($stmt_user->execute()) {
        // Update the order in the orders table
        $update_query = "UPDATE orders SET 
                            customer_name = ?, 
                            customer_email = ?, 
                            total_amount = ?, 
                            status = ?, 
                            shipment_date = ?, 
                            order_type = ?, 
                            delivery_address = ? 
                        WHERE id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssdssssi", $customer_name, $customer_email, $total_amount, $status, $shipment_date, $order_type, $delivery_address, $order_id);

        if ($stmt->execute()) {
            echo "<script>Swal.fire('Success', 'Order and customer updated successfully!', 'success');</script>";
        } else {
            echo "<script>Swal.fire('Error', 'Error updating order: " . $stmt->error . "', 'error');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>Swal.fire('Error', 'Error updating user: " . $stmt_user->error . "', 'error');</script>";
    }
    $stmt_user->close();
}

// Fetch the order and user details to display in the form
$query = "SELECT o.*, u.first_name, u.last_name, u.email AS user_email FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Order not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome Icons -->

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.js"></script>

</head>
<body>

<style>

/* Basic reset */
* {
    margin: 0;
    
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.form-container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
    margin-top: 160px;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #2c3e50;
}

h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #34495e;
}

.form-section {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
}

input:focus, select:focus, textarea:focus {
    border-color: #3498db;
    outline: none;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #2980b9;
}

textarea {
    resize: vertical;
    height: 100px;
}

i {
    margin-right: 8px;
    color: #3498db;
}


</style>



<div class="form-container">
    <h2>Edit Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>

    <form method="post">
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">

        <section class="form-section">
            <h3>Customer Information</h3>
            <label for="customer_name"><i class="fas fa-user"></i> Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>" required>

            <label for="customer_email"><i class="fas fa-envelope"></i> Customer Email:</label>
            <input type="email" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($order['user_email']); ?>" required>
        </section>

        <section class="form-section">
            <h3>Order Details</h3>
            <label for="total_amount"><i class="fas fa-dollar-sign"></i> Total Amount:</label>
            <input type="number" step="0.01" id="total_amount" name="total_amount" value="<?php echo htmlspecialchars($order['total_amount']); ?>" required>

            <label for="status"><i class="fas fa-clipboard-list"></i> Status:</label>
            <select id="status" name="status">
                <?php
                $statuses = ['Confirmed', 'Processing', 'Out for delivery', 'Shipped', 'Ready for collection', 'Delivered', 'Collected', 'Order Problem', 'Delay'];
                foreach ($statuses as $status) {
                    echo "<option value=\"$status\"";
                    if ($order['status'] === $status) echo " selected";
                    echo ">$status</option>";
                }
                ?>
            </select>
            <label for="shipment_date"><i class="fas fa-calendar-alt"></i> Shipment Date:</label>
<input type="datetime-local" id="shipment_date" name="shipment_date" 
       value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($order['shipment_date']))); ?>"
       min="<?php echo date('Y-m-d\TH:i'); ?>">

            <script>
    document.addEventListener('DOMContentLoaded', function() {
        const shipmentDateInput = document.getElementById('shipment_date');
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        shipmentDateInput.min = `${year}-${month}-${day}T${hours}:${minutes}`;
    });
</script>

        </section>

        <section class="form-section">
            <h3>Delivery Information</h3>
            <label for="order_type"><i class="fas fa-truck"></i> Order Type:</label>
            <select id="order_type" name="order_type">
                <option value="Collect" <?php if ($order['order_type'] === 'Collect') echo 'selected'; ?>>Collect</option>
                <option value="Delivery" <?php if ($order['order_type'] === 'Delivery') echo 'selected'; ?>>Delivery</option>
            </select>

            <label for="delivery_address"><i class="fas fa-map-marker-alt"></i> Delivery Address:</label>
            <textarea id="delivery_address" name="delivery_address" rows="3"><?php echo htmlspecialchars($order['delivery_address']); ?></textarea>
        </section>

        <button type="submit">Update Order</button>
    </form>
</div>

</body>
</html>
