<?php
session_start();
require_once '../db/connect.php';

// Verify judge is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    header('Location: ../login/login.php');
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: grade_form.php?status=error&message=Invalid+request+method');
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Validate required fields
    $required = ['project_id', 'judge_id'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Calculate scores
    $scores = [
        'articulate' => 0,
        'tools' => 0,
        'presentation' => 0,
        'teamwork' => 0
    ];
    $total = 0;

    foreach ($_POST['criteria'] as $index => $criteria) {
        // Use accomplished score if provided, otherwise developing score
        $score = !empty($criteria['accomplished']) ? $criteria['accomplished'] : $criteria['developing'];
        $score = max(0, min(15, (int)$score)); // Ensure score is 0-15

        // Map to our score fields
        switch ($index) {
            case 0: $scores['articulate'] = $score; break;
            case 1: $scores['tools'] = $score; break;
            case 2: $scores['presentation'] = $score; break;
            case 3: $scores['teamwork'] = $score; break;
        }

        $total += $score;
    }

    // Begin database transaction
    $pdo->beginTransaction();

    // Insert grade
    $stmt = $pdo->prepare("
        INSERT INTO grades (
            project_id, judge_id,
            articulate_requirements, tools_methods,
            presentation, teamwork,
            comments, total_score
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $_POST['project_id'],
        $_POST['judge_id'],
        $scores['articulate'],
        $scores['tools'],
        $scores['presentation'],
        $scores['teamwork'],
        $_POST['comments'] ?? '',
        $total
    ]);

    if (!$success) {
        throw new Exception("Failed to insert grade record");
    }

    // Update group average
    $avgStmt = $pdo->prepare("
        SELECT AVG(total_score) as avg_score, COUNT(*) as judge_count 
        FROM grades 
        WHERE project_id = ?
    ");
    $avgStmt->execute([$_POST['project_id']]);
    $avgResult = $avgStmt->fetch();

    $groupAvgStmt = $pdo->prepare("
        INSERT OR REPLACE INTO group_averages (
            project_id, average_score, judge_count
        ) VALUES (?, ?, ?)
    ");
    $groupAvgStmt->execute([
        $_POST['project_id'],
        $avgResult['avg_score'] ?? 0,
        $avgResult['judge_count'] ?? 0
    ]);

    // Commit transaction
    $pdo->commit();

    // Redirect with success message
    header('Location: ../login/login.php');
    

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    header('Location: grade_form.php?status=error&message=' . urlencode("Database error occurred"));
    exit();
} catch (Exception $e) {
    error_log("Submission error: " . $e->getMessage());
    header('Location: grade_form.php?status=error&message=' . urlencode($e->getMessage()));
    exit();
}