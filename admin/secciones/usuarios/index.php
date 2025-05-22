<?php
include '../../bd.php';
include '../../templates/header.php';

$error_message = '';
$success_message = '';

// Display success message from GET parameter
if (isset($_GET['mensaje'])) {
    $success_message = htmlspecialchars($_GET['mensaje']);
}

// Handle Deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_user_to_delete = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

    if ($id_user_to_delete) {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                // Optional: Add check here to prevent deleting the last admin or current user
                // For now, direct deletion as per basic instructions.
                
                $sql_check = "SELECT username FROM users WHERE id_user = :id_user";
                $stmt_check = $db->prepare($sql_check);
                $stmt_check->bindParam(':id_user', $id_user_to_delete, PDO::PARAM_INT);
                $stmt_check->execute();
                $user_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($user_to_delete) {
                    $sql_delete = "DELETE FROM users WHERE id_user = :id_user";
                    $stmt_delete = $db->prepare($sql_delete);
                    $stmt_delete->bindParam(':id_user', $id_user_to_delete, PDO::PARAM_INT);

                    if ($stmt_delete->execute()) {
                        header("Location: index.php?mensaje=Usuario '" . urlencode($user_to_delete['username']) . "' eliminado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al eliminar el usuario. Es posible que esté referenciado en otras tablas.";
                        // error_log("PDO Error (User Delete): " . print_r($stmt_delete->errorInfo(), true));
                    }
                } else {
                     $error_message = "Error: Usuario con ID " . htmlspecialchars($id_user_to_delete) . " no encontrado.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al eliminar: " . $e->getMessage();
            // error_log("PDOException (User Delete): " . $e->getMessage());
        }
    } else {
        $error_message = "ID de usuario inválido para eliminar.";
    }
}

// Fetch all users (excluding password)
$users_list = [];
try {
    if (!isset($db) || $db === null) {
        $error_message .= ($error_message ? "<br>" : "") . "Error: Conexión a BD no disponible para listar usuarios.";
    } else {
        $sql_select = "SELECT id_user, username, email, role, enabled, date_created, date_updated FROM users ORDER BY date_created DESC";
        $stmt_select = $db->query($sql_select);
        if ($stmt_select) {
            $users_list = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_message .= ($error_message ? "<br>" : "") . "Error al obtener lista de usuarios.";
        }
    }
} catch (PDOException $e) {
    $error_message .= ($error_message ? "<br>" : "") . "Error de BD al listar usuarios: " . $e->getMessage();
    // error_log("PDOException (User List): " . $e->getMessage());
}
?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Gestión de Usuarios</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="crear.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</a>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Listado de Usuarios</h4>
        </div>
        <div class="card-body">
            <?php if (empty($users_list) && empty($error_message)): ?>
                <div class="alert alert-info" role="alert">
                    No hay usuarios registrados actualmente. <a href="crear.php">Crea el primero</a>.
                </div>
            <?php elseif (!empty($users_list)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Email</th>
                                <th scope="col">Rol</th>
                                <th scope="col" class="text-center">Habilitado</th>
                                <th scope="col">Fecha Creación</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users_list as $user): ?>
                                <tr>
                                    <th scope="row"><?php echo htmlspecialchars($user['id_user']); ?></th>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td class="text-center">
                                        <?php if ($user['enabled']): ?>
                                            <span class="badge bg-success">Sí</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($user['date_created']))); ?></td>
                                    <td class="text-center">
                                        <a href="editar.php?id=<?php echo htmlspecialchars($user['id_user']); ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="index.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar al usuario <?php echo htmlspecialchars(addslashes($user['username'])); ?>? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($user['id_user']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (empty($error_message)): ?>
                 <div class="alert alert-info" role="alert">
                    No hay usuarios para mostrar.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
