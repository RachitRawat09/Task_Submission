<?php
// Ensure database connection
include('../connection/conect.php');

// Initialize a variable to hold the alert message
$alertMessage = "";

// Check if the approval form has been submitted
if (isset($_POST['approve_project'])) {
    $projectID = $_POST['projectID'];

    // Use prepared statements to prevent SQL injection
    $approveQuery = "UPDATE projects SET status = 'Approved' WHERE projectID = ?";
    $stmt = $conn->prepare($approveQuery);
    $stmt->bind_param("s", $projectID); // 's' specifies the variable type => 'string'
    
    if ($stmt->execute()) {
        $alertMessage = "Project ID $projectID has been approved successfully!";
    } else {
        $alertMessage = "Error approving project: " . $stmt->error;
    }
    $stmt->close(); // Close the statement
}

// Fetch all projects for review
$fetch_Projects_Query = "
    SELECT p.*, GROUP_CONCAT(ptm.userid) AS teamMembers
    FROM projects p
    LEFT JOIN project_team_members ptm ON p.projectID = ptm.projectID
    GROUP BY p.projectID
";
$projects_Result = mysqli_query($conn, $fetch_Projects_Query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Light background color */
        }
        table {
            border: 1px solid #dee2e6; /* Border for the table */
        }
        th, td {
            vertical-align: middle; /* Center align cells vertically */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3>Review Submitted Projects</h3>
    
    <?php
    // Show the alert message if it exists
    if ($alertMessage != "") {
        echo "<script>alert('$alertMessage');</script>";
        // Optionally redirect to the same page after the alert
        echo "<script>window.location.href = '../Teacher/teacher_page.php';</script>";
    }
    ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Project ID</th>
                <th>Project Title</th>
                <th>Team Members</th>
                <th>Project Description</th>
                <th>Submission Date</th>
                <th>Status</th>
                <th>Actions</th> <!-- New column for actions -->
            </tr>
        </thead>
        <tbody>
        <?php
        while ($row = mysqli_fetch_assoc($projects_Result)) {
            echo "<tr>";
            echo "<td>{$row['projectID']}</td>";
            echo "<td>{$row['projectTitle']}</td>";
            echo "<td>{$row['teamMembers']}</td>"; // Display team members
            echo "<td>{$row['projectDescription']}</td>";
            echo "<td>{$row['submissionDate']}</td>";
            echo "<td>";
            echo $row['status'] == 'Pending' ? "<span class='badge bg-warning'>Pending</span>" : "<span class='badge bg-success'>Approved</span>";
            echo "</td>";
            echo "<td>";

            if ($row['status'] == 'Pending') {
                echo "<form method='POST'>"; // Form for approval
                echo "<input type='hidden' name='projectID' value='{$row['projectID']}'>";
                echo "<button type='submit' name='approve_project' class='btn btn-success'>Approve</button>";
                echo "</form>";
            }

            echo "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
