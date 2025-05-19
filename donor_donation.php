<?php
require_once('./vendor/autoload.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize database connection
include './connect.php';

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
    <title>Donation Form</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
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
            background-color: #926c54;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background-color: #7d5b46;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="images/logo.png" alt="Organization Logo" class="logo">
        </div>
        <h1>Make a Donation</h1>
        
        <div id="message" style="margin-bottom: 20px;"></div>
        
        <form id="donationForm" method="POST">
            <input type="hidden" name="event_name" value="General">
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
                            <option value="<?= htmlspecialchars($bank['ref_id'] ?? '') ?>">
                                <?= htmlspecialchars($bank['name'] ?? '') ?>
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
        
        <div id="progress" style="display: none; margin-top: 20px;">
            <h3>Payment Progress</h3>
            <div id="progressBar" style="height: 20px; background: #eee; border-radius: 10px;">
                <div id="progressFill" style="height: 100%; width: 0%; background: #4CAF50; border-radius: 10px;"></div>
            </div>
            <p id="progressText">Initializing payment...</p>
        </div>
    </div>

    <script>
    document.getElementById('donationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        document.querySelectorAll('.error').forEach(el => el.textContent = '');
        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = '';
        
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
        
        document.getElementById('progress').style.display = 'block';
        document.getElementById('progressText').textContent = 'Processing payment...';
        
        try {
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
                
                if (result.charge_id) {
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
        
        const maxAttempts = 30;
        const interval = 3000;
        
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
                    progressFill.style.width = '100%';
                    progressText.textContent = 'Payment verified successfully!';
                    
                    document.getElementById('message').innerHTML = `
                        <div style="color: green; padding: 10px; background: #e8f5e9; border-radius: 4px;">
                            Payment completed successfully! Thank you for your donation.
                        </div>`;
                    
                    setTimeout(() => {
                        // Redirect to the previous page if available, otherwise to index.php
                        const previousPage = document.referrer || 'index.php';
                        if (previousPage && !previousPage.includes(window.location.pathname)) {
                            window.location.href = previousPage;
                        } else {
                            window.location.href = 'index.php';
                        }
                    }, 2000);
                    return true;
                }
                
                if (result.message && result.message.toLowerCase().includes('fail')) {
                    progressText.textContent = 'Payment failed';
                    document.getElementById('message').innerHTML = `
                        <div style="color: red; padding: 10px; background: #ffebee; border-radius: 4px;">
                            Payment failed: ${result.message}
                        </div>`;
                    return false;
                }
                
                await new Promise(resolve => setTimeout(resolve, interval));
            } catch (error) {
                console.error('Verification error:', error);
                progressText.textContent = 'Verification error';
            }
        }
        
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