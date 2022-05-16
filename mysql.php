<?php
$servername = "localhost:3309";
$username = "root";
$password = "admin";
$database = "tugaskuliah";
// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
//echo("koneksi berhasil");


?>