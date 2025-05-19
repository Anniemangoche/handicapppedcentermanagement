<?php
// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle date filtering
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] . " 00:00:00" : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] . " 23:59:59" : null;

$whereClause = '';
if ($startDate && $endDate) {
    $whereClause = "WHERE updated_at BETWEEN '$startDate' AND '$endDate'";
}

// Fetch all donations for the table
$query = "SELECT event_name, fee AS amount, donor_name, updated_at FROM pay $whereClause";
$result = $conn->query($query);
$donations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
    }
}

// Group donations by event_name for the pie chart
$queryGroup = "SELECT IFNULL(event_name, 'No Event') AS event_name, SUM(fee) AS total_fee FROM pay $whereClause GROUP BY event_name";
$resultGroup = $conn->query($queryGroup);
$groupedDonations = [];
if ($resultGroup && $resultGroup->num_rows > 0) {
    while ($row = $resultGroup->fetch_assoc()) {
        $groupedDonations[] = $row;
    }
}

// Get total donation stats
$statsQuery = "SELECT 
                COUNT(*) AS total_donations,
                COUNT(DISTINCT donor_name) AS unique_donors,
                SUM(fee) AS total_amount,
                AVG(fee) AS avg_donation,
                MAX(fee) AS largest_donation,
                MIN(fee) AS smallest_donation
              FROM pay $whereClause";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get monthly donation trend data for line chart
$monthlyQuery = "SELECT 
                    DATE_FORMAT(updated_at, '%Y-%m') AS month,
                    SUM(fee) AS monthly_total
                 FROM pay
                 $whereClause
                 GROUP BY DATE_FORMAT(updated_at, '%Y-%m')
                 ORDER BY month ASC";
$monthlyResult = $conn->query($monthlyQuery);
$monthlyData = [];
if ($monthlyResult && $monthlyResult->num_rows > 0) {
    while ($row = $monthlyResult->fetch_assoc()) {
        $monthlyData[] = $row;
    }
}

// Get top donors
$topDonorsQuery = "SELECT 
                    donor_name,
                    SUM(fee) AS total_donated,
                    COUNT(*) AS donation_count
                  FROM pay
                  $whereClause
                  GROUP BY donor_name
                  ORDER BY total_donated DESC
                  LIMIT 5";
$topDonorsResult = $conn->query($topDonorsQuery);
$topDonors = [];
if ($topDonorsResult && $topDonorsResult->num_rows > 0) {
    while ($row = $topDonorsResult->fetch_assoc()) {
        $topDonors[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - Magdalene Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Adopted CSS from the provided code */
        :root {
            --primary-color: #926c54;
            --secondary-color: #7a5b47;
            --accent-color: #e74c3c;
            --background-color: #f5f5f5;
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-color: #e0e0e0;
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
            z-index: 10;
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
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: var(--secondary-color);
            border-radius: 4px;
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
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .topbar .user-actions {
            display: flex;
            gap: 15px;
        }

        .topbar .user-actions a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            padding: 15px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-card h3 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        .top-donors {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .top-donors h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .donor-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .donor-card {
            background-color: rgba(146, 108, 84, 0.1);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .donor-card h3 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--card-bg);
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
        }

        table, th, td {
            border: 1px solid var(--border-color);
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        tr:nth-child(even) {
            background-color: rgba(146, 108, 84, 0.05);
        }

        .filter-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            background-color: var(--card-bg);
            padding: 15px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .filter-form input[type="date"],
        .filter-form button {
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .filter-form button {
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .filter-form button:hover {
            background-color: var(--secondary-color);
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: var(--primary-color);
            color: white;
            margin-top: 30px;
        }

        .section-title {
            margin: 30px 0 15px;
            color: var(--primary-color);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 8px;
        }

        .export-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .export-btn:hover {
            background-color: var(--secondary-color);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
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
                <li><a href="admin_don.php" class="active"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="donated_materials.php"><i class="fas fa-box"></i> Donated Materials</a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h1>Donations</h1>
                <div class="user-actions">
                    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <section>
                <!-- Filter Form -->
                <form class="filter-form" method="GET" action="">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                    <button type="submit">Filter</button>
                    <a href="admin_don.php" class="reset-btn" style="background-color: #7a5b47; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; cursor: pointer;">Reset Filters</a>
                </form>

                <!-- Stats Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Donations</h3>
                        <div class="value"><?php echo number_format($stats['total_donations']); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Unique Donors</h3>
                        <div class="value"><?php echo number_format($stats['unique_donors']); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Amount</h3>
                        <div class="value">MWK <?php echo number_format($stats['total_amount'], 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Average Donation</h3>
                        <div class="value">MWK <?php echo number_format($stats['avg_donation'], 2); ?></div>
                    </div>
                </div>

                <!-- Charts -->
                <h2 class="section-title">Donation Analytics</h2>
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3>Donations by Event</h3>
                        <div class="chart-container">
                            <canvas id="donationPieChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3>Monthly Donation Trends</h3>
                        <div class="chart-container">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Donors Section -->
                <div class="top-donors">
                    <h2>Top Donors</h2>
                    <div class="donor-list">
                        <?php foreach ($topDonors as $donor): ?>
                            <div class="donor-card">
                                <h3><?php echo htmlspecialchars($donor['donor_name'] ?? 'Anonymous'); ?></h3>
                                <p>Total: MWK <?php echo number_format($donor['total_donated'], 2); ?></p>
                                <p>Donations: <?php echo $donor['donation_count']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Donation Table -->
                <div class="table-header">
                    <h2 class="section-title">Donation Details</h2>
                    <button class="export-btn" onclick="exportTableToCSV('donations.csv')">
                        <i class="fas fa-download"></i> Export to CSV
                    </button>
                </div>
                <table id="donations-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Amount (Fees)</th>
                            <th>Donor Name</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donation['event_name'] ?? 'No Event'); ?></td>
                                <td>MWK <?php echo number_format($donation['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($donation['donor_name'] ?? 'Anonymous'); ?></td>
                                <td><?php echo htmlspecialchars($donation['updated_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <footer class="footer">
        © 2025 Magdalene Home for Special Needs. All Rights Reserved.
    </footer>

    <script>
        // Prepare data for the pie chart
        const eventLabels = <?php echo json_encode(array_column($groupedDonations, 'event_name')); ?>;
        const eventData = <?php echo json_encode(array_column($groupedDonations, 'total_fee')); ?>;

        // Create the pie chart
        const pieCtx = document.getElementById('donationPieChart').getContext('2d');
        const donationPieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: eventLabels,
                datasets: [{
                    data: eventData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                        '#2E86C1', '#8E44AD', '#F1948A', '#73C6B6', '#F4D03F', '#A569BD'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let value = context.raw;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = Math.round((value / total) * 100);
                                return `${context.label}: MWK ${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Monthly trend chart
        const monthlyLabels = <?php echo json_encode(array_column($monthlyData, 'month')); ?>;
        const monthlyAmounts = <?php echo json_encode(array_column($monthlyData, 'monthly_total')); ?>;

        const lineCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        const monthlyTrendChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Donations',
                    data: monthlyAmounts,
                    fill: false,
                    borderColor: '#926c54',
                    tension: 0.1,
                    backgroundColor: '#926c54'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (MWK)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        // Function to export table data to CSV
        function exportTableToCSV(filename) {
            let csv = [];
            const rows = document.querySelectorAll('#donations-table tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText;
                    text = text.replace(/"/g, '""'); // Escape double quotes
                    row.push('"' + text + '"');
                }
                
                csv.push(row.join(','));
            }
            
            // Download CSV file
            downloadCSV(csv.join('\n'), filename);
        }

        function downloadCSV(csv, filename) {
            const csvFile = new Blob([csv], {type: "text/csv"});
            const downloadLink = document.createElement("a");
            
            // Set file name
            downloadLink.download = filename;
            
            // Create a link to the file
            downloadLink.href = window.URL.createObjectURL(csvFile);
            
            // Hide download link
            downloadLink.style.display = "none";
            
            // Add the link to DOM
            document.body.appendChild(downloadLink);
            
            // Click download link
            downloadLink.click();
            
            // Clean up and remove the link
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>