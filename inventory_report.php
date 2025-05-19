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

// Function to calculate inventory statistics
function calculateInventoryStats($conn) {
    $stats = [
        'total_items' => 0,
        'active_items' => 0,
        'archived_items' => 0,
        'categories' => [],
        'stock_status' => ['low' => 0, 'medium' => 0, 'high' => 0],
        'usage_history' => []
    ];
    
    // Total items count
    $query = "SELECT COUNT(*) as total FROM inventory_records";
    $result = $conn->query($query);
    $stats['total_items'] = $result->fetch_assoc()['total'];
    
    // Active/archived counts
    $query = "SELECT COUNT(*) as count, archived FROM inventory_records GROUP BY archived";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        if ($row['archived'] == 0) {
            $stats['active_items'] = $row['count'];
        } else {
            $stats['archived_items'] = $row['count'];
        }
    }
    
    // Categories
    $query = "SELECT category, COUNT(*) as count FROM inventory_records WHERE archived = 0 GROUP BY category";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $stats['categories'][$row['category']] = $row['count'];
    }
    
    // Stock status (low = <25%, medium = 25-75%, high = >75%)
    $query = "SELECT inventory_id, quantity, initial_quantity FROM inventory_records WHERE archived = 0";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $initial = $row['initial_quantity'] ?: $row['quantity'];
        if ($initial == 0) continue;
        
        $percentage = ($row['quantity'] / $initial) * 100;
        if ($percentage < 25) {
            $stats['stock_status']['low']++;
        } elseif ($percentage <= 75) {
            $stats['stock_status']['medium']++;
        } else {
            $stats['stock_status']['high']++;
        }
    }
    
    // Usage history (last 30 days)
    $query = "SELECT DATE(log_date) as date, SUM(previous_quantity - new_quantity) as used 
              FROM inventory_usage_log 
              WHERE log_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              GROUP BY DATE(log_date) 
              ORDER BY date";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $stats['usage_history'][$row['date']] = $row['used'];
    }
    
    // Top used items
    $query = "SELECT i.item_name, SUM(l.previous_quantity - l.new_quantity) as total_used 
              FROM inventory_usage_log l
              JOIN inventory_records i ON l.inventory_id = i.inventory_id
              GROUP BY l.inventory_id 
              ORDER BY total_used DESC 
              LIMIT 5";
    $result = $conn->query($query);
    $stats['top_used_items'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $stats;
}

$stats = calculateInventoryStats($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management Report</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7a5b47;
            --accent-color: #e74c3c;
            --background-color: #ffffff;
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-color: #e0e0e0;
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        body { font-family: Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4; }
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
        }
        .sidebar h2 { padding: 20px; font-size: 1.5rem; border-bottom: 1px solid rgba(204, 218, 223, 0.2); margin-bottom: 20px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin: 10px 0; }
        .sidebar ul li a { display: flex; align-items: center; padding: 12px 20px; color: white; text-decoration: none; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background-color: var(--secondary-color); border-radius: 4px; margin: 0 10px; }
        .sidebar ul li a i { margin-right: 12px; width: 20px; text-align: center; }
        .main-content { margin-left: 250px; flex: 1; padding: 30px; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .topbar h1 { font-size: 1.8rem; color: var(--primary-color); }
        .topbar .user-actions { display: flex; gap: 15px; }
        .topbar .user-actions a { color: var(--primary-color); text-decoration: none; font-size: 1.5rem; }
        .topbar .user-actions a:hover { color: var(--secondary-color); }
        #report-content { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: var(--shadow); }
        h1 { color: var(--primary-color); text-align: center; }
        h2 { color: var(--secondary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo img { max-width: 150px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: var(--primary-color); color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .chart-container { margin: 20px 0; text-align: center; }
        .chart-container canvas { max-width: 400px; margin: 0 auto; }
        .buttons { text-align: center; margin-bottom: 20px; }
        .buttons button { padding: 12px 24px; background-color: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; margin: 0 10px; }
        .buttons button:hover { background-color: var(--secondary-color); }
        .summary-card { background: #f8f4f1; border-radius: 8px; padding: 15px; margin: 15px 0; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .summary-item { flex: 1; text-align: center; padding: 10px; }
        .summary-item .value { font-size: 1.5rem; font-weight: bold; color: #7a5b47; }
        .summary-item .label { color: #666; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 4px; color: white; z-index: 1001; animation: fadeOut 5s forwards; }
        .notification.success { background-color: var(--success-color); }
        .notification.error { background-color: var(--error-color); }
        @keyframes fadeOut { 0% { opacity: 1; } 80% { opacity: 1; } 100% { opacity: 0; display: none; } }
        @media print {
            .sidebar, .topbar, .buttons, .notification { display: none; }
            .main-content { margin-left: 0; padding: 0; }
            #report-content { max-width: 100%; box-shadow: none; border-radius: 0; padding: 10px; }
            .chart-container canvas { max-width: 100%; }
            body { background: white; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="inventory_report.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Inventory Management Report</h1>
                <div class="user-actions">
                    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <?php if (isset($_SESSION['notification'])): ?>
                <div class="notification <?php echo strpos($_SESSION['notification'], 'Error') === false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($_SESSION['notification']); ?>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>

            <div class="buttons">
                <button onclick="printReport()">Print Report</button>
                <button onclick="window.location.href='reports.php'">Back to Reports</button>
            </div>

            <div id="report-content">
                <div class="logo">
                    <img src="images/logo.png" alt="Magdalene Logo">
                </div>
                <h1>Inventory Management Report</h1>
                <p style="text-align: center; color: #555;">Generated on <?php echo date('F j, Y'); ?></p>

                <div class="summary-card">
                    <div class="summary-row">
                        <div class="summary-item">
                            <div class="value"><?php echo $stats['total_items']; ?></div>
                            <div class="label">Total Items</div>
                        </div>
                        <div class="summary-item">
                            <div class="value"><?php echo $stats['active_items']; ?></div>
                            <div class="label">Active Items</div>
                        </div>
                        <div class="summary-item">
                            <div class="value"><?php echo $stats['archived_items']; ?></div>
                            <div class="label">Archived Items</div>
                        </div>
                    </div>
                </div>

                <h2>Inventory by Category</h2>
                <table>
                    <tr><th>Category</th><th>Number of Items</th></tr>
                    <?php foreach ($stats['categories'] as $category => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category); ?></td>
                            <td><?php echo $count; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>

                <h2>Stock Status</h2>
                <table>
                    <tr><th>Status</th><th>Number of Items</th><th>Description</th></tr>
                    <tr>
                        <td>Low Stock</td>
                        <td><?php echo $stats['stock_status']['low']; ?></td>
                        <td>Less than 25% of initial quantity remaining</td>
                    </tr>
                    <tr>
                        <td>Medium Stock</td>
                        <td><?php echo $stats['stock_status']['medium']; ?></td>
                        <td>25-75% of initial quantity remaining</td>
                    </tr>
                    <tr>
                        <td>High Stock</td>
                        <td><?php echo $stats['stock_status']['high']; ?></td>
                        <td>More than 75% of initial quantity remaining</td>
                    </tr>
                </table>
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>

                <h2>Top 5 Most Used Items (All Time)</h2>
                <table>
                    <tr><th>Item Name</th><th>Total Quantity Used</th></tr>
                    <?php foreach ($stats['top_used_items'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo $item['total_used']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <h2>Usage History (Last 30 Days)</h2>
                <div class="chart-container">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Category Pie Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($stats['categories'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($stats['categories'])); ?>,
                    backgroundColor: ['#0000FF', '#FF69B4', '#FFA500', '#87CEEB', '#FFB6C1']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Stock Status Pie Chart
        new Chart(document.getElementById('stockChart'), {
            type: 'pie',
            data: {
                labels: ['Low Stock', 'Medium Stock', 'High Stock'],
                datasets: [{
                    data: <?php echo json_encode(array_values($stats['stock_status'])); ?>,
                    backgroundColor: ['#0000FF', '#FF69B4', '#FFA500']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Usage History Line Chart
        const usageDates = <?php echo json_encode(array_keys($stats['usage_history'])); ?>;
        const usageData = <?php echo json_encode(array_values($stats['usage_history'])); ?>;
        
        new Chart(document.getElementById('usageChart'), {
            type: 'line',
            data: {
                labels: usageDates,
                datasets: [{
                    label: 'Items Used',
                    data: usageData,
                    borderColor: '#926c54',
                    backgroundColor: 'rgba(146, 108, 84, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Quantity Used' }
                    },
                    x: {
                        title: { display: true, text: 'Date' }
                    }
                }
            }
        });

        function printReport() {
            window.print();
        }
    </script>
</body>
</html>