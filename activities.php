<?php
// Strict error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "magdalene_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Send JSON error response for connection failure
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => "Database Connection Failed: " . $conn->connect_error
    ]);
    exit;
}

class TaskManager {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    // Validate staff existence
    public function isValidStaff($staff_name) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM staff_records WHERE staff_name = ?");
        $stmt->bind_param("s", $staff_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    // Add new task with improved error handling
    public function addTask($data) {
        // Validate inputs
        $requiredFields = ['title', 'description', 'assigned_to', 'due_date', 'priority', 'status'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                return [
                    'success' => false,
                    'message' => "The field '$field' is required.",
                    'error_type' => 'validation'
                ];
            }
        }

        // Validate staff existence
        if (!$this->isValidStaff($data['assigned_to'])) {
            return [
                'success' => false,
                'message' => "Staff member '{$data['assigned_to']}' is not registered.",
                'error_type' => 'staff_validation'
            ];
        }

        // Validate priority and status
        $validPriorities = ['Low', 'Medium', 'High', 'Urgent'];
        $validStatuses = ['Not Started', 'In Progress', 'Completed', 'On Hold'];

        if (!in_array($data['priority'], $validPriorities)) {
            return [
                'success' => false,
                'message' => "Invalid priority selected.",
                'error_type' => 'priority_validation'
            ];
        }

        if (!in_array($data['status'], $validStatuses)) {
            return [
                'success' => false,
                'message' => "Invalid status selected.",
                'error_type' => 'status_validation'
            ];
        }

        // Prepare SQL statement
        $stmt = $this->conn->prepare(
            "INSERT INTO activity_schedules 
            (title, description, assigned_to, due_date, priority, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );

        // Bind parameters
        $stmt->bind_param(
            "ssssss", 
            $data['title'], 
            $data['description'], 
            $data['assigned_to'], 
            $data['due_date'], 
            $data['priority'], 
            $data['status']
        );

        // Execute and handle result
        try {
            $executeResult = $stmt->execute();
            
            if ($executeResult) {
                $stmt->close();
                return [
                    'success' => true,
                    'message' => "Task successfully added for {$data['assigned_to']}"
                ];
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $stmt->close();
            return [
                'success' => false,
                'message' => "Error adding task: " . $e->getMessage(),
                'error_type' => 'database_error'
            ];
        }
    }

    // Get staff names for dropdown
    public function getStaffNames() {
        $staff_names = [];
        $result = $this->conn->query("SELECT staff_name FROM staff_records ORDER BY staff_name");
        
        while ($row = $result->fetch_assoc()) {
            $staff_names[] = $row['staff_name'];
        }
        
        return $staff_names;
    }
}

// Improved error handling for JSON responses
function sendJsonResponse($data) {
    // Clear any previous output
    ob_clean();
    
    // Set JSON header
    header('Content-Type: application/json');
    
    // Send JSON response
    echo json_encode($data);
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create task manager
        $taskManager = new TaskManager($conn);
        
        // Sanitize and prepare input data
        $taskData = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'assigned_to' => trim($_POST['assigned_to'] ?? ''),
            'due_date' => trim($_POST['due_date'] ?? ''),
            'priority' => trim($_POST['priority'] ?? ''),
            'status' => trim($_POST['status'] ?? '')
        ];
        
        // Process task addition
        $result = $taskManager->addTask($taskData);
        
        // Send JSON response
        sendJsonResponse($result);
    } catch (Exception $e) {
        // Catch any unexpected errors
        sendJsonResponse([
            'success' => false,
            'message' => 'Unexpected error: ' . $e->getMessage(),
            'error_type' => 'unexpected_error'
        ]);
    }
}

// Get staff names for dropdown
$taskManager = new TaskManager($conn);
$staffNames = $taskManager->getStaffNames();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Task | MAGDALENE Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            font-weight: bold;
        }
        input, select, textarea {
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:disabled {
            background-color: #cccccc;
        }
        #message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <form id="taskForm" method="POST">
        <h2>Add New Task</h2>
        
        <label for="title">Task Title</label>
        <input type="text" id="title" name="title" required maxlength="255">
        
        <label for="description">Description</label>
        <textarea id="description" name="description" required maxlength="1000"></textarea>
        
        <label for="assigned_to">Assigned To</label>
        <select id="assigned_to" name="assigned_to" required>
            <option value="">Select Staff Member</option>
            <?php foreach($staffNames as $name): ?>
                <option value="<?php echo htmlspecialchars($name); ?>">
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="due_date">Due Date</label>
        <input type="date" id="due_date" name="due_date" required 
               min="<?php echo date('Y-m-d'); ?>">
        
        <label for="priority">Priority</label>
        <select id="priority" name="priority" required>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Urgent">Urgent</option>
        </select>
        
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="Not Started">Not Started</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
            <option value="On Hold">On Hold</option>
        </select>
        
        <button type="submit" id="submitBtn">Add Task</button>
    </form>

    <div id="message"></div>

    <script>
    document.getElementById('taskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button
        const submitBtn = document.getElementById('submitBtn');
        const messageDiv = document.getElementById('message');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding Task...';
        messageDiv.textContent = '';
        messageDiv.className = '';
        
        const formData = new FormData(this);
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is OK and content type is JSON
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }
            return response.json();
        })
        .then(data => {
            // Display message
            messageDiv.textContent = data.message;
            messageDiv.className = data.success ? 'success' : 'error';
            
            // Reset form if successful
            if (data.success) {
                this.reset();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.textContent = 'An unexpected error occurred. Please check server response.';
            messageDiv.className = 'error';
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Add Task';
        });
    });
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>