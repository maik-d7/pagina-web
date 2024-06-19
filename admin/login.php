<?php
include "./bd.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <title>Login User</title>
</head>

<body>
    <!-- header -->
    <header>

    </header>

    <!-- main -->
    <main>
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <!-- First Column oculto -->
                </div>
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            Login
                        </div>
                        <div class="card-body">
                            <form
                                action=""
                                method="post"
                            >
                                <div class="mb-3">
                                    <label
                                        for="nombre"
                                        class="form-label"
                                    >Nombre</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="nombre"
                                        id="nombre"
                                        aria-describedby="No cometer error"
                                        placeholder="Ingrese nombre de Usuario"
                                    />
                                </div>
                                <div class="mb-3">
                                    <label
                                        for="correo"
                                        class="form-label"
                                    >Correo</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        name="correo"
                                        id="correo"
                                        aria-describedby="helpId"
                                        placeholder="Ingrese correo"
                                    />
                                </div>
                                <div class="mb-3">
                                    <label
                                        for="pass"
                                        class="form-label"
                                    >Password</label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        name="pass"
                                        id="pass"
                                        placeholder="Contrasenia"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >
                                    Ingresar
                                </button>
                                <input
                                    name="boton"
                                    id="boton"
                                    class="btn btn-warning"
                                    type="button"
                                    value="input Boton"
                                />

                            </form>
                        </div>

                        <div class="card-footer text-muted">
                            Footer
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </main>

    <!-- footer -->
    <footer>

    </footer>

    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>

</body>

</html>

<<<<<<< HEAD
<!-- se modificara en la rama tres  -->
 <!-- modificacion en rama uno -->
=======
<!-- modificacion en rama dos despues de login -->
>>>>>>> 13c5cfe5ff6fe2e8412ea25e90916e4ac37d26c8
