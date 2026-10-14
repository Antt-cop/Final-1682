<?php
// db_connect.php - Database connection helper
// Adjust the credentials as needed for your environment.

function getDbConnection() {
    $servername = "localhost"; // Typically localhost for XAMPP/Laragon
    $username   = "root";      // Default MySQL user for local setups
    $password   = "";          // Empty password for default XAMPP/Laragon installations
    $dbname     = "dongkhoistore"; // Updated database name for B2B platform

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Set charset to support UTF-8 (including emojis)
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
