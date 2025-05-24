<?php
// Include database connection
require_once '../includes/db_connect.php';

// Fetch all participants with their total scores
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.username,
            p.display_name,
            COALESCE(SUM(s.points), 0) as total_points,
            COUNT(DISTINCT s.judge_id) as judges_count
        FROM 
            participants p
        LEFT JOIN 
            scores s ON p.id = s.participant_id
        GROUP BY 
            p.id
        ORDER BY 
            total_points DESC, p.display_name
    ");
    $stmt->execute();
    $participants = $stmt->fetchAll();

    // Get total number of judges for percentage calculation
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM judges");
    $stmt->execute();
    $total_judges = $stmt->fetchColumn();
} catch(PDOException $e) {
    die("ERROR: Could not fetch participants. " . $e->getMessage());
}

// Function to determine the CSS class based on score
function getScoreClass($score) {
    if ($score >= 90) return 'bg-success text-white';
    if ($score >= 70) return 'bg-info text-white';
    if ($score >= 50) return 'bg-warning';
    if ($score > 0) return 'bg-danger text-white';
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoreboard - SmartScores</title>
    <link rel="stylesheet" href="../static/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../static/css/styles.css">
    <script src="../static/bootstrap/js/jquery-3.4.0.js"></script>
    <script src="../static/bootstrap/js/bootstrap.js"></script>
    <meta http-equiv="refresh" content="30">
    <style>
        .score-highlight {
            transition: background-color 0.5s ease;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.075);
        }
        .progress {
            height: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">SmartScores</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Scoreboard</a>
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
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="jumbotron">
            <h1 class="display-4">Live Scoreboard</h1>
            <p class="lead">Real-time scores for all participants</p>
            <hr class="my-4">
            <p>This page auto-refreshes every 30 seconds. Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
            <div class="d-flex justify-content-between">
                <div>
                    <button class="btn btn-primary mr-2" onclick="location.reload()">Refresh Now</button>
                    <a href="new_participant.php" class="btn btn-success">Add Participant</a>
                </div>
                <div>
                    <span class="badge bg-success text-white">90+</span>
                    <span class="badge bg-info text-white">70-89</span>
                    <span class="badge bg-warning">50-69</span>
                    <span class="badge bg-danger text-white">1-49</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Participants Ranking</h5>
            </div>
            <div class="card-body">
                <?php if(count($participants) > 0): ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Participant</th>
                                <th>Total Points</th>
                                <th>Judges Scored</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            $prev_score = null;
                            $rank_counter = 1;

                            foreach($participants as $index => $participant): 
                                // Handle tied ranks
                                if ($prev_score !== null && $prev_score != $participant['total_points']) {
                                    $rank = $rank_counter;
                                }
                                $prev_score = $participant['total_points'];
                                $rank_counter++;

                                // Calculate percentage of judges who have scored
                                $judges_percentage = $total_judges > 0 ? ($participant['judges_count'] / $total_judges) * 100 : 0;
                            ?>
                                <tr class="<?php echo getScoreClass($participant['total_points']); ?>">
                                    <td><?php echo $rank; ?></td>
                                    <td><?php echo htmlspecialchars($participant['display_name']); ?></td>
                                    <td class="font-weight-bold"><?php echo htmlspecialchars($participant['total_points']); ?></td>
                                    <td><?php echo $participant['judges_count']; ?> of <?php echo $total_judges; ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $judges_percentage; ?>%;" 
                                                 aria-valuenow="<?php echo $judges_percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo round($judges_percentage); ?>%
                                            </div>
                                        </div>
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

    <script>
        // Highlight new scores when the page refreshes
        document.addEventListener('DOMContentLoaded', function() {
            // This would be more sophisticated in a real app with AJAX updates
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.classList.add('score-highlight');
            });
        });
    </script>
</body>
</html>
