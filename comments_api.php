<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    // Add a new comment
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Log in to comment."]); exit;
    }
    $note_id = $_POST['note_id'];
    $user_id = $_SESSION['user_id'];
    $text = trim($_POST['comment_text']);

    if (!empty($text)) {
        $sql = "INSERT INTO comments (note_id, user_id, comment_text) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$note_id, $user_id, $text]);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Comment cannot be empty."]);
    }
} 
elseif ($action === 'get') {
    // Get all comments for a specific note
    $note_id = $_GET['note_id'];
    $sql = "SELECT c.comment_text, c.created_at, u.full_name, u.profile_image 
            FROM comments c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE c.note_id = ? 
            ORDER BY c.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$note_id]);
    echo json_encode(["status" => "success", "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
?>