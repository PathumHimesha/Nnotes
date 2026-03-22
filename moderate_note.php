<?php

session_start();
header('Content-Type: application/json');
require_once 'config.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer') {
    echo json_encode(["status" => "error", "message" => "Unauthorized."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $note_id = $_POST['note_id'];
    $action = $_POST['action'];

    try {
        if ($action === 'approve') {
            $sql = "UPDATE notes SET is_verified = 1 WHERE note_id = :note_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':note_id' => $note_id]);
            
            echo json_encode(["status" => "success", "message" => "Note successfully approved! It is now live."]);
            
        } else if ($action === 'reject') {
            $reason = isset($_POST['reason']) ? trim($_POST['reason']) : 'No specific reason provided.';

            
            $sql_details = "SELECT n.title, n.file_path, n.user_id FROM notes n WHERE n.note_id = :note_id";
            $stmt_details = $pdo->prepare($sql_details);
            $stmt_details->execute([':note_id' => $note_id]);
            $details = $stmt_details->fetch(PDO::FETCH_ASSOC);

            if ($details) {
                
                if (file_exists($details['file_path'])) {
                    unlink($details['file_path']); 
                }

                
                $sql_delete = "DELETE FROM notes WHERE note_id = :note_id";
                $stmt_delete = $pdo->prepare($sql_delete);
                $stmt_delete->execute([':note_id' => $note_id]);

                
                $message = "Your note titled '{$details['title']}' was rejected by a lecturer. Reason: {$reason}";
                $sql_notif = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
                $stmt_notif = $pdo->prepare($sql_notif);
                $stmt_notif->execute([':user_id' => $details['user_id'], ':message' => $message]);

                echo json_encode(["status" => "success", "message" => "Note rejected and student has been notified in-app."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Note not found."]);
            }
        }
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
}
?>