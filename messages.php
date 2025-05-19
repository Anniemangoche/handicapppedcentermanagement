<?php
// Start session and database connection
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

// Handle filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$whereClause = '';
if ($filter === 'unread') {
    $whereClause = "WHERE status = 'unread'";
} elseif ($filter === 'read') {
    $whereClause = "WHERE status = 'read'";
} elseif ($filter === 'replied') {
    $whereClause = "WHERE status = 'replied'";
}

// Fetch messages ordered by submitted_at DESC
$query = "SELECT * FROM contact_messages $whereClause ORDER BY submitted_at DESC";
$result = $conn->query($query);

// Handle Mark as Read action (triggered after confirmation via JavaScript)
if (isset($_GET['action']) && $_GET['action'] === 'mark_read' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $updateQuery = "UPDATE contact_messages SET status = 'read' WHERE id = $id";
    if ($conn->query($updateQuery) === TRUE) {
        header("Location: messages.php?filter=$filter&success=Message marked as read");
        exit;
    }
}

// Handle success/error messages
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Magdalene Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54; /* Main brown color */
            --secondary-color: #7a5b47; /* Darker shade for hover */
            --accent-color: #e74c3c; /* For errors */
            --background-color: #ffffff; /* White body background */
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .sidebar h2 {
            padding: 20px;
            font-size: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            color: white;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: var(--secondary-color);
            border-radius: 4px;
            margin: 0 10px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .topbar .user-actions {
            display: flex;
            gap: 15px;
        }

        .topbar .user-actions a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }

        .topbar .user-actions a:hover {
            color: var(--secondary-color);
        }

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary-color);
        }

        .message-container {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 1000px;
            margin: 0 auto;
        }

        .message-container h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-buttons a {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: var(--card-bg);
            color: var(--primary-color);
            text-decoration: none;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .filter-buttons a.active,
        .filter-buttons a:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .filter-buttons a i {
            margin-right: 8px;
        }

        .message-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .message-item {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease;
        }

        .message-item:hover {
            transform: translateY(-2px);
        }

        .message-content {
            flex: 1;
        }

        .message-content h3 {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .message-content p {
            color: var(--text-color);
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        .message-meta {
            font-size: 0.85rem;
            color: #777;
        }

        .message-actions {
            display: flex;
            gap: 10px;
        }

        .message-actions a {
            color: var(--primary-color);
            font-size: 1.2rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .message-actions a:hover {
            color: var(--secondary-color);
        }

        .no-messages {
            text-align: center;
            color: #777;
            font-size: 1rem;
            padding: 20px;
        }

        /* Confirmation Modal */
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .confirmation-content {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .confirmation-content p {
            margin-bottom: 20px;
            color: var(--text-color);
            font-size: 1rem;
        }

        .confirmation-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .confirmation-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .confirmation-buttons .confirm-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .confirmation-buttons .confirm-btn:hover {
            background-color: var(--secondary-color);
        }

        .confirmation-buttons .cancel-btn {
            background-color: #ccc;
            color: var(--text-color);
        }

        .confirmation-buttons .cancel-btn:hover {
            background-color: #bbb;
        }

        /* Reply Modal */
        .reply-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .reply-content {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 600px;
            width: 90%;
            position: relative;
        }

        .reply-content h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            color: var(--primary-color);
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .close-modal:hover {
            color: var(--secondary-color);
        }

        .reply-form .form-group {
            margin-bottom: 20px;
        }

        .reply-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-color);
        }

        .reply-form input,
        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .reply-form input[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        .reply-form textarea {
            min-height: 150px;
            resize: vertical;
        }

        .reply-form input:focus,
        .reply-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .reply-form button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s ease;
            width: 100%;
        }

        .reply-form button[type="submit"]:hover {
            background-color: var(--secondary-color);
        }

        /* Popup */
        .popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s, slideOut 0.3s 2.7s forwards;
        }

        .popup.success {
            background-color: #27ae60;
        }

        .popup.error {
            background-color: var(--accent-color);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
            }

            .topbar {
                flex-wrap: wrap;
                gap: 10px;
            }

            .topbar h1 {
                font-size: 1.5rem;
            }

            .message-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .message-actions {
                justify-content: flex-end;
                width: 100%;
            }

            .confirmation-content,
            .reply-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>Director Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php" id="dashboard-btn"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="child_retrive.php" id="child-records-btn"><i class="fas fa-child"></i> Child Records</a></li>
                <li><a href="auth/addstaff_retrive.php" id="user_management.php"><i class="fas fa-users"></i> Staff Management</a></li>
                <li><a href="admin_don.php" id="donations-btn"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php" id="events-btn"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="donated_materials.php"><i class="fas fa-box"></i> Donated Materials</a></li>
                <li><a href="tasks.php" id="tasks-btn"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php" id="messages-btn" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php" id="inventory-btn"><i class="fas fa-basees"></i> Inventory</a></li>
                <li><a href="reports.php" id="reports-btn"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>Messages</h1>
                <div class="user-actions">
                    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <section class="message-container">
                <h2>Messages</h2>
                <div class="filter-buttons">
                    <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-inbox"></i> All
                    </a>
                    <a href="?filter=unread" class="<?php echo $filter === 'unread' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Unread
                    </a>
                    <a href="?filter=read" class="<?php echo $filter === 'read' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope-open"></i> Read
                    </a>
                    <a href="?filter=replied" class="<?php echo $filter === 'replied' ? 'active' : ''; ?>">
                        <i class="fas fa-reply"></i> Replied
                    </a>
                </div>

                <div class="message-list">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="message-item" data-id="<?php echo $row['id']; ?>" data-email="<?php echo htmlspecialchars($row['email']); ?>">
                                <div class="message-content">
                                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                                    <div class="message-meta">
                                        From: <?php echo htmlspecialchars($row['email']); ?> | 
                                        Subject: <?php echo htmlspecialchars($row['subject']); ?> | 
                                        Sent: <?php echo date('M d, Y H:i', strtotime($row['submitted_at'])); ?> | 
                                        Status: <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                    </div>
                                </div>
                                <div class="message-actions">
                                    <a href="#" class="reply-btn" title="Reply">
                                        <i class="fas fa-reply"></i>
                                    </a>
                                    <?php if ($row['status'] !== 'read' && $row['status'] !== 'replied'): ?>
                                        <a href="#" class="mark-read-btn" title="Mark as Read">
                                            <i class="fas fa-envelope-open"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-messages">No messages found.</div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Confirmation Modal -->
    <div class="confirmation-modal" id="confirmationModal">
        <div class="confirmation-content">
            <p>Do you want to mark this message as read?</p>
            <div class="confirmation-buttons">
                <button class="confirm-btn" id="confirmMarkRead">Yes</button>
                <button class="cancel-btn" id="cancelMarkRead">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="reply-modal" id="replyModal">
        <div class="reply-content">
            <span class="close-modal" id="closeReplyModal">×</span>
            <h2>Reply to Message</h2>
            <form class="reply-form" method="POST" action="send_reply.php">
                <div class="form-group">
                    <label for="reply_to">To</label>
                    <input type="email" id="reply_to" name="to" readonly required>
                </div>
                <div class="form-group">
                    <label for="reply_message">Message</label>
                    <textarea id="reply_message" name="message" required></textarea>
                </div>
                <input type="hidden" name="message_id" id="message_id">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <!-- Success/Error Popups -->
    <?php if (!empty($success)): ?>
    <div class="popup success">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="popup error">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <script>
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmMarkRead = document.getElementById('confirmMarkRead');
        const cancelMarkRead = document.getElementById('cancelMarkRead');
        const replyModal = document.getElementById('replyModal');
        const closeReplyModal = document.getElementById('closeReplyModal');
        const replyToInput = document.getElementById('reply_to');
        const replyMessage = document.getElementById('reply_message');
        const messageIdInput = document.getElementById('message_id');

        let currentMessageId = null;
        let currentFilter = '<?php echo $filter; ?>';

        // Sidebar toggle
        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Mark as Read buttons
        document.querySelectorAll('.mark-read-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                currentMessageId = button.closest('.message-item').dataset.id;
                confirmationModal.style.display = 'flex';
            });
        });

        // Confirm Mark as Read
        confirmMarkRead.addEventListener('click', () => {
            if (currentMessageId) {
                window.location.href = `?action=mark_read&id=${currentMessageId}&filter=${currentFilter}`;
            }
            confirmationModal.style.display = 'none';
        });

        // Cancel Mark as Read
        cancelMarkRead.addEventListener('click', () => {
            confirmationModal.style.display = 'none';
            currentMessageId = null;
        });

        // Reply buttons
        document.querySelectorAll('.reply-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const messageItem = button.closest('.message-item');
                const email = messageItem.dataset.email;
                const id = messageItem.dataset.id;

                replyToInput.value = email;
                messageIdInput.value = id;
                replyMessage.value = '';
                replyModal.style.display = 'flex';
            });
        });

        // Close Reply Modal
        closeReplyModal.addEventListener('click', () => {
            replyModal.style.display = 'none';
        });

        // Close modal on outside click
        replyModal.addEventListener('click', (e) => {
            if (e.target === replyModal) {
                replyModal.style.display = 'none';
            }
        });

        confirmationModal.addEventListener('click', (e) => {
            if (e.target === confirmationModal) {
                confirmationModal.style.display = 'none';
            }
        });

        // Auto-remove popup
        const popups = document.querySelectorAll('.popup');
        popups.forEach(popup => {
            setTimeout(() => {
                popup.style.animation = 'slideOut 0.3s forwards';
                setTimeout(() => popup.remove(), 300);
            }, 3000);
        });

        // Highlight active sidebar item
        document.addEventListener('DOMContentLoaded', () => {
            const path = window.location.pathname;
            const sidebarLinks = {
                'dashboard.php': 'dashboard-btn',
                'child_retrive.php': 'child-records-btn',
                'addstaff_retrive.php': 'staff-management-btn',
                'admin_donation.php': 'donations-btn',
                'eventsadd.php': 'events-btn',
                'tasks.php': 'tasks-btn',
                'messages.php': 'messages-btn',
                'inventory.php': 'inventory-btn',
                'reports.php': 'reports-btn'
            };

            for (const [url, id] of Object.entries(sidebarLinks)) {
                if (path.includes(url)) {
                    document.getElementById(id)?.classList.add('active');
                    break;
                }
            }
        });
    </script>
</body>
</html>