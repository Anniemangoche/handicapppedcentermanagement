<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magdalene_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form values
$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$subject = $conn->real_escape_string($_POST['subject']);
$message = $conn->real_escape_string($_POST['message']);
$status = 'unread'; // Set default status

// Insert into database
$sql = "INSERT INTO contact_messages (name, email, subject, message, status)
        VALUES ('$name', '$email', '$subject', '$message', '$status')";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Message sent successfully!');</script>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
