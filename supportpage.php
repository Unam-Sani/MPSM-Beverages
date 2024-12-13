<?php
include_once('configlogin.php');  // Include your database connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include blueprint.php for the navigation bar and sidebar
include('blueprint.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styling specific to the support page */
        .content {
            margin-left: 250px; /* Account for the sidebar width */
            padding: 20px;
            margin-top:150px;
        }

        /* Support header styling */
        .support-header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 20px;
        }

        .support-header h2 {
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        .support-header a {
            color: #fbb01b;
            text-decoration: none;
            font-size: 1em;
        }

        /* Tile container styling */
        .tile-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Individual tile styling */
        .tile {
            height: 250px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            gap: 8px;
        }

        /* Circular image styling */
        .tile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .tile .name {
            font-size: 1.1em;
        }

        .tile .role {
            font-size: 0.9em;
            font-weight: normal;
            opacity: 0.8;
        }

        /* Tile colors */
        .tile.pink { background-color: #ffccd5; color: #b8004c; }
        .tile.purple { background-color: #d1c4e9; color: #4a148c; }
        .tile.baby-blue { background-color: #b3e5fc; color: #0277bd; }
        .tile.light-green { background-color: #c8e6c9; color: #1b5e20; }

        /**********************************Email************************ */
        .contact1 a {
            color: #b8004c; /* Pink color */
            text-decoration: none; /* Removes underline */
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .contact1 a i {
            margin-right: 8px; /* Space between icon and text */
        }

        .contact1 a:hover {
            color: #ff69b4; /* Slightly lighter pink on hover */
        }

        .contact2 a {
            color: #4a148c; /* Purple color */
            text-decoration: none; /* Removes underline */
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .contact2 a i {
            margin-right: 8px; /* Space between icon and text */
        }

        .contact2 a:hover {
            color: #8e24aa; /* Slightly lighter purple on hover */
        }

        .contact3 a {
            color: #0277bd; /* blue color */
            text-decoration: none; /* Removes underline */
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .contact3 a i {
            margin-right: 8px; /* Space between icon and text */
        }

        .contact3 a:hover {
            color: #64b5f6; /* Slightly lighter blue on hover */
        }

        .contact4 a {
            color: #1b5e20; /* green color */
            text-decoration: none; /* Removes underline */
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .contact4 a i {
            margin-right: 8px; /* Space between icon and text */
        }

        .contact4 a:hover {
            color: #66bb6a; /* Slightly lighter green on hover */
        }


        

    </style>
</head>

<body>

<div class="content">
    <!-- Support Header Section -->
    <div class="support-header">
        <h2>Support Team</h2>
        <a href="mailto:support@mpsbeverages.co.za">support@mpsbeverages.co.za</a>
    </div>

    <!-- Square Tiles Section in 2x2 Grid -->
    <div class="tile-container">
        <div class="tile pink">
            <img src="images/unam.jpg" alt="Unam">
            <div class="name">Unam Sani</div>
            <div class="role">Back-end developer and Deployment Specialist</div>
            <div class="contact1">
                <a href="mailto:unamsani@icloud.com">
                    <i class="fa-solid fa-envelope"></i> unamsani@icloud.com
                </a>
            </div>
            <div class="contact1">
                <a href="tel:0662538147">
                <i class="fa-solid fa-phone"></i> 0662538147
                </a>
            </div>
    </div>
        <div class="tile purple">
            <img src="images/bongi.jpg" alt="Bongi">
            <div class="name">Noliqwa Mhlambi</div>
            <div class="role">Front-end Developer and UI/UX Designer</div>
            <div class="contact2">
                <a href="mailto:noliqwamhlambi@gmail.com">
                    <i class="fa-solid fa-envelope"></i> noliqwamhlambi@gmail.com
                </a>
            </div>
            <div class="contact2">
                <a href="tel:0813957923">
                <i class="fa-solid fa-phone"></i> 0813957923
                </a>
            </div>
        </div>
        <div class="tile baby-blue">
            <img src="images/cathy.jpg" alt="Cathy">
            <div class="name">Cathrine Maponya</div>
            <div class="role">Full Stack Developer and Security Specialist</div>
            <div class="contact3">
                <a href="mailto:cmaponya357@gmail.com">
                    <i class="fa-solid fa-envelope"></i> cmaponya357@gmail.com
                </a>
            </div>
            <div class="contact3">
                <a href="tel:0659725354">
                <i class="fa-solid fa-phone"></i> 0659725354
                </a>
            </div>
        </div>
        <div class="tile light-green">
            <img src="images/nthabi.jpg" alt="Nthabi">
            <div class="name">Nthabiseng Moshidi</div>
            <div class="role">Project Manager and Quality Assurrance</div>
            <div class="contact4">
                <a href="mailto:triniteemoshidi@gmail.com">
                    <i class="fa-solid fa-envelope"></i> triniteemoshidi@gmail.com
                </a>
            </div>
            <div class="contact4">
                <a href="tel:0764072640">
                <i class="fa-solid fa-phone"></i> 0764072640
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
