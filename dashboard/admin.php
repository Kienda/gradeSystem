<?php
session_start();
require_once '../db/connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

// Initialize variables
$users = [];
$grades = [];
$averages = [];
$error = '';

try {
    // Check if tables exist
    $tablesExist = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    // Get users
    if (in_array('users', $tablesExist)) {
        $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
    } else {
        $error = "Users table doesn't exist. Please run setup.";
    }
    
    // Get grades with projects and users if tables exist
    if (in_array('grades', $tablesExist) && in_array('projects', $tablesExist) && in_array('users', $tablesExist)) {
        $grades = $pdo->query("
        SELECT 
            p.group_number AS group_num,
            p.project_title,
            COALESCE(u.username, 'Unknown Judge') AS judge_name,
            g.total_score,
            datetime(g.created_at) as created_at
        FROM grades g
        INNER JOIN projects p ON g.project_id = p.id
        LEFT JOIN users u ON g.judge_id = u.id
        ORDER BY g.created_at DESC
    ")->fetchAll();
    }
    
    // Get averages with projects if tables exist
    if (in_array('group_averages', $tablesExist) && in_array('projects', $tablesExist)) {
        // Replace the averages query with this:
$averages = $pdo->query("
SELECT 
    p.group_number,
    ROUND(ga.average_score, 2) as average_score,
    ga.judge_count,
    strftime('%Y-%m-%d %H:%M', ga.last_updated) as last_updated
FROM group_averages ga
JOIN projects p ON ga.project_id = p.id
ORDER BY ga.average_score DESC
")->fetchAll();
$missingJudges = $pdo->query("
    SELECT DISTINCT g.judge_id 
    FROM grades g
    LEFT JOIN users u ON g.judge_id = u.id
    WHERE u.id IS NULL
")->fetchAll();

if (!empty($missingJudges)) {
    echo "<div class='alert error'>Warning: Grades exist for non-existent judge IDs: ";
    echo implode(', ', array_column($missingJudges, 'judge_id'));
    echo "</div>";
}
    }

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <h2>Admin Dashboard</h2>
        <a href="../login/logout.php" class="logout-btn">Logout</a>
    </header>

    <div class="container">
        <div class="tabs">
            <button class="tab-button active" onclick="openTab('users')">Users</button>
            <button class="tab-button" onclick="openTab('grades')">Grades</button>
            <button class="tab-button" onclick="openTab('averages')">Averages</button>
        </div>

        <div id="users" class="tab-content active">
            <h3>User Management</h3>
            <table>
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
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= ucfirst($user['role']) ?></td>
                        <td><?= $user['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <div id="grades" class="tab-content">
    <h3>Grade Submissions</h3>
    
    <?php
    // Show missing judge warning if needed
    if (!empty($missingJudges)): ?>
        <div class="alert error">
            Warning: Some grades were submitted by judges that no longer exist in the system.
        </div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Group</th>
                <th>Project</th>
                <th>Judge</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($grades)): ?>
                <tr>
                    <td colspan="5" class="no-data">
                        No grades found in database.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($grades as $grade): ?>
                <tr>
                    <td><?= htmlspecialchars($grade['group_num']) ?></td>
                    <td><?= htmlspecialchars($grade['project_title']) ?></td>
                    <td><?= htmlspecialchars($grade['judge_name']) ?></td>
                    <td><?= $grade['total_score'] ?></td>
                    <td><?= htmlspecialchars($grade['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
 
<div id="averages" class="tab-content">
    <h3>Group Averages</h3>
    <?php if (empty($averages)): ?>
        <div class="alert info">
            No averages calculated yet. Grades need to be submitted first.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Average Score</th>
                    <th># Judges</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($averages as $avg): ?>
                <tr>
                    <td><?= htmlspecialchars($avg['group_number'] ?? 'N/A') ?></td>
                    <td><?= number_format($avg['average_score'] ?? 0, 2) ?></td>
                    <td><?= $avg['judge_count'] ?? 0 ?></td>
                    <td><?= !empty($avg['last_updated']) ? htmlspecialchars($avg['last_updated']) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
    <script>
        function openTab(tabName) {
            const tabContents = document.getElementsByClassName('tab-content');
            const tabButtons = document.getElementsByClassName('tab-button');
            
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
                tabButtons[i].classList.remove('active');
            }
            
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>