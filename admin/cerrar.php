<?php
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Determine the path to login.php
// Assuming cerrar.php is in /admin/, login.php is in the same directory.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$admin_path = dirname($_SERVER['SCRIPT_NAME']); // Should be /admin if script is in /admin/cerrar.php

// Ensure it ends with a slash if it's not the root and is a directory
if ($admin_path !== '/' && substr($admin_path, -1) !== '/') {
    $admin_path .= '/';
} elseif ($admin_path === '/') {
    // If script is at root, dirname might be '/', make it empty for relative path or handle correctly.
    // For this project, admin is expected to be a directory, so /admin/ is likely.
}

// Path to login.php within the admin directory
$login_page_name = "login.php"; 
$login_url = $protocol . $host . $admin_path . $login_page_name;

// Clean up potential double slashes in path, e.g., http://server//login.php
$login_url = preg_replace_callback('#://([^/]+)/+#', function($m) { return '://'.$m[1].'/'; }, $login_url);
// Special case for root: if admin_path was '/' and host doesn't have trailing slash, 
// it might become http://host//login.php. This regex handles it.
// If admin_path correctly becomes /admin/, then it's http://host/admin/login.php

header("Location: " . $login_url);
exit;
?>
