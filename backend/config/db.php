<?php
$serverName = "localhost";
$userName   = "root";
$password   = "";
$dbName     = "recipe_db";

$conn = new mysqli($serverName, $userName, $password, $dbName);

return $conn;
