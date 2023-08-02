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
        <h1>Dashboard</h1>
        <br>

        <?php
        $id = $_SESSION['id'];

        $result = $db->query("SELECT * FROM accounts WHERE id='$id'");
        $account = $db->fetch_assoc($result);
        
        // welcome msg
        echo '<p>Welcome back, ' . $account['username'] . '.</p>';

        // sub status
        $current_date = date('Y-m-d H:i:s');
        $expiration_date = $account['expiration_date'];
        if ($expiration_date > $current_date) {
            echo '<p><b>Subscription status:</b> <span style="color:green;">ACTIVE</span></p>'; 
        } else {
            echo '<p><b>Subscription status:</b> <span style="color:red;">INACTIVE</span></p>';
        }

        // sub expiration date
        echo '<p><b>Expires on:</b> ' . $account['expiration_date'] . '</p>';
        if ($expiration_date < $current_date) {
            echo '<a href="buy.php">Buy subscription</a><br>';
        } else {
            echo '<a href="client.php">Download client</a><br>';
        }
        ?>
        
        <a href="logout.php">Logout</a>

        <br><br>
    </div>
</body>
</html>