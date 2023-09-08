<?php

session_start();

require_once('config.php');
require_once('database.class.php');

$db = new Database();

// check auth session
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// check account subscription status
$sub_active = false;

$id = $_SESSION['id'];
$result = $db->query("SELECT * FROM accounts WHERE id='$id'");
$account = $db->fetch_assoc($result);

$current_date = date('Y-m-d H:i:s');
$expired_date = $account['expired_date'];
if ($expired_date > $current_date) {
    // if subscription is active - download the file
    $file_path = "files/client.exe";

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));

    readfile($file_path);
} else {
    // subscription inactive
    header('HTTP/1.0 404 Not Found');
}

?>