<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "inicio de sesion");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si se ha enviado el formulario
$error = null; // Inicializa la variable de error
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Consulta preparada para evitar inyecciones SQL
    $stmt = $conn->prepare("SELECT * FROM registros WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verificar la contraseña usando password_verify
        if (password_verify($password, $usuario['Contraseña'])) {
            // Iniciar sesión
            session_start();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre']; 
            $_SESSION['correo'] = $usuario['correo'];
            header("Location: index.php"); 
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No existe una cuenta con este correo.";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="sti.css">
</head>
<body>
    <div class="background"></div>
    <div class="form-container">
        <h1>Inicia Sesión</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" placeholder="Ingresa tu correo" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                    <button type="button" class="show-password" onclick="togglePassword('password', this)">
                        <img src="https://cdn-icons-png.flaticon.com/512/31/31482.png" alt="Ver contraseña">
                    </button>
                </div>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <div class="link">
            <p>¿No tienes una cuenta? <a href="registro1.php">Regístrate</a></p>
        </div>
    </div>

    <!-- Contenedor de alertas -->
    <div class="alert-container">
        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const img = button.querySelector("img");
            if (field.type === "password") {
                field.type = "text";
                img.src = "https://cdn-icons-png.flaticon.com/512/31/31482.png"; // Ojo abierto
            } else {
                field.type = "password";
                img.src = "https://cdn-icons-png.flaticon.com/512/94/94674.png"; // Ojo cerrado
            }
        }
    </script>
</body>
</html>
