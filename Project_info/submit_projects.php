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

// Initialize an array to hold uploaded filenames
$fileUploads = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Final submission handling
    $projectID = $_POST['projectID'];
    
    // Handle file uploads
    if (isset($_FILES['report'], $_FILES['code'], $_FILES['presentation'])) {
        // Define the upload directory (uploads)
        $uploadDirectory = 'uploads/';
        
        // Ensure the upload directory exists
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true); // Create the directory if it doesn't exist
        }

        // Use original filenames for uploaded files
        $reportFile = basename($_FILES['report']['name']);
        $codeFile = basename($_FILES['code']['name']);
        $presentationFile = basename($_FILES['presentation']['name']);

        // Move uploaded files to the uploads directory
        move_uploaded_file($_FILES['report']['tmp_name'], $uploadDirectory . $reportFile);
        move_uploaded_file($_FILES['code']['tmp_name'], $uploadDirectory . $codeFile);
        move_uploaded_file($_FILES['presentation']['tmp_name'], $uploadDirectory . $presentationFile);

        // Insert filenames into the project_files table without checks for duplicates
        $query = "INSERT INTO project_files (projectID, userid, report, code_snippet, presentation) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iisss', $projectID, $userid, $reportFile, $codeFile, $presentationFile);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Files submitted successfully.');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }

    // Final submission handling
    if (isset($_POST['final_submission'])) {
        // Define the directory for final submission
        $finalSubmitDirectory = 'final-submit/';
        
        // Ensure the final submission directory exists
        if (!is_dir($finalSubmitDirectory)) {
            mkdir($finalSubmitDirectory, 0777, true); // Create the directory if it doesn't exist
        }

        // Define paths for the final submission files using original filenames
        $finalReportFilePath = $finalSubmitDirectory . $reportFile;
        $finalCodeFilePath = $finalSubmitDirectory . $codeFile;
        $finalPresentationFilePath = $finalSubmitDirectory . $presentationFile;

        // Move the files from the uploads directory to the final-submit directory
        if (file_exists($uploadDirectory . $reportFile)) {
            rename($uploadDirectory . $reportFile, $finalReportFilePath);
        }
        if (file_exists($uploadDirectory . $codeFile)) {
            rename($uploadDirectory . $codeFile, $finalCodeFilePath);
        }
        if (file_exists($uploadDirectory . $presentationFile)) {
            rename($uploadDirectory . $presentationFile, $finalPresentationFilePath);
        }

        // Insert the new file paths into the final_submissions table
        $query = "INSERT INTO final_submissions (projectID, userid, report, code_snippet, presentation, submission_date) VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iisss', $projectID, $userid, $finalReportFilePath, $finalCodeFilePath, $finalPresentationFilePath);

        // Execute the query for final submission
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Final submission successful. Files moved to final-submit directory.');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}
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
                        <th>Status</th>
                        <th>Project Report</th> <!-- New column for Project Report -->
                        <th>Code Snippet</th>   <!-- New column for Code Snippet -->
                        <th>Presentation</th>    <!-- New column for Presentation -->
                        <th>Remarks</th>    <!-- New column for Presentation -->
                        <th>Marks</th>    <!-- New column for Presentation -->
                    </tr>
                </thead>
                <tbody>
    <tr>
        <td><?php echo htmlspecialchars($row['projectTitle']); ?></td>
        <td><?php echo htmlspecialchars($row['team_members']); ?></td>
        <td class="status <?php echo htmlspecialchars($row['status']); ?>">
            <?php echo htmlspecialchars($row['status']); ?>
        </td>
        <td>
            <?php
            // Fetch the latest uploaded files for the project
            $fileQuery = "
                SELECT * FROM project_files 
                WHERE projectID = ? AND userid = ? 
                ORDER BY uploadDate DESC LIMIT 1"; // Get only the latest file
            $fileStmt = mysqli_prepare($conn, $fileQuery);
            mysqli_stmt_bind_param($fileStmt, 'ii', $row['projectID'], $userid);
            mysqli_stmt_execute($fileStmt);
            $fileResult = mysqli_stmt_get_result($fileStmt);

            // Initialize variables for uploaded file names
            $reportFileName = "No files uploaded yet.";
            $codeFileName = "No files uploaded yet.";
            $presentationFileName = "No files uploaded yet.";

            if (mysqli_num_rows($fileResult) > 0) {
                $fileRow = mysqli_fetch_assoc($fileResult);
                $reportFileName = htmlspecialchars($fileRow['report']);
                $codeFileName = htmlspecialchars($fileRow['code_snippet']);
                $presentationFileName = htmlspecialchars($fileRow['presentation']);
            }

            echo $reportFileName; // Display report filename
            ?>
        </td>
        <td>
            <?php echo $codeFileName; // Display code snippet filename ?>
        </td>
        <td>
            <?php echo $presentationFileName; // Display presentation filename ?>
        </td>
        
        <?php
        // Fetch remarks and marks from the final_submissions table
        $finalQuery = "
            SELECT remark, rating FROM final_submissions 
            WHERE projectID = ? AND userid = ?";
        $finalStmt = mysqli_prepare($conn, $finalQuery);
        mysqli_stmt_bind_param($finalStmt, 'ii', $row['projectID'], $userid);
        mysqli_stmt_execute($finalStmt);
        $finalResult = mysqli_stmt_get_result($finalStmt);

        // Initialize variables for remarks and marks
        $remarks = "No remarks available.";
        $marks = "Not graded yet.";

        if (mysqli_num_rows($finalResult) > 0) {
            $finalRow = mysqli_fetch_assoc($finalResult);
            $remarks = htmlspecialchars($finalRow['remark']);
            $marks = htmlspecialchars($finalRow['rating']);
        }
        ?>
        
        <td><?php echo $remarks; // Display remarks ?></td>
        <td><?php echo $marks; // Display marks ?></td>
    </tr>
</tbody>

            </table>

            <?php
            // Check if the project has been finally submitted by querying the final_submissions table
            $finalQuery = "
                SELECT * FROM final_submissions 
                WHERE projectID = ? AND userid = ?
            ";
            $finalStmt = mysqli_prepare($conn, $finalQuery);
            mysqli_stmt_bind_param($finalStmt, 'ii', $row['projectID'], $userid);
            mysqli_stmt_execute($finalStmt);
            $finalResult = mysqli_stmt_get_result($finalStmt);

            // Flag to indicate if the final submission has been made
            $finalSubmitted = mysqli_num_rows($finalResult) > 0;
            ?>

            <!-- Show file upload form only if project is Approved -->
            <?php if ($row['status'] === 'Approved'): ?>
                <form action="" method="post" enctype="multipart/form-data"> <!-- Updated action to submit to the same page -->
                    <input type="hidden" name="projectID" value="<?php echo htmlspecialchars($row['projectID']); ?>">

                    <div class="mb-3">
                        <label for="report" class="form-label">Upload Project Report (PDF, max 2MB):</label>
                        <input type="file" name="report" id="report" class="form-control" accept=".pdf" 
                            <?php echo $finalSubmitted ? 'disabled' : ''; ?> required>
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Upload Code Snippet (PDF, max 2MB):</label>
                        <input type="file" name="code" id="code" class="form-control" accept=".pdf"
                            <?php echo $finalSubmitted ? 'disabled' : ''; ?> required>
                    </div>

                    <div class="mb-3">
                        <label for="presentation" class="form-label">Upload Presentation (PPT, max 2MB):</label>
                        <input type="file" name="presentation" id="presentation" class="form-control" accept=".ppt, .pptx"
                            <?php echo $finalSubmitted ? 'disabled' : ''; ?> required>
                    </div>

                    <!-- Disable buttons if the final submission has been made -->
                    <?php if (!$finalSubmitted): ?>
                        <button type="submit" class="btn btn-primary">Submit Files</button>
                        <button type="submit" name="final_submission" class="btn btn-success">Final Submission</button>
                    <?php else: ?>
                        <p class="text-success">Final submission has already been made. No further changes allowed.</p>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No projects found.</p>
    <?php endif; ?>
</div>

</body>
</html>   