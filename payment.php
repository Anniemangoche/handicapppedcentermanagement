<?php
session_start();
require_once './vendor/autoload.php';
include './auth/connect.php';

ob_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$client = new \GuzzleHttp\Client();
$paychangu_secret_key = getenv('PAYCHANGU_SECRET_KEY') ?: 'SEC-dF33XmJXmafjMN8uUpxAsumo91knYGfx';
$response = ['success' => false, 'message' => 'Unknown error'];

error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request Headers: " . json_encode(getallheaders()));
error_log("POST Data: " . json_encode($_POST));

if (!$conn) {
    $response['message'] = 'Database connection failed';
    error_log("Database connection failed: " . mysqli_connect_error());
    echo json_encode($response);
    ob_end_flush();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method. Expected POST, received ' . $_SERVER['REQUEST_METHOD'];
    echo json_encode($response);
    ob_end_flush();
    exit;
}

try {
    if (isset($_POST['verify_payment']) && isset($_POST['charge_id'])) {
        // Verify payment status
        $charge_id = $_POST['charge_id'];

        // Validate charge ID format (8 hexadecimal digits)
        if (!preg_match('/^[0-9a-f]{8}$/', $charge_id)) {
            $response = ['success' => false, 'message' => 'Invalid charge ID format'];
            echo json_encode($response);
            ob_end_flush();
            exit;
        }

        // Check if charge ID exists in database
        $stmt = $conn->prepare("SELECT charge_id FROM pay WHERE charge_id = ?");
        $stmt->bind_param("s", $charge_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $response = ['success' => false, 'message' => 'Charge ID not found in database'];
            echo json_encode($response);
            ob_end_flush();
            exit;
        }
        $stmt->close();

        try {
            $paychangu_response = $client->request('GET', "https://api.paychangu.com/mobile-money/payments/$charge_id/verify", [
                'headers' => [
                    'accept' => 'application/json',
                    'Authorization' => "Bearer $paychangu_secret_key",
                ],
            ]);
            $paychangu_data = json_decode($paychangu_response->getBody()->getContents(), true);
            error_log("Verification Response: " . json_encode($paychangu_data));

            if ($paychangu_data['status'] === 'successful' && $paychangu_data['data']['status'] === 'success') {
                $stmt = $conn->prepare("UPDATE pay SET status = 'success' WHERE charge_id = ?");
                $stmt->bind_param("s", $charge_id);
                $stmt->execute();
                $stmt->close();

                $response = ['success' => true, 'message' => 'Payment verified successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Payment is pending or not successful'];
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                $response = ['success' => false, 'message' => 'Charge ID not found'];
                error_log("Verification Error: Charge ID $charge_id not found");
            } else {
                throw $e;
            }
        }
    } else {
        // Initiate payment
        $required_fields = ['fname', 'lname', 'email', 'event_name', 'fees', 'bank', 'phone'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $response['message'] = "Missing required field: $field";
                echo json_encode($response);
                ob_end_flush();
                exit;
            }
        }

        $fname = is_array($_POST['fname']) ? implode('', $_POST['fname']) : $_POST['fname'];
        $lname = is_array($_POST['lname']) ? implode('', $_POST['lname']) : $_POST['lname'];
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
        $event_name = is_array($_POST['event_name']) ? implode('', $_POST['event_name']) : $_POST['event_name'];

            // Process fee
            $fee = $_POST['fees'];
            $fee = str_replace('MWK', '', $fee); 
            $fee = preg_replace('/[^0-9.]/', '', $fee); 
            $fee = (float)$fee;

            if ($fee < 50) {
                $response['message'] = 'Error: The amount must be at least 50.';
                echo json_encode($response);
                ob_end_flush();
                exit;
            }

        $bank_ref_id = $_POST['bank'];
        $phone = preg_replace('/[^0-9]/', '', is_array($_POST['phone']) ? implode('', $_POST['phone']) : $_POST['phone']);

        if (!$email) {
            $response['message'] = 'Invalid email address';
            echo json_encode($response);
            ob_end_flush();
            exit;
        }

        if ($fee <= 0) {
            $response['message'] = 'Invalid payment amount';
            echo json_encode($response);
            ob_end_flush();
            exit;
        }

        if (strlen($phone) < 9) {
            $response['message'] = 'Phone number must be at least 9 digits';
            echo json_encode($response);
            ob_end_flush();
            exit;
        }

        // Generate 8-digit hexadecimal charge ID
        $charge_id = sprintf('%08x', random_int(0, 0xffffffff));

        $payload = [
            'mobile_money_operator_ref_id' => $bank_ref_id,
            'amount' => $fee,
            'mobile' => $phone,
            'email' => $email,
            'first_name' => $fname,
            'last_name' => $lname,
            'description' => "donation payment for $event_name",
            'charge_id' => $charge_id,
        ];

        error_log("Sending Paychangu Payload: " . json_encode($payload));
        $paychangu_response = $client->request('POST', 'https://api.paychangu.com/mobile-money/payments/initialize', [
            'headers' => [
                'accept' => 'application/json',
                'Authorization' => "Bearer $paychangu_secret_key",
            ],
            'json' => $payload,
        ]);

        if ($paychangu_response->getStatusCode() === 200) {
            $paychangu_data = json_decode($paychangu_response->getBody()->getContents(), true);
            error_log("Paychangu Response: " . json_encode($paychangu_data));

            $stmt = $conn->prepare("INSERT INTO pay (first_name, last_name, email, event_name, fee, status, charge_id) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->bind_param("ssssds", $fname, $lname, $email, $event_name, $fee, $charge_id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = $paychangu_data['message'] ?? 'Payment request sent to your phone. Please approve the payment to proceed.';
                $response['charge_id'] = $charge_id;
                $response['instructions'] = $paychangu_data['data']['instructions'] ?? 'Please check your phone to approve the payment.';
            } else {
                error_log("Database Insert Error: " . $stmt->error);
                $response['message'] = 'Failed to save donation details';
            }
            $stmt->close();
        } else {
            error_log("Paychangu API Error: Status " . $paychangu_response->getStatusCode());
            $response['message'] = 'Failed to initiate payment. Please try again.';
        }
    }
} catch (\GuzzleHttp\Exception\RequestException $e) {
    $error_message = $e->getMessage();
    $error_body = '';
    if ($e->hasResponse()) {
        $error_body = $e->getResponse()->getBody()->getContents();
        error_log("Paychangu API Error Response: " . $error_body);
        $error_data = json_decode($error_body, true);
        $error_message = is_array($error_data) && isset($error_data['message']) && is_string($error_data['message'])
            ? $error_data['message']
            : (is_array($error_data) ? json_encode($error_data) : $error_body);
    }
    error_log("Payment Error: " . $error_message);
    $response['message'] = "Payment operation failed: $error_message";
} catch (\Exception $e) {
    error_log("Unexpected Error: " . $e->getMessage());
    $response['message'] = 'An unexpected error occurred: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
ob_end_flush();
exit;
?>