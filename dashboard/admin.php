<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

require_once '../db/connect.php';

// Get all users and grades
$users = [];
$grades = [];
$averages = [];
try {
    // Check if created_at column exists
    $createdAtExists = $pdo->query("SELECT 1 FROM pragma_table_info('users') WHERE name='created_at'")->fetchColumn();
    
    // Get users with conditional created_at
    $usersQuery = $createdAtExists ?
        "SELECT id, username, role, created_at FROM users ORDER BY created_at DESC" :
        "SELECT id, username, role, NULL as created_at FROM users ORDER BY id DESC";
    $users = $pdo->query($usersQuery)->fetchAll();
    
    // Get all grades
    $grades = $pdo->query("SELECT * FROM grades ORDER BY id DESC")->fetchAll();
    
    // Get group averages
    $averages = $pdo->query("SELECT * FROM group_averages ORDER BY average_score DESC")->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: 20px auto;
        }
        header {
            background: #333;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logout-btn {
            color: #fff;
            text-decoration: none;
            background: #d9534f;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .logout-btn:hover {
            background: #c9302c;
        }
        .dashboard-content {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .admin-badge {
            background-color: #5cb85c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .judge-badge {
            background-color: #337ab7;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        .filter-container {
            margin-bottom: 15px;
        }
        .filter-container input, .filter-container select {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .tab-container {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background: #f1f1f1;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
        }
        .tab.active {
            background: #fff;
            border: 1px solid #ddd;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <header>
        <h2>Admin Dashboard</h2>
        <a href="../login/logout.php" class="logout-btn">Logout</a>
    </header>

    <div class="container">
        <div class="dashboard-content">
            <div class="tab-container">
                <div class="tab active" onclick="switchTab('users')">User Management</div>
                <div class="tab" onclick="switchTab('grades')">Grade Submissions</div>
                <div class="tab" onclick="switchTab('averages')">Group Averages</div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Users Tab -->
            <div id="users" class="tab-content active">
                <div class="filter-container">
                    <input type="text" id="userFilter" placeholder="Filter users..." oninput="filterTable('userFilter', 'usersTable')">
                    <select id="roleFilter" onchange="filterTable('userFilter', 'usersTable')">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="judge">Judge</option>
                    </select>
                </div>
                <div class="table-container">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <span class="<?php echo $user['role'] === 'admin' ? 'admin-badge' : 'judge-badge'; ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Grades Tab -->
            <div id="grades" class="tab-content">
                <div class="filter-container">
                    <input type="text" id="gradeFilter" placeholder="Filter by group number..." oninput="filterTable('gradeFilter', 'gradesTable')">
                </div>
                <div class="table-container">
                    <table id="gradesTable">
                        <thead>
                            <tr>
                                <th>Group Number</th>
                                <th>Project Title</th>
                                <th>Judge</th>
                                <th>Total Score</th>
                                <th>Date Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['group_number']); ?></td>
                                <td><?php echo htmlspecialchars($grade['project_title']); ?></td>
                                <td><?php echo htmlspecialchars($grade['judge_name']); ?></td>
                                <td><?= htmlspecialchars($grade['total_score'] ?? 0) ?></td>
                                <td><?= !empty($grade['created_at']) ? htmlspecialchars($grade['created_at']) : 'N/A' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Averages Tab -->
            <div id="averages" class="tab-content">
                <div class="filter-container">
                    <input type="text" id="averageFilter" placeholder="Filter by group number..." oninput="filterTable('averageFilter', 'averagesTable')">
                </div>
                <div class="table-container">
                    <table id="averagesTable">
                        <thead>
                            <tr>
                                <th>Group Number</th>
                                <th>Average Score</th>
                                <th># of Judges</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($averages as $avg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($avg['group_number']); ?></td>
                                <td><?php echo number_format($avg['average_score'], 2); ?></td>
                                <td><?php echo htmlspecialchars($avg['judge_count']); ?></td>
                                <td><?php echo htmlspecialchars($avg['last_updated']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Deactivate all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activate selected tab
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        // Table filtering
        function filterTable(inputId, tableId) {
            const filter = document.getElementById(inputId).value.toLowerCase();
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            const roleFilter = inputId === 'userFilter' ? document.getElementById('roleFilter').value.toLowerCase() : '';
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let text = '';
                let roleMatch = true;
                
                // Combine all cell text for searching
                cells.forEach((cell, index) => {
                    // Skip role column for text filtering
                    if (index !== 2) {
                        text += cell.textContent.toLowerCase() + ' ';
                    }
                    
                    // Special handling for role filtering
                    if (inputId === 'userFilter' && index === 2 && roleFilter) {
                        const role = cell.querySelector('span').textContent.toLowerCase();
                        roleMatch = role.includes(roleFilter);
                    }
                });
                
                const match = text.includes(filter) && roleMatch;
                row.style.display = match ? '' : 'none';
            });
        }
    </script>
</body>
</html>