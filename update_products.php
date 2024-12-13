<?php
include 'db_connection.php'; // Include the database connection

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemID = $_POST['ItemID'];
    $productName = $_POST['productName'];
    $SKU = $_POST['SKU'];
    $price = $_POST['price'];
    $category = $_POST['Category'];
    $stockLevel = $_POST['stockLevel'];
    $restockLevel = $_POST['restockLevel'];
    $availabilityStatus = $_POST['availabilityStatus'];
    $volume = $_POST['volume'];
    $expirationDate = !empty($_POST['expirationDate']) ? $_POST['expirationDate'] : null;
    
    $imageURL = null; // Initialize image URL variable

    // Handle image upload
    if (isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Set the upload directory
        $fileName = basename($_FILES['imageURL']['name']);
        $targetFilePath = $uploadDir . uniqid() . '-' . $fileName; // Generate a unique file name
        
        // Check if upload directory exists, if not create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['imageURL']['tmp_name'], $targetFilePath)) {
            $imageURL = $targetFilePath; // Set the image URL
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
    }

    try {
        // Prepare the SQL query
        $query = "UPDATE products SET 
                    productName = ?, 
                    SKU = ?, 
                    Price = ?, 
                    Category = ?, 
                    stockLevel = ?, 
                    restockLevel = ?, 
                    availabilityStatus = ?, 
                    volume = ?, 
                    expirationDate = ?, 
                    imageURL = ? 
                  WHERE ItemID = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }

        // Bind parameters, including the image URL
        $stmt->bind_param(
            "ssdsisssssi", 
            $productName, 
            $SKU, 
            $price, 
            $category, 
            $stockLevel, 
            $restockLevel, 
            $availabilityStatus, 
            $volume, 
            $expirationDate, 
            $imageURL, 
            $itemID
        );

        // Execute the update query
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            throw new Exception('Failed to execute update: ' . $stmt->error);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        // Return the error to the front end for debugging
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
