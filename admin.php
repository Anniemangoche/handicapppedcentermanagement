<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Director Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>Director Panel</h2>
            <ul>
                <li><a href="add_staff.php"><i class="fas fa-users"></i> User Management</a></li>
                <li><a href="admin_donations.html"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="child_records.php"><i class="fas fa-child"></i> Child Records</a></li>
                <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar">☰</button>
                <h1>Welcome, Director</h1>
            </header>
            <script src="js/app.js"></script>
            
            <section class="features">
                <h2>Manage Children</h2>
                <table id="children-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Caretaker</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Children -->
                    </tbody>
                </table>
                <button id="add-child">Add Child</button>
            </section>
            
            <section class="features">
                <h2>Assign Tasks to Caretakers</h2>
                <form id="task-form">
                    <label for="caretaker">Assigned to:</label>
                    <select id="caretaker" required>
                        <option value="">Select Caretaker</option>
                        <option value="1">Annie</option>
                        <option value="2">Jane</option>
                    </select>
                    
                    <label for="task">Task:</label>
                    <input type="text" id="task" placeholder="Enter task" required>
                    
                    <button type="submit">Assign Task</button>
                </form>
                <table id="tasks-table">
                    <thead>
                        <tr>
                            <th>Caretaker</th>
                            <th>Task</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Tasks -->
                    </tbody>
                </table>
                <script src="js/admin.js"></script>    
            </section>
            
        </main>
    </div>
    <script src="js/app.js"></script>
</body>
</html>
