<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "alymchems_db";

$conn = mysqli_connect(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    getenv('MYSQLPORT')
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['fullname']);
    $grade = $conn->real_escape_string($_POST['grade']);
    $password_raw = $_POST['password'];

    // 1. Проверка: не занято ли имя
    $checkUser = $conn->query("SELECT id FROM users WHERE fullname = '$name'");
    if ($checkUser->num_rows > 0) {
        header("Location: login.html?error=exists");
        exit();
    }

    // 2. Проверка сложности пароля
    if (strlen($password_raw) < 6) {
        header("Location: login.html?error=short");
        exit();
    }
    if (count(count_chars($password_raw, 1)) == 1) {
        header("Location: login.html?error=same");
        exit();
    }

    // 3. Сохранение
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (fullname, grade, password) VALUES ('$name', '$grade', '$password_hashed')";

    if ($conn->query($sql) === TRUE) {
        // АВТОВХОД: после создания аккаунта сразу кидаем на главную с именем
        $userNameEncoded = urlencode($name);
        header("Location: indkkk.html?status=success&user=$userNameEncoded");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
