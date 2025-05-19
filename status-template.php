<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "magdalene_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Store transaction in database if payment is successful and $paymentInfo exists
if (isset($paymentInfo) && $status === 'successful') {
    $sql = "INSERT INTO transactions (
        transaction_id,
        charge_id,
        amount,
        status,
        provider,
        phone,
        customer_fname,
        customer_lname,
        customer_email,
        reference,
        created_date,
        completed_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssss",
        $paymentInfo['transactionId'],
        $paymentInfo['chargeId'],
        $paymentInfo['amount'],
        $paymentInfo['status'],
        $paymentInfo['provider'],
        $paymentInfo['phone'],
        $paymentInfo['customer']['firstName'],
        $paymentInfo['customer']['lastName'],
        $paymentInfo['customer']['email'],
        $paymentInfo['reference'],
        $paymentInfo['created'],
        $paymentInfo['completed']
    );

    if (!$stmt->execute()) {
        error_log("Failed to store transaction: " . $stmt->error);
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <style>
        body { font-family: system-ui; max-width: 800px; margin: 40px auto; padding: 20px; }
        pre { background: #f3f4f6; padding: 15px; border-radius: 6px; overflow-x: auto; }
        .status { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .success { background: #ecfdf5; color: #047857; }
        .error { background: #fef2f2; color: #b91c1c; }
        .pending { background: #fffbeb; color: #92400e; }
        .transaction-details { background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .transaction-details dl { display: grid; grid-template-columns: 150px 1fr; gap: 10px; margin: 0; }
        .transaction-details dt { font-weight: bold; }
        .transaction-details dd { margin: 0; }
    </style>
    <?php if ($status === 'pending'): ?>
    <script>
        setTimeout(() => window.location.reload(), 5000);
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="status <?php echo $status === 'successful' ? 'success' : ($status === 'pending' ? 'pending' : 'error'); ?>">
        <h2>Payment Status: <?php echo ucfirst($status); ?></h2>
        <p><?php echo $message; ?></p>
    </div>

    <?php if ($paymentInfo): ?>
    <div class="transaction-details">
        <h3>Transaction Details:</h3>
        <dl>
            <dt>Amount:</dt>
            <dd><?php echo htmlspecialchars($paymentInfo['amount']); ?></dd>

            <dt>Provider:</dt>
            <dd><?php echo htmlspecialchars($paymentInfo['provider']); ?></dd>

            <dt>Phone:</dt>
            <dd><?php echo htmlspecialchars($paymentInfo['phone']); ?></dd>

            <dt>Customer Name:</dt>
            <dd><?php echo htmlspecialchars($paymentInfo['customer']['firstName'] . ' ' . $paymentInfo['customer']['lastName']); ?></dd>

            <dt>Email:</dt>
            <dd><?php echo htmlspecialchars($paymentInfo['customer']['email']); ?></dd>

            <dt>Created:</dt>
            <dd><?php echo (new DateTime($paymentInfo['created']))->format('Y-m-d H:i:s'); ?></dd>

            <dt>Completed:</dt>
            <dd><?php echo (new DateTime($paymentInfo['completed']))->format('Y-m-d H:i:s'); ?></dd>

            <dt>Reference:</dt>
            <dd><?php echo htmlspecialchars($paymentInfo['reference']); ?></dd>
        </dl>
    </div>

    <details>
        <summary>Raw Transaction Data</summary>
        <pre><?php echo json_encode($paymentInfo, JSON_PRETTY_PRINT); ?></pre>
    </details>
    <?php endif; ?>

    <p><a href="index.php">Return</a></p>
</body>
</html>