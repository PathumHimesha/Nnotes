<?php
// get_leaderboard.php
header('Content-Type: application/json');
require_once 'config.php';

try {
    // Get top 4 students based on downloads
    $sql = "SELECT full_name, downloads_count, profile_image 
            FROM users 
            WHERE role = 'student' 
            ORDER BY downloads_count DESC 
            LIMIT 4";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $users]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error."]);
}
?>