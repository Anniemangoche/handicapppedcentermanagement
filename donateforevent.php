<?php
require_once('./vendor/autoload.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize database connection
include './connect.php';

// Get event name from URL, sanitize it
$event_name = isset($_GET['event_name']) ? htmlspecialchars(urldecode(trim($_GET['event_name']))) : '';
$donation_id = isset($_GET['donation_id']) ? intval($_GET['donation_id']) : 0;

// Debug: Log the raw GET parameters
error_log("Raw GET[event_name]: " . ($_GET['event_name'] ?? 'not set'));
error_log("Raw GET[donation_id]: " . ($_GET['donation_id'] ?? 'not set'));

// Redirect to index.php if no event_name is provided
if (empty($event_name)) {
    error_log("No event_name specified, redirecting to index.php");
    header('Location: index.php');
    exit;
}

// Log sanitized event_name
error_log("Sanitized event_name: $event_name");

$client = new \GuzzleHttp\Client();
$banks = [];

try {
    $response = $client->request('GET', 'https://api.paychangu.com/mobile-money', [
        'headers' => [
            'accept' => 'application/json',
            'Authorization' => 'SEC-dF33XmJXmafjMN8uUpxAsumo91knYGfx',
        ],
    ]);

    if ($response->getStatusCode() === 200) {
        $banks = json_decode($response->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg());
            $banks = [
                'data' => [
                    ['ref_id' => 'airtel', 'name' => 'Airtel Money'],
                    ['ref_id' => 'tnm', 'name' => 'TNM Mpamba']
                ]
            ];
        }
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    $banks = [
        'data' => [
            ['ref_id' => 'airtel', 'name' => 'Airtel Money'],
            ['ref_id' => 'tnm', 'name' => 'TNM Mpamba']
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Form - Magdalene Home</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7d5b46;
            --accent-color: #e3a073;
            --light-color: #f8f1e9;
            --text-color: #333;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            margin: 0;
            padding: 20px;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .logo {
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 10px;
        }

        .event-info {
            text-align: center;
            font-size: 1.1rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-color);
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            width: 100%;
            margin-top: 10px;
            transition: var(--transition);
        }

        button:hover {
            background-color: var(--accent-color);
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }

        #message {
            margin-bottom: 20px;
            text-align: center;
        }

        #progress {
            margin-top: 20px;
        }

        #progressBar {
            height: 20px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
        }

        #progressFill {
            height: 100%;
            width: 0%;
            background: #4CAF50;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        /* Pop-up Styles */
        #paymentPopup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            z-index: 1000;
            text-align: center;
            max-width: 90%;
            width: 400px;
            border: 2px solid var(--primary-color);
        }

        #paymentPopup p {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        #paymentPopup button {
            background-color: var(--primary-color);
            padding: 10px 20px;
            font-size: 14px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 15px;
            }
            h1 {
                font-size: 1.5rem;
            }
            .event-info {
                font-size: 1rem;
            }
            input, select {
                font-size: 14px;
                padding: 8px;
            }
            button {
                font-size: 14px;
                padding: 10px;
            }
            .logo {
                max-width: 80px;
            }
            #paymentPopup {
                width: 90%;
                padding: 15px;
            }
            #paymentPopup p {
                font-size: 1rem;
            }
        }

        @media (max-width: 400px) {
            body {
                padding: 8px;
            }
            .container {
                padding: 12px;
            }
            h1 {
                font-size: 1.4rem;
            }
            .event-info {
                font-size: 0.9rem;
            }
            input, select {
                font-size: 13px;
                padding: 7px;
            }
            button {
                font-size: 13px;
                padding: 8px;
            }
            .logo {
                max-width: 70px;
            }
            #paymentPopup {
                padding: 10px;
            }
            #paymentPopup p {
                font-size: 0.9rem;
            }
        }

        @media (min-width: 1200px) {
            .container {
                max-width: 700px;
            }
        }
    </style>
</head>
<body>
    <div class="overlay" id="popupOverlay"></div>
    <div id="paymentPopup">
        <p>Please check your phone to approve the payment.</p>
        <button onclick="closePopup()">OK</button>
    </div>

    <div class="container">
        <img src="images/logo.png" alt="Magdalene Home Logo" class="logo">
        <h1>Make a Donation</h1>
        <div class="event-info">You are making a donation for: <strong><?php echo htmlspecialchars($event_name); ?></strong></div>
        <div id="message"></div>
        
        <form id="donationForm" method="POST" action="process_payment.php">
            <input type="hidden" name="event_name" value="<?php echo htmlspecialchars($event_name); ?>">
            <input type="hidden" name="donation_id" value="<?php echo $donation_id; ?>">
            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="fname" required minlength="2">
                <span class="error" id="fnameError"></span>
            </div>
            
            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" required minlength="2">
                <span class="error" id="lnameError"></span>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <span class="error" id="emailError"></span>
            </div>
            
            <div class="form-group">
                <label for="fees">Amount (MWK)</label>
                <input type="number" id="fees" name="fees" min="50" required>
                <span class="error" id="feesError"></span>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{9,15}" required placeholder="e.g., 997123456">
                <span class="error" id="phoneError"></span>
            </div>
            
            <div class="form-group">
                <label for="bank">Mobile Network</label>
                <select id="bank" name="bank" required>
                    <option value="">Select a network</option>
                    <?php if (isset($banks['data']) && is_array($banks['data'])): ?>
                        <?php foreach ($banks['data'] as $bank): ?>
                            <option value="<?php echo htmlspecialchars($bank['ref_id'] ?? ''); ?>">
                                <?php echo htmlspecialchars($bank['name'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="airtel">Airtel Money</option>
                        <option value="tnm">TNM Mpamba</option>
                    <?php endif; ?>
                </select>
                <span class="error" id="bankError"></span>
            </div>
            
            <button type="submit">Donate Now</button>
        </form>
        
        <div id="progress" style="display: none;">
            <h3>Payment Progress</h3>
            <div id="progressBar">
                <div id="progressFill"></div>
            </div>
            <p id="progressText">Initializing payment...</p>
        </div>
    </div>

    <script>
    function showPopup() {
        document.getElementById('paymentPopup').style.display = 'block';
        document.getElementById('popupOverlay').style.display = 'block';
        // Auto-hide after 5 seconds
        setTimeout(closePopup, 5000);
    }

    function closePopup() {
        document.getElementById('paymentPopup').style.display = 'none';
        document.getElementById('popupOverlay').style.display = 'none';
    }

    document.getElementById('donationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous errors
        document.querySelectorAll('.error').forEach(el => el.textContent = '');
        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = '';
        
        // Validate form
        let isValid = true;
        const fname = document.getElementById('fname').value.trim();
        if (!fname) {
            document.getElementById('fnameError').textContent = 'First name is required';
            isValid = false;
        }
        
        const lname = document.getElementById('lname').value.trim();
        if (!lname) {
            document.getElementById('lnameError').textContent = 'Last name is required';
            isValid = false;
        }
        
        const email = document.getElementById('email').value.trim();
        if (!email || !/^[^@]+@[^@]+\.[a-z]{2,}$/i.test(email)) {
            document.getElementById('emailError').textContent = 'Invalid email format';
            isValid = false;
        }
        
        const fees = parseFloat(document.getElementById('fees').value);
        if (isNaN(fees) || fees < 50) {
            document.getElementById('feesError').textContent = 'Minimum donation is MWK 50';
            isValid = false;
        }
        
        const phone = document.getElementById('phone').value.trim();
        if (!phone || !/^[0-9]{9,15}$/.test(phone)) {
            document.getElementById('phoneError').textContent = 'Invalid phone number';
            isValid = false;
        }
        
        const bank = document.getElementById('bank').value;
        if (!bank) {
            document.getElementById('bankError').textContent = 'Please select a mobile network';
            isValid = false;
        }
        
        if (!isValid) return;
        
        // Show progress
        document.getElementById('progress').style.display = 'block';
        document.getElementById('progressText').textContent = 'Processing payment...';
        
        try {
            // Submit form data
            const formData = new FormData(this);
            
            const response = await fetch('process_payment.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                messageDiv.innerHTML = `<div style="color: green; padding: 10px; background: #e8f5e9; border-radius: 4px;">
                    ${result.message}
                </div>`;
                
                // If we have a charge ID, show popup and start verification
                if (result.charge_id) {
                    showPopup();
                    document.getElementById('progressText').textContent = result.instructions || 'Please approve the payment on your phone';
                    await verifyPayment(result.charge_id);
                }
            } else {
                messageDiv.innerHTML = `<div style="color: red; padding: 10px; background: #ffebee; border-radius: 4px;">
                    ${result.message || 'Payment failed'}
                </div>`;
            }
        } catch (error) {
            console.error('Error:', error);
            messageDiv.innerHTML = `<div style="color: red; padding: 10px; background: #ffebee; border-radius: 4px;">
                An error occurred: ${error.message}
            </div>`;
        }
    });
    
    async function verifyPayment(chargeId) {
        const progressText = document.getElementById('progressText');
        const progressFill = document.getElementById('progressFill');
        
        // Verify payment status periodically
        const maxAttempts = 30;
        const interval = 3000; // 3 seconds
        
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            progressFill.style.width = `${(attempt/maxAttempts)*100}%`;
            progressText.textContent = `Verifying payment (${attempt}/${maxAttempts})...`;
            
            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `verify_payment=true&charge_id=${chargeId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closePopup();
                    progressFill.style.width = '100%';
                    progressText.textContent = 'Payment verified successfully!';
                    
                    // Show success message
                    document.getElementById('message').innerHTML = `
                        <div style="color: green; padding: 10px; background: #e8f5e9; border-radius: 4px;">
                            Payment completed successfully! Thank you for your donation.
                        </div>`;
                    
                    // Redirect to index.php after a short delay
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                    return true;
                }
                
                // If payment failed
                if (result.message && result.message.toLowerCase().includes('fail')) {
                    closePopup();
                    progressText.textContent = 'Payment failed';
                    document.getElementById('message').innerHTML = `
                        <div style="color: red; padding: 10px; background: #ffebee; border-radius: 4px;">
                            Payment failed: ${result.message}
                        </div>`;
                    return false;
                }
                
                // Wait before next attempt
                await new Promise(resolve => setTimeout(resolve, interval));
            } catch (error) {
                console.error('Verification error:', error);
                progressText.textContent = 'Verification error';
            }
        }
        
        // If we get here, verification timed out
        closePopup();
        progressText.textContent = 'Verification timed out';
        document.getElementById('message').innerHTML = `
            <div style="color: orange; padding: 10px; background: #fff3e0; border-radius: 4px;">
                Payment verification timed out. Please check your mobile money account.
            </div>`;
        return false;
    }
    </script>
</body>
</html>