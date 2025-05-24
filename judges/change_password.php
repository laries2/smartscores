<?php
// Include database connection
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if judge is logged in
if (!isset($_SESSION['judge_logged_in']) || $_SESSION['judge_logged_in'] !== true) {
    // Redirect to login page
    header('Location: login.php');
    exit;
}

$judge_id = $_SESSION['judge_id'];

// Verify judge exists and needs to change password
try {
    $stmt = $pdo->prepare("SELECT * FROM judges WHERE id = ?");
    $stmt->execute([$judge_id]);
    $judge = $stmt->fetch();
    
    if (!$judge) {
        // Clear session and redirect to login
        session_destroy();
        header('Location: login.php?error=Invalid judge account');
        exit;
    }
    
    // If not first login, redirect to dashboard
    if (!$judge['is_first_login']) {
        header('Location: dashboard.php');
        exit;
    }
} catch(PDOException $e) {
    die("ERROR: Could not fetch judge. " . $e->getMessage());
}

// Initialize variables
$error = '';
$success = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate new password
    if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
        $error = 'Both password fields are required';
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error = 'Passwords do not match';
    } elseif ($_POST['new_password'] === 'password') {
        $error = 'New password cannot be the default password';
    } else {
        $new_password = $_POST['new_password'];
        
        try {
            // Update password and set is_first_login to false
            $stmt = $pdo->prepare("UPDATE judges SET password = ?, is_first_login = FALSE WHERE id = ?");
            $stmt->execute([$new_password, $judge_id]);
            
            $success = 'Password changed successfully. You will be redirected to the dashboard.';
            
            // Redirect after a short delay
            header("Refresh: 3; URL=dashboard.php");
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - SmartScores</title>
    <link rel="stylesheet" href="../static/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../static/css/styles.css">
    <script src="../static/bootstrap/js/jquery-3.4.0.js"></script>
    <script src="../static/bootstrap/js/bootstrap.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#">SmartScores Judge Portal</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../public/index.php">View Scoreboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Change Your Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php else: ?>
                            <p class="mb-4">Welcome, <?php echo htmlspecialchars($judge['display_name']); ?>! Since this is your first login, you need to change your default password.</p>
                            
                            <form method="POST" action="change_password.php">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">Change Password</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light py-3 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> SmartScores. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>