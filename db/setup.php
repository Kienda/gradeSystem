<!-- setup.php  -->
<?php
$dbPath = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS grades (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        group_number TEXT NOT NULL,
        judge_name TEXT NOT NULL,
        project_title TEXT NOT NULL,
        group_members TEXT NOT NULL,
        total INTEGER NOT NULL,
        comments TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    $message = '';
    $isError = false;
    
    if ($adminCount == 0) {
        // Create default admin
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute(['admin', $hashedPassword]);
        
        $message = "Database tables created and default admin account set up:<br>
                   <strong>Username:</strong> admin<br>
                   <strong>Password:</strong> admin123";
    } else {
        $message = "Database tables exist and admin account already created.";
    }

} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
    $isError = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Setup</title>
    <style>
        :root {
            --primary-color: #3498db;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f9f9f9;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .setup-container {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        
        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .setup-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: <?php echo $isError ? 'var(--error-color)' : 'var(--success-color)'; ?>;
            color: white;
            text-align: left;
        }
        
        .credentials {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            text-align: left;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-icon">⚙️</div>
        <h1>System Setup</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message">
                <?php 
                if ($isError) {
                    echo '<p><strong>Error:</strong> ' . htmlspecialchars($message) . '</p>';
                } else {
                    echo $message;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$isError && $adminCount == 0): ?>
            <div class="credentials">
                <p><strong>Important:</strong> Please change the default password after logging in.</p>
            </div>
        <?php endif; ?>
        
        <a href="../login/login.php" class="btn">Continue to Login</a>
        
        <div class="footer">
            <p>Computer Science Project Grading System</p>
        </div>
    </div>
</body>
</html>