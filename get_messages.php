<?php
// get_messages.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

try {
    // Get the last 50 messages, join with users to get names and pictures
    $sql = "SELECT c.message, DATE_FORMAT(c.created_at, '%h:%i %p') as time, 
                   u.full_name, u.profile_image, u.user_id 
            FROM community_chat c
            JOIN users u ON c.user_id = u.user_id
            ORDER BY c.created_at ASC
            LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add a flag so the frontend knows which messages belong to the logged-in user
    foreach ($messages as &$msg) {
        $msg['is_mine'] = ($msg['user_id'] == $_SESSION['user_id']);
    }
    
    echo json_encode(["status" => "success", "data" => $messages]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error"]);
}
?>