<?php
session_start();

// Clear all session data
$_SESSION = [];

// Destroy session completely
session_unset();
session_destroy();

// Remove session cookie (VERY IMPORTANT)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

header("Location: login.php");
exit();
?>