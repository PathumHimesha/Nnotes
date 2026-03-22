<?php
// get_notifications.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT notification_id, message, DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') as created_at FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $notifications]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error"]);
}
?>