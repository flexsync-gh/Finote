<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "web2";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = mysqli_connect($host, $user, $pass, $dbname);
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
    die("Database connection failed.");
}

//echo "Connected successfully";

?>
