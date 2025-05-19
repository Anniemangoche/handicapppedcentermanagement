<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'magdalene_management';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the "archived" column exists in the "inventory_records" table
$check_column = "SHOW COLUMNS FROM inventory_records LIKE 'archived'";
$result = $conn->query($check_column);
if ($result->num_rows == 0) {
    $add_column = "ALTER TABLE inventory_records ADD COLUMN archived TINYINT DEFAULT 0";
    $conn->query($add_column);
}

// Ensure the "initial_quantity" column exists in the "inventory_records" table
$check_column = "SHOW COLUMNS FROM inventory_records LIKE 'initial_quantity'";
$result = $conn->query($check_column);
if ($result->num_rows == 0) {
    $add_column = "ALTER TABLE inventory_records ADD COLUMN initial_quantity INT AFTER quantity";
    $conn->query($add_column);
}

// Create inventory_usage_log table if it doesn't exist
$check_table = "SHOW TABLES LIKE 'inventory_usage_log'";
$result = $conn->query($check_table);
if ($result->num_rows == 0) {
    $create_table = "CREATE TABLE inventory_usage_log (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        inventory_id INT NOT NULL,
        previous_quantity INT NOT NULL,
        new_quantity INT NOT NULL,
        usage_purpose TEXT NOT NULL,
        log_date DATETIME NOT NULL,
        user_id INT,
        FOREIGN KEY (inventory_id) REFERENCES inventory_records(inventory_id)
    )";
    $conn->query($create_table);
}

// Handle Add Inventory Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_add'])) {
    $item_name = trim($_POST['item_name']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $date_updated = date('Y-m-d H:i:s');

    // Server-side validation
    $errors = [];
    if (empty($item_name) || !preg_match('/^[a-zA-Z0-9\s-]+$/', $item_name)) {
        $errors[] = "Item name is required and must contain only letters, numbers, spaces, or hyphens.";
    }
    if (empty($category) || !preg_match('/^[a-zA-Z0-9\s-]+$/', $category)) {
        $errors[] = "Category is required and must contain only letters, numbers, spaces, or hyphens.";
    }
    if ($quantity <= 0) {
        $errors[] = "Quantity must be a positive number.";
    }

    if (empty($errors)) {
        $query = "INSERT INTO inventory_records (item_name, category, quantity, initial_quantity, stock_status, date_updated, archived) 
                  VALUES (?, ?, ?, ?, 'active', ?, 0)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiis", $item_name, $category, $quantity, $quantity, $date_updated);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Inventory item added successfully!";
        } else {
            $_SESSION['error'] = "Error adding inventory item: " . $stmt->error;
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Handle Edit Inventory Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit'])) {
    $inventory_id = (int)$_POST['inventory_id'];
    $item_name = trim($_POST['item_name']);
    $category = trim($_POST['category']);
    $new_quantity = (int)$_POST['quantity'];
    $date_updated = date('Y-m-d H:i:s');

    // Server-side validation
    $errors = [];
    if (empty($item_name) || !preg_match('/^[a-zA-Z0-9\s-]+$/', $item_name)) {
        $errors[] = "Item name is required and must contain only letters, numbers, spaces, or hyphens.";
    }
    if (empty($category) || !preg_match('/^[a-zA-Z0-9\s-]+$/', $category)) {
        $errors[] = "Category is required and must contain only letters, numbers, spaces, or hyphens.";
    }
    if ($new_quantity < 0) {
        $errors[] = "Quantity cannot be negative.";
    }

    // Get the current quantity
    $get_current = "SELECT quantity, initial_quantity FROM inventory_records WHERE inventory_id = ?";
    $stmt = $conn->prepare($get_current);
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $current_result = $stmt->get_result();
    $current_data = $current_result->fetch_assoc();
    $previous_quantity = $current_data['quantity'];
    $initial_quantity = $current_data['initial_quantity'] ?: $previous_quantity;

    // If quantity decreased, require usage information
    if ($new_quantity < $previous_quantity) {
        $usage_purpose = trim($_POST['usage_purpose'] ?? '');
        if (empty($usage_purpose) || strlen($usage_purpose) < 5) {
            $errors[] = "Usage purpose is required and must be at least 5 characters when reducing quantity.";
        }
    }

    if (empty($errors)) {
        if ($new_quantity < $previous_quantity && isset($_POST['usage_purpose'])) {
            $usage_purpose = $_POST['usage_purpose'];
            
            // Log the usage
            $log_query = "INSERT INTO inventory_usage_log (inventory_id, previous_quantity, new_quantity, usage_purpose, log_date, user_id) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_query);
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
            $log_stmt->bind_param("iiissi", $inventory_id, $previous_quantity, $new_quantity, $usage_purpose, $date_updated, $user_id);
            $log_stmt->execute();
            
            // Update the inventory record
            $query = "UPDATE inventory_records SET item_name = ?, category = ?, quantity = ?, initial_quantity = ?, date_updated = ? 
                    WHERE inventory_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssiisi", $item_name, $category, $new_quantity, $initial_quantity, $date_updated, $inventory_id);
            
            if ($stmt->execute()) {
                $_SESSION['notification'] = "Inventory item updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating inventory item: " . $stmt->error;
            }
        } 
        // If quantity increased or remained the same
        else if ($new_quantity >= $previous_quantity) {
            $query = "UPDATE inventory_records SET item_name = ?, category = ?, quantity = ?, initial_quantity = ?, date_updated = ? 
                    WHERE inventory_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssiisi", $item_name, $category, $new_quantity, $initial_quantity, $date_updated, $inventory_id);
            
            if ($stmt->execute()) {
                $_SESSION['notification'] = "Inventory item updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating inventory item: " . $stmt->error;
            }
        } else {
            $_SESSION['error'] = "Please provide usage purpose when reducing quantity!";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Handle Archive/Restore Actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_archive'])) {
    $inventory_id = (int)$_POST['inventory_id'];
    $query = "UPDATE inventory_records SET archived = 1 WHERE inventory_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $inventory_id);
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Inventory item archived successfully!";
    } else {
        $_SESSION['error'] = "Error archiving inventory item.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_restore'])) {
    $inventory_id = (int)$_POST['inventory_id'];
    $query = "UPDATE inventory_records SET archived = 0 WHERE inventory_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $inventory_id);
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Inventory item restored successfully!";
    } else {
        $_SESSION['error'] = "Error restoring inventory item.";
    }
}

// Determine whether to show active or archived records
$show_archived = isset($_GET['show_archived']) ? (int)$_GET['show_archived'] : 0;
$show_logs = isset($_GET['show_logs']) ? true : false;

// Show inventory records
if (!$show_logs) {
    $query = "SELECT * FROM inventory_records WHERE archived = ? ORDER BY inventory_id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $show_archived);
    $stmt->execute();
    $result = $stmt->get_result();
} 
// Show usage logs
else {
    $query = "SELECT l.*, i.item_name FROM inventory_usage_log l 
              JOIN inventory_records i ON l.inventory_id = i.inventory_id 
              ORDER BY l.log_date DESC";
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7a5b47;
            --background-color: #f8f4f1;
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --border-color: #e0e0e0;
            --highlight: #f1e4d8;
            --edit-color: #007bff;
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

        .action-buttons {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-buttons button {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .action-buttons button:hover {
            background-color: var(--secondary-color);
        }

        .action-buttons button.active {
            background-color: var(--secondary-color);
        }

        .search-container {
            margin: 20px auto;
            display: flex;
            align-items: center;
            max-width: 400px;
            position: relative;
            width: 100%;
        }

        .search-container input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            background-color: #fafafa;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .search-container input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(146, 108, 84, 0.2);
            background-color: white;
        }

        .search-container i {
            position: absolute;
            right: 15px;
            color: #777;
            font-size: 1.2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: var(--highlight);
        }

        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-popup h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .form-popup h2:after {
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

        .form-popup input,
        .form-popup textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #fafafa;
        }

        .form-popup input:focus,
        .form-popup textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(146, 108, 84, 0.2);
            background-color: white;
        }

        .form-popup textarea {
            height: 100px;
            resize: vertical;
        }

        .usage-purpose {
            display: none;
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .form-popup button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .form-popup button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
        }

        .form-popup button[type="submit"]:hover {
            background-color: var(--secondary-color);
        }

        .form-popup button[type="button"] {
            background-color: #f0f0f0;
            color: #666;
        }

        .form-popup button[type="button"]:hover {
            background-color: #e0e0e0;
        }

        .action-buttons button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Style for Edit button */
        table td button[onclick*="showEditInventoryForm"] {
            background-color: var(--edit-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            margin-right: 5px;
            transition: background-color 0.2s ease;
        }

        table td button[onclick*="showEditInventoryForm"]:hover {
            background-color: #0056b3;
        }

        /* Style for Archive and Restore buttons */
        table td button[name="submit_archive"],
        table td button[name="submit_restore"] {
            background-color: #6c757d;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        table td button[name="submit_archive"]:hover,
        table td button[name="submit_restore"]:hover {
            background-color: #5a6268;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
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

            .topbar {
                flex-wrap: wrap;
                gap: 10px;
            }

            .topbar h1 {
                font-size: 1.5rem;
            }

            table {
                font-size: 0.9rem;
            }

            table th, table td {
                padding: 10px;
            }

            table td button[onclick*="showEditInventoryForm"],
            table td button[name="submit_archive"],
            table td button[name="submit_restore"] {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .search-container {
                max-width: 100%;
                padding: 0 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Director Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="child_retrive.php"><i class="fas fa-child"></i> Child Records</a></li>
                <li><a href="auth/addstaff_retrive.php"><i class="fas fa-users"></i> Staff Management</a></li>
                <li><a href="admin_don.php"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="donated_materials.php"><i class="fas fa-box"></i> Donated Materials</a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php" class="active"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h1>Inventory Management</h1>
                <div class="user-actions">
                    <a href="profile.php"><i class="fas fa-user"></i></a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['notification'])): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($_SESSION['notification']); ?>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>
            
            <?php if (!$show_logs): ?>
            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search by item name..." onkeyup="filterTable()">
                <i class="fas fa-search"></i>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <button onclick="showAddInventoryForm()"><i class="fas fa-plus"></i> Add Inventory Item</button>
                <a href="?show_archived=0"><button class="<?php echo (!$show_logs && $show_archived == 0) ? 'active' : ''; ?>"><i class="fas fa-box"></i> Active Records</button></a>
                <a href="?show_archived=1"><button class="<?php echo (!$show_logs && $show_archived == 1) ? 'active' : ''; ?>"><i class="fas fa-archive"></i> Archived Records</button></a>
                <a href="?show_logs=1"><button class="<?php echo $show_logs ? 'active' : ''; ?>"><i class="fas fa-history"></i> Usage Logs</button></a>
            </div>

            <?php if (!$show_logs): ?>
            <!-- Inventory Records Table -->
            <table id="inventoryTable">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Current Quantity</th>
                        <th>Initial Quantity</th>
                        <th>Stock Status</th>
                        <th>Date Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['initial_quantity'] ?: $row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['stock_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_updated']); ?></td>
                            <td>
                                <button onclick="showEditInventoryForm(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                <?php if ($row['archived'] == 0): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmArchive()">
                                        <input type="hidden" name="inventory_id" value="<?php echo $row['inventory_id']; ?>">
                                        <button type="submit" name="submit_archive">Archive</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmRestore()">
                                        <input type="hidden" name="inventory_id" value="<?php echo $row['inventory_id']; ?>">
                                        <button type="submit" name="submit_restore">Restore</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <!-- Usage Logs Table -->
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Previous Quantity</th>
                        <th>New Quantity</th>
                        <th>Usage Purpose</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['previous_quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['new_quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['usage_purpose']); ?></td>
                            <td><?php echo htmlspecialchars($row['log_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </main>
    </div>

    <!-- Add Inventory Form -->
    <div class="form-popup" id="addInventoryForm">
        <h2>Add Inventory Item</h2>
        <form method="POST" id="addForm" onsubmit="return validateAddForm()">
            <div class="form-group">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" name="item_name" id="item_name" placeholder="Enter item name" required>
            </div>
            <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" placeholder="Enter category" required>
            </div>
            <div class="form-group">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" placeholder="Enter quantity" required min="1">
            </div>
            <div class="form-buttons">
                <button type="submit" name="submit_add"><i class="fas fa-plus"></i> Add Item</button>
                <button type="button" onclick="hideAddInventoryForm()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Edit Inventory Form -->
    <div class="form-popup" id="editInventoryForm">
        <h2>Edit Inventory Item</h2>
        <form method="POST" id="editForm" onsubmit="return validateEditForm()">
            <input type="hidden" name="inventory_id" id="edit_inventory_id">
            <div class="form-group">
                <label for="edit_item_name" class="form-label">Item Name</label>
                <input type="text" name="item_name" id="edit_item_name" placeholder="Enter item name" required>
            </div>
            <div class="form-group">
                <label for="edit_category" class="form-label">Category</label>
                <input type="text" name="category" id="edit_category" placeholder="Enter category" required>
            </div>
            <div class="form-group">
                <label for="edit_quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="edit_quantity" placeholder="Enter quantity" required min="0">
            </div>
            <input type="hidden" id="current_quantity">
            <div id="usage_purpose_div" class="usage-purpose">
                <label for="usage_purpose" class="form-label">Usage Purpose (Required for quantity reduction)</label>
                <textarea name="usage_purpose" id="usage_purpose" rows="4" placeholder="Explain how/why these items were used"></textarea>
            </div>
            <div class="form-buttons">
                <button type="submit" name="submit_edit"><i class="fas fa-save"></i> Update Item</button>
                <button type="button" onclick="hideEditInventoryForm()">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function showAddInventoryForm() {
            document.getElementById('addInventoryForm').style.display = 'block';
        }

        function hideAddInventoryForm() {
            document.getElementById('addInventoryForm').style.display = 'none';
        }

        function showEditInventoryForm(data) {
            document.getElementById('edit_inventory_id').value = data.inventory_id;
            document.getElementById('edit_item_name').value = data.item_name;
            document.getElementById('edit_category').value = data.category;
            document.getElementById('edit_quantity').value = data.quantity;
            document.getElementById('current_quantity').value = data.quantity;

            document.getElementById('editInventoryForm').style.display = 'block';
            document.getElementById('usage_purpose_div').style.display = 'none';
        }

        function hideEditInventoryForm() {
            document.getElementById('editInventoryForm').style.display = 'none';
        }

        // Show/hide usage purpose field based on quantity change
        document.getElementById('edit_quantity').addEventListener('input', function() {
            const newQuantity = parseInt(this.value) || 0;
            const currentQuantity = parseInt(document.getElementById('current_quantity').value) || 0;
            
            if (newQuantity < currentQuantity) {
                document.getElementById('usage_purpose_div').style.display = 'block';
                document.getElementById('usage_purpose').required = true;
            } else {
                document.getElementById('usage_purpose_div').style.display = 'none';
                document.getElementById('usage_purpose').required = false;
            }
        });

        // Client-side table filtering
        function filterTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('inventoryTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header
                const cells = rows[i].getElementsByTagName('td');
                const itemName = cells[0].textContent.toLowerCase();
                rows[i].style.display = itemName.includes(input) ? '' : 'none';
            }
        }

        // Archive confirmation
        function confirmArchive() {
            return confirm('Are you sure you want to archive this item?');
        }

        // Restore confirmation
        function confirmRestore() {
            return confirm('Are you sure you want to restore this item?');
        }

        // Validate Add Inventory Form
        function validateAddForm() {
            const itemName = document.getElementById('item_name').value.trim();
            const category = document.getElementById('category').value.trim();
            const quantity = parseInt(document.getElementById('quantity').value) || 0;

            const nameRegex = /^[a-zA-Z0-9\s-]+$/;
            let errors = [];

            if (!itemName || !nameRegex.test(itemName)) {
                errors.push('Item name is required and must contain only letters, numbers, spaces, or hyphens.');
            }
            if (!category || !nameRegex.test(category)) {
                errors.push('Category is required and must contain only letters, numbers, spaces, or hyphens.');
            }
            if (quantity <= 0) {
                errors.push('Quantity must be a positive number.');
            }

            if (errors.length > 0) {
                alert('Please fix the following errors:\n- ' + errors.join('\n- '));
                return false;
            }
            return true;
        }

        // Validate Edit Inventory Form
        function validateEditForm() {
            const itemName = document.getElementById('edit_item_name').value.trim();
            const category = document.getElementById('edit_category').value.trim();
            const quantity = parseInt(document.getElementById('edit_quantity').value) || 0;
            const currentQuantity = parseInt(document.getElementById('current_quantity').value) || 0;
            const usagePurpose = document.getElementById('usage_purpose').value.trim();

            const nameRegex = /^[a-zA-Z0-9\s-]+$/;
            let errors = [];

            if (!itemName || !nameRegex.test(itemName)) {
                errors.push('Item name is required and must contain only letters, numbers, spaces, or hyphens.');
            }
            if (!category || !nameRegex.test(category)) {
                errors.push('Category is required and must contain only letters, numbers, spaces, or hyphens.');
            }
            if (quantity < 0) {
                errors.push('Quantity cannot be negative.');
            }
            if (quantity < currentQuantity && (!usagePurpose || usagePurpose.length < 5)) {
                errors.push('Usage purpose is required and must be at least 5 characters when reducing quantity.');
            }

            if (errors.length > 0) {
                alert('Please fix the following errors:\n- ' + errors.join('\n- '));
                return false;
            }
            return true;
        }
    </script>
</body>
</html>