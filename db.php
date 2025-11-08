<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "projekt";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}
?>
