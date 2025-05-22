<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';
$success_message = ''; // For potential future use
$user_data = null;
$id_user = null;

// 1. Check for ID in GET request and validate
if (isset($_GET['id'])) {
    $id_user = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_user === false || $id_user <= 0) {
        $error_message = "ID de usuario inválido o no proporcionado.";
    } else {
        // 2. Fetch the user data (excluding password)
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql_fetch = "SELECT id_user, username, email, role, enabled FROM users WHERE id_user = :id_user";
                $stmt_fetch = $db->prepare($sql_fetch);
                $stmt_fetch->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                $stmt_fetch->execute();
                $user_data = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

                if (!$user_data) {
                    $error_message = "Usuario no encontrado con ID: " . htmlspecialchars($id_user) . ".";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al buscar el usuario: " . $e->getMessage();
            // error_log("PDOException (User Fetch): " . $e->getMessage());
        }
    }
} else {
    $error_message = "No se especificó ID de usuario para editar.";
}

// 3. Handle POST request for updating
if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_data) { // Ensure user_data was loaded
    $id_user_post = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password']; // New password, if provided
    $enabled = isset($_POST['enabled']) ? 1 : 0; // Checkbox for enabled status

    // Data for sticky form, merge with original if POST fails
    $current_data_for_form = array_merge($user_data, $_POST);


    if (empty($username) || empty($email) || empty($role)) {
        $error_message = "Los campos Usuario, Email y Rol son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del correo electrónico no es válido.";
    } elseif ($id_user_post !== $id_user) {
         $error_message = "Error de consistencia de ID. Inténtalo de nuevo.";
    } else {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible para la actualización.";
            } else {
                // Check if username or email already exists for another user
                $sql_check_unique = "SELECT id_user FROM users WHERE (username = :username OR email = :email) AND id_user != :id_user_current";
                $stmt_check_unique = $db->prepare($sql_check_unique);
                $stmt_check_unique->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt_check_unique->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_check_unique->bindParam(':id_user_current', $id_user, PDO::PARAM_INT);
                $stmt_check_unique->execute();

                if ($stmt_check_unique->fetch()) {
                    $error_message = "El nuevo nombre de usuario o correo electrónico ya están en uso por otro usuario.";
                } else {
                    // Build the SQL query
                    $sql_update_parts = [
                        "username = :username",
                        "email = :email",
                        "role = :role",
                        "enabled = :enabled" // Added enabled field
                    ];
                    $params = [
                        ':username' => $username,
                        ':email' => $email,
                        ':role' => $role,
                        ':enabled' => $enabled,
                        ':id_user' => $id_user
                    ];

                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql_update_parts[] = "password = :password";
                        $params[':password'] = $hashed_password;
                    }

                    $sql_update = "UPDATE users SET " . implode(", ", $sql_update_parts) . " WHERE id_user = :id_user";
                    $stmt_update = $db->prepare($sql_update);

                    if ($stmt_update->execute($params)) {
                        header("Location: index.php?mensaje=Usuario '" . urlencode($username) . "' actualizado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al actualizar el usuario. Por favor, inténtalo de nuevo.";
                        // error_log("PDO Error (User Update): " . print_r($stmt_update->errorInfo(), true));
                    }
                }
            }
        } catch (PDOException $e) {
             if ($e->getCode() == '23000') { // Integrity constraint violation
                $error_message = "Error: El nombre de usuario o el correo electrónico ya existen (violación de restricción única).";
            } else {
                $error_message = "Error de base de datos al actualizar: " . $e->getMessage();
            }
            // error_log("PDOException (User Update): " . $e->getMessage());
        }
    }
    // If error, update $user_data for sticky form
    $user_data = $current_data_for_form;
} elseif (!$user_data && empty($error_message)) {
    // If user_data is null and no specific error message is set (e.g. invalid ID from start)
    $error_message = "No se pudo cargar la información del usuario para editar. Puede que el ID no exista o sea inválido.";
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Editar Usuario</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($user_data): // Only show form if user_data was successfully fetched ?>
                    <form action="editar.php?id=<?php echo htmlspecialchars($id_user); ?>" method="post" novalidate>
                        <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($user_data['id_user']); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña (Opcional)</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">Dejar en blanco para mantener la contraseña actual.</small>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rol</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Selecciona un rol...</option>
                                <option value="admin" <?php echo (isset($user_data['role']) && $user_data['role'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="editor" <?php echo (isset($user_data['role']) && $user_data['role'] == 'editor') ? 'selected' : ''; ?>>Editor</option>
                                <!-- Add other roles as needed -->
                            </select>
                        </div>
                         <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" <?php echo (isset($user_data['enabled']) && $user_data['enabled'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enabled">Habilitado</label>
                            <small class="form-text text-muted d-block">Desmarca para deshabilitar el acceso del usuario.</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-save"></i> Guardar Cambios</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                    <?php elseif (empty($error_message)): ?>
                        <div class="alert alert-info" role="alert">
                            Cargando datos del usuario... Si este mensaje persiste, el usuario podría no existir o el ID ser incorrecto.
                        </div>
                        <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                    <?php endif; ?>
                     <?php if ($error_message && !$user_data): // If there was an error and user could not be loaded at all ?>
                         <a href="index.php" class="btn btn-primary mt-3"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
