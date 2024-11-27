<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="sti.css"> <!-- Donde el estilo se convierte en moda -->
</head>
<body>
    <!-- La capa de fondo, porque un formulario sin fondo es como una fiesta sin decoración -->
    <div class="background"></div>

    <!-- La caja de registro, donde empieza el viaje para unirse al club -->
    <div class="form-container">
        <h1>Registro</h1>
        <form id="registroForm" method="POST" action="" onsubmit="return validarFormulario()">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ingresa tu nombre" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" placeholder="Ingresa tu apellido" required>
            </div>
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" placeholder="Ingresa tu correo" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                    <button type="button" class="show-password" onclick="togglePassword('password', this)">
                        <img src="https://cdn-icons-png.flaticon.com/512/31/31482.png" alt="Ver contraseña">
                    </button>
                </div>
                <p id="passwordError" class="error" style="display: none; color: red;">La contraseña debe tener al menos 8 caracteres.</p>
            </div>
            <div class="form-group">
                <label for="confirmarPassword">Confirmar Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="confirmarPassword" name="confirmarPassword" placeholder="Confirma tu contraseña" required>
                    <button type="button" class="show-password" onclick="togglePassword('confirmarPassword', this)">
                        <img src="https://cdn-icons-png.flaticon.com/512/31/31482.png" alt="Ver contraseña">
                    </button>
                </div>
                <p id="confirmError" class="error" style="display: none; color: red;">Las contraseñas no coinciden.</p>
            </div>
            <button type="submit" name="submit" class="btn">Registrarse</button>
        </form>
        <div class="link">
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
    </div>

    <!-- Contenedor de alertas dinámicas -->
    <div class="alert-container">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $correo = $_POST['correo'];
            $password = $_POST['password'];
            $confirmarPassword = $_POST['confirmarPassword'];

            if ($password !== $confirmarPassword) {
                echo "<div class='alert error'>Las contraseñas no coinciden.</div>";
            } elseif (strlen($password) < 8) {
                echo "<div class='alert error'>La contraseña debe tener al menos 8 caracteres.</div>";
            } else {
                // Conexión a la base de datos
                $conexion = new mysqli('localhost', 'root', '', 'inicio de sesion');

                if ($conexion->connect_error) {
                    echo "<div class='alert error'>Error al conectar con la base de datos.</div>";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conexion->prepare("INSERT INTO registros (nombre, apellido, correo, contraseña) VALUES (?, ?, ?, ?)");

                    if ($stmt) {
                        $stmt->bind_param("ssss", $nombre, $apellido, $correo, $hashedPassword);

                        if ($stmt->execute()) {
                            echo "<div class='alert success'>Registro exitoso. ¡Bienvenido!</div>";
                        } else {
                            echo "<div class='alert error'>Error al guardar los datos.</div>";
                        }

                        $stmt->close();
                    } else {
                        echo "<div class='alert error'>Error al preparar la consulta.</div>";
                    }

                    $conexion->close();
                }
            }
        }
        ?>
    </div>

    <script>
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            if (field.type === "password") {
                field.type = "text";
                button.querySelector("img").src = "https://cdn-icons-png.flaticon.com/512/31/31482.png"; // Ojo abierto
            } else {
                field.type = "password";
                button.querySelector("img").src = "https://cdn-icons-png.flaticon.com/512/94/94674.png"; // Ojo cerrado
            }
        }

        function validarFormulario() {
            const password = document.getElementById("password").value;
            const confirmarPassword = document.getElementById("confirmarPassword").value;

            const passwordError = document.getElementById("passwordError");
            const confirmError = document.getElementById("confirmError");

            let esValido = true;

            if (password.length < 8) {
                passwordError.style.display = "block";
                esValido = false;
            } else {
                passwordError.style.display = "none";
            }

            if (password !== confirmarPassword) {
                confirmError.style.display = "block";
                esValido = false;
            } else {
                confirmError.style.display = "none";
            }

            return esValido;
        }
    </script>
</body>
</html>
