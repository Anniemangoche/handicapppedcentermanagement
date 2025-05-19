<?php
// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set header for JSON response
header('Content-Type: application/json');

try {
    // Fetch all event names from the database
    $sql = "SELECT name FROM events ORDER BY name ASC";
    $result = $conn->query($sql);
    
    // Check if we have any results
    if ($result && $result->num_rows > 0) {
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row['name'];
        }
        echo json_encode(['success' => true, 'events' => $events]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No events found', 'events' => []]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'events' => []]);
} finally {
    // Close the connection
    $conn->close();
}
?>