<?php
session_start();
require_once '../db/connect.php';

if (!isset($_SESSION['judge_logged_in']) || $_SESSION['judge_logged_in'] !== true) {
    header('Location: ../login/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?status=error');
    exit();
}

try {
    // Calculate total score
    $total_score = 0;
    for ($i = 1; $i <= 4; $i++) {
        $total_score += (int)$_POST["criteria_{$i}_developing"] + (int)$_POST["criteria_{$i}_accomplished"];
    }

    // Insert grades
    $stmt = $pdo->prepare("
        INSERT INTO grades (
            group_number, group_members, project_title, judge_name,
            criteria_1, criteria_2, criteria_3, criteria_4,
            total_score, comments
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $criteria1 = (int)$_POST['criteria_1_developing'] + (int)$_POST['criteria_1_accomplished'];
    $criteria2 = (int)$_POST['criteria_2_developing'] + (int)$_POST['criteria_2_accomplished'];
    $criteria3 = (int)$_POST['criteria_3_developing'] + (int)$_POST['criteria_3_accomplished'];
    $criteria4 = (int)$_POST['criteria_4_developing'] + (int)$_POST['criteria_4_accomplished'];
    
    $stmt->execute([
        $_POST['group_number'],
        $_POST['group_members'],
        $_POST['project_title'],
        $_POST['judge_name'],
        $criteria1,
        $criteria2,
        $criteria3,
        $criteria4,
        $total_score,
        $_POST['comments']
    ]);

    // Update group averages
    updateGroupAverage($_POST['group_number'], $pdo);
    
    header('Location: ../index.php?status=success');
    exit();
} catch (PDOException $e) {
    header('Location: ../index.php?status=error');
    exit();
}

function updateGroupAverage($group_number, $pdo) {
    // Calculate new average
    $stmt = $pdo->prepare("SELECT AVG(total_score) as avg_score, COUNT(*) as judge_count FROM grades WHERE group_number = ?");
    $stmt->execute([$group_number]);
    $result = $stmt->fetch();
    
    // Insert or update average
    $stmt = $pdo->prepare("
        INSERT INTO group_averages (group_number, average_score, judge_count) 
        VALUES (?, ?, ?)
        ON CONFLICT(group_number) DO UPDATE SET
            average_score = excluded.average_score,
            judge_count = excluded.judge_count,
            last_updated = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$group_number, $result['avg_score'], $result['judge_count']]);
}
?>