<?php
session_start();

// If already logged in, redirect to admin index
if (isset($_SESSION['user_id'])) {
    // Need to determine the correct path to admin/index.php from admin/login.php
    // Assuming login.php is in /admin/, then index.php is in the same directory.
    // If $url_base was available here, it would be ideal.
    // For now, a relative path should work if they are in the same directory.
    // Or, construct it simply.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // SCRIPT_NAME for admin/login.php is /admin/login.php
    // dirname($_SERVER['SCRIPT_NAME']) is /admin
    $admin_path = dirname($_SERVER['SCRIPT_NAME']);
    // Ensure it ends with a slash if it's not the root
    if ($admin_path !== '/' && substr($admin_path, -1) !== '/') {
        $admin_path .= '/';
    } elseif ($admin_path === '/') {
         // If script is at root, dirname might be '/', ensure it's empty for relative path or correct for absolute
         $admin_path = '/'; // Or adjust if admin is in a subfolder of root.
    }
    // This logic assumes admin is at a path like /admin/. If it's at the root, $admin_path might just be '/'
    // If login.php is at /admin/login.php, then index.php is at /admin/index.php
    // $base_app_url = $protocol . $host . $admin_path; //This would be http://server/admin/
    header("Location: index.php"); // Simpler: redirect to index in same dir
    exit;
}

include 'bd.php'; // Database connection

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password_attempt = $_POST['password']; // Password to verify

    if (empty($username) || empty($password_attempt)) {
        $error_message = "Por favor, ingresa tu nombre de usuario y contraseña.";
    } else {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql = "SELECT id_user, username, password, role, enabled FROM users WHERE username = :username";
                // Consider allowing login via email too:
                // $sql = "SELECT id_user, username, password, role, enabled FROM users WHERE username = :login_identifier OR email = :login_identifier";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                // $stmt->bindParam(':login_identifier', $username, PDO::PARAM_STR); // If using email too
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && $user['enabled'] == 1) {
                    if (password_verify($password_attempt, $user['password'])) {
                        session_regenerate_id(true); // Regenerate session ID
                        $_SESSION['user_id'] = $user['id_user'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        header("Location: index.php"); // Redirect to admin dashboard
                        exit;
                    } else {
                        $error_message = "Credenciales inválidas. Por favor, inténtalo de nuevo.";
                    }
                } elseif ($user && $user['enabled'] == 0) {
                    $error_message = "Tu cuenta está deshabilitada. Contacta al administrador.";
                } 
                else {
                    $error_message = "Credenciales inválidas. Por favor, inténtalo de nuevo.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos durante el inicio de sesión: " . $e->getMessage();
            // error_log("PDOException (Login): " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa; /* Light gray background */
        }
        .login-card {
            width: 100%;
            max-width: 420px; /* Increased width slightly */
            padding: 25px; /* More padding */
            border: none; /* Remove border */
            border-radius: 0.5rem; /* Softer border radius */
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1); /* Softer shadow */
        }
        .login-card .card-header {
            background-color: transparent;
            border-bottom: none;
            text-align: center;
            padding-bottom: 1.5rem; /* More space for icon/title */
        }
        .login-card .card-header .fas {
            font-size: 3rem; /* Larger icon */
            color: #0d6efd; /* Bootstrap primary blue */
        }
        .login-card .form-floating label {
            color: #6c757d; /* Muted label color */
        }
        .login-card .btn-primary {
            padding: 0.75rem; /* Larger button */
        }
    </style>
</head>
<body>
    <div class="login-card card">
        <div class="card-header">
            <i class="fas fa-shield-alt mb-3"></i> <!-- Icon -->
            <h3>Panel de Administración</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form action="login.php" method="post" novalidate>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Nombre de usuario" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required autofocus>
                    <label for="username">Nombre de usuario</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                    <label for="password">Contraseña</label>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary btn-lg" type="submit"><i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center text-muted small py-3">
            &copy; <?php echo date("Y"); ?> - Mi Agencia CMS
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
