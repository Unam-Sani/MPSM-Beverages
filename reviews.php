<?php  
include_once('blueprint.php');  // Include your main blueprint
include('config.php');

if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

 
$searchCustomer = isset($_POST['searchCustomer']) ? $_POST['searchCustomer'] : '';
$searchProduct = isset($_POST['searchProduct']) ? $_POST['searchProduct'] : '';
$filterRating = isset($_POST['filterRating']) ? $_POST['filterRating'] : '';

// Base query with filters
$query = "SELECT reviews.reviewID, reviews.reviewerName, reviews.rating, reviews.created_at, reviews.reviewText, 
        products.productName, products.ItemID 
    FROM reviews 
    JOIN products ON reviews.ItemID = products.ItemID 
    WHERE 1=1";

// Add customer name filter
if (!empty($searchCustomer)) {
    $query .= " AND reviews.reviewerName LIKE '%" . $conn->real_escape_string($searchCustomer) . "%'";
}

// Add product name filter
if (!empty($searchProduct)) {
    $query .= " AND products.productName LIKE '%" . $conn->real_escape_string($searchProduct) . "%'";
}

// Add rating filter
if (!empty($filterRating)) {
    $query .= " AND reviews.rating = " . (int)$filterRating;
}


if (!$result) {
    die("Error fetching reviews: " . $conn->error);
}else{
    $result = $conn->query($query);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-bottom: 60px;
            overflow-x: hidden;
        }

        /* Title styling */
        h1 {
            text-align: center;
            color: #2980b9;
            margin-top: 80px;
            animation: fadeIn 1s;
        }

        /* Container for the reviews */
        .reviews-container {
            max-width: 90%;
            margin: 50px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-left: 350px;
            margin-right: 100px;
            margin-top: 80px;
        }

        /* Styling for individual review cards */
        .review-card {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .review-card h3 {
            color: orange;
            margin: 0 0 10px;
            font-size: 18px;
        }

        .review-card p {
            color: #333;
            margin: 0 0 10px;
        }

        .review-card .review-author {
            color: #555;
            font-style: italic;
        }

        /* Button styles for reply */
        .reply-button {
            padding: 10px 20px;
            background-color: white;
            border: 1px solid orange;
            color: orange;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .reply-button:hover {
            background-color: orange;
            color: white;
        }

        .table-container {
            max-width: 100%;
            overflow-x: auto;
            margin-left: 250px;
            margin-top: 20px;
        }

        /* Adjusting table within the container */
        table {
            width: 100%;
            border-collapse: collapse;
            margin -top: 20px;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0; /* For smaller screens, remove the margin */
            }
        }

        /* Animation for fade in effect */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .reviews-container {
                margin-left: 20px;
                margin-right: 20px;
            }

            .review-card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>


<div class="table-container">
    <h1>Customer Reviews</h1>
    <form method="post" id="filterForm">
        <label for="searchCustomer">Search Customer Name:</label>
        <input type="text" id="searchCustomer" name="searchCustomer">

        <label for="searchProduct">Search Product Name:</label>
        <input type="text" id="searchProduct" name="searchProduct">

        <label for="filterRating">Filter by Rating:</label>
        <select id="filterRating" name="filterRating">
            <option value="">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="2">2 Stars</option>
            <option value="1">1 Star</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <form method="post" id="bulkDeleteForm">
        <table id="reviewsTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Customer Name</th>
                    <th>Product Name</th>
                    <th>Rating</th>
                    <th>Date Submitted</th>
                    <th>Review</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="review_ids[]" value="<?= htmlspecialchars($row['reviewID']); ?>"></td>
                        <td><?= htmlspecialchars($row['reviewerName']); ?></td>
                        <td><a href="product.php?id=<?= htmlspecialchars($row['ItemID']); ?>"><?= htmlspecialchars($row['productName']); ?></a></td>
                        <td>
                            <?php for ($i = 0; $i < $row['rating']; $i++): ?>
                                <i class="fas fa-star rating"></i>
                            <?php endfor; ?>
                        </td>
                        <td><?= htmlspecialchars($row['created_at']); ?></td>
                        <td><?= htmlspecialchars($row['reviewText']); ?></td>
                        <td><button type="button" class="deleteBtn" data-id="<?= htmlspecialchars($row['reviewID']); ?>">Delete</button></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button type="submit" id="bulkDeleteBtn">Delete Selected</button>
    </form>
</di>
<script>

        
    $(document).ready(function() {
        const table = $('#reviewsTable').DataTable();

        // Filter by customer name
        $('#searchCustomer').on('keyup', function() {
            table.column(1).search(this.value).draw();
        });

        // Filter by product name
        $('#searchProduct').on('keyup', function() {
            table.column(2).search(this.value).draw();
        });

        // Filter by rating
        $('#filterRating').on('change', function() {
            const rating = this.value ? '^' + this.value + '$' : ''; // Use regex for exact match
            table.column(3).search(rating, true, false).draw();
        });
    
        // Select all reviews
        $('#selectAll').on('click', function() {
            $('input[name="review_ids[]"]').prop('checked', this.checked);
        });
        
        $('.deleteBtn').on('click', function() {
            const reviewId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This review will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_review.php',
                        type: 'POST',
                        data: { id: reviewId },
                        success: function(response) {
                            let data = JSON.parse(response);
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', 'The review has been deleted.', 'success');
                                location.reload();
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'There was an issue deleting the review.', 'error');
                        }
                    });
                }
            });
        });

        
    });
</script>

</body>
</html>
