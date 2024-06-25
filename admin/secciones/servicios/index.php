<?php include '../../templates/header.php'; ?>

<div class="card">
    <div class="card-header">
        Servicios
        <a name="botonRegistro" id="botonRegistro" class="btn btn-primary" href="crear.php" role="button">Nuevo servicios</a>
    </div>
    <div class="card-body">

        <div class="table-responsive-sm">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col" class="w-25">Icono</th>
                        <th scope="col" class="w-25">Titulo</th>
                        <th scope="col" class="w-25">Descripcion</th>
                        <th scope="col" class="w-25">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="">
                        <td scope="row">Monito</td>
                        <td>El monito</td>
                        <td>Descripcion monito</td>
                        <td>Editar | Eliminar</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted">Footer</div>
</div>


<?php include '../../templates/footer.php'; ?>