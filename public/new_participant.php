<?php
// Include database connection
require_once '../includes/db_connect.php';

// Initialize variables
$username = $display_name = '';
$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate username
    if (empty($_POST['username'])) {
        $errors['username'] = 'Username is required';
    } else {
        $username = trim($_POST['username']);

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors['username'] = 'Username already exists';
        }
    }

    // Validate display name
    if (empty($_POST['display_name'])) {
        $errors['display_name'] = 'Display name is required';
    } else {
        $display_name = trim($_POST['display_name']);
    }

    // If no errors, insert new participant
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO participants (username, display_name) VALUES (?, ?)");
            $stmt->execute([$username, $display_name]);

            // Redirect to scoreboard with success message
            header('Location: ../index.php?success=Participant added successfully');
            exit;
        } catch(PDOException $e) {
            $errors['db'] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Participant - SmartScores</title>
    <link rel="stylesheet" href="../static/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../static/css/styles.css">
    <script src="../static/bootstrap/js/jquery-3.4.0.js"></script>
    <script src="../static/bootstrap/js/bootstrap.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">SmartScores</a>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Scoreboard</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="new_participant.php">Add Participant</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../judges/login.php">Judge Portal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/login.php">Admin Panel</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1>Add New Participant</h1>
            </div>
        </div>

        <?php if(isset($errors['db'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($errors['db']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Participant Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="new_participant.php">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                               id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        <?php if(isset($errors['username'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['username']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['display_name']) ? 'is-invalid' : ''; ?>" 
                               id="display_name" name="display_name" value="<?php echo htmlspecialchars($display_name); ?>">
                        <?php if(isset($errors['display_name'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['display_name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add Participant</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
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
