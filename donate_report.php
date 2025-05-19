<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magdalene_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$start_date = '';
$end_date = '';
$donations = [];
$total_donations = 0;
$total_donors = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Query to fetch donations within the selected date range
    $query = "SELECT donor_name, donor_email, event_name, fee, payment_method, status, payment_date 
              FROM pay 
              WHERE type = 'Deposit' 
                AND status = 'success'
                AND DATE(payment_date) BETWEEN '$start_date' AND '$end_date'
              ORDER BY payment_date DESC";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
        $total_donations += $row['fee'];
        if (!isset($donor_names[$row['donor_name']])) {
            $total_donors++;
            $donor_names[$row['donor_name']] = true;  // Prevent counting the same donor twice
        }
    }

    // Handle download request
    if (isset($_POST['download'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="donation_report_'.$start_date.'_to_'.$end_date.'.csv"');
        $output = fopen("php://output", "w");
        fputcsv($output, ['Donor Name', 'Donor Email', 'Event Name', 'Amount', 'Payment Method', 'Status', 'Date of Donation']);
        foreach ($donations as $row) {
            fputcsv($output, [
                $row['donor_name'],
                $row['donor_email'],
                $row['event_name'],
                number_format($row['fee'], 2),
                $row['payment_method'],
                ucfirst($row['status']),
                date('d M Y', strtotime($row['payment_date']))
            ]);
        }
        fclose($output);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h2 {
            color: #333;
        }
        form {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        label {
            margin-right: 10px;
        }
        input[type="date"] {
            padding: 6px;
            margin-right: 10px;
        }
        button {
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            color: #fff;
            cursor: pointer;
        }
        .filter-btn {
            background-color: #007bff;
        }
        .download-btn {
            background-color: #28a745;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #eee;
        }
        .summary {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
        }
        .chart-container {
            width: 50%;
            margin: auto;
        }
    </style>
</head>
<body>

<h2>Donation Report</h2>

<form method="POST">
    <label>From: <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required></label>
    <label>To: <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required></label>
    <button class="filter-btn" type="submit" name="filter">Filter</button>

    <?php if (!empty($donations)): ?>
        <button class="download-btn" type="submit" name="download">Download CSV</button>
    <?php endif; ?>
</form>

<?php if (!empty($donations)): ?>
    <div class="summary">
        <p>Total Donations: MK <?= number_format($total_donations, 2) ?></p>
        <p>Total Donors: <?= $total_donors ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Donor Name</th>
                <th>Donor Email</th>
                <th>Event</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($donations as $donation): ?>
                <tr>
                    <td><?= htmlspecialchars($donation['donor_name']) ?></td>
                    <td><?= htmlspecialchars($donation['donor_email']) ?></td>
                    <td><?= htmlspecialchars($donation['event_name']) ?></td>
                    <td>MK <?= number_format($donation['fee'], 2) ?></td>
                    <td><?= $donation['payment_method'] ?></td>
                    <td><?= ucfirst($donation['status']) ?></td>
                    <td><?= date('d M Y', strtotime($donation['payment_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Chart: Donation Method Distribution -->
    <div class="chart-container">
        <canvas id="donationChart"></canvas>
    </div>

    <script>
        const donationData = <?php echo json_encode($donations); ?>;
        
        // Calculate donations by method
        const donationMethods = donationData.reduce((acc, donation) => {
            acc[donation.payment_method] = (acc[donation.payment_method] || 0) + parseFloat(donation.fee);
            return acc;
        }, {});

        const methods = Object.keys(donationMethods);
        const amounts = Object.values(donationMethods);

        const ctx = document.getElementById('donationChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: methods,
                datasets: [{
                    label: 'Donation Distribution by Payment Method',
                    data: amounts,
                    backgroundColor: ['#ff6384', '#36a2eb'],
                    hoverOffset: 4
                }]
            }
        });
    </script>

<?php elseif ($start_date && $end_date): ?>
    <p>No donations found between <?= htmlspecialchars($start_date) ?> and <?= htmlspecialchars($end_date) ?>.</p>
<?php endif; ?>

</body>
</html>
