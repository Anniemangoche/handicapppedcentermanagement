
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $age = trim($_POST['age']);
    $medical_info = trim($_POST['medical_info']);
    $education_info = trim($_POST['education_info']);
    $staff_name = trim($_POST['staff_name']);
    $relatives_phonenumber = trim($_POST['relatives_phonenumber']);
    $child_backgroundinfo = trim($_POST['child_backgroundinfo']);
    $relatives_address = trim($_POST['relatives_address']);

    // Check if phone number already exists in the database
    $check_phone_query = "SELECT * FROM child_records WHERE relatives_phonenumber = '$relatives_phonenumber'";
    $result = $conn->query($check_phone_query);

    if ($result->num_rows > 0) {
        // Phone number exists, show error
        echo "<script>window.onload = function() { showPopup('error', 'This phone number is already associated with another child relative.'); };</script>";
    } else {
        // Proceed with inserting the data if no duplicate phone number
        if (!empty($fname) && !empty($lname) && !empty($age) &&  !empty($medical_info) &&  !empty($education_info) && !empty($relatives_phonenumber) && !empty($child_backgroundinfo) && !empty($relatives_address)) {
            $query = "INSERT INTO child_records (fname, lname, age, medical_info, education_info, staff_name, relatives_phonenumber, child_backgroundinfo, relatives_address) 
                      VALUES ('$fname', '$lname', '$age', '$medical_info',  '$education_info' , '$staff_name', '$relatives_phonenumber' , '$child_backgroundinfo' , '$relatives_address')";

            if (mysqli_query($conn, $query)) {
               
                echo "<script>window.onload = function() { showPopup('success', 'child record added successfully!'); };</script>";
            } else {
                // If there is an error, show error message
                echo "<script>window.onload = function() { showPopup('error', 'Error adding record!'); };</script>";
            }
        } else {
            // If any fields are empty, show error message
            echo "<script>window.onload = function() { showPopup('error', 'All fields are required!'); };</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Child Records - Magdalene Home</title>
    <style>
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
    color: #333;
    box-sizing: border-box;
    height: 100%;
    display: flex;
    flex-direction: column;
}

main {
    flex: 1; /* Pushes the footer to the bottom */
    padding: 20px;
    box-sizing: border-box; /* Ensure padding doesn't increase size */
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    background-color: #4CAF50;
    color: white;
}

header .logo {
    font-size: 1.5em;
    font-weight: bold;
}

.logo a{
    text-decoration: none;
    color: #fff;
}

nav a {
    margin-left: 20px;
    color: white;
    text-decoration: none;
    font-weight: bold;
}


.container {
            width: 600px;
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            align-self:center;
            display:flex;
            flex-direction:column;
            margin-top:100px;
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
            width: 95%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            width: 95%;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
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
<header>
        <div class="logo"><a href="index.html">MAGDALENE</a></div>
        <nav>
            <a href="admin_dashboard.html">Dashboard</a>
            <a href="logout.html">Logout</a>
        </nav>
    </header>

<div class="container">
    <h2>Add Child Information</h2>

            <form method="POST" action="" onsubmit="return validateForm()">
                
                        <label for="fname">First Name:</label>
                    <input type="text" name="fname" id="fname" required>
                    
                        <label for="lname">Last Name:</label>
                        <input type="text" name="lname" id="lname" required>
                    

                    <label>Age:</label>
                <input type="number" name="age"  id="age" required>
                
                <label>Medical Info:</label>
                <textarea name="medical_info" id="medical_info" required></textarea>
                
                <label>Education Info:</label>
                <textarea name="education_info" id="education_info" required></textarea>
                
                <label>Staff Name:</label>
                <input type="name" name="staff_name" id="staff_name" required>

                <label for="relatives_phonenumber">Relatives_Phone number:</label>
                <input type="number" name="relatives_phonenumber" id="relatives_phonenumber" required>
                    
                        <label for="child_backgroundinfo">Child_Background Information:</label>
                        <input type="text" name="child_backgroundinfo" id="child_backgroundinfo" required>
                    

                        <label for="relatives_address">Relatives_Address:</label>
                        <textarea name="relatives_address" id="relatives_address" required></textarea>
                    
                        <colspan="2"><button type="submit">Add child</button>
                
                
            </form>
</div>

<!-- Success/Error Popup -->
<div id="popup" class="popup"></div>

<script>
    // Popup function to display success/error messages
    function showPopup(type, message) {
        let popup = document.getElementById("popup");
        popup.textContent = message;
        popup.className = `popup ${type}`;
        popup.style.display = "block";

        setTimeout(() => {
            popup.style.display = "none";
        }, 3000);
    }

    // Form validation function
    function validateForm() {
        let fname = document.getElementById("fname").value.trim();
        let lname = document.getElementById("lname").value.trim();
        let age = document.getElementById("age").value.trim();
        let medical_info = document.getElementById("medical_info").value.trim();
        let education_info = document.getElementById("education_info").value.trim();
        let relatives_phonenumber = document.getElementById("relatives_phonenumber").value.trim();
        let child_backgroundinfo = document.getElementById("child_backgroundinfo").value.trim();
        let arelatives_address = document.getElementById("relatives_address").value.trim();

        if (fname === "" || lname === "" ||age==="" || medical_info===   || education_info===""   ||relatives_phonenumber === "" || child_backgroundinfo === "" || relatives_address === "") {
            showPopup('error', "All fields are required!");
            return false;
        }

        return true;
    }
</script>

</body>
</html>
