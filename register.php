<?php
// register.php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = strtolower(trim($_POST['email'])); // Make sure email is lowercase
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $university = $_POST['university'];
    $faculty = $_POST['faculty']; // NEW FACULTY FIELD
    
    // ==========================================
    // 🛡️ EXCLUSIVE STUDENT EMAIL VERIFICATION
    // ==========================================
    $email_parts = explode('@', $email);
    $domain = end($email_parts);

    if ($domain === 'students.nsbm.ac.lk') {
        $role = 'student';
    } else if ($domain === 'nsbm.ac.lk') {
        // Block lecturers from self-registering
        echo json_encode([
            "status" => "error", 
            "message" => "Lecturers cannot self-register. Please contact the System Administrator to create your account."
        ]);
        exit;
    } else {
        // Friendly error message for non-university emails
        echo json_encode([
            "status" => "error", 
            "message" => "Please register using your official student email (@students.nsbm.ac.lk)."
        ]);
        exit;
    }
    // ==========================================
    
    // Profile Image Upload Handling
    $profile_image = 'default_avatar.png'; // Default if no image uploaded
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/profiles/";
        
        // Ensure the directory exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        
        if (in_array($file_ext, $allowed_types)) {
            $new_filename = uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $new_filename)) {
                $profile_image = $target_dir . $new_filename;
            }
        }
    }

    // Check if email already exists
    $check_sql = "SELECT email FROM users WHERE email = :email";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([':email' => $email]);
    
    if ($check_stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered!"]);
        exit;
    }

    // Insert User into DB (now including the Faculty)
    $sql = "INSERT INTO users (full_name, email, password_hash, role, university, faculty, profile_image) 
            VALUES (:full_name, :email, :password_hash, :role, :university, :faculty, :profile_image)";
    
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':password_hash' => $password,
            ':role' => $role,
            ':university' => $university,
            ':faculty' => $faculty, // Binding the faculty to the database
            ':profile_image' => $profile_image
        ]);
        
        echo json_encode(["status" => "success", "message" => "Registration successful! Welcome to Nnotes."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
}
?>