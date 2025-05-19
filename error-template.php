<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body { font-family: system-ui; max-width: 800px; margin: 40px auto; padding: 20px; }
        .error { background: #fef2f2; color: #b91c1c; padding: 15px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="error">
        <h2>Error Checking Payment Status</h2>
        <p><?php echo $message; ?></p>
    </div>
    <p><a href="index.php">Try another payment</a></p>
</body>
</html>
