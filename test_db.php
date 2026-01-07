<?php
$host = "localhost";
$user = "root";
$pass = "12345"; // CHANGE THIS IF YOU HAVE A PASSWORD
$dbname = "gram_sahayak";

$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo "<h2 style='color:red'>Connection Failed</h2>";
    echo "<b>Error:</b> " . $conn->connect_error . "<br><br>";
    echo "<b>Checklist:</b><br>";
    echo "1. Is XAMPP MySQL running?<br>";
    echo "2. Did you create the database 'gram_sahayak' in phpMyAdmin?<br>";
    echo "3. Does your root user have a password? (Edit line 4 of this file to match)";
} else {
    echo "<h2 style='color:green'>Connection Successful!</h2>";
    echo "Database <b>$dbname</b> is accessible.<br>";
    
    $result = $conn->query("SHOW TABLES");
    echo "<b>Tables found:</b><br><ul>";
    while($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
}
?>