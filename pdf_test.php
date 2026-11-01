<?php
session_start();
include "db.php";

echo "<h3>Debug Info</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>DB Connected: " . ($conn->connect_error ? "NO - ".$conn->connect_error : "YES") . "</p>";

// Check table exists
$r = $conn->query("SHOW TABLES LIKE 'bill_pdfs'");
echo "<p>bill_pdfs table exists: " . ($r->num_rows > 0 ? "YES" : "NO") . "</p>";

// Check upload folder
$dir = __DIR__ . '/uploads/pdfs/';
echo "<p>Upload folder exists: " . (is_dir($dir) ? "YES" : "NO") . "</p>";
echo "<p>Upload folder writable: " . (is_writable($dir) ? "YES" : "NO") . "</p>";

// Check php upload settings
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>file_uploads enabled: " . (ini_get('file_uploads') ? "YES" : "NO") . "</p>";
?>
