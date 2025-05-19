<?php
session_start();
require_once './vendor/autoload.php';

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'magdalene_management',
    'port' => 3306
];

// PayChangu API configuration
$apiConfig = [
    'base_url' => 'https://api.paychangu.com',
    'secret_key' => 'SEC-dF33XmJXmafjMN8uUpxAsumo91knYGfx',
    'timeout' => 30
];

// Initialize response
header('Content-Type: application/json');
$response = [
    'success' => false,
    'message' => 'Unknown error',
    'data' => null
];

// Enable detailed error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/payment_errors.log');

try {
    // Create database connection
    $conn = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database'],
        $dbConfig['port']
    );

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Create HTTP client
    $client = new \GuzzleHttp\Client([
        'base_uri' => $apiConfig['base_url'],
        'timeout' => $apiConfig['timeout']
    ]);

    // Handle verification request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
        $charge_id = $_POST['charge_id'] ?? '';
        
        if (empty($charge_id)) {
            throw new Exception("Charge ID is required for verification");
        }

        // Check database first
        $stmt = $conn->prepare("SELECT status FROM pay WHERE charge_id = ?");
        $stmt->bind_param("s", $charge_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Transaction not found in our records");
        }
        
        $row = $result->fetch_assoc();
        if ($row['status'] === 'success') {
            $response = ['success' => true, 'message' => 'Payment already verified'];
            echo json_encode($response);
            exit;
        }
        
        // Verify with PayChangu API
        $apiResponse = $client->request('GET', "/mobile-money/payments/{$charge_id}/verify", [
            'headers' => [
                'accept' => 'application/json',
                'Authorization' => "Bearer {$apiConfig['secret_key']}",
            ]
        ]);

        $apiData = json_decode($apiResponse->getBody(), true);
        
        if ($apiResponse->getStatusCode() !== 200) {
            throw new Exception($apiData['message'] ?? "Failed to verify payment");
        }

        // Check payment status
        if (($apiData['status'] === 'successful') && ($apiData['data']['status'] ?? '') === 'success') {
            // Update database
            $stmt = $conn->prepare("UPDATE pay SET status = 'success', updated_at = NOW() WHERE charge_id = ?");
            $stmt->bind_param("s", $charge_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update transaction status: " . $stmt->error);
            }
            
            $response = [
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => $apiData['data']
            ];
        } else {
            $response = [
                'success' => false,
                'message' => $apiData['message'] ?? 'Payment is still pending',
                'data' => $apiData
            ];
        }
        
        echo json_encode($response);
        exit;
    }

    // Handle new payment initialization
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required fields
        $requiredFields = ['fname', 'lname', 'email', 'fees', 'bank', 'phone', 'event_name'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missingFields));
        }

        // Sanitize and validate inputs
        $fname = trim($conn->real_escape_string($_POST['fname']));
        $lname = trim($conn->real_escape_string($_POST['lname']));
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $event_name = trim($conn->real_escape_string($_POST['event_name']));
        $bank_ref_id = trim($conn->real_escape_string($_POST['bank']));
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        
        if (!$email) {
            throw new Exception("Invalid email address format");
        }

        // Validate amount
        $fee = (float) preg_replace('/[^0-9.]/', '', $_POST['fees']);
        if ($fee < 50) {
            throw new Exception("Minimum donation amount is MWK 50");
        }

        // Validate phone number
        if (strlen($phone) < 9 || strlen($phone) > 15) {
            throw new Exception("Phone number must be 9-15 digits");
        }

        // Generate unique charge ID
        $charge_id = 'pay_' . bin2hex(random_bytes(4)) . '_' . time();

        // Prepare API payload
        $payload = [
            'mobile_money_operator_ref_id' => $bank_ref_id,
            'amount' => $fee,
            'mobile' => $phone,
            'email' => $email,
            'first_name' => $fname,
            'last_name' => $lname,
            'charge_id' => $charge_id,
            'callback_url' => 'https://yourdomain.com/webhook.php' // Replace with your actual webhook URL
        ];

        // Initialize payment with PayChangu
        $apiResponse = $client->request('POST', '/mobile-money/payments/initialize', [
            'headers' => [
                'accept' => 'application/json',
                'Authorization' => "Bearer {$apiConfig['secret_key']}",
                'Content-Type' => 'application/json'
            ],
            'json' => $payload
        ]);

        $apiData = json_decode($apiResponse->getBody(), true);
        
        if ($apiResponse->getStatusCode() !== 200) {
            throw new Exception($apiData['message'] ?? "Failed to initialize payment");
        }

        // Save to database
        $stmt = $conn->prepare("INSERT INTO pay (
            charge_id, fee, payment_date, payment_method, 
            donor_name, donor_email, event_name, status, type
        ) VALUES (?, ?, NOW(), ?, ?, ?, ?, 'pending', 'Deposit')");
        
        $donor_name = "{$fname} {$lname}";
        $payment_method = 'Mobile Money';
        
        $stmt->bind_param(
            "sdssss", 
            $charge_id, 
            $fee, 
            $payment_method, 
            $donor_name, 
            $email, 
            $event_name
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save transaction record: " . $stmt->error);
        }

        $response = [
            'success' => true,
            'message' => 'Payment initiated successfully',
            'charge_id' => $charge_id,
            'instructions' => $apiData['data']['instructions'] ?? 'Please approve the payment on your phone'
        ];
    } else {
        throw new Exception("Invalid request method");
    }
} catch (\GuzzleHttp\Exception\RequestException $e) {
    $errorResponse = $e->getResponse();
    $errorBody = $errorResponse ? $errorResponse->getBody()->getContents() : null;
    $errorData = $errorBody ? json_decode($errorBody, true) : null;
    
    $errorMessage = $errorData['message'] ?? $e->getMessage();
    error_log("API Request Error: " . $errorMessage);
    
    $response['message'] = "Payment processing error: " . $errorMessage;
} catch (Exception $e) {
    error_log("System Error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
exit;
?>