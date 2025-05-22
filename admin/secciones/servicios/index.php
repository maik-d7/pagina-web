<?php
// Database connection and header
include '../../bd.php';
include '../../templates/header.php';

$error_message = '';
$success_message = ''; // To display messages from redirects (e.g., after creation/update)

// Display success message from GET parameter if present
if (isset($_GET['mensaje'])) {
    $success_message = htmlspecialchars($_GET['mensaje']);
}

// Handle Deletion of a service
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_servicio_to_delete = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

    if ($id_servicio_to_delete) {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                // First, check if the service exists to provide a more specific message
                $sql_check = "SELECT titulo FROM tbl_servicios WHERE id_servicio = :id_servicio";
                $stmt_check = $db->prepare($sql_check);
                $stmt_check->bindParam(':id_servicio', $id_servicio_to_delete, PDO::PARAM_INT);
                $stmt_check->execute();
                $service_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($service_to_delete) {
                    $sql_delete = "DELETE FROM tbl_servicios WHERE id_servicio = :id_servicio";
                    $stmt_delete = $db->prepare($sql_delete);
                    $stmt_delete->bindParam(':id_servicio', $id_servicio_to_delete, PDO::PARAM_INT);

                    if ($stmt_delete->execute()) {
                        // Redirect to refresh the list and show success message
                        header("Location: index.php?mensaje=Servicio '" . urlencode($service_to_delete['titulo']) . "' eliminado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al eliminar el servicio. Por favor, inténtalo de nuevo.";
                    }
                } else {
                    $error_message = "Error: Servicio con ID " . htmlspecialchars($id_servicio_to_delete) . " no encontrado para eliminar.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al eliminar: " . $e->getMessage();
        }
    } else {
        $error_message = "ID de servicio inválido para eliminar.";
    }
}

// Fetch all services from the database
$servicios = [];
try {
    if (!isset($db) || $db === null) {
        $error_message = $error_message ? $error_message."<br>" : ""; // Append if there's already an error
        $error_message .= "Error: La conexión a la base de datos no está disponible para listar servicios.";
    } else {
        $sql_select = "SELECT * FROM tbl_servicios ORDER BY orden ASC, fecha_creacion DESC";
        $stmt_select = $db->query($sql_select); // Using query() for simple select without params
        if ($stmt_select) {
            $servicios = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
        } else {
             $error_message = $error_message ? $error_message."<br>" : "";
             $error_message .= "Error al obtener la lista de servicios.";
        }
    }
} catch (PDOException $e) {
    $error_message = $error_message ? $error_message."<br>" : "";
    $error_message .= "Error de base de datos al listar servicios: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Gestión de Servicios</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Crear Nuevo Servicio</a>
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
            <h4 class="mb-0">Listado de Servicios</h4>
        </div>
        <div class="card-body">
            <?php if (empty($servicios) && empty($error_message)): ?>
                <div class="alert alert-info" role="alert">
                    No hay servicios registrados actualmente. <a href="crear.php">Crea el primero</a>.
                </div>
            <?php elseif (!empty($servicios)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Icono</th>
                                <th scope="col">Título</th>
                                <th scope="col">Descripción</th>
                                <th scope="col" class="text-center">Orden</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $index => $servicio): ?>
                                <tr>
                                    <th scope="row"><?php echo $index + 1; ?></th>
                                    <td><i class="<?php echo htmlspecialchars($servicio['icono']); ?>" title="<?php echo htmlspecialchars($servicio['icono']); ?>"></i></td>
                                    <td><?php echo htmlspecialchars($servicio['titulo']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars(substr($servicio['descripcion'], 0, 100) . (strlen($servicio['descripcion']) > 100 ? '...' : ''))); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($servicio['orden']); ?></td>
                                    <td class="text-center">
                                        <a href="editar.php?id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="index.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este servicio: <?php echo htmlspecialchars(addslashes($servicio['titulo'])); ?>?');">
                                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($servicio['id_servicio']); ?>">
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
            <?php elseif (empty($error_message)): // Only show if $servicios is empty AND there was no error fetching them ?>
                 <div class="alert alert-info" role="alert">
                    No hay servicios para mostrar.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
