<?php
$serverName = "localhost";
$userName = "recipe_user" ;
$password = "recipe_pass123";
$dbName = "recipe_db";

$conn = new mysqli($serverName, $userName, $password, $dbName);

if ($conn->connect_error) {
    die("Connection failed.");
}

return $conn;
?>