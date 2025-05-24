<?php
// Include database connection
require_once '../includes/db_connect.php';

// Initialize variables
$username = '';
$error = '';

// Start session
session_start();

// Check if judge is already logged in
if (isset($_SESSION['judge_logged_in']) && $_SESSION['judge_logged_in'] === true) {
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate username and password
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = 'Username and password are required';
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Check if username exists and password matches
        try {
            $stmt = $pdo->prepare("SELECT * FROM judges WHERE username = ?");
            $stmt->execute([$username]);
            $judge = $stmt->fetch();

            if ($judge && $password === $judge['password']) {
                // Set session variables
                $_SESSION['judge_logged_in'] = true;
                $_SESSION['judge_id'] = $judge['id'];
                $_SESSION['judge_username'] = $judge['username'];
                $_SESSION['judge_display_name'] = $judge['display_name'];

                // Check if it's first login
                if ($judge['is_first_login']) {
                    // Redirect to password change page
                    header('Location: change_password.php');
                    exit;
                } else {
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $error = 'Invalid username or password';
            }
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
    <title>Judge Login - SmartScores</title>
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
                        <a class="nav-link" href="../admin/login.php">Admin Panel</a>
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
                        <h4 class="mb-0">Judge Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <p class="mb-4">Please enter your judge credentials to continue:</p>

                        <form method="POST" action="login.php">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">Default password is 'password' for new accounts.</small>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">Login</button>
                                <a href="../public/index.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
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
