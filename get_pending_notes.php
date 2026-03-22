<?php

session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Security: Only allow lecturers to view the moderation queue
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer' || !isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access. Lecturers only."]);
    exit;
}

try {
    // 1. Find the logged-in lecturer's faculty
    $fac_stmt = $pdo->prepare("SELECT faculty FROM users WHERE user_id = :user_id");
    $fac_stmt->execute([':user_id' => $_SESSION['user_id']]);
    $lecturer_data = $fac_stmt->fetch(PDO::FETCH_ASSOC);
    
    $lecturer_faculty = $lecturer_data['faculty'] ?? '';

    if (empty($lecturer_faculty)) {
        echo json_encode(["status" => "error", "message" => "No faculty assigned to this lecturer profile."]);
        exit;
    }

    
    $sql = "SELECT 
                n.note_id, 
                n.title, 
                n.file_path, 
                n.faculty,
                u.full_name as author, 
                m.module_name as module, 
                m.university as univ,
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
            WHERE n.is_verified = 0 AND n.faculty = :faculty
            ORDER BY n.created_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':faculty' => $lecturer_faculty]);
    
    $pending_notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $pending_notes, "faculty" => $lecturer_faculty]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>