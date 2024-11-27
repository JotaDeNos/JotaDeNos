<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_docentes";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

// Inicializar $tablaSeleccionada para evitar advertencias
$tablaSeleccionada = isset($_POST['tabla']) ? $_POST['tabla'] : null;
$columns = [];

// Obtener las tablas de la base de datos
$queryTablas = "SHOW TABLES";
$resultTablas = $conn->query($queryTablas);
$tablas = [];
if ($resultTablas) {
    while ($row = $resultTablas->fetch_row()) {
        $tablas[] = $row[0];
    }
}

// Obtener las columnas de la tabla seleccionada
if ($tablaSeleccionada) {
    $query = "DESCRIBE $tablaSeleccionada";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row;
        }
    } else {
        echo "Error al obtener columnas: " . $conn->error;
    }
}

// Procesar formulario de inserción
if (isset($_POST['insertar'])) {
    $valores = [];
    foreach ($columns as $column) {
        $nombreColumna = $column['Field'];
        if (isset($_POST[$nombreColumna])) {
            $valores[] = "'" . $conn->real_escape_string($_POST[$nombreColumna]) . "'";
        }
    }
    $valores = implode(", ", $valores);
    $queryInsertar = "INSERT INTO $tablaSeleccionada (" . implode(", ", array_column($columns, 'Field')) . ") VALUES ($valores)";
    if ($conn->query($queryInsertar)) {
        echo "Registro insertado con éxito.";
    } else {
        echo "Error al insertar: " . $conn->error;
    }
}
?>
c