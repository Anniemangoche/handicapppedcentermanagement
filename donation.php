<?php
session_start();
require_once('./vendor/autoload.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
try {
    $conn = new mysqli("localhost", "root", "", "magdalene_management");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Form</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4267B2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #365899;
        }
        .error {
            color: red;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Make a Donation</h1>
        <form id="donationForm" action="process_payments.php" method="POST">
            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="fname" required minlength="2">
                <span class="error" id="fnameError"></span>
            </div>
            
            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" required minlength="2">
                <span class="error" id="lnameError"></span>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <span class="error" id="emailError"></span>
            </div>
            
            <div class="form-group">
                <label for="event_name">Event</label>
                <select id="event_name" name="event_name" required>
                    <option value="">Select an event</option>
                    <option value="Event 1">Event 1</option>
                    <option value="Event 2">Event 2</option>
                    <option value="Event 3">Event 3</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fees">Amount (MWK)</label>
                <input type="number" id="fees" name="fees" min="50" required>
                <span class="error" id="feesError"></span>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{9,15}" required>
                <span class="error" id="phoneError"></span>
            </div>
            
            <div class="form-group">
                <label for="bank">Mobile Network</label>
                <select id="bank" name="bank" required>
                    <option value="">Select a network</option>
                    <option value="airtel">Airtel Money</option>
                    <option value="tnm">Tnm Mpamba</option>
                </select>
            </div>
            
            <button type="submit">Donate Now</button>
        </form>
    </div>
    <script>
        // Basic client-side form validation
        const form = document.getElementById('donationForm');
        form.addEventListener('submit', function(event) {
            let valid = true;
            const errors = document.querySelectorAll('.error');
            errors.forEach(error => error.textContent = '');

            const fname = document.getElementById('fname').value.trim();
            if (!fname) {
                document.getElementById('fnameError').textContent = 'First name is required';
                valid = false;
            }

            const lname = document.getElementById('lname').value.trim();
            if (!lname) {
                document.getElementById('lnameError').textContent = 'Last name is required';
                valid = false;
            }

            const email = document.getElementById('email').value.trim();
            if (!email || !email.match(/^[^@]+@[^@]+\.[a-z]{2,}$/i)) {
                document.getElementById('emailError').textContent = 'Invalid email format';
                valid = false;
            }

            const fees = document.getElementById('fees').value.trim();
            if (!fees || isNaN(fees) || fees < 50) {
                document.getElementById('feesError').textContent = 'Minimum donation is MWK 50';
                valid = false;
            }

            const phone = document.getElementById('phone').value.trim();
            if (!phone || !phone.match(/^[0-9]{9,15}$/)) {
                document.getElementById('phoneError').textContent = 'Invalid phone number';
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>