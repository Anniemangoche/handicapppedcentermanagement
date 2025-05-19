
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
        $body = $response->getBody()->getContents();
        $banks = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg());
            $banks = [];
        }
    } else {
        error_log("API Request Failed with status: " . $response->getStatusCode());
    }
} catch (\GuzzleHttp\Exception\RequestException $e) {
    error_log("API Request Exception: " . $e->getMessage());
} catch (Exception $e) {
    error_log("Unexpected Error: " . $e->getMessage());
}


?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../images/logo.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@200;400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>/* General Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 14px;
}

/* Header Styles */
header {
    background-color: var(--white);
    box-shadow: var(--shadow);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    transition: var(--transition);
    padding: 15px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header.scrolled {
    padding: 10px 5%;
}

.logo img {
    width: 100px;
    height: 100px;
}

.logo a {
    text-decoration: none;
    color: inherit;
}

nav {
    display: flex;
    align-items: center;
    gap: 25px;
}

nav a {
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 500;
    transition: var(--transition);
    padding: 8px 12px;
    border-radius: 4px;
}

nav a:hover {
    color: var(--primary-color);
    background-color: rgba(146, 108, 84, 0.1);
}

/* Content Styles */
.content {
    margin-top: 80px;
    padding: 40px;
    background-color: var(--light-color);
    min-height: 100vh;
}

/* Form Styling */
.form-content {
    background-color: var(--white);
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

#addUserForm {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

#addUserForm h2 {
    text-align: center;
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 20px;
    color: var(--dark-color);
}

.form-container {
    display: flex;
    flex-direction: row;
    gap: 20px;
    justify-content: space-between;
}

.form-section {
    background-color: var(--light-color);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 10px;
    border-bottom: 2px solid var(--accent-color);
    padding-bottom: 5px;
}

.form-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group label {
    font-weight: 600;
    font-size: 14px;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    color: var(--dark-color);
    background-color: var(--white);
    box-sizing: border-box;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    background-color: var(--white);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 25px;
}

button {
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease;
}

button[type="submit"] {
    background-color: var(--primary-color);
    color: var(--white);
}

button[type="submit"]:hover {
    background-color: var(--secondary-color);
    transform: translateY(-1px);
}

button[type="button"] {
    background-color: #6c757d;
    color: var(--white);
}

button[type="button"]:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
}

/* Footer Styles */
.footer {
    padding: 20px;
    text-align: center;
    background-color: var(--dark-color);
    color: var(--white);
    font-size: 16px;
    font-weight: bold;
    margin-top: 50px;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .content {
        padding: 20px;
    }

    .form-container {
        flex-direction: column;
    }

    .form-section {
        max-width: 100%;
        flex: 1 1 100%;
    }

    header {
        padding: 10px 20px;
    }

    .logo {
        font-size: 1.2rem;
    }

    nav a {
        font-size: 0.9rem;
        padding: 6px 8px;
    }
}

@media screen and (max-width: 576px) {
    .form-content {
        padding: 20px;
    }

    button {
        padding: 10px 20px;
    }
}</style>
</head>
<body>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var dropdown = document.getElementById("servicesDropdown");
    if (dropdown) {
        dropdown.style.display = "block";
    }
    var consultationLink = document.querySelector('a[href="fees.php"]');
    if (consultationLink) {
        consultationLink.classList.add("active");
    }

    // Function to append progress messages
    function addProgressMessage(message, status = 'pending') {
        const progressList = document.getElementById('progress-list');
        if (!progressList) return;
        const li = document.createElement('li');
        li.className = status;
        li.innerHTML = `
            <span class="status-icon">
                ${status === 'success' ? '<i class="fas fa-check-circle"></i>' :
                  status === 'error' ? '<i class="fas fa-times-circle"></i>' :
                  '<i class="fas fa-spinner fa-spin"></i>'}
            </span>
            ${message}
        `;
        progressList.appendChild(li);
        progressList.scrollTop = progressList.scrollHeight;
    }

    // Function to update progress item status
    function updateProgressStatus(index, status, message) {
        const progressItems = document.getElementById('progress-list')?.children;
        if (progressItems && progressItems[index]) {
            progressItems[index].className = status;
            progressItems[index].innerHTML = `
                <span class="status-icon">
                    ${status === 'success' ? '<i class="fas fa-check-circle"></i>' :
                      status === 'error' ? '<i class="fas fa-times-circle"></i>' :
                      '<i class="fas fa-spinner fa-spin"></i>'}
                </span>
                ${message}
            `;
        }
    }

    // Function to clear transaction state
    function clearTransactionState() {
        sessionStorage.removeItem('pendingChargeId');
        const form = document.getElementById('addUserForm');
        const modal = document.getElementById('addUserModal');
        const progressContainer = document.getElementById('progress-container');
        const messageDiv = document.getElementById('message');
        if (form) {
            form.reset();
            enableFormInputs(true);
        }
        if (modal) modal.style.display = 'none';
        if (progressContainer) progressContainer.style.display = 'none';
        if (messageDiv) messageDiv.innerHTML = '';
    }

    // Function to enable/disable form inputs
    function enableFormInputs(enable) {
        const form = document.getElementById('addUserForm');
        if (!form) return;
        const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea, button[type="submit"]');
        inputs.forEach(input => {
            input.disabled = !enable;
        });
    }

    // Function to verify payment status
    async function verifyPayment(chargeId, maxAttempts = 30, interval = 5000) {
        let attempts = 0;
        const messageDiv = document.getElementById('message');
        const modal = document.getElementById('addUserModal');

        while (attempts < maxAttempts) {
            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        verify_payment: 'true',
                        charge_id: chargeId
                    }).toString()
                });

                const text = await response.text();
                console.log('Verify Payment Raw Response:', text);

                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}: ${text}`);
                }

                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    throw new Error(`Invalid JSON: ${text}`);
                }

                if (result.success) {
                    updateProgressStatus(2, 'success', 'Payment verified successfully');
                    messageDiv.innerHTML = `<div class="message-success">${result.message}</div>`;
                    setTimeout(() => {
                        clearTransactionState();
                        location.reload();
                    }, 2000);
                    return true;
                } else if (result.message.includes('pending') || result.message.includes('not successful')) {
                    attempts++;
                    updateProgressStatus(2, 'pending', `Verifying payment... (${maxAttempts - attempts} attempts remaining)`);
                    await new Promise(resolve => setTimeout(resolve, interval));
                    continue;
                } else if (result.message.includes('Charge ID not found')) {
                    updateProgressStatus(2, 'error', 'Payment record not found');
                    messageDiv.innerHTML = `<div class="message-error">Charge ID ${chargeId} not found. Transaction cancelled.</div>`;
                    enableFormInputs(true);
                    return false;
                } else {
                    updateProgressStatus(2, 'error', 'Payment verification failed');
                    messageDiv.innerHTML = `<div class="message-error">${result.message}</div>`;
                    enableFormInputs(true);
                    return false;
                }
            } catch (error) {
                updateProgressStatus(2, 'error', 'Verification error');
                messageDiv.innerHTML = `<div class="message-error">Verification error: ${error.message}</div>`;
                console.error('Verification Error:', error);
                enableFormInputs(true);
                return false;
            }
        }

        updateProgressStatus(2, 'error', 'Verification timed out');
        messageDiv.innerHTML = `
            <div class="message-error">
                Payment verification timed out. Please check your payment status or try again.
                <button onclick="verifyPayment('${chargeId}')">Retry Verification</button>
            </div>`;
        enableFormInputs(true);
        return false;
    }

    // Function to open consultation modal and handle pending transactions
    function openConsultationModal() {
        const modal = document.getElementById('addUserModal');
        const progressContainer = document.getElementById('progress-container');
        const progressList = document.getElementById('progress-list');
        const messageDiv = document.getElementById('message');
        const pendingChargeId = sessionStorage.getItem('pendingChargeId');

        if (modal) modal.style.display = 'block';
        if (progressList) progressList.innerHTML = '';
        if (messageDiv) messageDiv.innerHTML = '';

        if (pendingChargeId) {
            if (progressContainer) progressContainer.style.display = 'block';
            addProgressMessage('Payment initiated successfully', 'success');
            addProgressMessage('Awaiting your approval on your phone. Please check your mobile device...', 'pending');
            addProgressMessage('Verifying payment...', 'pending');
            enableFormInputs(false);
            verifyPayment(pendingChargeId).then(success => {
                if (!success) {
                    messageDiv.innerHTML += `<div class="message-info">You can retry verification or contact support if the payment was completed.</div>`;
                }
            });
        } else {
            if (progressContainer) progressContainer.style.display = 'none';
            enableFormInputs(true);
        }
    }

    // Handle modal close warning
    const closeButton = document.querySelector('#addUserModal .close');
    if (closeButton) {
        closeButton.addEventListener('click', function(e) {
            if (sessionStorage.getItem('pendingChargeId')) {
                if (!confirm('A payment is pending approval. Closing the modal may interrupt the process. Are you sure?')) {
                    e.preventDefault();
                    return;
                }
                sessionStorage.removeItem('pendingChargeId'); // Clear pending state
            }
            document.getElementById('addUserModal').style.display = 'none';
        });
    }

    // Handle cancel button
    const cancelButton = document.querySelector('#addUserForm button[type="button"]');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            if (sessionStorage.getItem('pendingChargeId')) {
                if (!confirm('A payment is pending approval. Cancelling may interrupt the process. Are you sure?')) {
                    return;
                }
                sessionStorage.removeItem('pendingChargeId');
            }
            document.getElementById('addUserModal').style.display = 'none';
        });
    }

    // Handle form submission
    document.getElementById('addUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const messageDiv = document.getElementById('message');
        const loader = document.getElementById('loader');
        const progressContainer = document.getElementById('progress-container');
        const progressList = document.getElementById('progress-list');

        // Clear previous states but keep modal open
        messageDiv.innerHTML = '';
        progressList.innerHTML = '';
        progressContainer.style.display = 'block';
        loader.style.display = 'block';
        enableFormInputs(false); // Lock form during processing

        // Step 1: Initiating payment
        addProgressMessage('Initiating payment...');

        try {
            const response = await fetch('payment.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            console.log('Initiate Payment Raw Response:', text);

            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}: ${text}`);
            }

            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                throw new Error(`Invalid JSON: ${text}`);
            }

            console.log('Initiate Payment Parsed Response:', result);

            loader.style.display = 'none';

            if (result.success && result.charge_id) {
                updateProgressStatus(0, 'success', 'Payment initiated successfully');
                addProgressMessage('Awaiting your approval on your phone. Please check your mobile device...', 'pending');
                sessionStorage.setItem('pendingChargeId', result.charge_id);
                addProgressMessage('Verifying payment...', 'pending');
                const verified = await verifyPayment(result.charge_id);
                if (!verified) {
                    enableFormInputs(true);
                }
            } else {
                updateProgressStatus(0, 'error', 'Payment initiation failed');
                messageDiv.innerHTML = `<div class="message-error">${result.message || 'Failed to initiate payment'}</div>`;
                progressContainer.style.display = 'block';
                enableFormInputs(true);
            }
        } catch (error) {
            updateProgressStatus(0, 'error', 'Payment initiation error');
            messageDiv.innerHTML = `<div class="message-error">An error occurred: ${error.message}</div>`;
            loader.style.display = 'none';
            progressContainer.style.display = 'block';
            enableFormInputs(true);
            console.error('Initiation Error:', error);
        }
    });

 
    

  
// Print table
   

    // Phone input validation
    document.getElementById('addUserForm').addEventListener('input', function(e) {
        if (e.target.id === 'phone') {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 9) {
                value = value.slice(-9);
            }
            e.target.value = value;
            // Uncomment if Paychangu requires +265
            // e.target.value = '+265' + value;
        }
    });

    // Bind openConsultationModal to consultation button
   /* const consultationButton = document.querySelector('button[onclick="openConsultationModal()"]');
    if (consultationButton) {
        consultationButton.addEventListener('click', openConsultationModal);
    }

    // Check for pending transaction on load
    //if (sessionStorage.getItem('pendingChargeId')) {
        openConsultationModal();
    }*/
</script>


<div class="content">

    <div class="form-content">
        <form id="addUserForm" enctype="multipart/form-data">
            <div id="message"></div>
            <div id="progress-container" style="display: none;">
                <h3>Transaction Progress</h3>
                <ul id="progress-list"></ul>
            </div>
            <h2>Donation Form</h2>
            <div class="form-container">
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="fname">First Name:</label>
                            <input type="text"  name="fname" id="fname" required>
                        </div>
                        <div class="form-group">
                            <label for="lname">Last Name:</label>
                            <input type="text"  name="lname" id="lname"  required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email"  required>
                        </div>
                        
                    </div>
                </div>
               
                <div class="form-section">
                    <h3>Payment Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="fee">Donation Fee:</label>
                            <input type="text"  name="fees" id="fee" >
                        </div>
                        <div class="form-group">
                            <label for="bank">Mobile Money Operator:</label>
                            <select id="bank" name="bank" required>
                            <?php
                                if (isset($banks['data']) && is_array($banks['data'])) {
                                    foreach ($banks['data'] as $bank) {
                                        $bank_ref_id = htmlspecialchars($bank['ref_id'] ?? '');
                                        $bank_name = htmlspecialchars($bank['name'] ?? '');
                                        echo "<option value='$bank_ref_id'>$bank_name</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No networks available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="text" name="phone" id="phone" placeholder="e.g., 998xxxx60">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Pay Now</button>
                <button type="button" onclick="document.getElementById('addUserForm').reset();">Cancel</button>
            </div>
        </form>
        <div id="loader" style="display: none;">Processing payment...</div>
    </div>
</div>

<div class="footer">
    <p>© 2025 Magdalene Home for Special Needs. All Rights Reserved.</p>
    <p>A sanctuary of hope and love for children with special needs</p>
</div>
</body>
</html>