<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php'; // Uses your InfinityFree connection

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Please log in first!"]);
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];
    $note_id = (int)$_POST['note_id'];
    $user_score = (int)$_POST['rating']; // The number of stars clicked

    // 1. Save or Update the individual user's score in the 'ratings' table
    $query = $pdo->prepare("INSERT INTO ratings (note_id, user_id, score) 
                            VALUES (:nid, :uid, :score) 
                            ON DUPLICATE KEY UPDATE score = :score2");
    $query->execute([
        ':nid' => $note_id,
        ':uid' => $user_id,
        ':score' => $user_score,
        ':score2' => $user_score
    ]);

    // 2. Calculate the new total average for this note
    $avg_query = $pdo->prepare("SELECT AVG(score) as average FROM ratings WHERE note_id = :nid");
    $avg_query->execute([':nid' => $note_id]);
    $row = $avg_query->fetch(PDO::FETCH_ASSOC);
    $new_avg = round($row['average'], 1);

    // 3. Update the main 'notes' table so the average shows up on the website
    $update = $pdo->prepare("UPDATE notes SET rating = :avg WHERE note_id = :nid");
    $update->execute([':avg' => $new_avg, ':nid' => $note_id]);

    echo json_encode(["status" => "success", "new_rating" => $new_avg]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>