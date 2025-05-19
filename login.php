<?php
// Start session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
// $_SESSION['loggedin'] = true;
// $_SESSION['role'] = 'admin'; 

// Define variables for error and success messages
$error_message = '';
$success_message = '';

// Check if there's a registration success message
if (isset($_SESSION['registration_success'])) {
    $success_message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']); // Clear the message after displaying
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $database = "magdalene_management";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate form data
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        // Check if email exists in staff_records
        $query = "SELECT * FROM staff_records WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, now we need to check password
            $user = $result->fetch_assoc();
            
            // Check if the password field exists in the user record
            if (isset($user['password']) && !empty($user['password'])) {
                // Check if the password is already hashed (starts with $)
                if (strpos($user['password'], '$2y$') === 0) {
                    // Password is hashed with bcrypt, use password_verify
                    if (password_verify($password, $user['password'])) {
                        // Password is correct, set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'] ?? $user['email'];
                        $_SESSION['role'] = $user['role'] ?? 'user';
                        $_SESSION['email'] = $user['email'];
                        
                        // Redirect based on role
                        if ($_SESSION['role'] == 'admin') {
                            header("Location: admin.php");
                        } elseif ($_SESSION['role'] == 'caregiver' || $_SESSION['role'] == 'volunteer') {
                            header("Location: volunteer_staff.php");
                        } elseif ($_SESSION['role'] == 'donor') {
                            header("Location: user_dashboard.php");
                        } else {
                            // Default destination if role doesn't match any specific dashboard
                            header("Location: donations.php");
                        }
                        exit();
                    } else {
                        $error_message = "Invalid password.";
                    }
                } else {
                    // Password might be stored as plaintext or using another hashing method
                    // WARNING: This is a temporary solution for migration purposes
                    if ($password === $user['password']) {
                        // Plaintext password matches - consider updating to a hashed version
                        
                        // Optionally update to hashed password
                        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        // $update_stmt = $conn->prepare("UPDATE staff_records SET password = ? WHERE id = ?");
                        // $update_stmt->bind_param("si", $hashed_password, $user['id']);
                        // $update_stmt->execute();
                        // $update_stmt->close();
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'] ?? $user['email'];
                        $_SESSION['role'] = $user['role'] ?? 'user';
                        $_SESSION['email'] = $user['email'];
                        
                        // Redirect based on role
                        if ($_SESSION['role'] == 'Admin') {
                            header("Location: admin.php");
                        } elseif ($_SESSION['role'] == 'caregiver' || $_SESSION['role'] == 'volunteer') {
                            header("Location: volunteer_staff.php");
                        } elseif ($_SESSION['role'] == 'donor') {
                            header("Location: user_dashboard.php");
                        } else {
                            // Default destination if role doesn't match any specific dashboard
                            header("Location: donations.php");
                        }
                        exit();
                    } else {
                        $error_message = "Invalid password.";
                    }
                }
            } else {
                $error_message = "Password field not found in database. Please contact the administrator.";
            }
        } else {
            $error_message = "No account found with this email.";
        }
        
        $stmt->close();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Magdalene Home for Special Needs</title>
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

        .auth-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: var(--white);
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 50px;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            display: block;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            transition: border-color 0.3s;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .text-center {
            text-align: center;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        footer {
            background-color: var(--dark-color);
            color: var(--white);
            text-align: center;
            padding: 30px 20px;
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

        .auth-links {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .auth-links a:hover {
            color: var(--accent-color);
            text-decoration: underline;
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
    <div class="auth-container">
        <h2>Login to Your Account</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password *</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
            
            <div class="auth-links">
                <a href="auth/signup.php">Create an Account</a>
                <a href="auth/forgot_pass.php">Forgot password</a>
            </div>
        </form>
    </div>
</main>

<footer>
    </div>
    <div class="copyright">
        <p>© 2025 Magdalene Home for Special Needs. All Rights Reserved.</p>
        <p>A sanctuary of hope and love for children with special needs</p>
    </div>
</footer>

</body>
</html>