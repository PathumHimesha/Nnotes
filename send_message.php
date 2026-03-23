<?php
// send_message.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message']);
    if (empty($message)) {
        echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO community_chat (user_id, message) VALUES (:user_id, :message)");
        $stmt->execute([':user_id' => $_SESSION['user_id'], ':message' => $message]);
        echo json_encode(["status" => "success"]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
}
?>