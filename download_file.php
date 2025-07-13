<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get file path from URL parameter
$file_path = $_GET['file'] ?? '';

// Security: Only allow downloads from uploads directory
if (empty($file_path) || !preg_match('/^uploads\//', $file_path)) {
    die("Invalid file path");
}

// Check if file exists
if (!file_exists($file_path)) {
    die("File not found: " . htmlspecialchars($file_path));
}

// Get file info
$file_info = pathinfo($file_path);
$filename = $file_info['basename'];
$extension = strtolower($file_info['extension']);

// Set appropriate content type
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'txt' => 'text/plain',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'mp4' => 'video/mp4',
    'mp3' => 'audio/mpeg',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed'
];

$content_type = $content_types[$extension] ?? 'application/octet-stream';

// Set headers for download
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output file content
readfile($file_path);
exit();
?> 