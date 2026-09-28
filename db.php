<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "learnora_db";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Auto-initialize tables and lessons if needed
require_once __DIR__ . "/init_db.php";

function log_user_activity($conn, $user_id, $course_id, $activity_type) {
    $user_id = (int)$user_id;
    $course_id = (int)$course_id;
    if ($user_id <= 0 || $course_id <= 0 || empty($activity_type)) {
        return false;
    }
    $allowed = ['view', 'search', 'like', 'enroll', 'complete'];
    if (!in_array($activity_type, $allowed)) {
        return false;
    }
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, course_id, activity_type) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $user_id, $course_id, $activity_type);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
    return false;
}

?>