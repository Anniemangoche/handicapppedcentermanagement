<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure graduate column exists
$check = "SHOW COLUMNS FROM child_records LIKE 'graduate'";
$result = $conn->query($check);
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE child_records ADD COLUMN graduate TINYINT DEFAULT 0");
}

// Function to calculate age from date of birth
function calculateAge($dob) {
    $today = new DateTime('2025-05-09');
    $birthDate = new DateTime($dob);
    $age = $today->diff($birthDate)->y;
    return $age;
}

// Function to categorize special needs (disabilities)
function categorizeSpecialNeeds($medical_info) {
    $medical_info = strtolower($medical_info);
    if (empty($medical_info) || strpos($medical_info, 'none') !== false) {
        return 'None';
    }
    if (strpos($medical_info, 'autism') !== false) {
        return 'Autism';
    }
    if (strpos($medical_info, 'hearing') !== false) {
        return 'Hearing Impairment';
    }
    if (strpos($medical_info, 'visual') !== false || strpos($medical_info, 'vision') !== false) {
        return 'Visual Impairment';
    }
    if (strpos($medical_info, 'physical') !== false || strpos($medical_info, 'mobility') !== false) {
        return 'Physical Impairment';
    }
    return 'Other';
}

// Function to categorize background info
function categorizeBackground($background) {
    $background = strtolower($background);
    if (strpos($background, 'orphan') !== false) {
        return 'Orphaned';
    }
    if (strpos($background, 'foster') !== false) {
        return 'Fostered';
    }
    return 'Other';
}

// Fetch statistics
$stats = [
    'gender' => ['Male' => 0, 'Female' => 0],
    'age_ranges' => ['0-5' => 0, '6-10' => 0, '11-15' => 0, '16+' => 0],
    'special_needs' => ['None' => 0, 'Autism' => 0, 'Hearing Impairment' => 0, 'Visual Impairment' => 0, 'Physical Impairment' => 0, 'Other' => 0],
    'background' => ['Orphaned' => 0, 'Fostered' => 0, 'Other' => 0],
    'graduates' => ['Graduated' => 0, 'Not Graduated' => 0]
];

// Total children
$total_query = "SELECT COUNT(*) as total FROM child_records WHERE archived = 0";
$total_result = $conn->query($total_query);
$total_children = $total_result->fetch_assoc()['total'];

// Total for gender (only Male/Female)
$total_gender_query = "SELECT COUNT(*) as total FROM child_records WHERE archived = 0 AND gender IN ('Male', 'Female')";
$total_gender_result = $conn->query($total_gender_query);
$total_gender = $total_gender_result->fetch_assoc()['total'];

// Fetch data
$query = "SELECT gender, dateofbirth, medical_info, child_backgroundinfo, graduate FROM child_records WHERE archived = 0";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    // Gender (only Male/Female)
    $gender = $row['gender'] ?? '';
    if (in_array($gender, ['Male', 'Female'])) {
        $stats['gender'][$gender]++;
    }

    // Age
    $age = calculateAge($row['dateofbirth']);
    if ($age <= 5) {
        $stats['age_ranges']['0-5']++;
    } elseif ($age <= 10) {
        $stats['age_ranges']['6-10']++;
    } elseif ($age <= 15) {
        $stats['age_ranges']['11-15']++;
    } else {
        $stats['age_ranges']['16+']++;
    }

    // Special Needs
    $special_needs = categorizeSpecialNeeds($row['medical_info']);
    $stats['special_needs'][$special_needs]++;

    // Background
    $background = categorizeBackground($row['child_backgroundinfo']);
    $stats['background'][$background]++;

    // Graduates
    $stats['graduates'][$row['graduate'] == 1 ? 'Graduated' : 'Not Graduated']++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Records Report</title>
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
        .report-actions { text-align: center; margin-bottom: 20px; }
        .report-actions button { padding: 12px 24px; background-color: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; margin: 0 10px; }
        .report-actions button:hover { background-color: var(--secondary-color); }
        #report-content { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: var(--shadow); }
        h1 { color: var(--primary-color); text-align: center; }
        h2 { color: var(--secondary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; }
        .logo { text-align: center; margin: 20px 0; }
        .logo img { max-width: 150px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: var(--primary-color); color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .chart-container { margin: 20px 0; text-align: center; }
        .chart-container canvas { max-width: 400px; margin: 0 auto; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 4px; color: white; z-index: 1001; animation: fadeOut 5s forwards; }
        .notification.success { background-color: var(--success-color); }
        .notification.error { background-color: var(--error-color); }
        @keyframes fadeOut { 0% { opacity: 1; } 80% { opacity: 1; } 100% { opacity: 0; display: none; } }
        @media print {
            .sidebar, .topbar, .report-actions, .notification { display: none; }
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
                <li><a href="child_report.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Child Records Report</h1>
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

            <div class="report-actions">
                <button onclick="printReport()">Print Report</button>
                <button onclick="window.location.href='reports.php'">Back to Reports</button>
            </div>

            <div id="report-content">
                <div class="logo">
                    <img src="images/logo.png" alt="Magdalene Logo">
                </div>
                <h1>Child Records Statistical Report</h1>
                <p style="text-align: center; color: #555;">Generated on May 09, 2025 | Total Children: <?php echo $total_children; ?></p>

                <h2>Gender Distribution</h2>
                <table>
                    <tr><th>Gender</th><th>Count</th><th>Percentage</th></tr>
                    <?php foreach ($stats['gender'] as $gender => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($gender); ?></td>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $total_gender > 0 ? number_format(($count / $total_gender) * 100, 1) : 0; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="chart-container">
                    <canvas id="genderChart"></canvas>
                </div>

                <h2>Age Distribution</h2>
                <table>
                    <tr><th>Age Range</th><th>Count</th><th>Percentage</th></tr>
                    <?php foreach ($stats['age_ranges'] as $range => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($range); ?></td>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $total_children > 0 ? number_format(($count / $total_children) * 100, 1) : 0; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="chart-container">
                    <canvas id="ageChart"></canvas>
                </div>

                <h2>Special Needs</h2>
                <table>
                    <tr><th>Category</th><th>Count</th><th>Percentage</th></tr>
                    <?php foreach ($stats['special_needs'] as $category => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category); ?></td>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $total_children > 0 ? number_format(($count / $total_children) * 100, 1) : 0; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="chart-container">
                    <canvas id="specialNeedsChart"></canvas>
                </div>

                <h2>Background Information</h2>
                <table>
                    <tr><th>Category</th><th>Count</th><th>Percentage</th></tr>
                    <?php foreach ($stats['background'] as $category => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category); ?></td>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $total_children > 0 ? number_format(($count / $total_children) * 100, 1) : 0; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <h2>Graduation Status</h2>
                <table>
                    <tr><th>Status</th><th>Count</th><th>Percentage</th></tr>
                    <?php foreach ($stats['graduates'] as $status => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($status); ?></td>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $total_children > 0 ? number_format(($count / $total_children) * 100, 1) : 0; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="chart-container">
                    <canvas id="graduatesChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Gender Pie Chart
        new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($stats['gender'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($stats['gender'])); ?>,
                    backgroundColor: ['#0000FF', '#FF69B4']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Age Bar Chart
        new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($stats['age_ranges'])); ?>,
                datasets: [{
                    label: 'Number of Children',
                    data: <?php echo json_encode(array_values($stats['age_ranges'])); ?>,
                    backgroundColor: '#926c54'
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Count' } } }
            }
        });

        // Special Needs Bar Chart
        new Chart(document.getElementById('specialNeedsChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($stats['special_needs'])); ?>,
                datasets: [{
                    label: 'Number of Children',
                    data: <?php echo json_encode(array_values($stats['special_needs'])); ?>,
                    backgroundColor: '#7a5b47'
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Count' } } }
            }
        });

        // Graduates Pie Chart
        new Chart(document.getElementById('graduatesChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($stats['graduates'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($stats['graduates'])); ?>,
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        function printReport() {
            window.print();
        }
    </script>
</body>
</html>