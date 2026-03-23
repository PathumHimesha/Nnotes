<?php
// FORCE PHP TO SHOW ERRORS (Add these 3 lines)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// login.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if variables are set to prevent undefined array key errors
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        echo json_encode(["status" => "error", "message" => "Email and password are required."]);
        exit;
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Secure query to find the user
    $sql = "SELECT user_id, full_name, password_hash, role FROM users WHERE email = :email";
    
    try {
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify the hashed password
                if (password_verify($password, $row['password_hash'])) {
                    // Password is correct, start a session
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['full_name'] = $row['full_name'];
                    $_SESSION['role'] = $row['role'];

                    // Send success response back to frontend
                    echo json_encode([
                        "status" => "success", 
                        "role" => $row['role'],
                        "name" => $row['full_name'],
                        "message" => "Login successful!"
                    ]);
                    exit;
                }
            }
        }
        // If execution reaches here, login failed
        echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
    } catch (PDOException $e) {
        // Catch any database query errors
        echo json_encode(["status" => "error", "message" => "Query Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request Method."]);
}
?>