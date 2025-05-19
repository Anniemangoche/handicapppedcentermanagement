<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magdalene_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error, 3, "debug.log");
    die("Connection failed: " . $conn->connect_error);
}

// Function to log errors
function log_error($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, "debug.log");
}

// Get current date for validation
$current_date = date('Y-m-d');

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'archive_material' && isset($_POST['material_id'])) {
            // Archive material
            $material_id = $_POST['material_id'];
            $stmt = $conn->prepare("UPDATE donated_materials SET is_archived = 1 WHERE id = ?");
            $stmt->bind_param("i", $material_id);
            
            if ($stmt->execute()) {
                echo "<script>alert('Material archived successfully!');</script>";
            } else {
                log_error("Error archiving material: " . $stmt->error);
                echo "<script>alert('Error archiving material: " . htmlspecialchars($stmt->error) . "');</script>";
            }
            $stmt->close();
        } 
        elseif ($_POST['action'] == 'restore_material' && isset($_POST['material_id'])) {
            // Restore material
            $material_id = $_POST['material_id'];
            $stmt = $conn->prepare("UPDATE donated_materials SET is_archived = 0 WHERE id = ?");
            $stmt->bind_param("i", $material_id);
            
            if ($stmt->execute()) {
                echo "<script>alert('Material restored successfully!');</script>";
            } else {
                log_error("Error restoring material: " . $stmt->error);
                echo "<script>alert('Error restoring material: " . htmlspecialchars($stmt->error) . "');</script>";
            }
            $stmt->close();
        } 
        elseif ($_POST['action'] == 'update_material' && isset($_POST['material_id'])) {
            // Update existing material
            $material_id = $_POST['material_id'];
            $donor_name = trim($_POST['donor_name']);
            $material_name = trim($_POST['material_name']);
            $quantity = (int)$_POST['quantity'];
            $description = trim($_POST['description']);
            $donation_date = $_POST['donation_date'];
            
            // Validate inputs
            if (empty($donor_name) || empty($material_name) || $quantity <= 0 || empty($description) || empty($donation_date)) {
                log_error("Validation failed: Empty or invalid fields");
                echo "<script>alert('All fields are required and quantity must be positive');</script>";
            } elseif ($donation_date > $current_date) {
                log_error("Validation failed: Donation date ($donation_date) is in the future");
                echo "<script>alert('Donation date cannot be in the future');</script>";
            } else {
                // Check if new image was uploaded
                if ($_FILES["image"]["size"] > 0) {
                    // Handle image upload
                    $target_dir = "Uploads/";
                    
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    if (!is_writable($target_dir)) {
                        log_error("Uploads directory is not writable");
                        echo "<script>alert('Error: Uploads directory is not writable');</script>";
                        exit;
                    }

                    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($file_extension, $allowed_extensions)) {
                        log_error("Invalid file extension: $file_extension");
                        echo "<script>alert('Error: Only JPG, PNG, or GIF files are allowed');</script>";
                        exit;
                    }

                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;

                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file;
                        
                        $stmt = $conn->prepare("UPDATE donated_materials SET donor_name=?, material_name=?, quantity=?, description=?, donation_date=?, image_path=? WHERE id=?");
                        $stmt->bind_param("ssisssi", $donor_name, $material_name, $quantity, $description, $donation_date, $image_path, $material_id);
                    } else {
                        log_error("Error uploading image: " . $_FILES["image"]["error"]);
                        echo "<script>alert('Error uploading image');</script>";
                        exit;
                    }
                } else {
                    // No new image, keep the existing one
                    $stmt = $conn->prepare("UPDATE donated_materials SET donor_name=?, material_name=?, quantity=?, description=?, donation_date=? WHERE id=?");
                    $stmt->bind_param("ssissi", $donor_name, $material_name, $quantity, $description, $donation_date, $material_id);
                }

                if ($stmt->execute()) {
                    echo "<script>alert('Material updated successfully!');</script>";
                } else {
                    log_error("Error updating material: " . $stmt->error);
                    echo "<script>alert('Error updating material: " . htmlspecialchars($stmt->error) . "');</script>";
                }
                $stmt->close();
            }
        }
    } else {
        // Add new material
        $donor_name = trim($_POST['donor_name']);
        $material_name = trim($_POST['material_name']);
        $quantity = (int)$_POST['quantity'];
        $description = trim($_POST['description']);
        $donation_date = $_POST['donation_date'];
        
        // Validate inputs
        if (empty($donor_name) || empty($material_name) || $quantity <= 0 || empty($description) || empty($donation_date)) {
            log_error("Validation failed: Empty or invalid fields");
            echo "<script>alert('All fields are required and quantity must be positive');</script>";
        } elseif ($donation_date > $current_date) {
            log_error("Validation failed: Donation date ($donation_date) is in the future");
            echo "<script>alert('Donation date cannot be in the future');</script>";
        } else {
            $image_path = null;
            if ($_FILES["image"]["size"] > 0) {
                // Handle image upload
                $target_dir = "Uploads/";
                
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (!is_writable($target_dir)) {
                    log_error("Uploads directory is not writable");
                    echo "<script>alert('Error: Uploads directory is not writable');</script>";
                    exit;
                }

                $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_extension, $allowed_extensions)) {
                    log_error("Invalid file extension: $file_extension");
                    echo "<script>alert('Error: Only JPG, PNG, or GIF files are allowed');</script>";
                    exit;
                }

                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;

                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    log_error("Error uploading image: " . $_FILES["image"]["error"]);
                    echo "<script>alert('Error uploading image');</script>";
                    exit;
                }
                $image_path = $target_file;
            }

            $stmt = $conn->prepare("INSERT INTO donated_materials (donor_name, material_name, quantity, description, donation_date, image_path, is_archived) VALUES (?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("ssisss", $donor_name, $material_name, $quantity, $description, $donation_date, $image_path);

            if ($stmt->execute()) {
                echo "<script>alert('Material donation added successfully!');</script>";
            } else {
                log_error("Error adding material: " . $stmt->error);
                echo "<script>alert('Error adding material: " . htmlspecialchars($stmt->error) . "');</script>";
            }
            $stmt->close();
        }
    }
}

// Check if we're viewing archived materials
$show_archived = isset($_GET['archived']) && $_GET['archived'] == 1;

// Pagination
$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Count total records for pagination
$total_records_query = "SELECT COUNT(*) as total FROM donated_materials WHERE is_archived = " . ($show_archived ? "1" : "0");
$result_count = $conn->query($total_records_query);
if (!$result_count) {
    log_error("Error counting records: " . $conn->error);
    die("Error counting records: " . $conn->error);
}
$total_records = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get materials with pagination
$query = "SELECT * FROM donated_materials WHERE is_archived = " . ($show_archived ? "1" : "0") . " ORDER BY donation_date DESC LIMIT $offset, $records_per_page";
$result = $conn->query($query);
if (!$result) {
    log_error("Error fetching materials: " . $conn->error);
    die("Error fetching materials: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Donations - Magdalene Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7a5b47;
            --accent-color: #e74c3c;
            --background-color: #f8f4f1;
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --border-color: #e0e0e0;
            --highlight: #f1e4d8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            transition: transform 0.3s ease;
            z-index: 100;
        }

        .sidebar h2 {
            padding: 20px;
            font-size: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            color: white;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: var(--secondary-color);
            border-radius: 4px;
            margin: 0 10px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .topbar .user-actions {
            display: flex;
            gap: 15px;
        }

        .topbar .user-actions a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }

        .topbar .user-actions a:hover {
            color: var(--secondary-color);
        }

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary-color);
        }

        .content-columns {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }

        .column {
            flex: 1;
        }

        .form-container {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .form-container h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #fafafa;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(146, 108, 84, 0.2);
            background-color: white;
        }

        input[type="file"] {
            background-color: #f9f9f9;
            border: 1px dashed var(--border-color);
            padding: 15px;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.2s;
        }

        input[type="file"]::file-selector-button:hover {
            background-color: var(--secondary-color);
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button[type="submit"]:hover {
            background-color: var(--secondary-color);
        }

        .materials-container {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .materials-container h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .materials-container h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .material-card {
            background-color: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .material-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .material-content {
            display: flex;
        }

        .material-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

        .material-details {
            padding: 15px;
            flex: 1;
        }

        .material-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .material-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            color: #666;
            font-size: 0.9rem;
        }

        .material-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .material-description {
            font-size: 0.95rem;
            margin-bottom: 10px;
            color: #555;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .material-quantity {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .material-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-edit {
            background-color: #e9f0fd;
            color: #3b7ddd;
        }

        .btn-edit:hover {
            background-color: #d6e4fc;
        }

        .btn-archive {
            background-color: #fde9e9;
            color: #dd3b3b;
        }

        .btn-archive:hover {
            background-color: #fcdada;
        }

        .btn-restore {
            background-color: #e9f9f0;
            color: #2ecc71;
        }

        .btn-restore:hover {
            background-color: #d1f2e0;
        }

        .archive-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        .archive-toggle button {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .archive-toggle .active-toggle {
            background-color: var(--primary-color);
            color: white;
        }

        .archive-toggle .inactive-toggle {
            background-color: #f0f0f0;
            color: #666;
        }

        .archive-toggle .inactive-toggle:hover {
            background-color: #e0e0e0;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }

        .pagination a, .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .pagination a {
            background-color: white;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .pagination a:hover {
            background-color: var(--highlight);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination .active {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }

        .pagination .disabled {
            background-color: #f5f5f5;
            color: #aaa;
            cursor: not-allowed;
            border: 1px solid #e0e0e0;
        }

        .no-materials {
            text-align: center;
            padding: 40px 0;
            color: #777;
        }

        .no-materials i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
            display: block;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #777;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-image {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
            max-height: 300px;
            object-fit: cover;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        @media (max-width: 992px) {
            .content-columns {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
            }

            .topbar {
                flex-wrap: wrap;
                gap: 10px;
            }

            .topbar h1 {
                font-size: 1.5rem;
            }

            .material-content {
                flex-direction: column;
            }

            .material-image {
                width: 100%;
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>Director Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="child_retrive.php"><i class="fas fa-child"></i> Child Records</a></li>
                <li><a href="auth/addstaff_retrive.php"><i class="fas fa-users"></i> Staff Management</a></li>
                <li><a href="admin_don.php"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="donated_materials.php" class="active"><i class="fas fa-box"></i> Donated Materials</a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>Material Donations</h1>
                <div class="user-actions">
                    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="content-columns">
                <!-- Form Column -->
                <div class="column">
                    <div class="form-container">
                        <h2>Add New Material Donation</h2>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="form-label" for="donor_name">Donor Name</label>
                                <input type="text" id="donor_name" name="donor_name" placeholder="Enter donor's name" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="material_name">Material Name</label>
                                <input type="text" id="material_name" name="material_name" placeholder="e.g., Clothes, Books" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" min="1" placeholder="Enter quantity" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" placeholder="Describe the donated materials" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="donation_date">Donation Date</label>
                                <input type="date" id="donation_date" name="donation_date" max="<?php echo $current_date; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="image">Material Image (Optional)</label>
                                <input type="file" id="image" name="image" accept="image/*">
                            </div>
                            
                            <button type="submit"><i class="fas fa-plus-circle"></i> Add Donation</button>
                        </form>
                    </div>
                </div>
                
                <!-- Materials List Column -->
                <div class="column">
                    <div class="materials-container">
                        <h2><?php echo $show_archived ? 'Archived Donations' : 'Active Donations'; ?></h2>
                        
                        <!-- Archive toggle buttons -->
                        <div class="archive-toggle">
                            <button class="<?php echo !$show_archived ? 'active-toggle' : 'inactive-toggle'; ?>" 
                                    onclick="window.location.href='?archived=0'">
                                Active Donations
                            </button>
                            <button class="<?php echo $show_archived ? 'active-toggle' : 'inactive-toggle'; ?>" 
                                    onclick="window.location.href='?archived=1'">
                                Archived Donations
                            </button>
                        </div>
                        
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($material = $result->fetch_assoc()): ?>
                                <div class="material-card">
                                    <div class="material-content">
                                        <img src="<?php echo htmlspecialchars($material['image_path'] ? $material['image_path'] : 'images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($material['material_name']); ?>" class="material-image">
                                        <div class="material-details">
                                            <h3 class="material-name"><?php echo htmlspecialchars($material['material_name']); ?></h3>
                                            <div class="material-meta">
                                                <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($material['donation_date'])); ?></span>
                                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($material['donor_name']); ?></span>
                                            </div>
                                            <p class="material-description"><?php echo htmlspecialchars($material['description']); ?></p>
                                            <p class="material-quantity"><i class="fas fa-box"></i> Quantity: <?php echo htmlspecialchars($material['quantity']); ?></p>
                                            <div class="material-actions">
                                                <button class="btn btn-edit edit-material" data-id="<?php echo $material['id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <?php if($show_archived): ?>
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to restore this donation?');">
                                                        <input type="hidden" name="action" value="restore_material">
                                                        <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                                        <button type="submit" class="btn btn-restore">
                                                            <i class="fas fa-undo"></i> Restore
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to archive this donation?');">
                                                        <input type="hidden" name="action" value="archive_material">
                                                        <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                                        <button type="submit" class="btn btn-archive">
                                                            <i class="fas fa-archive"></i> Archive
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <!-- Pagination -->
                            <div class="pagination">
                                <?php if($page > 1): ?>
                                    <a href="?page=1&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-double-left"></i></a>
                                    <a href="?page=<?php echo $page-1; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-left"></i></a>
                                <?php else: ?>
                                    <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                                    <span class="disabled"><i class="fas fa-angle-left"></i></span>
                                <?php endif; ?>
                                
                                <?php 
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for($i = $start_page; $i <= $end_page; $i++): 
                                ?>
                                    <?php if($i == $page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page+1; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-right"></i></a>
                                    <a href="?page=<?php echo $total_pages; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-double-right"></i></a>
                                <?php else: ?>
                                    <span class="disabled"><i class="fas fa-angle-right"></i></span>
                                    <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                                <?php endif; ?>
                            </div>
                            
                        <?php else: ?>
                            <div class="no-materials">
                                <i class="fas fa-box-open"></i>
                                <p>No <?php echo $show_archived ? 'archived' : 'active'; ?> material donations found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal for material editing -->
    <div class="modal-backdrop" id="materialModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Material Donation</h3>
                <button class="modal-close" id="closeModal">×</button>
            </div>
            <form id="editMaterialForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body" id="modalContent">
                    <input type="hidden" name="action" value="update_material">
                    <input type="hidden" name="material_id" id="editMaterialId">
                    
                    <img id="currentMaterialImage" src="" alt="Current Material Image" class="modal-image">
                    
                    <div class="form-group">
                        <label class="form-label" for="editDonorName">Donor Name</label>
                        <input type="text" id="editDonorName" name="donor_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editMaterialName">Material Name</label>
                        <input type="text" id="editMaterialName" name="material_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editQuantity">Quantity</label>
                        <input type="number" id="editQuantity" name="quantity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editDonationDate">Donation Date</label>
                        <input type="date" id="editDonationDate" name="donation_date" max="<?php echo $current_date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editImage">Change Image (Leave blank to keep current)</label>
                        <input type="file" id="editImage" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-archive" id="closeModalBtn">Cancel</button>
                    <button type="submit" class="btn btn-edit"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Material edit modal functionality
        const modal = document.getElementById('materialModal');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const editMaterialForm = document.getElementById('editMaterialForm');
        const editButtons = document.querySelectorAll('.edit-material');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const materialId = this.getAttribute('data-id');
                
                // Get the parent material card data
                const card = this.closest('.material-card');
                const materialName = card.querySelector('.material-name').textContent;
                const donorName = card.querySelector('.material-meta span:nth-child(2)').textContent.replace('', '');
                const dateText = card.querySelector('.material-meta span:nth-child(1)').textContent.replace('', '');
                const description = card.querySelector('.material-description').textContent;
                const quantity = card.querySelector('.material-quantity').textContent.replace('Quantity:', '').trim();
                const imageSrc = card.querySelector('.material-image').src;
                
                // Convert display date to YYYY-MM-DD format
                const dateParts = dateText.split(' ');
                const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                const monthIndex = monthNames.indexOf(dateParts[0]);
                const day = dateParts[1].replace(',', '');
                const year = dateParts[2];
                const formattedDate = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${day.padStart(2, '0')}`;
                
                // Populate modal form
                document.getElementById('editMaterialId').value = materialId;
                document.getElementById('editDonorName').value = donorName;
                document.getElementById('editMaterialName').value = materialName;
                document.getElementById('editQuantity').value = quantity;
                document.getElementById('editDescription').value = description;
                document.getElementById('editDonationDate').value = formattedDate;
                document.getElementById('currentMaterialImage').src = imageSrc;
                
                // Show modal
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });

        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        // Close modal if clicked outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Handle form submission
        editMaterialForm.addEventListener('submit', function(e) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        // Client-side date validation
        const donationDateInput = document.getElementById('donation_date');
        const editDonationDateInput = document.getElementById('editDonationDate');
        const today = new Date();
        const maxDate = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD

        donationDateInput.addEventListener('change', function() {
            if (this.value > maxDate) {
                alert('Donation date cannot be in the future');
                this.value = '';
            }
        });

        editDonationDateInput.addEventListener('change', function() {
            if (this.value > maxDate) {
                alert('Donation date cannot be in the future');
                this.value = '';
            }
        });
    </script>
</body>
</html>