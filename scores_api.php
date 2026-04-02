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
$action = $_GET['action'] ?? '';

// ─── GET LEADERBOARD ───────────────────────────────────────────────────────
if ($action === 'leaderboard') {
    $result = $conn->query("
        SELECT fullname, grade, total_score
        FROM users
        ORDER BY total_score DESC
        LIMIT 20
    ");
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
    exit();
}

// ─── GET USER SCORE & COMPLETED ACTIVITIES ────────────────────────────────
if ($action === 'user_data' && isset($_GET['user'])) {
    $name = $conn->real_escape_string($_GET['user']);
    $userResult = $conn->query("SELECT id, total_score FROM users WHERE fullname = '$name'");
    if ($userResult->num_rows === 0) {
        echo json_encode(['error' => 'User not found']);
        exit();
    }
    $userData = $userResult->fetch_assoc();
    $userId = $userData['id'];
    $totalScore = $userData['total_score'];

    // Get all completed activity keys for this user
    $keysResult = $conn->query("SELECT activity_key FROM scores WHERE user_id = $userId");
    $completedKeys = [];
    while ($row = $keysResult->fetch_assoc()) {
        $completedKeys[] = $row['activity_key'];
    }

    // Get rank
    $rankResult = $conn->query("SELECT COUNT(*) as rank FROM users WHERE total_score > $totalScore");
    $rankRow = $rankResult->fetch_assoc();
    $rank = $rankRow['rank'] + 1;

    echo json_encode([
        'total_score' => $totalScore,
        'completed' => $completedKeys,
        'rank' => $rank
    ]);
    exit();
}

// ─── SAVE SCORE ───────────────────────────────────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $conn->real_escape_string($data['user'] ?? '');
    $activityKey = $conn->real_escape_string($data['activity_key'] ?? '');
    $points = intval($data['points'] ?? 0);

    if (!$name || !$activityKey || $points <= 0) {
        echo json_encode(['error' => 'Invalid data']);
        exit();
    }

    // Get user id
    $userResult = $conn->query("SELECT id FROM users WHERE fullname = '$name'");
    if ($userResult->num_rows === 0) {
        echo json_encode(['error' => 'User not found']);
        exit();
    }
    $userId = $userResult->fetch_assoc()['id'];

    // Try to insert (IGNORE duplicate = already completed)
    $insertResult = $conn->query("
        INSERT IGNORE INTO scores (user_id, activity_key, points)
        VALUES ($userId, '$activityKey', $points)
    ");

    if ($conn->affected_rows > 0) {
        // Update total score
        $conn->query("UPDATE users SET total_score = total_score + $points WHERE id = $userId");
        echo json_encode(['success' => true, 'points_added' => $points]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Already completed']);
    }
    exit();
}

echo json_encode(['error' => 'Unknown action']);
$conn->close();
?>
