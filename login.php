<?php
session_start();

$conn = new mysqli(
    getenv('MYSQLHOST')     ?: $_ENV['MYSQLHOST']     ?? '',
    getenv('MYSQLUSER')     ?: $_ENV['MYSQLUSER']     ?? '',
    getenv('MYSQLPASSWORD') ?: $_ENV['MYSQLPASSWORD'] ?? '',
    getenv('MYSQLDATABASE') ?: $_ENV['MYSQLDATABASE'] ?? '',
    (int)(getenv('MYSQLPORT') ?: $_ENV['MYSQLPORT']   ?? 3306)
);
if ($conn->connect_error) {
    die(json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($conn->real_escape_string($_POST['fullname'] ?? ''));
    $pass_raw = $_POST['password'] ?? '';
    $role     = trim($conn->real_escape_string($_POST['role']     ?? 'student'));

    if (!$name || !$pass_raw) {
        header("Location: login.html?loginerror=empty");
        exit();
    }

    $result = $conn->query("SELECT id, fullname, password, grade, role, subject FROM users WHERE fullname = '$name'");

    if ($result->num_rows === 0) {
        header("Location: login.html?loginerror=notfound");
        exit();
    }

    $user = $result->fetch_assoc();

    if (!password_verify($pass_raw, $user['password'])) {
        header("Location: login.html?loginerror=wrongpass");
        exit();
    }

    // Сохраняем в сессию
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['fullname'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_grade']= $user['grade'];
    $_SESSION['user_subj'] = $user['subject'];

    $userNameEncoded = urlencode($user['fullname']);

    if ($user['role'] === 'teacher') {
        header("Location: teacher_dashboard.html?user=$userNameEncoded");
    } else {
        header("Location: index.html?status=success&user=$userNameEncoded");
    }
    exit();
}
$conn->close();
?>
