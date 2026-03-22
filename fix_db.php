<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "nnotes_db"; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// This SQL command automatically adds the missing 'rating' column to your notes table!
$sql = "ALTER TABLE notes ADD COLUMN rating DECIMAL(3,1) DEFAULT 0.0;";

if ($conn->query($sql) === TRUE) {
    echo "<h1 style='color: green; font-family: sans-serif; text-align: center; margin-top: 50px;'>Success! ✨</h1>";
    echo "<p style='text-align: center; font-family: sans-serif;'>The 'rating' column was successfully added to your notes table. You can now close this page and test the rating system!</p>";
} else {
    echo "<h1 style='color: red; font-family: sans-serif; text-align: center; margin-top: 50px;'>Something went wrong.</h1>";
    echo "<p style='text-align: center; font-family: sans-serif;'>Error: " . $conn->error . "</p>";
    echo "<p style='text-align: center; font-family: sans-serif;'>(If it says 'Duplicate column name', that means it is already fixed!)</p>";
}

$conn->close();
?>