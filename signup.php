<?php
$conn = new mysqli(
    getenv('MYSQLHOST')     ?: $_ENV['MYSQLHOST']     ?? '',
    getenv('MYSQLUSER')     ?: $_ENV['MYSQLUSER']     ?? '',
    getenv('MYSQLPASSWORD') ?: $_ENV['MYSQLPASSWORD'] ?? '',
    getenv('MYSQLDATABASE') ?: $_ENV['MYSQLDATABASE'] ?? '',
    (int)(getenv('MYSQLPORT') ?: $_ENV['MYSQLPORT']   ?? 3306)
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $conn->real_escape_string($_POST['fullname'] ?? '');
    $grade    = $conn->real_escape_string($_POST['grade']    ?? '');
    $pass_raw = $_POST['password'] ?? '';

    if (!$name || !$grade || !$pass_raw) {
        header("Location: login.html?error=empty");
        exit();
    }

    // Проверка: не занято ли имя
    $check = $conn->query("SELECT id FROM users WHERE fullname = '$name'");
    if ($check->num_rows > 0) {
        header("Location: login.html?error=exists");
        exit();
    }

    // Проверка пароля
    if (strlen($pass_raw) < 6) {
        header("Location: login.html?error=short");
        exit();
    }

    $password = password_hash($pass_raw, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (fullname, grade, password) VALUES ('$name', '$grade', '$password')";

    if ($conn->query($sql) === TRUE) {
        $userNameEncoded = urlencode($name);
        header("Location: indkkk.html?status=success&user=$userNameEncoded");
        exit();
    } else {
        echo "DB Error: " . $conn->error;
    }
}

$conn->close();
?>
