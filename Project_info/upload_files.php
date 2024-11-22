<?php
session_start();
include('../connection/conect.php'); // Include your database connection

// Assuming you have fetched userID from session
$userid = $_SESSION['userid'];

// Initialize a variable for the alert message
$alertMessage = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get project ID and user ID
    $projectID = $_POST['projectID'];

    // First, check if the projectID or userid already exists in the final_submission table
    $checkQuery = "SELECT * FROM final_submissions WHERE projectID = ? AND userid = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, 'ii', $projectID, $userid);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($result) > 0) {
        // If the projectID and userid combination already exists, display an alert and stop the process
        $alertMessage = "You have already made the final submission for this project.";
        echo "<script>alert('$alertMessage');</script>";
        exit;
    }

    mysqli_stmt_close($checkStmt); // Close the statement

    // Proceed with file uploads if no duplicates are found
    $target_dir = "../uploads/";
    $report_path = $code_path = $presentation_path = "";

    // File upload validation (2MB limit)
    foreach ($_FILES as $file) {
        if ($file['size'] > 2 * 1024 * 1024) {
            $alertMessage = "File size exceeds 2MB.";
            echo "<script>alert('$alertMessage');</script>"; // Alert for file size error
            exit;
        }
    }

    // Handle Project Report upload
    if (isset($_FILES['report']) && $_FILES['report']['error'] == 0) {
        $report_path = $target_dir . "report_" . time() . "_" . basename($_FILES['report']['name']);
        move_uploaded_file($_FILES['report']['tmp_name'], $report_path);
    }

    // Handle Code Snippet upload
    if (isset($_FILES['code']) && $_FILES['code']['error'] == 0) {
        $code_path = $target_dir . "code_" . time() . "_" . basename($_FILES['code']['name']);
        move_uploaded_file($_FILES['code']['tmp_name'], $code_path);
    }

    // Handle Presentation upload
    if (isset($_FILES['presentation']) && $_FILES['presentation']['error'] == 0) {
        $presentation_path = $target_dir . "presentation_" . time() . "_" . basename($_FILES['presentation']['name']);
        move_uploaded_file($_FILES['presentation']['tmp_name'], $presentation_path);
    }

    // Insert file paths into the database
    $query = "INSERT INTO project_files (projectID, userid, report, code_snippet, presentation) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iisss', $projectID, $userid, $report_path, $code_path, $presentation_path);

    // Execute the statement and provide feedback
    if (mysqli_stmt_execute($stmt)) {
        $alertMessage = "Files uploaded and data stored successfully.";
        echo "<script>alert('$alertMessage');</script>"; // Success alert
        header('Location:submit_projects.php'); // Redirect to the project list page
    } else {
        $alertMessage = "Error: " . mysqli_error($conn);
        echo "<script>alert('$alertMessage');</script>"; // Error alert
    }

    mysqli_stmt_close($stmt);
}
?>
