<?php
// Start session and include database connection
session_start();
include('../connection/conect.php');

// Fetch all records from the final_submissions table
$query = "
    SELECT fs.projectID, fs.userid, fs.report, fs.code_snippet, fs.presentation, 
           fs.submission_date, fs.remark, fs.rating, p.projectTitle 
    FROM final_submissions fs
    JOIN projects p ON fs.projectID = p.projectID
";

$result = mysqli_query($conn, $query);

// Check if the form is submitted for remarks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_remark'])) {
    $projectID = $_POST['projectID'];
    $userid = $_POST['userid'];
    $remark = $_POST['remark'];
    $rating = $_POST['rating'];

    // Update the submission with remark and rating
    $update_query = "
        UPDATE final_submissions 
        SET remark = ?, rating = ? 
        WHERE projectID = ? AND userid = ?
    ";

    if ($stmt = mysqli_prepare($conn, $update_query)) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, 'siis', $remark, $rating, $projectID, $userid);
        
        // Execute the statement
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error executing query: " . mysqli_error($conn); // Display error if execution fails
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn); // Display error if preparation fails
    }

    // Refresh the result set to show updated values
    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remark Projects</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0; 
        }
    </style>
    
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Final Submissions</h2>

        <!-- Check if there are any submissions -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Project Title</th>
                        <th>User ID</th>
                        <th>Report</th>
                        <th>Code Snippet</th>
                        <th>Presentation</th>
                        <th>Submission Date</th>
                        <th>Remark</th>
                        <th>Marks (1-10)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['projectID']); ?></td>
                        <td><?php echo htmlspecialchars($row['projectTitle']); ?></td>
                        <td><?php echo htmlspecialchars($row['userid']); ?></td>
                        
                        <td>
                            <a href="final-submit/<?php echo htmlspecialchars(basename($row['report'])); ?>" target="_blank">
                                <?php echo htmlspecialchars(basename($row['report'])); ?>
                            </a>
                        </td>
                        
                        <td>
                            <a href="final-submit/<?php echo htmlspecialchars(basename($row['code_snippet'])); ?>" target="_blank">
                                <?php echo htmlspecialchars(basename($row['code_snippet'])); ?>
                            </a>
                        </td>
                        
                        <td>
                            <a href="final-submit/<?php echo htmlspecialchars(basename($row['presentation'])); ?>" target="_blank">
                                <?php echo htmlspecialchars(basename($row['presentation'])); ?>
                            </a>
                        </td>
                        
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        
                        <td>
                            <form method="post" action="">
                                <textarea name="remark" class="form-control" required>
                                    <?php echo isset($row['remark']) ? htmlspecialchars($row['remark']) : ''; ?>
                                </textarea>
                        </td>
                        
                        <td>
                            <input type="number" name="rating" class="form-control" min="1" max="10" required 
                                   value="<?php echo isset($row['rating']) ? htmlspecialchars($row['rating']) : ''; ?>">
                        </td>
                        
                        <td>
                                <input type="hidden" name="projectID" value="<?php echo htmlspecialchars($row['projectID']); ?>">
                                <input type="hidden" name="userid" value="<?php echo htmlspecialchars($row['userid']); ?>">
                                
                                <button type="submit" name="submit_remark" class="btn btn-primary">Submit Remark</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No submissions found.</p>
        <?php endif; ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
