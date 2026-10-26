<?php
$host = 'localhost';
$db_name = 'grannyvp_db';
$username = 'root'; // XAMPP default username
$password = '';     // XAMPP default password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Set error mode to exception to catch database issues easily
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
