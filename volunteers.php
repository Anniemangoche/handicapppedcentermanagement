<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "magdalene_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $type = $_POST['type'];
    $description = $_POST['description'];

    // Handle image upload
    $target_dir = "uploads/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;

        $stmt = $conn->prepare("INSERT INTO donations (name, location, date, time, type, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $location, $date, $time, $type, $description, $image_path);

        if ($stmt->execute()) {
            echo "<script>alert('Donation activity added successfully!');</script>";
        } else {
            echo "<script>alert('Error adding donation activity');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error uploading image');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Donation Activity</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form action="insert_donation.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Donation Name" required>
        <input type="text" name="location" placeholder="Location" required>
        <input type="date" name="date" required>
        <input type="time" name="time" required>
        <input type="text" name="type" placeholder="Donation Type" required>
        <textarea name="description" placeholder="Donation Description" required></textarea>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Upload Donation</button>
    </form>
</body>
</html>
