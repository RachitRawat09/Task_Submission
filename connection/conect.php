<?php


// Database connection details
$host = 'localhost'; // Replace with your host
$db   = 'project_submission'; // Replace with your database name
$user = 'root'; // Replace with your database username
$pass = ''; // Replace with your database password, if any
$port = '3307'; // Use the correct port number

// Establish connection to the database
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}?>