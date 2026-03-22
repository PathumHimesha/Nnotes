<?php
// update_profile.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name']);
    
    if (empty($full_name)) {
        echo json_encode(["status" => "error", "message" => "Name cannot be empty."]);
        exit;
    }

    try {
        // 1. Update the Name
        $sql = "UPDATE users SET full_name = :name WHERE user_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $full_name, ':id' => $user_id]);
        
        // Update session so UI updates
        $_SESSION['full_name'] = $full_name;

        // 2. Handle Profile Image Upload (if provided)
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                // IMPORTANT: Create this folder in File Manager if it doesn't exist
                $uploadDir = 'uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $newFileName = $user_id . '_' . time() . '.' . $ext;
                $targetFilePath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFilePath)) {
                    // Update DB with new image path
                    $sql_img = "UPDATE users SET profile_image = :img WHERE user_id = :id";
                    $stmt_img = $pdo->prepare($sql_img);
                    $stmt_img->execute([':img' => $targetFilePath, ':id' => $user_id]);
                }
            } else {
                 echo json_encode(["status" => "error", "message" => "Name updated, but image failed (only JPG/PNG allowed)."]);
                 exit;
            }
        }

        echo json_encode([
            "status" => "success", 
            "message" => "Profile updated successfully!",
            "new_name" => $full_name
        ]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
}
?>