<?php

$mysqli = include __DIR__ . "/connection.php";

$email = $_POST["email"];

$count = 0; // Initialize $count to avoid undefined behavior
do {
    $token = bin2hex(random_bytes(16));
    $token_hash = hash('sha256', $token);

    $check_query = "SELECT COUNT(*) FROM staff_records WHERE reset_token_hash = ?";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param("s", $token_hash);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();
} while ($count > 0);

$expiry = date('Y-m-d H:i:s', time() + 60 * 30);

$sqli = "UPDATE staff_records 
         SET reset_token_hash  = ?,
             reset_token_expires_at = ? 
         WHERE email = ?";

$stmt = $mysqli->prepare($sqli);

$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();


