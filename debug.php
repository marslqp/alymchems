<?php
header('Content-Type: text/plain');

echo "=== ENV VARS ===\n";
echo "MYSQLHOST: " . (getenv('MYSQLHOST') ?: 'NOT FOUND') . "\n";
echo "MYSQLUSER: " . (getenv('MYSQLUSER') ?: 'NOT FOUND') . "\n";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ?: 'NOT FOUND') . "\n";
echo "MYSQLPORT: " . (getenv('MYSQLPORT') ?: 'NOT FOUND') . "\n";

echo "\n=== CONNECTION TEST ===\n";
$conn = new mysqli(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    (int)getenv('MYSQLPORT')
);

if ($conn->connect_error) {
    echo "FAILED: " . $conn->connect_error . "\n";
} else {
    echo "SUCCESS!\n";
    
    // Проверяем таблицу users
    $result = $conn->query("SHOW TABLES");
    echo "\n=== TABLES ===\n";
    while($row = $result->fetch_array()) {
        echo $row[0] . "\n";
    }
}
?>
