<?php
session_start();
include 'blueprint.php';

// Get the customer ID from the URL
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch customer details from the users table
$customer_query = "SELECT id, first_name, last_name, email, phone, street_address, suburb, city, province, postal_code, created_at, last_login, is_active 
                   FROM users WHERE id = $customer_id AND role = 'customer'";
$customer_result = $conn->query($customer_query);
$customer = $customer_result->fetch_assoc();

// Fetch order history for the customer
$order_query = "SELECT o.id AS order_id, o.order_number, o.created_at AS order_date, o.total_amount, o.status, 
                GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') AS products 
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = $customer_id
                GROUP BY o.id";
$order_result = $conn->query($order_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details</title>
    <style>
    /* General Styles */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f7fa;
        line-height: 1.6;
        margin: 0;
        padding: 0;
        color: #333;
    }

    /* Header Section */
    h2 {
        background-color: #0044cc;
        color: white;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    /* Header Layout with Sidebar Offset */
    header {
        padding: 16px 24px;
        margin-left: 240px; /* Increased left margin for sidebar width */
        margin-top: 16px; /* Adds space above the header */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Main Content Section */
    .main-content {
        margin-left: 240px; /* Increased margin to align content after the sidebar */
        margin-top: 16px;    /* Top margin below the header */
        padding-right: 16px; /* Right padding for spacing */
    }

    /* Strong Labels */
    strong {
        color: #555;
    }

    /* Section Styles */
    section {
        background-color: white;
        margin: 20px 20px 20px 40px; /* Add more space on the left */
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        max-width: 1100px; /* Make the section smaller */
        margin-left: auto;
        margin-right: auto;
    }

    section h3 {
        color: #0044cc;
        margin-bottom: 15px;
    }

    /* Paragraphs inside sections */
    section p {
        font-size: 1em;
        margin: 5px 0;
    }

    /* Paragraph Styling */
    p {
        font-size: 16px;
        line-height: 1.5;
        color: #333;
        margin-bottom: 10px;
    }

    /* Strong Text Styling within paragraphs */
    p strong {
        color: #0044cc;
    }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    /* Table Header and Data */
    table th, table td {
        padding: 12px;
        text-align: right;
        border-bottom: 1px solid #ddd;
    }

    table th {
        background-color: #f4f7fa;
        color: #333;
    }

    table td {
        background-color: #fff;
    }

    /* Table Hover Effect */
    table tr:hover {
        background-color: #f1f1f1;
    }

    /* Status Indicator Styles */
    .status {
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
    }

    .status.active {
        background-color: #28a745;
        color: white;
    }

    .status.inactive {
        background-color: #dc3545;
        color: white;
    }

    /* No Data Placeholder */
    .no-data {
        text-align: center;
        color: #999;
    }

    /* Customer Table */
    .customer-table {
        width: 100%;
        margin-top: 16px;
        border-collapse: collapse;
    }

    .customer-table th, .customer-table td {
        text-align: left;  /* Align text to the left for readability */
        padding: 12px 20px;
    }

    .customer-table th {
        font-weight: bold;
    }

    .customer-table td {
        font-size: 14px;
    }

    /* Customer Details Panel */
    .customer-details-panel {
        padding: 24px;
        margin-left: 240px; /* Adds margin if next to the content */
    }

    /* Customer Details Section */
    .customer-details-section {
        margin-top: 16px;
    }

    .customer-details-section h3 {
        margin-bottom: 8px;
        text-align: left; /* Align headings to the left */
    }

    /* Action Button Styles */
    .action-buttons {
        display: flex;
        justify-content: flex-start;  /* Align buttons to the left */
        margin-top: 16px;
    }

    .action-buttons button {
        margin-right: 12px; /* Space out buttons */
    }

    /* Footer Section */
    footer {
        padding: 16px;
        margin-left: 240px; /* Offset from the left sidebar */
        margin-top: 24px; /* Space above the footer */
        background-color: #f1f1f1;
        text-align: center;
    }
/* Container for the Entire Page */
.container {
    max-width: 1100px; /* Make container smaller */
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-left: 400px;  /* Create a lot of space towards the left */
    margin-right: auto;   /* Ensure it aligns to the right */
}


    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0; /* Remove left margin for small screens */
            margin-top: 8px;
        }

        /* Table Layout Adjustment for Small Screens */
        .customer-table {
            display: block; /* Change layout to card view on small screens */
            padding: 8px;
        }

        /* Customer Details Panel Adjustment */
        .customer-details-panel {
            margin-left: 0;
            padding: 16px;
        }

        /* Stacked Action Buttons on Small Screens */
        .action-buttons {
            flex-direction: column;  /* Stack buttons vertically on mobile */
            align-items: flex-start;
        }
    }
</style>


    </style>
</head>
<body>
<div class="container">
    <h2>Customer Details</h2>

    <!-- Customer Information Section -->
    <section>
        <h3>Customer Information</h3>
        <?php if ($customer): ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
            <p><strong>Account Created:</strong> <?php echo htmlspecialchars($customer['created_at']); ?></p>
            <p><strong>Last Login:</strong> <?php echo htmlspecialchars($customer['last_login']); ?></p>
            <p><strong>Status:</strong> 
                <span class="status <?php echo $customer['is_active'] ? 'active' : 'inactive'; ?>">
                    <?php echo $customer['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
            </p>
        <?php else: ?>
            <p class="no-data">Customer not found.</p>
        <?php endif; ?>
    </section>

    <!-- Address Information Section -->
    <section>
        <h3>Address Information</h3>
        <?php if ($customer): ?>
            <p><strong>Street Address:</strong> <?php echo htmlspecialchars($customer['street_address']); ?></p>
            <p><strong>Suburb:</strong> <?php echo htmlspecialchars($customer['suburb']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($customer['city']); ?></p>
            <p><strong>Province:</strong> <?php echo htmlspecialchars($customer['province']); ?></p>
            <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($customer['postal_code']); ?></p>
        <?php else: ?>
            <p class="no-data">Address information not found.</p>
        <?php endif; ?>
    </section>

    <!-- Order History Section -->
    <section>
        <h3>Order History</h3>
        <?php if ($order_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Products</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $order_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($order['total_amount']); ?></td>
                            <td>
                                <span class="status <?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['products']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No orders found for this customer.</p>
        <?php endif; ?>
    </section>
</div>

</body>
</html>
