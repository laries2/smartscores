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

// Verify judge exists
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
} catch(PDOException $e) {
    die("ERROR: Could not fetch judge. " . $e->getMessage());
}

// Fetch all participants
try {
    $stmt = $pdo->prepare("SELECT * FROM participants ORDER BY display_name");
    $stmt->execute();
    $participants = $stmt->fetchAll();
} catch(PDOException $e) {
    die("ERROR: Could not fetch participants. " . $e->getMessage());
}

// Fetch scores given by this judge
try {
    $stmt = $pdo->prepare("
        SELECT scores.*, participants.display_name 
        FROM scores 
        JOIN participants ON scores.participant_id = participants.id 
        WHERE scores.judge_id = ?
    ");
    $stmt->execute([$judge_id]);
    $scores = $stmt->fetchAll();

    // Create a lookup array for easy access
    $participant_scores = [];
    foreach ($scores as $score) {
        $participant_scores[$score['participant_id']] = $score['points'];
    }
} catch(PDOException $e) {
    die("ERROR: Could not fetch scores. " . $e->getMessage());
}

// Handle score submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_score'])) {
    $participant_id = (int)$_POST['participant_id'];
    $points = (int)$_POST['points'];

    // Validate points (1-100)
    if ($points < 1 || $points > 100) {
        $error = "Points must be between 1 and 100";
    } else {
        try {
            // Check if score already exists for this participant and judge
            $stmt = $pdo->prepare("SELECT id FROM scores WHERE participant_id = ? AND judge_id = ?");
            $stmt->execute([$participant_id, $judge_id]);
            $existing_score = $stmt->fetch();

            if ($existing_score) {
                // Update existing score
                $stmt = $pdo->prepare("UPDATE scores SET points = ? WHERE id = ?");
                $stmt->execute([$points, $existing_score['id']]);
                $success = "Score updated successfully";
            } else {
                // Insert new score
                $stmt = $pdo->prepare("INSERT INTO scores (participant_id, judge_id, points) VALUES (?, ?, ?)");
                $stmt->execute([$participant_id, $judge_id, $points]);
                $success = "Score added successfully";
            }

            // Update the lookup array
            $participant_scores[$participant_id] = $points;
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge Dashboard - SmartScores</title>
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
                    <li class="nav-item active">
                        <a class="nav-link" href="dashboard.php">Scoring</a>
                    </li>
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

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1>Welcome, <?php echo htmlspecialchars($judge['display_name']); ?></h1>
                <p class="lead">Assign points to participants below (1-100)</p>
            </div>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Participants</h5>
            </div>
            <div class="card-body">
                <?php if(count($participants) > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Participant</th>
                                <th>Current Score</th>
                                <th>Assign Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($participants as $participant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['id']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['display_name']); ?></td>
                                    <td>
                                        <?php 
                                        if (isset($participant_scores[$participant['id']])) {
                                            echo htmlspecialchars($participant_scores[$participant['id']]);
                                        } else {
                                            echo 'Not scored';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="form-inline">
                                            <input type="hidden" name="participant_id" value="<?php echo $participant['id']; ?>">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="points" min="1" max="100" 
                                                       value="<?php echo isset($participant_scores[$participant['id']]) ? $participant_scores[$participant['id']] : ''; ?>" 
                                                       required>
                                                <div class="input-group-append">
                                                    <button type="submit" name="submit_score" class="btn btn-success">Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center">No participants found.</p>
                <?php endif; ?>
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
