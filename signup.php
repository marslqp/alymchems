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

// Сначала убедись что колонки role и subject существуют:
// ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'student';
// ALTER TABLE users ADD COLUMN IF NOT EXISTS subject VARCHAR(100) DEFAULT NULL;

define('TEACHER_SECRET_CODE', 'ALYMCHEM2025'); // поменяй на свой секретный код

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($conn->real_escape_string($_POST['fullname']      ?? ''));
    $role     = trim($conn->real_escape_string($_POST['role']          ?? 'student'));
    $pass_raw = $_POST['password'] ?? '';

    if (!$name || !$pass_raw || !$role) {
        header("Location: login.html?error=empty&form=signup");
        exit();
    }

    if (strlen($pass_raw) < 6) {
        header("Location: login.html?error=short&form=signup");
        exit();
    }

    // Проверка: не занято ли имя
    $check = $conn->query("SELECT id FROM users WHERE fullname = '$name'");
    if ($check->num_rows > 0) {
        header("Location: login.html?error=exists&form=signup");
        exit();
    }

    if ($role === 'teacher') {
        $teacher_code = $_POST['teacher_code'] ?? '';
        if ($teacher_code !== TEACHER_SECRET_CODE) {
            header("Location: login.html?error=badcode&form=signup");
            exit();
        }
        $subject = $conn->real_escape_string($_POST['subject'] ?? 'Other');
        $grade   = 'NULL';
        $password = password_hash($pass_raw, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, grade, password, role, subject)
                VALUES ('$name', NULL, '$password', 'teacher', '$subject')";
    } else {
        $grade = (int)($_POST['grade'] ?? 0);
        if ($grade < 7 || $grade > 11) {
            header("Location: login.html?error=empty&form=signup");
            exit();
        }
        $password = password_hash($pass_raw, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, grade, password, role)
                VALUES ('$name', '$grade', '$password', 'student')";
    }

    if ($conn->query($sql) === TRUE) {
        $userNameEncoded = urlencode($name);
        $redirect = ($role === 'teacher') ? 'teacher_dashboard.html' : 'index.html';
        header("Location: $redirect?status=success&user=$userNameEncoded");
        exit();
    } else {
        header("Location: login.html?error=dberror&form=signup");
        exit();
    }
}
$conn->close();
?>
