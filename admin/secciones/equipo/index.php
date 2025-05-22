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
    $id_miembro_to_delete = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

    if ($id_miembro_to_delete) {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql_check = "SELECT nombre FROM tbl_equipo WHERE id_miembro = :id_miembro";
                $stmt_check = $db->prepare($sql_check);
                $stmt_check->bindParam(':id_miembro', $id_miembro_to_delete, PDO::PARAM_INT);
                $stmt_check->execute();
                $item_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($item_to_delete) {
                    $sql_delete = "DELETE FROM tbl_equipo WHERE id_miembro = :id_miembro";
                    $stmt_delete = $db->prepare($sql_delete);
                    $stmt_delete->bindParam(':id_miembro', $id_miembro_to_delete, PDO::PARAM_INT);

                    if ($stmt_delete->execute()) {
                        header("Location: index.php?mensaje=Miembro del equipo '" . urlencode($item_to_delete['nombre']) . "' eliminado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al eliminar el miembro del equipo.";
                    }
                } else {
                     $error_message = "Error: Miembro del equipo con ID " . htmlspecialchars($id_miembro_to_delete) . " no encontrado.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al eliminar: " . $e->getMessage();
        }
    } else {
        $error_message = "ID de miembro inválido para eliminar.";
    }
}

// Fetch all team members
$equipo_items = [];
try {
    if (!isset($db) || $db === null) {
        $error_message .= ($error_message ? "<br>" : "") . "Error: Conexión a BD no disponible para listar.";
    } else {
        $sql_select = "SELECT * FROM tbl_equipo ORDER BY orden ASC, nombre ASC";
        $stmt_select = $db->query($sql_select);
        if ($stmt_select) {
            $equipo_items = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_message .= ($error_message ? "<br>" : "") . "Error al obtener lista del equipo.";
        }
    }
} catch (PDOException $e) {
    $error_message .= ($error_message ? "<br>" : "") . "Error de BD al listar el equipo: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Gestión de Equipo</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="crear.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Agregar Nuevo Miembro</a>
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
            <h4 class="mb-0">Listado de Miembros del Equipo</h4>
        </div>
        <div class="card-body">
            <?php if (empty($equipo_items) && empty($error_message)): ?>
                <div class="alert alert-info" role="alert">
                    No hay miembros en el equipo actualmente. <a href="crear.php">Agrega el primero</a>.
                </div>
            <?php elseif (!empty($equipo_items)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center">Orden</th>
                                <th scope="col">Imagen</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Puesto</th>
                                <th scope="col" class="text-center">Redes Sociales</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipo_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['orden']); ?></td>
                                    <td>
                                        <?php if (!empty($item['imagen'])): ?>
                                            <img src="<?php echo htmlspecialchars(filter_var($item['imagen'], FILTER_SANITIZE_URL)); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($item['puesto']); ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($item['twitter_url'])): ?>
                                            <a href="<?php echo htmlspecialchars(filter_var($item['twitter_url'], FILTER_SANITIZE_URL)); ?>" target="_blank" class="btn btn-sm btn-outline-info me-1" title="Twitter"><i class="fab fa-twitter"></i></a>
                                        <?php endif; ?>
                                        <?php if (!empty($item['facebook_url'])): ?>
                                            <a href="<?php echo htmlspecialchars(filter_var($item['facebook_url'], FILTER_SANITIZE_URL)); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                                        <?php endif; ?>
                                        <?php if (!empty($item['linkedin_url'])): ?>
                                            <a href="<?php echo htmlspecialchars(filter_var($item['linkedin_url'], FILTER_SANITIZE_URL)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                                        <?php endif; ?>
                                        <?php if (empty($item['twitter_url']) && empty($item['facebook_url']) && empty($item['linkedin_url'])): ?>
                                            <small class="text-muted">N/A</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="editar.php?id=<?php echo htmlspecialchars($item['id_miembro']); ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="index.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar a <?php echo htmlspecialchars(addslashes($item['nombre'])); ?> del equipo?');">
                                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($item['id_miembro']); ?>">
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
                    No hay miembros del equipo para mostrar.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
