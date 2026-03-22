<?php
// upload.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to upload."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $faculty = $_POST['faculty']; // NEW FACULTY FIELD ADDED HERE!
    $univ = $_POST['university'];
    $deg = $_POST['degree'];
    $year = $_POST['year'];
    $subject = $_POST['module_name'];
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['note_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // CHECK 1: Is the file a PDF or Word Document?
        if (in_array($ext, $allowed)) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            // Create unique file name
            $newFileName = uniqid() . '_' . basename($filename);
            $targetFilePath = $uploadDir . $newFileName;

            // CHECK 2: Did the server successfully save the file?
            if (move_uploaded_file($_FILES['note_file']['tmp_name'], $targetFilePath)) {
                try {
                    // SMART LOGIC: Find the module ID. If it doesn't exist, create it!
                    $stmt = $pdo->prepare("SELECT module_id FROM modules WHERE university=? AND degree=? AND year=? AND module_name=?");
                    $stmt->execute([$univ, $deg, $year, $subject]);
                    
                    if ($stmt->rowCount() > 0) {
                        $module_id = $stmt->fetchColumn();
                    } else {
                        // Create the new module automatically
                        $stmt2 = $pdo->prepare("INSERT INTO modules (university, degree, year, module_name) VALUES (?, ?, ?, ?)");
                        $stmt2->execute([$univ, $deg, $year, $subject]);
                        $module_id = $pdo->lastInsertId();
                    }

                    // Insert the note linked to the module AND the faculty (is_verified = 0 means pending)
                    $sql = "INSERT INTO notes (user_id, module_id, title, file_path, faculty, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
                    $pdo->prepare($sql)->execute([$user_id, $module_id, $title, $targetFilePath, $faculty]);

                    echo json_encode(["status" => "success", "message" => "Note uploaded successfully!"]);
                } catch(PDOException $e) {
                    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to save file. Check 'uploads' folder permissions."]);
            }
        } else {
            // *** THE FIX: This stops image uploads and tells the user why ***
            echo json_encode(["status" => "error", "message" => "Invalid file type! Please upload a PDF or DOCX file."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed or no file was selected."]);
    }
}
?>