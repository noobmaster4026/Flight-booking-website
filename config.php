<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "flightbookingdb";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection Failed: ". $conn->connect_error);
}
?>
