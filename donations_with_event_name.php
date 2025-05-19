<?php
session_start();
require_once './vendor/autoload.php';


$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ob_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$client = new \GuzzleHttp\Client();
$paychangu_secret_key = 'SEC-dF33XmJXmafjMN8uUpxAsumo91knYGfx'; 
$response = ['success' => false, 'message' => 'Unknown error'];

error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request Headers: " . json_encode(getallheaders()));
error_log("POST Data: " . json_encode($_POST));

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

        // Retry logic for verifying payment
        $attempts = 3;
        for ($i = 0; $i < $attempts; $i++) {
            try {
                $paychangu_response = $client->request('GET', "https://api.paychangu.com/mobile-money/payments/$charge_id/verify", [
                    'headers' => [
                        'accept' => 'application/json',
                        'Authorization' => "Bearer $paychangu_secret_key",
                    ],
                    'timeout' => 60, // Timeout set to 60 seconds
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
                break; // Exit retry loop if successful
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                error_log("Attempt $i: cURL Error " . $e->getMessage());
                if ($i === $attempts - 1) {
                    throw $e;
                }
                sleep(2); 
            }
        }
    } else {
        
        $required_fields = ['fname', 'lname', 'email', 'fees', 'bank', 'phone', 'event_id'];
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
        $event_id = (int)$_POST['event_id']; 

        
        $stmt = $conn->prepare("SELECT name FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $event_result = $stmt->get_result();

        if ($event_result->num_rows === 0) {
            $response['message'] = 'Invalid event ID. Event not found.';
            echo json_encode($response);
            $stmt->close();
            ob_end_flush();
            exit;
        }

        $event = $event_result->fetch_assoc();
        $event_name = $event['name'];
        $stmt->close();

        
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

        
        $charge_id = sprintf('%08x', random_int(0, 0xffffffff));

        $payload = [
            'mobile_money_operator_ref_id' => $bank_ref_id,
            'amount' => $fee,
            'mobile' => $phone,
            'email' => $email,
            'first_name' => $fname,
            'last_name' => $lname,
            'charge_id' => $charge_id,
        ];

       
        $attempts = 3;
        for ($i = 0; $i < $attempts; $i++) {
            try {
                error_log("Sending Paychangu Payload: " . json_encode($payload));
                $paychangu_response = $client->request('POST', 'https://api.paychangu.com/mobile-money/payments/initialize', [
                    'headers' => [
                        'accept' => 'application/json',
                        'Authorization' => "Bearer $paychangu_secret_key",
                    ],
                    'json' => $payload,
                    'timeout' => 60, 
                ]);
                break; 
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                error_log("Attempt $i: cURL Error " . $e->getMessage());
                if ($i === $attempts - 1) {
                    throw $e;
                }
                sleep(2); 
            }
        }

        if ($paychangu_response->getStatusCode() === 200) {
            $paychangu_data = json_decode($paychangu_response->getBody()->getContents(), true);
            error_log("Paychangu Response: " . json_encode($paychangu_data));

            $donor_name = $fname . ' ' . $lname;
            $payment_date = date('Y-m-d H:i:s');
            $payment_method = $bank_ref_id;
            $status = 'pending';

            $stmt = $conn->prepare("INSERT INTO pay (charge_id, amount, payment_date, payment_method, donor_name, donor_email, event_name, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssssss", $charge_id, $fee, $payment_date, $payment_method, $donor_name, $email, $event_name, $status);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = $paychangu_data['message'] ?? 'Payment request sent to your phone. Please approve the payment to proceed.';
                $response['charge_id'] = $charge_id;
                $response['instructions'] = $paychangu_data['data']['instructions'] ?? 'Please check your phone to approve the payment.';
            } else {
                error_log("Database Insert Error: " . $stmt->error);
                $response['message'] = 'Failed to save payment details';
            }
            $stmt->close();
        } else {
            error_log("Paychangu API Error: Status " . $paychangu_response->getStatusCode());
            $response['message'] = 'Failed to initiate payment. Please try again.';
        }
    }
} catch (\GuzzleHttp\Exception\RequestException $e) {
    $error_message = $e->getMessage();
    error_log("Request Exception: " . $error_message);
    $response['message'] = 'Unable to connect to the payment gateway. Please try again later.';
} catch (\Exception $e) {
    error_log("Unexpected Error: " . $e->getMessage());
    $response['message'] = 'An unexpected error occurred: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
ob_end_flush();
exit;
?>