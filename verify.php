<?php
session_start();
include './connection.php'; // Include your database connection file
require_once('./vendor/autoload.php');

use GuzzleHttp\Client;

// Get transaction reference from URL parameters
$reference = isset($_GET['tx_ref']) ? $_GET['tx_ref'] : null;

// Debug logging to help troubleshoot
error_log("Verification started. Reference: " . ($reference ?? 'NONE'));

// If no reference found in GET, check POST
if (!$reference && isset($_POST['tx_ref'])) {
    $reference = $_POST['tx_ref'];
    error_log("Found reference in POST: $reference");
}

// Fallback to session storage if needed
if (!$reference && isset($_SESSION['tx_ref'])) {
    $reference = $_SESSION['tx_ref'];
    error_log("Using reference from session: $reference");
}

// If we have a reference, attempt verification
if ($reference) {
    $client = new Client();
    
    try {
        error_log("Making API request to verify payment: $reference");
        
        // Call Paychangu verification API
        $response = $client->request('GET', 'https://api.paychangu.com/verify-payment', [
            'headers' => [
                'Authorization' => 'SEC-dF33XmJXmafjMN8uUpxAsumo91knYGfx',
                'accept' => 'application/json',
            ],
            'query' => [
                'reference' => $reference
            ]
        ]);
        
        $body = $response->getBody();
        $data = json_decode($body, true);
        
        error_log("API Response: " . json_encode($data));
        
        // Check if the payment was successful
        if (isset($data['status']) && $data['status'] == 'success') {
            $conn->begin_transaction(); // Start transaction
            
            try {
                // Extract data from the payment response
                $amount = $data['data']['amount'] ?? 0;
                $method = $data['data']['type'] ?? 'unknown';
                
                // First try to get metadata from the API response
                $donor_name = '';
                $donor_email = '';
                $event_name = '';
                
                // Check multiple possible locations for customer data
                if (isset($data['data']['meta']['donor_name'])) {
                    $donor_name = $data['data']['meta']['donor_name'];
                } elseif (isset($data['data']['customer']['name'])) {
                    $donor_name = $data['data']['customer']['name'];
                }
                
                if (isset($data['data']['customer']['email'])) {
                    $donor_email = $data['data']['customer']['email'];
                } elseif (isset($data['data']['meta']['donor_email'])) {
                    $donor_email = $data['data']['meta']['donor_email'];
                }
                
                if (isset($data['data']['meta']['event_name'])) {
                    $event_name = $data['data']['meta']['event_name'];
                }
                
                // If data is missing from API, try getting from session
                if (empty($donor_name) && isset($_SESSION['donor_name'])) {
                    $donor_name = $_SESSION['donor_name'];
                }
                
                if (empty($donor_email) && isset($_SESSION['donor_email'])) {
                    $donor_email = $_SESSION['donor_email'];
                }
                
                if (empty($event_name) && isset($_SESSION['event_name'])) {
                    $event_name = $_SESSION['event_name'];
                }
                
                // As a last resort, check if the client stored data in session storage
                // and passed it in the URL (for non-PHP clients)
                if (empty($donor_name) && isset($_GET['donor_name'])) {
                    $donor_name = $_GET['donor_name'];
                }
                
                if (empty($donor_email) && isset($_GET['donor_email'])) {
                    $donor_email = $_GET['donor_email'];
                }
                
                if (empty($event_name) && isset($_GET['event_name'])) {
                    $event_name = $_GET['event_name'];
                }
                
                // Get the donation type
                $donation_type = isset($_SESSION['donation_type']) ? $_SESSION['donation_type'] : 
                                (isset($_GET['donation_type']) ? $_GET['donation_type'] : 'general');
                
                error_log("Data to insert: Reference=$reference, Amount=$amount, Method=$method, " .
                          "Name=$donor_name, Email=$donor_email, Event=$event_name, Type=$donation_type");
                
                // Insert payment record into the database
                $payment_sql = "INSERT INTO pay (reference, amount, payment_date, payment_method, donor_name, donor_email, event_name, donation_type) 
                               VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($payment_sql);
                $stmt->bind_param("sssssss", $reference, $amount, $method, $donor_name, $donor_email, $event_name, $donation_type);
                
                if (!$stmt->execute()) {
                    throw new Exception("Database insert failed: " . $stmt->error);
                }
                
                $stmt->close();
                
                $conn->commit(); // Commit transaction
                
                // Store success status in session for thank-you page
                $_SESSION['payment_status'] = 'success';
                $_SESSION['payment_reference'] = $reference;
                $_SESSION['payment_amount'] = $amount;
                
                // Redirect to thank-you page
                header('Location: ./thank-you.php?status=success&reference=' . urlencode($reference));
                exit();
                
            } catch (Exception $e) {
                $conn->rollback(); // Rollback transaction on error
                error_log("Database error: " . $e->getMessage());
                header('Location: ./thank-you.php?status=error&message=' . urlencode($e->getMessage()));
                exit();
            }
        } else {
            // Payment verification failed
            error_log("Payment verification failed: Status not successful");
            $_SESSION['payment_status'] = 'failure';
            header('Location: ./thank-you.php?status=failure&reference=' . urlencode($reference));
            exit();
        }
    } catch (Exception $e) {
        error_log("API error: " . $e->getMessage());
        header('Location: ./thank-you.php?status=error&message=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // No reference provided
    error_log("No transaction reference provided");
    header('Location: ./index.php?status=missing_reference');
    exit();
}
?>