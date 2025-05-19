<?php
session_start();

// Check if confirmation parameter exists and is set to true
if (isset($_GET['confirm']) && $_GET['confirm'] == 'true') {
    // Perform logout actions
    session_unset();     // Clear all session variables
    session_destroy();   // Destroy the session
    
    // Redirect to index.php
    header("Location: index.php");
    exit();
} else {
    // Get the referring page (where the user came from)
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    
    // Display the confirmation page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        p {
            color: #666;
            margin-bottom: 25px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #dc3545;
            color: white;
        }
        .btn-primary:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Logout Confirmation</h2>
        <p>Are you sure you want to logout from your account?</p>
        <div>
            <a href="logout.php?confirm=true" class="btn btn-primary">Yes, Logout</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</body>
</html>
<?php
}
?>