<?php
$conn = new mysqli(
    getenv('MYSQLHOST')     ?: $_ENV['MYSQLHOST'],
    getenv('MYSQLUSER')     ?: $_ENV['MYSQLUSER'],
    getenv('MYSQLPASSWORD') ?: $_ENV['MYSQLPASSWORD'],
    getenv('MYSQLDATABASE') ?: $_ENV['MYSQLDATABASE'],
    (int)(getenv('MYSQLPORT') ?: $_ENV['MYSQLPORT'] ?: 3306)
);

if ($conn->connect_error) {
    die(json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['fullname']);
    $grade = $conn->real_escape_string($_POST['grade']);
    $password_raw = $_POST['password'];

    $checkUser = $conn->query("SELECT id FROM users WHERE fullname = '$name'");
    if ($checkUser->num_rows > 0) {
        header("Location: login.html?error=exists");
        exit();
    }

    if (strlen($password_raw) < 6) {
        header("Location: login.html?error=short");
        exit();
    }
    if (count(count_chars($password_raw, 1)) == 1) {
        header("Location: login.html?error=same");
        exit();
    }

    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (fullname, grade, password) VALUES ('$name', '$grade', '$password_hashed')";

    if ($conn->query($sql) === TRUE) {
        $userNameEncoded = urlencode($name);
        header("Location: index.html?status=success&user=$userNameEncoded");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
