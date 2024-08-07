<?php 

session_start();

require_once('config.php');
require_once('database.class.php');

$db = new Database();
$success = false;
$error = "";
$data = "";

$file_path = "files/putty.exe";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $db->safe_post("username");
    $pass = $db->safe_post("password");
    $hwid = $db->safe_post("hwid");

    // Check if username exist
    $result = $db->prepare_query("SELECT * FROM accounts WHERE username=?", $user);
    if ($db->num_rows($result) > 0) {
        $account = $db->fetch_assoc($result);
        
        // Check password
        if (password_verify($pass, $account['password'])) {
            
            // Check if account is banned
            if ($account['banned'] == false) {

                // Check if subscription is active 
                $current_date = date('Y-m-d H:i:s');
                $expired_date = $account['expired_date'];
                
                if ($expired_date > $current_date) {
                    // Check if HWID was generated previously
                    if ($account['hwid'] == '-') {
                        // Update new HWID
                        $db->prepare_query("UPDATE accounts SET hwid=? WHERE username=?", $hwid, $user);
                        // Read exe data 
                        if (!is_readable($file_path)) {
                            $error = "File doesn't exists or unreadable";
                        } else {
                            $success = true;
                            $data = file_get_contents($file_path);
                            $data = base64_encode($data);
                        }
                    } else {
                        if ($account['hwid'] == $hwid) {
                            // Read exe data 
                            if (!is_readable($file_path)) {
                                $error = "File doesn't exists or unreadable";
                            } else {
                                $success = true;
                                $data = file_get_contents($file_path);
                                $data = base64_encode($data);
                            }
                        } else {
                            // Ban account if HWID doesn't match to the previous one
                            $db->prepare_query("UPDATE accounts SET banned=? WHERE username=?", 1, $user);
                            $error = "Invalid HWID, account is locked.";
                        }
                    }
                } else {
                    $error = "Subscription is expired";
                }
            } else {
                $error = "Account is banned";    
            }
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid username";
    }

    // Return JSON response
    $response = array(
        'username' => $user,
        'success' => $success,
        'error_msg' => $error,
        'data' => $data
    );

    // Set the response content type to JSON
    header('Content-Type: application/json');

    // Convert the response array to JSON and echo it
    echo json_encode($response);

}

?>