<?php
session_start(); // Start the session

// Ensure database connection
include('../connection/conect.php');

// Check if form is submitted
if (isset($_POST['submit_project'])) {
    $projectID = $_POST['projectID'];
    $projectTitle = $_POST['projectTitle'];
    $teamMember1 = $_POST['teamMember1']; // Required team member (userid)
    $teamMember2 = !empty($_POST['teamMember2']) ? $_POST['teamMember2'] : null; // Optional team member (userid)
    $projectDescription = $_POST['projectDescription'];
    $userid = $_SESSION['userid']; // The logged-in user's userid

    // Combine team members into a string for the 'projects' table
    $teamMembers = $teamMember2 ? "$teamMember1, $teamMember2" : $teamMember1;

    // Check if any team member (userid) is already associated with the same projectID in the team member table
    $checkTeamMemberQuery = "
        SELECT COUNT(*) AS count 
        FROM project_team_members 
        WHERE projectID = '$projectID' AND userid IN ('$teamMember1', '$teamMember2')
    ";
    $checkTeamMemberResult = mysqli_query($conn, $checkTeamMemberQuery);
    $teamMemberCount = mysqli_fetch_assoc($checkTeamMemberResult)['count'];

    if ($teamMemberCount > 0) {
        echo "<script>alert('One or both team members are already associated with this projectID. Please use a different projectID.')</script>";
        exit; // Stop further execution if validation fails
    }

    // Check if the user has already submitted this project
    $checkUserProjectQuery = "
        SELECT * 
        FROM projects 
        WHERE projectID = '$projectID' AND userid = '$userid'
    ";
    $checkUserProjectResult = mysqli_query($conn, $checkUserProjectQuery);

    if (mysqli_num_rows($checkUserProjectResult) > 0) {
        echo "<script>alert('You have already submitted this project. You cannot submit the same project twice.')</script>";
    } else {
        // Insert the project data into the projects table
        $insert_Project_Query = "
            INSERT INTO projects (projectID, projectTitle, projectDescription, submissionDate, userid, status) 
            VALUES ('$projectID', '$projectTitle', '$projectDescription', NOW(), '$userid', 'Pending')
        ";
        $insertProject = mysqli_query($conn, $insert_Project_Query);

        if ($insertProject) {
            // Insert team members into the project_team_members table
            $insertTeamMemberQuery1 = "
                INSERT INTO project_team_members (projectID, userid) 
                VALUES ('$projectID', '$teamMember1')
            ";
            mysqli_query($conn, $insertTeamMemberQuery1);

            if ($teamMember2) {
                $insertTeamMemberQuery2 = "
                    INSERT INTO project_team_members (projectID, userid) 
                    VALUES ('$projectID', '$teamMember2')
                ";
                mysqli_query($conn, $insertTeamMemberQuery2);
            }

            // Redirect to the student page after successful submission
             
            header("Location: ../Student/student_page.php?success=1");
            exit; // Make sure to exit after redirecting
        } else {
            echo "<script>alert('Error submitting project. Please try again.')</script>";
        }
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Submit Project Information</h4>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="projectID" class="form-label">Project ID</label>
                            <input type="text" class="form-control" id="projectID" name="projectID" required>
                        </div>
                        <div class="mb-3">
                            <label for="projectTitle" class="form-label">Project Title</label>
                            <input type="text" class="form-control" id="projectTitle" name="projectTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="teamMember1" class="form-label">Team Member 1 (Required - UserID)</label>
                            <input type="text" class="form-control" id="teamMember1" name="teamMember1" required>
                        </div>
                        <div class="mb-3">
                            <label for="teamMember2" class="form-label">Team Member 2 (Optional - UserID)</label>
                            <input type="text" class="form-control" id="teamMember2" name="teamMember2">
                        </div>
                        <div class="mb-3">
                            <label for="projectDescription" class="form-label">Project Description</label>
                            <textarea class="form-control" id="projectDescription" name="projectDescription" rows="4" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" name="submit_project">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
