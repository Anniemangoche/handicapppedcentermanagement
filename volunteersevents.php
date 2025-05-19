<?php
// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "magdalene_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM events ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events</title>
    <link rel="stylesheet" href="css/event-styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        /* Page Title */
        .page-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 600;
        }

        /* Container for all donations */
        .donations-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        /* Individual Donation Card */
        .donation {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .donation:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        /* Donation Image */
        .donation img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        /* Donation Content */
        .donation h2 {
            font-size: 20px;
            padding: 15px 20px 5px;
            margin: 0;
            color: #2c3e50;
        }

        .donation p {
            padding: 0 20px;
            margin: 10px 0;
            font-size: 15px;
            color: #555;
            line-height: 1.5;
        }

        /* Highlight the type with a badge */
        .donation p:nth-of-type(2) {
            font-weight: 500;
        }

        /* Type badge styling */
        .donation p:nth-of-type(2) {
            margin: 10px 20px;
            display: inline-block;
            padding: 4px 10px;
            background-color: #e3f2fd;
            border-radius: 15px;
            font-size: 14px;
            color: #1976d2;
        }

        /* Description text with limit */
        .donation p:nth-of-type(3) {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 20px;
        }

        /* Donate Button */
        .donate-btn {
            display: block;
            text-align: center;
            background-color: #926c54;
            color: white;
            text-decoration: none;
            padding: 12px 0;
            margin: 15px 20px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .donate-btn:hover {
            background-color: #7d5b46;
        }

        /* No donations message */
        .no-donations {
            text-align: center;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            grid-column: 1 / -1;
            color: #777;
            font-size: 18px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .donations-container {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
            
            .donation h2 {
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .donations-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <h1 class="page-title">Donation Events</h1>
    
    <div class="donations-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='donation'>";
                echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Donation Image'>";
                echo "<h2>" . htmlspecialchars($row['name']) . "</h2>";
                echo "<p>Date: " . htmlspecialchars($row['date']) . " at " . htmlspecialchars($row['time']) . "</p>";
                echo "<p>amount: " . htmlspecialchars($row['amount']) . "</p>";
                echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                echo "<a href='donations.php?donation_id=" . $row['id'] . "' class='donate-btn'>Donate Now</a>";
                echo "</div>";
            }
        } else {
            echo "<div class='no-donations'>No donation events available at this time.</div>";
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
