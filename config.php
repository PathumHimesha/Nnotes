<?php
// Database connection for InfinityFree
$host = "sql206.infinityfree.com";
$db   = "if0_41405038_nnotes";
$user = "if0_41405038";
$pass = "Ma9RM9vkMxRmSTj"; // This password is from your previous screenshot

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If it fails, this will help you see why
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]));
}
?>