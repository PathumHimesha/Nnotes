<?php
// admin_add_lecturer.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Security Check: Only Admins can do this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = strtolower(trim($_POST['email'])); // Ensure email is lowercase
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $university = $_POST['university'];
    $faculty = $_POST['faculty']; // NEW FACULTY FIELD
    $role = 'lecturer'; // Forcing role to lecturer

    // ==========================================
    // 🛡️ EXCLUSIVE LECTURER EMAIL VERIFICATION
    // ==========================================
    $email_parts = explode('@', $email);
    if (end($email_parts) !== 'nsbm.ac.lk') {
        echo json_encode([
            "status" => "error", 
            "message" => "Lecturers must be registered with a valid @nsbm.ac.lk email address."
        ]);
        exit;
    }
    // ==========================================

    // Insert User into DB (Including Faculty and setting a default profile image)
    $sql = "INSERT INTO users (full_name, email, password_hash, role, university, faculty, profile_image) 
            VALUES (:full_name, :email, :password_hash, :role, :university, :faculty, 'default_avatar.png')";
    
    if ($stmt = $pdo->prepare($sql)) {
        try {
            $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':password_hash' => $password,
                ':role' => $role,
                ':university' => $university,
                ':faculty' => $faculty
            ]);
            echo json_encode([
                "status" => "success", 
                "message" => "Lecturer account created successfully for the " . $faculty . "!"
            ]);
        } catch (PDOException $e) {
            // Catch block will trigger if the email already exists in the database
            echo json_encode([
                "status" => "error", 
                "message" => "This Lecturer email is already registered!"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Database error."
        ]);
    }
}
?>