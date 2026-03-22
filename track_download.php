<?php
// track_download.php
require_once 'config.php';
header('Content-Type: application/json');

// Force PHP to show errors if it crashes
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['note_id'])) {
    $note_id = $_POST['note_id'];

    try {
        // 1. Add +1 to the Author's total score
        $sql = "UPDATE users SET downloads_count = downloads_count + 1 
                WHERE user_id = (SELECT user_id FROM notes WHERE note_id = :note_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':note_id' => $note_id]);

        // 2. Add +1 to the Note's specific score (optional, but good for future features)
        $sql2 = "UPDATE notes SET download_count = download_count + 1 WHERE note_id = :note_id";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([':note_id' => $note_id]);

        // Check if anything actually updated
        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Score updated!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Note ID not found, or author doesn't exist."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "SQL Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request. Missing Note ID."]);
}
?>