<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = filter_var($_POST['to'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));
    $message_id = intval($_POST['message_id']);

    if (!empty($to) && !empty($message) && $message_id > 0) {
        // Prepare the email
        $subject = 'Reply from Magdalene Management';
        $htmlBody = '<h3>Reply from Magdalene Management</h3>' .
                    '<p>Dear recipient,</p>' .
                    '<p>We have responded to your message. Please find our reply below:</p>' .
                    '<blockquote style="color:blue;">' . nl2br($message) . '</blockquote>' .
                    '<p>Thank you for reaching out to us.</p>' .
                    '<p>Best regards,<br>Magdalene Management Team</p>';

        $mail = new PHPMailer(true);
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cen-01-42-21@unilia.ac.mw'; // Replace with your Gmail address
            $mail->Password = 'ksvh sety oqcv cgyv'; // Replace with your Gmail App Password
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Email settings
            $mail->setFrom('cen-01-42-21@unilia.ac.mw', 'Magdalene Management');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            // Send the email
            $mail->send();

            // Update message status to 'replied'
            $updateQuery = "UPDATE contact_messages SET status = 'replied' WHERE id = $message_id";
            if ($conn->query($updateQuery) === TRUE) {
                header("Location: messages.php?success=Reply sent successfully");
            } else {
                header("Location: messages.php?error=Failed to update message status");
            }
            exit;
        } catch (Exception $e) {
            header("Location: messages.php?error=Failed to send reply: {$mail->ErrorInfo}");
            exit;
        }
    } else {
        header("Location: messages.php?error=Invalid input");
        exit;
    }
} else {
    header("Location: messages.php");
    exit;
}

mysqli_close($conn);
?>