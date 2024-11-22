
<?php




if (isset($_POST['register'])) {
    // Escape user inputs for security
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $userid = mysqli_real_escape_string($conn, $_POST['userid']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Use prepared statements to prevent SQL injection
    $check_User_Query = "SELECT * FROM users WHERE userid = ? AND username = ? AND role = ?";
    $stmt = mysqli_prepare($conn, $check_User_Query);

    // Check if the statement prepared successfully
    if ($stmt === false) {
        die("MySQL prepare statement error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'sss', $userid, $username, $role);
    mysqli_stmt_execute($stmt);
    $check_User_Result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_User_Result) > 0) {
        // User exists, proceed to insert into login_data
        $insert_Query = "INSERT INTO login_data (userid, username, role, Date_Time) VALUES (?, ?, ?, NOW())";
        $stmt_insert = mysqli_prepare($conn, $insert_Query);
        
        if ($stmt_insert === false) {
            die("MySQL prepare statement error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_insert, 'sss', $userid, $username, $role);
        $sql_execute = mysqli_stmt_execute($stmt_insert);

        if ($sql_execute) {
            // Set session variables
            $_SESSION['username'] = $username;
            $_SESSION['userid'] = $userid;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role == 'Student') {
                header("Location: Student/student_page.php"); // Redirect to the student page
                exit(); // Exit after header redirect
            } elseif ($role == 'Teacher') {
                header("Location: Teacher/teacher_page.php"); // Redirect to the teacher page
                exit(); // Exit after header redirect
            }
        } else {
            echo "<script>alert('Error in login process. Please try again.')</script>";
        }
    } else {
        // User does not exist
        echo "<script>alert('Invalid credentials. Please check your Username, User ID, and Role.')</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Form</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa; /* Light background color */
    }
    .card {
      border: none; /* Remove border */
      border-radius: 10px; /* Rounded corners */
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }
    .card-header {
      background-color: #007bff; /* Primary color for header */
      color: white; /* White text */
      border-top-left-radius: 10px; /* Rounded top corners */
      border-top-right-radius: 10px; /* Rounded top corners */
    }
    .btn-primary {
      background-color: #0056b3; /* Darker primary color */
      border: none; /* No border */
    }
    .btn-primary:hover {
      background-color: #004085; /* Even darker on hover */
    }
    .form-label {
      font-weight: bold; /* Bold labels */
    }
    .form-control {
      border-radius: 5px; /* Rounded input fields */
    }
    .footer {
      text-align: center; /* Centered footer text */
      margin-top: 20px; /* Space above the footer */
      color: #6c757d; /* Gray color */
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
          <h4>Login Form</h4>
        </div>
        <div class="card-body">
          <form action="" method="POST">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
              <label for="userid" class="form-label">User ID</label>
              <input type="text" class="form-control" id="userid" name="userid" required>
            </div>

            <div class="mb-3">
              <label for="role" class="form-label">Role</label>
              <select class="form-select" id="role" name="role" required>
                <option value="Student">Student</option>
                <option value="Teacher">Teacher</option>
              </select>
            </div>

            <div class="d-grid">
              <input type="submit" class="btn btn-primary" name="register" value="Login">
            </div>
          </form>
        </div>
      </div>
      <div class="footer">
        <p>&copy; 2024 Your Company</p>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
