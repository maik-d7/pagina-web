<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';
$success_message = ''; // For potential future use, though redirecting is primary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input values
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password']; // Will be hashed, not directly used in SQL query
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);
    // 'enabled' defaults to 1 in DB, so not explicitly set here unless needed

    // Basic validation
    if (empty($username) || empty($password) || empty($email) || empty($role)) {
        $error_message = "Por favor, completa todos los campos obligatorios (Usuario, Contraseña, Email, Rol).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del correo electrónico no es válido.";
    } else {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                // Check if username or email already exists (assuming they should be unique)
                $sql_check = "SELECT id_user FROM users WHERE username = :username OR email = :email";
                $stmt_check = $db->prepare($sql_check);
                $stmt_check->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt_check->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_check->execute();
                
                if ($stmt_check->fetch()) {
                    $error_message = "El nombre de usuario o el correo electrónico ya existen. Por favor, elige otros diferentes.";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $sql_insert = "INSERT INTO users (username, password, email, role) 
                                   VALUES (:username, :password, :email, :role)";
                    $stmt_insert = $db->prepare($sql_insert);
                    
                    $stmt_insert->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt_insert->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                    $stmt_insert->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt_insert->bindParam(':role', $role, PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        header("Location: index.php?mensaje=Usuario '" . urlencode($username) . "' creado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al crear el usuario. Por favor, inténtalo de nuevo.";
                        // error_log("PDO Error (User Insert): " . print_r($stmt_insert->errorInfo(), true));
                    }
                }
            }
        } catch (PDOException $e) {
            // Check for unique constraint violation (code 23000)
            if ($e->getCode() == '23000') {
                $error_message = "Error: El nombre de usuario o el correo electrónico ya existen (violación de restricción única).";
            } else {
                $error_message = "Error de base de datos: " . $e->getMessage();
            }
            // error_log("PDOException (User Insert): " . $e->getMessage());
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Crear Nuevo Usuario</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="crear.php" method="post" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rol</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Selecciona un rol...</option>
                                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="editor" <?php echo (isset($_POST['role']) && $_POST['role'] == 'editor') ? 'selected' : ''; ?>>Editor</option>
                                <!-- Add other roles as needed -->
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-user-plus"></i> Crear Usuario</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
