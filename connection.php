<?php
$servername = "localhost";
$username   = "root";   // adjust if needed
$password   = "";
$dbname     = "sales_management"; // change to your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
