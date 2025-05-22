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
    $id_entrada_to_delete = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

    if ($id_entrada_to_delete) {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql_check = "SELECT titulo FROM tbl_entradas WHERE id_entrada = :id_entrada";
                $stmt_check = $db->prepare($sql_check);
                $stmt_check->bindParam(':id_entrada', $id_entrada_to_delete, PDO::PARAM_INT);
                $stmt_check->execute();
                $item_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($item_to_delete) {
                    $sql_delete = "DELETE FROM tbl_entradas WHERE id_entrada = :id_entrada";
                    $stmt_delete = $db->prepare($sql_delete);
                    $stmt_delete->bindParam(':id_entrada', $id_entrada_to_delete, PDO::PARAM_INT);

                    if ($stmt_delete->execute()) {
                        header("Location: index.php?mensaje=Entrada '" . urlencode($item_to_delete['titulo']) . "' eliminada con éxito");
                        exit;
                    } else {
                        $error_message = "Error al eliminar la entrada.";
                    }
                } else {
                     $error_message = "Error: Entrada con ID " . htmlspecialchars($id_entrada_to_delete) . " no encontrada.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al eliminar: " . $e->getMessage();
        }
    } else {
        $error_message = "ID de entrada inválido para eliminar.";
    }
}

// Fetch all entrada items
$entradas_items = [];
try {
    if (!isset($db) || $db === null) {
        $error_message .= ($error_message ? "<br>" : "") . "Error: Conexión a BD no disponible para listar.";
    } else {
        // Ordered by orden, then by fecha_creacion as fecha_evento is text and might not sort well for all cases.
        $sql_select = "SELECT * FROM tbl_entradas ORDER BY orden ASC, fecha_creacion DESC";
        $stmt_select = $db->query($sql_select);
        if ($stmt_select) {
            $entradas_items = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_message .= ($error_message ? "<br>" : "") . "Error al obtener lista de entradas.";
        }
    }
} catch (PDOException $e) {
    $error_message .= ($error_message ? "<br>" : "") . "Error de BD al listar entradas: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Gestión de Entradas (Timeline/Blog)</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Crear Nueva Entrada</a>
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
            <h4 class="mb-0">Listado de Entradas</h4>
        </div>
        <div class="card-body">
            <?php if (empty($entradas_items) && empty($error_message)): ?>
                <div class="alert alert-info" role="alert">
                    No hay entradas registradas actualmente. <a href="crear.php">Crea la primera</a>.
                </div>
            <?php elseif (!empty($entradas_items)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center">Orden</th>
                                <th scope="col">Fecha Evento</th>
                                <th scope="col">Imagen</th>
                                <th scope="col">Título</th>
                                <th scope="col">Subtítulo</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Alineación Timeline</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entradas_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['orden']); ?></td>
                                    <td><?php echo htmlspecialchars($item['fecha_evento']); ?></td>
                                    <td>
                                        <?php if (!empty($item['imagen'])): ?>
                                            <img src="<?php echo htmlspecialchars(filter_var($item['imagen'], FILTER_SANITIZE_URL)); ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>" style="width: 80px; height: auto; object-fit: cover;">
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($item['subtitulo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($item['tipo_entrada']); ?></td>
                                    <td>
                                        <?php 
                                        if ($item['alineacion_timeline'] === 'timeline-inverted') {
                                            echo 'Invertida';
                                        } elseif (empty($item['alineacion_timeline'])) {
                                            echo 'Normal';
                                        } else {
                                            echo htmlspecialchars($item['alineacion_timeline']); // Should ideally not happen if data is clean
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="editar.php?id=<?php echo htmlspecialchars($item['id_entrada']); ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="index.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar: <?php echo htmlspecialchars(addslashes($item['titulo'])); ?>?');">
                                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($item['id_entrada']); ?>">
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
                    No hay entradas para mostrar.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
