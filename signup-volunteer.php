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

$error_message = "";
$registration_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $fname = mysqli_real_escape_string($conn, trim($_POST['fname']));
    $lname = mysqli_real_escape_string($conn, trim($_POST['lname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $address = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Set role as volunteer
    $role = "volunteer";

    // Check if email already exists in db
    $check_email_query = "SELECT * FROM staff_records WHERE email = '$email'";
    $result = $conn->query($check_email_query);

    if ($result->num_rows > 0) {
        $error_message = "This email is already associated with another user.";
    } else {
        // Check if username already exists
        $check_username_query = "SELECT * FROM staff_records WHERE username = '$username'";
        $username_result = $conn->query($check_username_query);
        
        if ($username_result->num_rows > 0) {
            $error_message = "This username is already taken.";
        } else {
            // Check if passwords match
            if ($password !== $confirm_password) {
                $error_message = "Passwords do not match!";
            } else {
                // Proceed with inserting the data
                if (!empty($fname) && !empty($lname) && !empty($email) && !empty($username) && !empty($password)) {
                    // Hash the password before storing
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert into staff_records table
                    $query = "INSERT INTO staff_records (fname, lname, email, phone, role, address, username, password, confirm_password) 
                              VALUES ('$fname', '$lname', '$email', '$phone', '$role', '$address', '$username', '$hashed_password', '$hashed_password')";

                    if (mysqli_query($conn, $query)) {
                        $registration_success = true;
                        // Set a success message in session
                        $_SESSION['registration_success'] = "Volunteer registration successful! Please login with your credentials.";
                        // Redirect to login page
                        header("Location: login.php");
                        exit(); // Stop script execution after redirect
                    } else {
                        $error_message = "Error adding record: " . mysqli_error($conn);
                    }
                } else {
                    $error_message = "All required fields must be filled!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7d5b46;
            --accent-color: #e3a073;
            --light-color: #f8f1e9;
            --dark-color: #2c3e50;
            --text-color: #333;
            --white: #ffffff;
            --gray-light: #f5f5f5;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background-color: var(--light-color);
            overflow-x: hidden;
            padding-top: 80px; /* Space for fixed header */
        }

        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logo a {
            text-decoration: none;
            color: var(--primary-color);
            transition: var(--transition);
        }

        .logo a:hover {
            color: var(--accent-color);
        }

        nav {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        nav a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: var(--transition);
        }

        nav a:hover {
            background-color: rgba(146, 108, 84, 0.1);
        }

        .registration-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
        }

        .alert {
            margin-top: 20px;
            text-align: center;
        }

        footer {
            background-color: var(--dark-color);
            color: var(--white);
            text-align: center;
            padding: 30px 20px;
            margin-top: 50px;
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            max-width: 1200px;
            margin: 0 auto 30px;
        }

        .footer-section {
            width: 30%;
            min-width: 250px;
            margin-bottom: 20px;
        }

        .footer-section h4 {
            color: var(--accent-color);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section a {
            color: var(--light-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-section a:hover {
            color: var(--accent-color);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .social-links a {
            color: var(--white);
            background-color: rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }

        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<header>
    <div class="logo"><a href="index.php">MAGDALENE HOME</a></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="don.php" class="donate-btn">Donate</a>
        <a href="services.php">Our Services</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact</a>
        <a href="login.php">Login</a>
    </nav>
</header>

<main>
    <div class="registration-container">
        <h2>Volunteer Registration</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($registration_success): ?>
            <div class="alert alert-success" role="alert">
                Registration successful! Please <a href="login.php">login</a> with your credentials.
            </div>
        <?php else: ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="fname" class="form-label">First Name *</label>
                    <input type="text" class="form-control" id="fname" name="fname" required>
                </div>
                <div class="col-md-6">
                    <label for="lname" class="form-label">Last Name *</label>
                    <input type="text" class="form-control" id="lname" name="lname" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone">
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label">Username *</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
               
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Register as Volunteer</button>
            </div>
            
            <div class="mt-3 text-center">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
        
        <?php endif; ?>
    </div>
</main>

<footer>
   
        <div class="footer-section">
           
    <div class="copyright">
        <p>© 2025 Magdalene Home for Special Needs. All Rights Reserved.</p>
        <p>A sanctuary of hope and love for children with special needs</p>
    </div>
</footer>

</body>
</html>
