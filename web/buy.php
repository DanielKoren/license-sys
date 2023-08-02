<?php 

session_start();

require_once('config.php');
require_once('database.class.php');

$db = new Database();

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sub_type = $db->safe_post('sub_type');
    
    $curr_date = date('Y-m-d H:i:s');
    $new_date = "";

    switch($sub_type) {
        case 1:
            $timestamp = strtotime($curr_date . " + 1 month");
            $new_date = date('Y-m-d H:i:s', $timestamp);
            break;
        case 2:
            $timestamp = strtotime($curr_date . " + 3 month");
            $new_date = date('Y-m-d H:i:s', $timestamp);
            break;
        case 3:
            $timestamp = strtotime($curr_date . " + 6 month");
            $new_date = date('Y-m-d H:i:s', $timestamp);
            break;
    }

    $id = $_SESSION['id'];
    $db->query("UPDATE accounts SET expiration_date = '$new_date' WHERE id = '$id'");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $site_title;?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="small-container">
        <h1>Buy subscription</h1>
        <form method="post">
            <label for="sub_type">Choose subscription -</label>
            <select id="sub_type" name="sub_type">
              <option value="1">1 month - 29.99 ₪</option>
              <option value="2">3 month - 49.99 ₪</option>
              <option value="3">6 month - 69.99 ₪</option>
            </select>
            <input type="submit" value="Pay using Paypal">
        </form>
    </div>
</body>
</html>