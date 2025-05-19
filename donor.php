<?php
// Start session and database connection
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$dbusername = "root";  // Database username
$dbpassword = "";      // Database password
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $address = trim($_POST['address']);

    if (!empty($fname) && !empty($lname) && !empty($email) && !empty($phone) && !empty($role) && !empty($address)) {
        $query = "INSERT INTO staff_records (fname, lname, email, phone, role, address) 
                  VALUES ('$fname', '$lname', '$email', '$phone', '$role', '$address')";

        if (mysqli_query($conn, $query)) {
            echo "<script>showPopup('success', 'Staff record added successfully!');</script>";
        } else {
            echo "<script>showPopup('error', 'Error adding record!');</script>";
        }
    } else {
        echo "<script>showPopup('error', 'All fields are required!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff - Magdalene Home</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        label {
            font-weight: bold;
            display: block;
        }

        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Popup Styles */
        .popup {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        .popup.success {
            background-color: green;
        }

        .popup.error {
            background-color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Staff</h2>

    <form method="POST" action="" onsubmit="return validateForm()">
        <table>
            <tr>
                <td><label for="fname">First Name:</label></td>
                <td><input type="text" name="fname" id="fname"></td>
            </tr>
            <tr>
                <td><label for="lname">Last Name:</label></td>
                <td><input type="text" name="lname" id="lname"></td>
            </tr>
            <tr>
                <td><label for="email">Email:</label></td>
                <td><input type="email" name="email" id="email"></td>
            </tr>
            <tr>
                <td><label for="phone">Phone:</label></td>
                <td><input type="text" name="phone" id="phone"></td>
            </tr>
            <tr>
                <td><label for="role">Role:</label></td>
                <td><input type="text" name="role" id="role"></td>
            </tr>
            <tr>
                <td><label for="address">Address:</label></td>
                <td><textarea name="address" id="address"></textarea></td>
            </tr>
            <tr>
                <td colspan="2"><button type="submit">Add Staff</button></td>
            </tr>
        </table>
    </form>
</div>

<!-- Success/Error Popup -->
<div id="popup" class="popup"></div>

<script>
    function validateForm() {
        let fname = document.getElementById("fname").value.trim();
        let lname = document.getElementById("lname").value.trim();
        let email = document.getElementById("email").value.trim();
        let phone = document.getElementById("phone").value.trim();
        let role = document.getElementById("role").value.trim();
        let address = document.getElementById("address").value.trim();

        if (fname === "" || lname === "" || email === "" || phone === "" || role === "" || address === "") {
            showPopup('error', "All fields are required!");
            return false;
        }

        return true;
    }

    function showPopup(type, message) {
        let popup = document.getElementById("popup");
        popup.textContent = message;
        popup.className = `popup ${type}`;
        popup.style.display = "block";

        setTimeout(() => {
            popup.style.display = "none";
        }, 3000);
    }
</script>

</body>
</html>
