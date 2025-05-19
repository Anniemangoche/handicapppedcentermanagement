<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - Magdalene Home of Handicapped</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Donation page specific styles */
        .hero{
            text-align: center;
        }

        header{
            position: relative;
        }

        .auth-container{
            margin: 10px auto; /* Center the container */
            padding: 20px;
            border: none;
            border-radius: 10px;
            width: 500px; /* Increased width for better form display */
            background-color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-self: center;
        }
        
        /* Tab styling */
        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 15px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            background-color: #f0f0f0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background-color: #4CAF50;
            color: white;
        }
        
        .tab-pane {
            display: none;
            padding: 20px;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        /* Form styling */
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        /* Donation popup/modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-dialog {
            max-width: 500px;
            margin: 50px auto;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-body {
            padding: 15px;
        }
        
        .close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="index.php">MAGDALENE</a></div>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.html" id="logout-button">Logout</a>
        </nav>
    </header>
    <main>
        <section class="hero">
            <h1>Support Us</h1>
            <p>Your contributions make a difference in the lives of others.</p>
        </section>
        <section class="features">
            <div class="auth-container">
                <h2>Donate</h2>
                <div class="tabs">
                    <button class="tab-button active" data-tab="cash-donation"><i class="fas fa-money-bill-wave"></i> Cash Donations</button>
                    <button class="tab-button" data-tab="resource-donation"><i class="fas fa-box"></i> Donate Resources</button>
                </div>
                <div class="tab-content">
                    <!-- Cash Donations -->
                    <div id="cash-donation" class="tab-pane active">
                        <h3>Make a Cash Donation</h3>
                        <form id="cash-donation-form" method="post" action="verify.php">
                            <div class="form-group">
                                <input type="text" id="donor_name" name="donor_name" placeholder="Enter your name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" id="donor_email" name="donor_email" placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <input type="number" id="donation_amount" name="donation_amount" placeholder="Enter donation amount" required>
                            </div>
                            <div class="form-group">
                                <input type="text" id="donation_amount" name="donation_amount" placeholder="select operator" required>
                            </div>
                            <input type="hidden" name="donation_type" value="general">
                            <div class="payment-redirect">
                            
                            <input type="text" name="mobile Money operator:" placeholder="e.g., 99xxx50" required>
                                <button type="button" id="cash-donation-button" class="payment-button">Proceed to Payment</button>
                              
                            </div>
                        </form>
                    </div>
                    
                    <!-- Donate Resources -->
                    <div id="resource-donation" class="tab-pane">
                        <h3>Donate Resources</h3>
                        <form id="resource-donation-form">
                            <input type="text" id="resource_donor_name" name="donor_name" placeholder="Enter your name" required>
                            <input type="text" id="resource-name" name="resource-name" placeholder="Enter Resource Name" required>
                            <textarea id="resource-description" name="resource-description" placeholder="Enter Resource Description" rows="4" required></textarea>
                            <button type="submit">Donate Resource</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Donation Popup -->
        <div id="donationPopup" class="modal" tabindex="-1" role="dialog" style="display: none;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Donate to Event</h5>
                        <button type="button" class="close" onclick="closeDonationPopup()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="donationForm" method="post" action="verify.php">
                            <div class="form-group">
                                <label for="donorName">Name</label>
                                <input type="text" class="form-control" id="donorName" name="donor_name" placeholder="Enter your name" required>
                            </div>
                            <div class="form-group">
                                <label for="donorEmail">Email</label>
                                <input type="email" class="form-control" id="donorEmail" name="donor_email" placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label for="donationAmount">Amount</label>
                                <input type="number" class="form-control" id="donationAmount" name="donation_amount" placeholder="Enter donation amount" required>
                            </div>
                            <input type="hidden" id="eventName" name="event_name" value="">
                            <input type="hidden" name="donation_type" value="event">
                            <button type="button" id="start-payment-button" class="btn btn-primary" onClick="redirectToPayment()">Donate Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p>© Magdalene Home of Handicapped. All Rights Reserved.</p>
    </footer>

    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            // Add click event listeners to tab buttons
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and panes
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show corresponding pane
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Handle Cash Donation button click - DIRECT REDIRECT WITH FORM DATA
            const cashDonationButton = document.getElementById('cash-donation-button');
            if (cashDonationButton) {
                cashDonationButton.addEventListener('click', function() {
                    // Get the form values for validation
                    const donorName = document.getElementById('donor_name').value;
                    const donorEmail = document.getElementById('donor_email').value;
                    const donationAmount = document.getElementById('donation_amount').value;
                    
                    // Validate the form
                    if (!donorName || !donorEmail || !donationAmount) {
                        alert('Please fill in all required fields');
                        return;
                    }
                    
                    // Store donation data in session storage for later retrieval
                    sessionStorage.setItem('donor_name', donorName);
                    sessionStorage.setItem('donor_email', donorEmail);
                    sessionStorage.setItem('donation_amount', donationAmount);
                    sessionStorage.setItem('donation_type', 'general');
                    
                    // Generate a unique transaction reference
                    const txRef = 'DON-' + Math.floor((Math.random() * 1000000000) + 1);
                    sessionStorage.setItem('tx_ref', txRef);
                    
                    // Redirect to Paychangu payment page with return URL to verify.php
                    const returnUrl = encodeURIComponent(window.location.origin + '/verify.php?tx_ref=' + txRef);
                    window.location.href = "https://pay.paychangu.com/SC-EQQJRT?return_url=" + returnUrl;
                });
            }

            // Handle Resource Donation form submission
            const resourceDonationForm = document.getElementById('resource-donation-form');
            if (resourceDonationForm) {
                resourceDonationForm.addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent form submission
                    
                    // Get form data
                    const donorName = document.getElementById('resource_donor_name').value;
                    const resourceName = document.getElementById('resource-name').value;
                    const resourceDesc = document.getElementById('resource-description').value;
                    
                    // Here you would typically send this data to the server
                    // For demonstration, we'll just show an alert and redirect to thank you page
                    alert(`Thank you ${donorName} for donating ${resourceName}!`);
                    
                    // Store in session storage
                    sessionStorage.setItem('donor_name', donorName);
                    sessionStorage.setItem('resource_name', resourceName);
                    sessionStorage.setItem('resource_description', resourceDesc);
                    sessionStorage.setItem('donation_type', 'resource');
                    
                    // Generate a transaction reference for tracking
                    const txRef = 'RES-' + Math.floor((Math.random() * 1000000000) + 1);
                    sessionStorage.setItem('tx_ref', txRef);
                    
                    // For resource donations, we'll go directly to thank you page
                    window.location.href = "verify.php?tx_ref=" + txRef + "&status=successful&donation_type=resource";
                    
                    resourceDonationForm.reset(); // Clear the form
                });
            }
        });

        // Event donation popup functions
        function redirectToPayment() {
            const donorName = document.getElementById('donorName').value;
            const donorEmail = document.getElementById('donorEmail').value;
            const donationAmount = document.getElementById('donationAmount').value;
            const eventName = document.getElementById('eventName').value;
            
            // Validate inputs
            if (!donorName || !donorEmail || !donationAmount) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Store donation data in session storage for later retrieval
            sessionStorage.setItem('donor_name', donorName);
            sessionStorage.setItem('donor_email', donorEmail);
            sessionStorage.setItem('donation_amount', donationAmount);
            sessionStorage.setItem('event_name', eventName);
            sessionStorage.setItem('donation_type', 'event');
            
            // Generate a unique transaction reference
            const txRef = 'EVT-' + Math.floor((Math.random() * 1000000000) + 1);
            sessionStorage.setItem('tx_ref', txRef);
            
            // Redirect to Paychangu payment page with return URL to verify.php
            const returnUrl = encodeURIComponent(window.location.origin + '/verify.php?tx_ref=' + txRef);
            window.location.href = "https://pay.paychangu.com/SC-EQQJRT?return_url=" + returnUrl;
        }

        function openDonationPopup(eventName) {
            document.getElementById('eventName').value = eventName;
            document.getElementById('donationPopup').style.display = 'block';
        }
        
        function closeDonationPopup() {
            document.getElementById('donationPopup').style.display = 'none';
        }
        
        document.getElementById('donationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            redirectToPayment();
        });
    </script>
</body>
</html>