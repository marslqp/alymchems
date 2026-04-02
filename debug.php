<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== RAILWAY ENV VARS ===\n";
echo "MYSQLHOST:     " . (getenv('MYSQLHOST')     ?: 'НЕ НАЙДЕНО') . "\n";
echo "MYSQLUSER:     " . (getenv('MYSQLUSER')     ?: 'НЕ НАЙДЕНО') . "\n";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? 'ЕСТЬ' : 'НЕ НАЙДЕНО') . "\n";
echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ?: 'НЕ НАЙДЕНО') . "\n";
echo "MYSQLPORT:     " . (getenv('MYSQLPORT')     ?: 'НЕ НАЙДЕНО') . "\n";

echo "\n=== ПОПЫТКА ПОДКЛЮЧЕНИЯ ===\n";
$conn = new mysqli(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    (int)(getenv('MYSQLPORT') ?: 3306)
);

if ($conn->connect_error) {
    echo "ОШИБКА: " . $conn->connect_error . "\n";
} else {
    echo "ПОДКЛЮЧЕНИЕ УСПЕШНО!\n";

    echo "\n=== ТАБЛИЦЫ В БД ===\n";
    $tables = $conn->query("SHOW TABLES");
    while ($row = $tables->fetch_array()) {
        echo " - " . $row[0] . "\n";
    }

    echo "\n=== СТРУКТУРА ТАБЛИЦЫ users ===\n";
    $cols = $conn->query("DESCRIBE users");
    if ($cols) {
        while ($row = $cols->fetch_assoc()) {
            echo " - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Таблица users не найдена!\n";
    }

    echo "\n=== ТЕСТОВАЯ ВСТАВКА ===\n";
    $test = $conn->query("INSERT INTO users (fullname, grade, password) VALUES ('TEST_USER', '7', 'test123')");
    if ($test) {
        echo "Вставка прошла успешно! ID: " . $conn->insert_id . "\n";
        // Удаляем тестового пользователя
        $conn->query("DELETE FROM users WHERE fullname = 'TEST_USER'");
        echo "Тестовый пользователь удалён.\n";
    } else {
        echo "Ошибка вставки: " . $conn->error . "\n";
    }
}

$conn->close();
?>
