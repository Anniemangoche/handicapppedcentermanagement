<?php
session_start();
require_once './vendor/autoload.php';

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;

// Database connection
try {
    $conn = new mysqli("localhost", "root", "", "magdalene_management");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// PaymentProcessor class
class PaymentProcessor {
    private $baseUrl;
    private $zenpayUrl = 'https://zen-store.onrender.com'; // Replace with actual Zenpay URL
    private $client;

    public function __construct() {
        $this->baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/Magdalene-main';
        $this->client = new Client(['verify' => false]); // For development only
    }

    // Store transaction in session
    private function storeTransaction($chargeId) {
        $_SESSION['transactions'][$chargeId] = [
            'status' => 'initiated',
            'created' => date('Y-m-d H:i:s')
        ];
        $_SESSION['currentChargeId'] = $chargeId; // Store current charge ID for later use
    }

    // Initialize payment
    public function initiatePayment($data) {
        try {
            $chargeId = Uuid::uuid4()->toString();
            $this->storeTransaction($chargeId);

            // Prepare payment data
            $paymentData = [
                'amount' => $data['fees'],
                'phone' => $data['phone'],
                'operator' => $data['bank'], // e.g., 'airtel' or 'tnm'
                'firstName' => $data['fname'],
                'lastName' => $data['lname'],
                'email' => $data['email'],
                'chargeId' => $chargeId,
                'callbackUrl' => "{$this->baseUrl}/payment-complete.php?chargeId={$chargeId}",
                'returnUrl' => "{$this->baseUrl}/payment-complete.php?chargeId={$chargeId}"
            ];

            // Redirect to payment page
            $paymentUrl = $this->zenpayUrl . '/pay_ment?' . http_build_query($paymentData);
            header("Location: {$paymentUrl}");
            exit;
        } catch (Exception $e) {
            $this->displayError($e->getMessage());
        }
    }

    // Handle payment callback
    public function handleCallback($chargeId) {
        if (isset($_SESSION['transactions'][$chargeId])) {
            $_SESSION['transactions'][$chargeId]['status'] = 'completed';
            $_SESSION['currentChargeId'] = $chargeId;
            
            // Check payment status directly instead of redirecting
            return $this->checkStatus($chargeId);
        }
        $this->displayError("Invalid transaction");
    }

    // Check payment status
    public function checkStatus($chargeId) {
        try {
            $verifyUrl = "{$this->zenpayUrl}/pay_ment/api/verify/{$chargeId}";
            $response = $this->client->get($verifyUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            $status = isset($result['status']) && $result['status'] === 'success' ? 'successful' : ($result['status'] ?? 'pending');
            $message = $result['message'] ?? 'Processing payment';
            
            // Update session
            if (isset($_SESSION['transactions'][$chargeId])) {
                $_SESSION['transactions'][$chargeId]['status'] = $status;
                $_SESSION['transactions'][$chargeId]['paymentInfo'] = $result['paymentInfo'] ?? null;
            }

            $this->displayStatusPage($result);
        } catch (Exception $e) {
            $this->displayError($e->getMessage());
        }
    }

    // Display status page
    private function displayStatusPage($result) {
        $status = $result['status'] ?? 'pending';
        $message = $result['message'] ?? 'Processing payment';
        $paymentInfo = $result['paymentInfo'] ?? null;

        include 'status-template.php';
    }

    // Display error page
    private function displayError($message) {
        include 'error-template.php';
    }
}

// Main logic to handle requests
$paymentProcessor = new PaymentProcessor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle payment initiation
    $requiredFields = ['fname', 'lname', 'email', 'event_name', 'fees', 'phone', 'bank'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            die("Error: Missing required field '{$field}'");
        }
    }
    $paymentProcessor->initiatePayment($_POST);
} elseif (isset($_GET['chargeId']) && strpos($_SERVER['PHP_SELF'], 'payment-complete.php') !== false) {
    // Handle payment callback
    $paymentProcessor->handleCallback($_GET['chargeId']);
} elseif (isset($_SESSION['currentChargeId']) && strpos($_SERVER['PHP_SELF'], 'check-status.php') !== false) {
    // Check payment status
    $paymentProcessor->checkStatus($_SESSION['currentChargeId']);
} else {
    die("Error: Invalid request");
}