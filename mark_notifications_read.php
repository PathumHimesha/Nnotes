<?php
// mark_notifications_read.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    echo json_encode(["status" => "success"]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error"]);
}
?>