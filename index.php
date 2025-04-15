<?php
session_start();
include 'db/connect.php';
if (!isset($_SESSION['judge_logged_in']) || $_SESSION['judge_logged_in'] !== true) {
    header('Location: login/login.php');
    exit();
}    
$status = $_GET['status'] ?? '';
if ($status === 'success') {
    echo '<div class="alert success">Grades submitted successfully!</div>';
} elseif ($status === 'error') {
    echo '<div class="alert error">There was an error submitting the grades.</div>';
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Project Grading</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="./login/logout.php" class="logout-btn">Logout</a>
        <form action="./dashboard/admin.php" id="gradeForm" method="POST">
            <h2>Grading System</h2>
            <div class="grid">
                <label>Group Members: <input type="text" name="group_members" required></label>
                <label>Group Number: <input type="text" name="group_number" required></label>
                <label>Project Title: <input type="text" name="project_title" required></label>
            </div>

            <table>
                <tr><th>Criteria</th><th>Developing (0-10)</th><th>Accomplished (10-15)</th><th>Total</th></tr>
                <?php
                $criteria = [
                    'Articulate requirements',
                    'Choose appropriate tools and methods for each task',
                    'Give clear and coherent oral presentation',
                    'Functioned well as a team'
                ];
                foreach ($criteria as $index => $label): ?>
                <tr>
                    <td><?= htmlspecialchars($label) ?></td>
                    <td><input type="number" class="developing" name="criteria[<?= $index ?>][developing]" min="0" max="10" onchange="calculateRowTotal(this)"></td>
                    <td><input type="number" class="accomplished" name="criteria[<?= $index ?>][accomplished]" min="10" max="15" onchange="calculateRowTotal(this)"></td>
                    <td class="row-total">0</td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3"><strong>Grand Total</strong></td>
                    <td id="grand-total">0</td>
                </tr>
            </table>

            <label>Judge's Name: <input type="text" name="judge_name" value="<?= htmlspecialchars($_SESSION['judge_name'] ?? '') ?>" readonly></label>
            <label>Comments: <textarea name="comments"></textarea></label>

            <button type="submit">Submit Grades</button>
        </form>
    </div>
    <script src="script.js"> </script>
</body>
</html>