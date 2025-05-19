<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>forgot password</title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <form method="POST" action="sendpassword_reset.php">
        <label for="email">email</label>
        <input type="email" name="email" id="email"  required>
        <button>Send</button>

    </form>
</body>
    </html>