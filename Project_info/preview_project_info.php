<?php
// Start session to access session variables
session_start();

// Ensure database connection
include('../connection/conect.php');

// Fetch the user's submitted projects using 'userid'
$userid = $_SESSION['userid'];

// Modify the query to join project_team_members and projects tables
$query = "
    SELECT p.*, GROUP_CONCAT(ptm.userid) AS team_members
    FROM projects p
    JOIN project_team_members ptm ON p.projectID = ptm.projectID
    WHERE ptm.userid = '$userid'
    GROUP BY p.projectID
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Submitted Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
        }
        h3 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40; /* Darker text color */
        }
        h4 {
            margin-top: 30px;
            color: #007bff; /* Bootstrap primary color */
        }
        table {
            border: 2px solid #007bff; /* Table border color */
            border-radius: 5px;
            overflow: hidden;
            background-color: #ffffff; /* White background for tables */
        }
        th {
            background-color: #007bff; /* Header background color */
            color: white; /* Header text color */
        }
        td {
            border: 1px solid #007bff; /* Cell border color */
        }
        .status {
            font-weight: bold;
        }
        .status.Pending {
            color: red;
        }
        .status.Approved {
            color: green;
        }
        .status.Unknown {
            color: orange;
        }
        .card {
            margin-bottom: 20px; /* Space between cards */
            border: 1px solid #007bff; /* Card border color */
            border-radius: 5px; /* Card border radius */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3>My Submitted Projects</h3>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <h4>Project ID: <?php echo htmlspecialchars($row['projectID']); ?></h4>
            <table class="table table-bordered mb-4">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Team Members</th>
                        <th>Description</th>
                        <th>Submission Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($row['projectTitle']); ?></td>
                        <td><?php echo htmlspecialchars($row['team_members']); ?></td>
                        <td><?php echo htmlspecialchars($row['projectDescription']); ?></td>
                        <td><?php echo htmlspecialchars($row['submissionDate']); ?></td>
                        <td class="status <?php echo htmlspecialchars($row['status']); ?>">
                            <?php if ($row['status'] === 'Pending'): ?>
                                Pending
                            <?php elseif ($row['status'] === 'Approved'): ?>
                                Approved
                            <?php else: ?>
                                Status Unknown
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No projects submitted yet.</p>
    <?php endif; ?>
</div>

<!-- Close database connection -->
<?php mysqli_close($conn); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
