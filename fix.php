<?php
require_once 'config.php';

// Generate a fresh hash for 'password123' using YOUR computer's PHP
$new_hash = password_hash('password123', PASSWORD_DEFAULT);

try {
    // Update the Admin and Lecturer accounts with the new working password
    $sql = "UPDATE users SET password_hash = :hash WHERE email IN ('admin@nnotes.lk', 'lecturer@nsbm.ac.lk')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':hash' => $new_hash]);
    
    echo "<h2 style='color: green; text-align: center; margin-top: 50px;'>Success! Passwords fixed.</h2>";
    echo "<p style='text-align: center;'>You can now log in using <b>password123</b>.</p>";
    echo "<div style='text-align: center;'><a href='index.html' style='padding: 10px 20px; background: #22c55e; color: white; text-decoration: none; border-radius: 50px;'>Go to Login</a></div>";
} catch (PDOException $e) {
    echo "Error fixing passwords: " . $e->getMessage();
}
?>