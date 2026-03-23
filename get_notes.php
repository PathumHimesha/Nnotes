<?php

header('Content-Type: application/json');
require_once 'config.php';

try {
    
    $sql = "SELECT 
                n.note_id, 
                n.title, 
                n.file_path, 
                n.faculty, 
                n.average_rating as rating, 
                n.is_verified as verified,
                u.full_name as author, 
                m.module_name as module, 
                m.university as univ,
                m.year, 
                m.degree,
                CASE 
                    WHEN m.module_name LIKE '%Database%' THEN 'fa-database'
                    WHEN m.module_name LIKE '%Computing%' THEN 'fa-laptop'
                    WHEN m.module_name LIKE '%Web%' THEN 'fa-code'
                    ELSE 'fa-book-open'
                END as icon
            FROM notes n
            JOIN users u ON n.user_id = u.user_id
            JOIN modules m ON n.module_id = m.module_id
            WHERE n.is_verified = 1
            ORDER BY n.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    echo json_encode(["status" => "success", "data" => $notes]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>