<?php
// Start session
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

// Handle task editing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activityId = $_POST['activity_id'];
    $activityName = $_POST['activity_name'];
    $description = $_POST['description'];
    $activityDate = $_POST['activity_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    
    $query = "UPDATE activity_schedules SET 
              activity_name = ?, 
              description = ?, 
              activity_date = ?, 
              start_time = ?, 
              end_time = ? 
              WHERE activity_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $activityName, $description, $activityDate, $startTime, $endTime, $activityId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Task updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating task: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
header("Location: viewtask.php");
exit();
?>