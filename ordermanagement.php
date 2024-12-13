<?php
// Include blueprint.php for top and side navigation styles
include('blueprint.php');

// Check if we're fetching order details
if (isset($_GET['viewOrder']) && isset($_GET['orderID'])) {
    $orderID = $_GET['orderID'];

    // Fetch order details
    $orderSql = "SELECT orders.id, orders.order_number, orders.total_amount, orders.status, 
                        orders.shipment_date, orders.promo_code, orders.delivery_address, 
                        users.first_name as customer_name, users.email as customer_email, users.city 
                 FROM orders 
                 JOIN users ON orders.user_id = users.id 
                 WHERE orders.id = ?";
                 
    $stmt = $conn->prepare($orderSql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    $orderDetails = $orderResult->fetch_assoc();

    // Fetch order items
    $itemsSql = "SELECT sku, product_name, quantity, price, total_price 
                 FROM order_items 
                 WHERE order_id = ?";
    $itemStmt = $conn->prepare($itemsSql);
    $itemStmt->bind_param("i", $orderID);
    $itemStmt->execute();
    $itemsResult = $itemStmt->get_result();

    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }

    // Prepare response
    $response = [
        'error' => null,
        'order' => $orderDetails,
        'items' => $items
    ];

    // Set header to JSON and return response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Stop further execution
}

// Fetch all orders for display
$sql = "SELECT orders.id, orders.order_number, orders.created_at, orders.total_amount, orders.status, orders.shipment_date, 
        orders.delivery_address, orders.order_type, users.first_name, users.last_name, users.email, users.city 
        FROM orders 
        JOIN users ON orders.user_id = users.id 
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- SweetAlert for notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS (including popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <title>Orders Management</title>
    <style>
/* Page Title Styling */
h1 {
    text-align: center;
    color: #2980b9;
    margin-top: 80px; /* Adjusted for fixed nav */
    animation: fadeIn 1s;
}
h2 {
    text-align: center;
    color: #2980b9;
    margin-top: 80px; /* Adjusted for fixed nav */
    animation: fadeIn 1s;
    font-size: 20px;
}

/* Container for the table */
.table-container {
    max-width: 80%; /* Set a smaller maximum width */
    margin: 40px auto; /* Center and provide margin */
    padding: 20px; /* White space around table */
    padding: 20px; /* White space around table */
    background-color: rgba(255, 255, 255, 0.8); /* Light background for contrast */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    margin-right: 50px;
    margin-top: 150px;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    animation: fadeIn 1s;
}

/* Table headers (th) */
th, td {
    padding: 10px; /* Reduced padding */
    text-align: left;
    border-bottom: 1px solid grey; /* Grey border for rows */
}

/* Header styles */
th {
    background-color: orange; /* Orange background for headers */
    color: white;
}

/* Hover effect for table rows */
tr:hover {
    background-color: rgba(255, 165, 0, 0.1); /* Hint of orange on hover */
    transition: background-color 0.3s ease;
}

/* Product image in table */
.product-image {
    width: 50px; /* Smaller image size */
    height: auto;
    border-radius: 5px;     
}

/* Action buttons styling */
.action-button {
    padding: 5px 10px;
    border: 1px solid orange;
    background-color: white;
    color: orange;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Action buttons hover effect */
.action-button:hover {
    background-color: orange;
    color: white;
}

/* Fade-in Animation */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Responsive Styles */
@media (max-width: 1024px) {
    .table-container { max-width: 95%; }
    table { font-size: 13px; }
    .action-button { padding: 3px 7px; }
}

@media (max-width: 768px) {
    .table-container { max-width: 100%; padding: 10px; }
    th, td { padding: 8px; font-size: 12px; }
    .product-image { width: 40px; }
    .action-button { padding: 2px 5px; }
}

@media (max-width: 480px) {
    th, td { font-size: 11px; padding: 6px; }
    .action-button { font-size: 10px; padding: 1px 3px; }
}




        /*-----------------Edit Modal Form--------------*/ 
        /* Modal background */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: white;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 600px; /* Maximum width */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
}

/* Close button */
.close-button {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-button:hover,
.close-button:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Submit button */
.submit-btn {
    background-color: #2980b9;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.submit-btn:hover {
    background-color: #1a669b;
}

/*-----------------------Edit Modal--------------------------*/
/* General Modal Styling */
#editModal {
    position: fixed; /* Stay in place */
    z-index: 1000; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.7); /* Black w/ opacity */
    display: none; /* Hidden by default */
}

/* Modal Content */
.modal-content {
    background-color: #fff;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 600px; /* Maximum width */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
}

/* Close Button */
.close-button {
    color: #aaa; /* Gray */
    float: right; /* Align to the right */
    font-size: 28px; /* Large size */
    font-weight: bold; /* Bold */
    cursor: pointer; /* Pointer on hover */
}

.close-button:hover,
.close-button:focus {
    color: #000; /* Change color on hover */
    text-decoration: none; /* Remove underline */
    cursor: pointer; /* Pointer on hover */
}

/* Form Elements */
form {
    display: flex; /* Use flexbox for layout */
    flex-direction: column; /* Arrange elements vertically */
}

label {
    margin-bottom: 5px; /* Space below labels */
    font-weight: bold; /* Bold labels */
}

input[type="text"],
input[type="email"],
input[type="number"],
input[type="date"],
select {
    padding: 10px; /* Padding for inputs */
    margin-bottom: 15px; /* Space below inputs */
    border: 1px solid #ccc; /* Border color */
    border-radius: 5px; /* Rounded corners */
    font-size: 16px; /* Font size */
}

/* Submit Button */
.submit-btn {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
    border: none; /* No border */
    padding: 10px 20px; /* Padding */
    text-align: center; /* Center text */
    text-decoration: none; /* Remove underline */
    display: inline-block; /* Inline block */
    font-size: 16px; /* Font size */
    margin: 4px 2px; /* Margins */
    cursor: pointer; /* Pointer on hover */
    border-radius: 5px; /* Rounded corners */
}

.submit-btn:hover {
    background-color: #45a049; /* Darker green on hover */
}

/*----------------------Styling For View Form ---------------------*/
/* Modal Background */
#viewOrderModal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1000; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
}

/* Modal Content */
#viewOrderModal form {
    background-color: #fff;
    margin: 10% auto; /* Center vertically */
    padding: 20px;
    border: 1px solid #888;
    border-radius: 5px;
    width: 90%; /* Slightly more flexible */
    max-width: 600px; /* Limit the width */
    max-height: 80vh; /* Limit height to 80% of viewport */
    overflow-y: auto; /* Enable vertical scroll if content exceeds max-height */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Input Fields */
#viewOrderModal input[type="text"] {
    width: calc(100% - 22px); /* Full width with padding */
    padding: 10px;
    margin: 8px 0; /* Space between inputs */
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s, box-shadow 0.3s; /* Smooth transition */
}

/* Input Focus Style */
#viewOrderModal input[type="text"]:focus {
    border-color: #4CAF50; /* Highlight on focus */
    outline: none; /* Remove default outline */
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5); /* Optional shadow */
}

/* Button Styles */
#viewOrderModal button {
    background-color: #4CAF50; /* Green */
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s; /* Smooth transition */
}

#viewOrderModal button:hover {
    background-color: #45a049; /* Darker green on hover */
}

/* Header Style */
#viewOrderModal h2 {
    margin: 0 0 15px; /* Space below the header */
    font-size: 24px;
}

/* Order Items Container */
#orderItemsContainer {
    margin-top: 15px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f9f9f9;
}

/* Individual Order Item Style */
#orderItemsContainer div {
    padding: 5px;
    border-bottom: 1px solid #ccc;
}

#orderItemsContainer div:last-child {
    border-bottom: none; /* Remove last border */
}

/* Custom Scrollbar */
#viewOrderModal::-webkit-scrollbar {
    width: 8px;
}

#viewOrderModal::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}

/* Custom Scrollbar Hover */
#viewOrderModal::-webkit-scrollbar-thumb:hover {
    background: #aaa; /* Darker on hover */
}

/*-------------------------- Filtering section----------------------------*/
.filter-section {
    display: flex;
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
    gap: 10px; /* Space between filters */
    margin-top: 80px;
    margin-left: 350px;
    margin-bottom: 10px; /* Space below the filter section */
    text-align: center;

}

.filter-section input,
.filter-section select {
    padding: 8px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 150px; /* Fixed width for inputs */
}

.filter-section input::placeholder {
    color: #888; /* Light gray placeholder */
}

    </style>

</head>

<body>

    <!-- Page title -->
    <h1>Orders Management</h1>

<h2>Filter Section</h2>

<!-- Filter Section -->
<div class="filter-section">
    <input type="text" id="filterOrderNumber" placeholder="Order Number" oninput="filterTable()">
    <input type="text" id="filterCustomerName" placeholder="Customer Name" oninput="filterTable()">
    <input type="text" id="filterEmail" placeholder="Email" oninput="filterTable()">
    <input type="text" id="filterOrderDate" placeholder="Order Date" oninput="filterTable()">
    <input type="text" id="filterTotalAmount" placeholder="Total Amount" oninput="filterTable()">
    <select id="filterStatus" onchange="filterTable()">
        <option value="">Status</option>
        <option value="Confirmed">Confirmed</option>
        <option value="Processing">Processing</option>
        <option value="Out for delivery">Out for delivery</option>
        <option value="Shipped">Shipped</option>
        <option value="Ready for collection">Ready for collection</option>
        <option value="Delivered">Delivered</option>
        <option value="Collected">Collected</option>
        <option value="Order Problem">Order Problem</option>
        <option value="Delay">Delay</option>
    </select>
    <select id="filterOrderType" onchange="filterTable()">
        <option value="">Order Type</option>
        <option value="Collect">Collect</option>
        <option value="Delivery">Delivery</option>
    </select>
    <input type="text" id="filterDeliveryAddress" placeholder="Delivery Address" oninput="filterTable()">
</div>
    

<!-- Table container -->
<div class="table-container">
    <!-- Orders table -->
    <table class="order-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Number</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Order Date</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Shipment Date</th>
                <th>Order Type</th>
                <th>Delivery Address</th>
                <th>Actions</th> <!-- Action buttons for View, Edit, Delete, and Info -->
            </tr>
        </thead>
        <tbody>
    <?php
    // Check if there are any orders available
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['order_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>R" . htmlspecialchars($row['total_amount']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['shipment_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['order_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['delivery_address']) . "</td>";
            echo "<td>
                <button class='action-button viewOrderBtn' data-id='" . htmlspecialchars($row['id']) . "'>
                    <i class='fas fa-eye'></i> View
                </button>
                <button class='action-button edit-btn' data-id='" . htmlspecialchars($row['id']) . "'>
                    <i class='fas fa-edit'></i> Edit
                </button>
                <button class='action-button delete-btn' data-id='" . htmlspecialchars($row['id']) . "'>
                    <i class='fas fa-trash'></i> Delete
                </button>
                <a href='order_details.php?id=" . htmlspecialchars($row['id']) . "' class='action-button info-btn'>
                    <i class='fas fa-info-circle'></i> Info
                </a>
            </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='11'>No orders found</td></tr>";
    }
    ?>
</tbody>

    </table>
</div>

<!-- ----------------------View Form----------------------------->
 <style>
    .order-item {
    border: 1px solid #ccc;
    margin: 5px 0;
    padding: 10px;
    border-radius: 4px;
    background-color: #f9f9f9;
}

 </style>
 <!-- --------------View Form --------------- -->

 <!-- View Order Modal -->
<div id="viewOrderModal" class="modal" style="display:none;" tabindex="-1" aria-labelledby="viewOrderHeading" role="dialog">
    <form id="viewOrderForm" aria-describedby="viewOrderDescription">
        <h2 id="viewOrderHeading">Order Details</h2>
        <p id="viewOrderDescription" style="display:none;">Displays details for the selected order.</p>
        
        <!-- Order Items Section -->
        <fieldset>
            <legend>Order Items</legend>
            <div id="viewOrderItemsContainer" aria-live="polite"></div>
        </fieldset>

        <!-- Order Details Section -->
        <fieldset>
            <legend>Order Details</legend>
            <div>
                <label for="viewOrderID">Order ID:</label>
                <input type="text" id="viewOrderID" readonly aria-label="Order ID">
            </div>
            <div>
                <label for="viewOrderNumber">Order Number:</label>
                <input type="text" id="viewOrderNumber" readonly aria-label="Order Number">
            </div>
            <div>
                <label for="viewTotalAmount">Total Amount:</label>
                <input type="text" id="viewTotalAmount" readonly aria-label="Total Amount">
            </div>
            <div>
                <label for="viewStatus">Status:</label>
                <input type="text" id="viewStatus" readonly aria-label="Status">
            </div>
            <div>
                <label for="viewShipmentDate">Shipment Date:</label>
                <input type="text" id="viewShipmentDate" readonly aria-label="Shipment Date">
            </div>
            <div>
                <label for="viewPromoCode">Promo Code:</label>
                <input type="text" id="viewPromoCode" readonly aria-label="Promo Code">
            </div>
        </fieldset>

        <!-- Customer Information Section -->
        <fieldset>
            <legend>Customer Information</legend>
            <div>
                <label for="viewCustomerName">Customer Name:</label>
                <input type="text" id="viewCustomerName" readonly aria-label="Customer Name">
            </div>
            <div>
                <label for="viewCustomerEmail">Customer Email:</label>
                <input type="email" id="viewCustomerEmail" readonly aria-label="Customer Email">
            </div>
            <div>
                <label for="viewCity">City:</label>
                <input type="text" id="viewCity" readonly aria-label="City">
            </div>
        </fieldset>

        <!-- Delivery Information Section -->
        <fieldset id="deliveryInformationFieldset" style="display: none;">
            <legend>Delivery Information</legend>
            <div>
                <label for="viewDeliveryAddress">Delivery Address:</label>
                <input type="text" id="viewDeliveryAddress" readonly aria-label="Delivery Address">
            </div>
            <div>
                <label for="viewDeliveryPhone">Phone:</label>
                <input type="text" id="viewDeliveryPhone" readonly aria-label="Delivery Phone">
            </div>
            <div>
                <label for="viewDeliveryNotes">Notes:</label>
                <input type="text" id="viewDeliveryNotes" readonly aria-label="Delivery Notes">
            </div>
            <div>
                <label for="viewDeliveryOption">Delivery Option:</label>
                <input type="text" id="viewDeliveryOption" readonly aria-label="Delivery Option">
            </div>
        </fieldset>

        <!-- Collection Message Section -->
        <fieldset id="collectionMessageFieldset" style="display: none;">
            <legend>Collection Information</legend>
            <p>This order is for collection. No delivery details are available.</p>
        </fieldset>

        <button type="button" onclick="closeViewModal()">Close</button>
    </form>
</div>

<!-- Edit Order Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-button" onclick="closeEditModal()">&times;</span>
        <h2>Edit Order</h2>
        <form id="editForm" aria-labelledby="editModal">
            <!-- Hidden field for order ID -->
            <input type="hidden" name="order_id" id="editOrderID">

            <!-- Customer Details Section -->
            <fieldset>
                <legend>Customer Details</legend>
                <div>
                    <label for="editCustomerName">Customer Name:</label>
                    <input type="text" name="customer_name" id="editCustomerName" required>
                </div>
                <div>
                    <label for="editEmail">Email:</label>
                    <input type="email" name="customer_email" id="editEmail" required>
                </div>
                <div>
                    <label for="editPhone">Phone Number:</label>
                    <input type="tel" name="phone" id="editPhone" required placeholder="Enter phone number" pattern="\d{10}">
                    <small>Phone number should be 10 digits</small>
                </div>
            </fieldset>

            <!-- Order Details Section -->
            <fieldset>
                <legend>Order Details</legend>
                <div>
                    <label for="editTotalAmount">Total Amount:</label>
                    <input type="number" step="0.01" name="total_amount" id="editTotalAmount" required>
                </div>
                <div>
                    <label for="editStatus">Status:</label>
                    <select name="status" id="editStatus" required>
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Out for delivery">Out for delivery</option>
                        <option value="Ready for collection">Ready for collection</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Order Problem">Problem</option>
                        <option value="Delay">Delay</option>
                    </select>
                </div>
                <div> 
                    <label for="editShipmentDate">Shipment Date:</label>
                    <input type="date" name="shipment_date" id="editShipmentDate" required>
                </div>
                <div>
                    <label for="editPromoCode">Promo Code:</label>
                    <input type="text" name="promo_code" id="editPromoCode">
                </div>
            </fieldset>

            <!-- Delivery Address Section -->
            <fieldset>
                <legend>Delivery Address</legend>
                <div>
                    <label for="editDeliveryAddress">Delivery Address:</label>
                    <input type="text" name="delivery_address" id="editDeliveryAddress" required>
                </div>
                <div>
    <label for="editDeliveryPhone">Delivery Phone:</label>
    <input type="tel" name="delivery_phone" id="editDeliveryPhone" placeholder="Enter delivery phone number">
</div>

                <div>
                    <label for="city">City:</label>
                    <input type="text" name="city" id="city">
                </div>
            </fieldset>

            <!-- Order Items Section -->
            <h3>Order Items</h3>
            <div id="editOrderItemsContainer">
                <!-- Dynamic order items will appear here -->
            </div>

            <!-- Submit Button -->
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>


</script>


            <div>
                <label for="editPromoCode">Promo Code:</label>
                <input type="text" name="promo_code" id="editPromoCode">
            </div>

            <div>
                <label for="editDeliveryAddress">Delivery Address:</label>
                <textarea name="delivery_address" id="editDeliveryAddress" required></textarea>
            </div>

            <!-- Add the City field here -->
            <label for="city">City:</label>
                <select id="city" name="city" >
                    <option value="">Select a city</option>
                    <option value="Johannesburg">Johannesburg</option>
                    <option value="Pretoria">Pretoria</option>
                    <option value="Ekurhuleni">Ekurhuleni</option>
                    <option value="Tshwane">Tshwane</option>
                    <option value="Midrand">Midrand</option>
                    <option value="Soweto">Soweto</option>
                    <option value="Centurion">Centurion</option>
                    <option value="Benoni">Benoni</option>
                    <option value="Brakpan">Brakpan</option>
                    <option value="Randburg">Randburg</option>
                    <option value="Roodepoort">Roodepoort</option>
                    <option value="Roodepoort">other</option>
                    <!-- Add more cities as needed -->
                </select>

            <h3>Order Items:</h3>
            <div id="editOrderItemsContainer">
                <!-- Dynamic order items will be inserted here -->
            </div>

            <button type="submit" class="submit-btn">Save Changes</button>
        </form>
    </div>
</div>

    </body>



<!-- ------------------JavaScript Section ---------------------->

<script>

//--------------Edit Form----------------------------- 

// Function to set min attribute to today's date
window.onload = function() {
    const today = new Date();
    const minDate = today.toISOString().split('T')[0]; // Get YYYY-MM-DD format
    document.getElementById('editShipmentDate').setAttribute('min', minDate);
};
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editForm').reset();
}


// Form submission handler for editing an order
document.getElementById('editForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission

    // Get form data
    const orderID = document.getElementById('editOrderID').value;
    const customerName = document.getElementById('editCustomerName').value;
    const customerEmail = document.getElementById('editEmail').value;
    const phone = document.getElementById('editPhone').value; // Get the updated phone number
    const totalAmount = document.getElementById('editTotalAmount').value;
    const status = document.getElementById('editStatus').value;
    const shipmentDate = document.getElementById('editShipmentDate').value;
    const promoCode = document.getElementById('editPromoCode').value || '';
    const deliveryAddress = document.getElementById('editDeliveryAddress').value || '';
    const city = document.getElementById('city').value || ''; // Ensure the correct ID

    // Basic form validation
    if (!orderID || !customerName || !customerEmail || !shipmentDate || !totalAmount) {
        Swal.fire('Error', 'Please fill out all required fields.', 'error');
        return;
    }

    // Prepare form data using URLSearchParams
    const params = new URLSearchParams({
    order_id: orderID,
    customer_name: customerName,
    customer_email: customerEmail,
    phone: phone, // Add phone number
    total_amount: totalAmount,
    status: status,
    shipment_date: shipmentDate,
    promo_code: promoCode,
    delivery_address: deliveryAddress,
    city: city,
    delivery_phone: document.getElementById('editDeliveryPhone') ? document.getElementById('editDeliveryPhone').value : '' // Add delivery phone
});

    // Gather order items data
    const orderItems = [];
    document.querySelectorAll('#editOrderItemsContainer > div').forEach(inputDiv => {
        const quantity = inputDiv.querySelector('input[name^="order_items["][type="number"]').value;
        const itemId = inputDiv.querySelector('input[name^="order_items["][type="hidden"]').value;
        const price = inputDiv.querySelector('input[name^="order_items["][type="hidden"]').nextElementSibling.value;
        const productName = inputDiv.querySelector('input[name^="order_items["][name$="[product_name]"]').value || '';
        const sku = inputDiv.querySelector('input[name^="order_items["][name$="[sku]"]').value || '';
        const imageUrl = inputDiv.querySelector('input[name^="order_items["][name$="[image_url]"]').value || '';

        orderItems.push({ id: itemId, quantity, price, product_name: productName, sku, image_url: imageUrl });
    });

    params.append('order_items', JSON.stringify(orderItems));

    // Send data to update_order.php using XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_order.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
        if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Order updated successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    closeEditModal(); // Close the modal
                    location.reload(); // Refresh the page
                });
            } else {
                Swal.fire('Error', data.message || 'Unable to update order.', 'error');
            }
        } else {
            Swal.fire('Error', `Error updating order: ${xhr.statusText}`, 'error');
        }
    };

    xhr.onerror = function () {
        Swal.fire('Error', 'Request failed. Please try again.', 'error');
    };

    xhr.send(params);
});

// Close modal function
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
};

// Fetch order details for editing
function fetchOrderDetails(orderID) {
    console.log('Fetching order details for Order ID:', orderID);

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `get_order.php?orderID=${encodeURIComponent(orderID)}`, true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            console.log('Fetched data:', data);

            if (!data || !data.id) {
                console.error('Invalid data received:', data);
                alert('No order details found.');
                return;
            }

            // Populate the modal with fetched order data
            document.getElementById('editOrderID').value = data.id;
            document.getElementById('editCustomerName').value = data.customer_name;
            document.getElementById('editEmail').value = data.customer_email;
            document.getElementById('editPhone').value = data.user_phone || ''; // Populate phone from users table
            document.getElementById('editTotalAmount').value = data.total_amount;
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editShipmentDate').value = data.shipment_date;
            document.getElementById('editPromoCode').value = data.promo_code || '';
            document.getElementById('editDeliveryAddress').value = data.delivery_address || '';
            document.getElementById('editDeliveryPhone').value = data.delivery_phone || '';
            document.getElementById('city').value = data.city || '';

            // Set the delivery phone in the appropriate input field (under the delivery section)
            document.getElementById('editDeliveryPhone').value = data.delivery_phone || ''; // Populate phone from deliveries table

            // Populate order items dynamically
const orderItemsContainer = document.getElementById('editOrderItemsContainer');
orderItemsContainer.innerHTML = ''; // Clear previous items

if (Array.isArray(data.items) && data.items.length > 0) {
    data.items.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.innerHTML = `
            <label>Product: ${item.product_name} (SKU: ${item.sku})</label><br>
            <label>Product Name:</label>
            <input type="text" name="order_items[${item.id}][product_name]" value="${item.product_name}" required>
            <label>SKU:</label>
            <input type="text" name="order_items[${item.id}][sku]" value="${item.sku}" required>
            <label>Quantity:</label>
            <input type="number" name="order_items[${item.id}][quantity]" value="${item.quantity}" required>
            <input type="hidden" name="order_items[${item.id}][id]" value="${item.id}">
            <input type="hidden" name="order_items[${item.id}][price]" value="${item.price}">
            <label>Image URL:</label>
            <input type="text" name="order_items[${item.id}][image_url]" value="${item.image_url}">
            <button type="button" class="removeItemButton" data-item-id="${item.id}">Remove Item</button>
        `;
        orderItemsContainer.appendChild(itemDiv);
    });
} else {
    console.warn("No order items found in the response data.");
}


            // Event delegation for remove item buttons
            orderItemsContainer.addEventListener('click', function(event) {
                if (event.target && event.target.classList.contains('removeItemButton')) {
                    const itemId = event.target.getAttribute('data-item-id');
                    event.target.closest('div').remove(); // Remove item from UI
                }
            });

            // Show the modal
            document.getElementById('editModal').style.display = 'block';
            console.log('Modal displayed with order details.');
        } else {
            console.error('Error fetching order details:', xhr.statusText);
            alert('Unable to fetch order details.');
        }
    };

    xhr.onerror = function () {
        console.error('Request failed. Please try again.');
        alert('Unable to fetch order details.');
    };

    xhr.send();
}































//----------------- View Javascript code----------------

document.addEventListener('DOMContentLoaded', () => {
    // Attach event listeners to all view order buttons if they exist
    const viewButtons = document.querySelectorAll('.viewOrderBtn');
    if (viewButtons.length > 0) {
        viewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const orderID = button.getAttribute('data-id');
                console.log(`View button clicked for Order ID: ${orderID}`);
                fetchOrderDetailsForView(orderID);
            });
        });
    }

    // Close modal when clicking outside of it
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('viewOrderModal');
        if (event.target === modal) {
            closeViewModal();
        }
    });
});

// Log debug messages for tracking issues
function logDebug(message) {
    const logMessage = {
        message,
        timestamp: new Date().toISOString()
    };
    console.log(logMessage); // Use console as fallback; replace with server logging if needed
}

// Fetch order details based on selected order ID
function fetchOrderDetailsForView(orderID) {
    console.log(`Fetching order details for Order ID: ${orderID}`);

    // Show loading indicator
    document.getElementById('viewOrderModal').classList.add('loading');

    fetch(`get_order.php?viewOrder=true&orderID=${orderID}`)
        .then(response => {
            if (!response.ok) {
                logDebug(`Network response was not ok for Order ID: ${orderID}`);
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(orderDetails => {
            if (orderDetails.error) {
                Swal.fire('Error', orderDetails.error, 'error');
                return;
            }
            populateModal(orderDetails);
            document.getElementById('viewOrderModal').style.display = 'block';
        })
        .catch(error => {
            console.error(`Error fetching order details: ${error}`);
            logDebug(`Error fetching order details for Order ID ${orderID}: ${error.message}`);
            Swal.fire('Error', 'Unable to fetch order details.', 'error');
        })
        .finally(() => {
            // Remove loading indicator
            document.getElementById('viewOrderModal').classList.remove('loading');
        });
}

function populateModal(orderDetails) {
    // Fill in general order information
    document.getElementById('viewOrderID').value = orderDetails.id;
    document.getElementById('viewOrderNumber').value = orderDetails.order_number;
    document.getElementById('viewTotalAmount').value = orderDetails.total_amount;
    document.getElementById('viewStatus').value = orderDetails.status;
    document.getElementById('viewShipmentDate').value = orderDetails.shipment_date;
    document.getElementById('viewPromoCode').value = orderDetails.promo_code || '';

    document.getElementById('viewCustomerName').value = orderDetails.customer_name;
    document.getElementById('viewCustomerEmail').value = orderDetails.customer_email;
    document.getElementById('viewCity').value = orderDetails.city || '';

    // Conditionally display delivery or collection information
    const deliverySection = document.getElementById('deliveryInformationFieldset');
    const collectionSection = document.getElementById('collectionMessageFieldset');
    if (orderDetails.order_type === 'Delivery') {
        // Populate delivery fields
        document.getElementById('viewDeliveryAddress').value = orderDetails.delivery ? 
            `${orderDetails.delivery.street_address}, ${orderDetails.delivery.suburb_address}, ${orderDetails.delivery.city}, ${orderDetails.delivery.province}` : 
            'No delivery details available.';
        document.getElementById('viewDeliveryPhone').value = orderDetails.delivery ? orderDetails.delivery.phone : '';
        document.getElementById('viewDeliveryNotes').value = orderDetails.delivery ? orderDetails.delivery.notes : '';
        document.getElementById('viewDeliveryOption').value = orderDetails.delivery ? orderDetails.delivery.delivery_option : '';
        // Show delivery section, hide collection
        deliverySection.style.display = 'block';
        collectionSection.style.display = 'none';
    } else {
        // Show collection message, hide delivery section
        deliverySection.style.display = 'none';
        collectionSection.style.display = 'block';
    }

    // Clear and populate order items
    const orderItemsContainer = document.getElementById('viewOrderItemsContainer');
    orderItemsContainer.innerHTML = ''; // Clear previous items
    if (orderDetails.items && orderDetails.items.length > 0) {
        orderDetails.items.forEach(item => {
            const itemDiv = document.createElement('div');
            styleOrderItem(itemDiv, item); // Style and add each item
            orderItemsContainer.appendChild(itemDiv);
        });
    } else {
        orderItemsContainer.innerHTML = '<p>No items found for this order.</p>';
    }
}

// Style each order item displayed in the modal
// Updated function to style the order items in the modal
function styleOrderItem(itemDiv, item) {
    // Apply flexbox styling to layout items side by side
    itemDiv.style.display = 'flex';
    itemDiv.style.alignItems = 'center';  // Align items vertically
    itemDiv.style.justifyContent = 'space-between';  // Add space between item info and image
    itemDiv.style.border = '1px solid #ccc';
    itemDiv.style.margin = '5px 0';
    itemDiv.style.padding = '10px';
    itemDiv.style.borderRadius = '4px';
    itemDiv.style.backgroundColor = '#f9f9f9';

    // Create left side container for item details (SKU, Name, Quantity, etc.)
    const leftDiv = document.createElement('div');
    leftDiv.style.flex = '1';  // Take up remaining space
    leftDiv.innerHTML = `  
        <strong>SKU:</strong> ${item.sku}<br>
        <strong>Product Name:</strong> ${item.product_name}<br>
        <strong>Quantity:</strong> ${item.quantity}<br>
        <strong>Price:</strong> $${parseFloat(item.price).toFixed(2)}<br>
        <strong>Total Price:</strong> $${parseFloat(item.total_price).toFixed(2)}
    `;
    itemDiv.appendChild(leftDiv);

    // Create right side container for the product image
    const imageDiv = document.createElement('div');
    imageDiv.style.marginLeft = '20px';  // Add some space between details and image

    if (item.image_url) {
        const image = document.createElement('img');
        image.src = item.image_url;
        image.alt = `Image of ${item.product_name}`;
        image.style.maxWidth = '100px';  // Set max width for images
        image.style.height = 'auto';  // Keep aspect ratio
        imageDiv.appendChild(image);
    } else {
        imageDiv.innerHTML = '<em>No image available</em>';
    }

    itemDiv.appendChild(imageDiv);
}

// Function to populate the modal with order details including items
function populateModal(orderDetails) {
    // Fill in general order information
    document.getElementById('viewOrderID').value = orderDetails.id;
    document.getElementById('viewOrderNumber').value = orderDetails.order_number;
    document.getElementById('viewTotalAmount').value = orderDetails.total_amount;
    document.getElementById('viewStatus').value = orderDetails.status;
    document.getElementById('viewShipmentDate').value = orderDetails.shipment_date;
    document.getElementById('viewPromoCode').value = orderDetails.promo_code;

    document.getElementById('viewCustomerName').value = orderDetails.customer_name;
    document.getElementById('viewCustomerEmail').value = orderDetails.customer_email;
    document.getElementById('viewCity').value = orderDetails.city;

    // Conditionally display delivery or collection information
    const deliverySection = document.getElementById('deliveryInformationFieldset');
    const collectionSection = document.getElementById('collectionMessageFieldset');
    if (orderDetails.order_type === 'Delivery') {
        document.getElementById('viewDeliveryAddress').value = orderDetails.delivery ?
            `${orderDetails.delivery.street_address}, ${orderDetails.delivery.suburb_address}, ${orderDetails.delivery.city}, ${orderDetails.delivery.province}` :
            'No delivery details available.';
        document.getElementById('viewDeliveryPhone').value = orderDetails.delivery ? orderDetails.delivery.phone : '';
        document.getElementById('viewDeliveryNotes').value = orderDetails.delivery ? orderDetails.delivery.notes : '';
        document.getElementById('viewDeliveryOption').value = orderDetails.delivery ? orderDetails.delivery.delivery_option : '';
        // Show delivery section, hide collection
        deliverySection.style.display = 'block';
        collectionSection.style.display = 'none';
    } else {
        // Show collection message, hide delivery section
        deliverySection.style.display = 'none';
        collectionSection.style.display = 'block';
    }

    // Clear and populate order items
    const orderItemsContainer = document.getElementById('viewOrderItemsContainer');
    orderItemsContainer.innerHTML = ''; // Clear previous items
    if (orderDetails.items && orderDetails.items.length > 0) {
        orderDetails.items.forEach(item => {
            const itemDiv = document.createElement('div');
            styleOrderItem(itemDiv, item); // Apply styles and add item to container
            orderItemsContainer.appendChild(itemDiv);
        });
    } else {
        orderItemsContainer.innerHTML = '<p>No items found for this order.</p>';
    }
}


// Close the modal
function closeViewModal() {
    document.getElementById('viewOrderModal').style.display = 'none';
}





























// --------------- Delete  Functionality ------------------------       
document.addEventListener('DOMContentLoaded', () => { 
    // Handle Delete button click
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const orderID = e.target.getAttribute('data-id');
            console.log('Delete button clicked for Order ID:', orderID);
            confirmDeleteOrder(orderID);
        });
    });
});

// Confirm and delete the order
function confirmDeleteOrder(orderID) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteOrder(orderID);
        }
    });
}

// Delete order by sending a POST request
function deleteOrder(orderID) {
    fetch('delete_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `orderID=${orderID}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Deleted!', 'The order has been deleted.', 'success');
            // Reload the page after successful deletion
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire('Error', 'Unable to delete order.', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting order:', error);
        Swal.fire('Error', 'Unable to delete order.', 'error');
    });
}
document.addEventListener('DOMContentLoaded', () => {
    // Handle Edit button click
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            const orderID = e.target.getAttribute('data-id');
            console.log('Edit button clicked for Order ID:', orderID);
            fetchOrderDetails(orderID);
        });
    });
});

//------------------------------Filtering section--------------------------

function filterTable() {
    const orderNumberFilter = document.getElementById('filterOrderNumber').value.toLowerCase();
    const customerNameFilter = document.getElementById('filterCustomerName').value.toLowerCase();
    const emailFilter = document.getElementById('filterEmail').value.toLowerCase();
    const orderDateFilter = document.getElementById('filterOrderDate').value.toLowerCase();
    const totalAmountFilter = document.getElementById('filterTotalAmount').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
    const orderTypeFilter = document.getElementById('filterOrderType').value.toLowerCase();
    const deliveryAddressFilter = document.getElementById('filterDeliveryAddress').value.toLowerCase();

    const table = document.querySelector('.order-table');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) { // Skip the header row
        const cells = rows[i].getElementsByTagName('td');

        // Perform the checks for each cell based on the filters
        const orderNumberMatch = cells[1].innerText.toLowerCase().includes(orderNumberFilter);
        const customerNameMatch = cells[2].innerText.toLowerCase().includes(customerNameFilter);
        const emailMatch = cells[3].innerText.toLowerCase().includes(emailFilter);
        const orderDateMatch = cells[4].innerText.toLowerCase().includes(orderDateFilter);
        const totalAmountMatch = cells[5].innerText.toLowerCase().includes(totalAmountFilter);
        const statusMatch = cells[6].innerText.toLowerCase().includes(statusFilter);
        const orderTypeMatch = cells[8].innerText.toLowerCase().includes(orderTypeFilter);
        const deliveryAddressMatch = cells[9].innerText.toLowerCase().includes(deliveryAddressFilter);

        // Show row if all filters match, otherwise hide it
        if (orderNumberMatch && customerNameMatch && emailMatch && orderDateMatch && totalAmountMatch && statusMatch && orderTypeMatch && deliveryAddressMatch) {
            rows[i].style.display = ''; // Show the row
        } else {
            rows[i].style.display = 'none'; // Hide the row
        }
    }
}

//------------------ Filtering Section Javascript--------------------------------------


</script>

</html>

<?php
$conn->close();
?>