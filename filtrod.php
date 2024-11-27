<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtro Dinámico Completo</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Función de búsqueda en tiempo real
        function searchText() {
            var searchValue = document.getElementById("searchInput").value.toLowerCase();
            var cells = document.querySelectorAll("table td");
            var found = false;

            cells.forEach(function(cell) {
                var text = cell.textContent.toLowerCase();
                if (text.includes(searchValue)) {
                    cell.style.backgroundColor = "yellow"; // Resalta las celdas
                    found = true;
                } else {
                    cell.style.backgroundColor = ""; // Elimina el resalte
                }
            });

            if (!found && searchValue) {
                // Si no se encuentra nada y el campo no está vacío, mostrar mensaje
                document.getElementById("noResultsMessage").style.display = "block";
            } else {
                document.getElementById("noResultsMessage").style.display = "none";
            }
        }
    </script>
</head>
<body>
    <h1>Filtro Dinámico Completo</h1>

    <?php
    // Conexión a la base de datos
    function conectarBD() {
        $conexion = new mysqli("localhost", "root", "", "gestion_docentes");
        if ($conexion->connect_error) {
            die("Error de conexión: " . $conexion->connect_error);
        }
        return $conexion;
    }

    $conexion = conectarBD();

    // Botones de navegación
    echo "<form method='GET' action='' style='margin-bottom: 20px; display: inline-block;'>
            <button type='submit'>Volver al principio</button>
          </form>";

    if (isset($_GET['tablas']) || isset($_GET['columnas']) || isset($_GET['filtro_valor'])) {
        echo "<form method='GET' action='' style='margin-bottom: 20px; display: inline-block;'>";
        if (isset($_GET['columnas']) && !isset($_GET['filtro_valor'])) {
            foreach ($_GET['tablas'] as $tabla) {
                echo "<input type='hidden' name='tablas[]' value='$tabla'>";
            }
        } elseif (isset($_GET['filtro_valor'])) {
            foreach ($_GET['tablas'] as $tabla) {
                echo "<input type='hidden' name='tablas[]' value='$tabla'>";
            }
            foreach ($_GET['columnas'] as $tabla => $columnas) {
                foreach ($columnas as $columna) {
                    echo "<input type='hidden' name='columnas[$tabla][]' value='$columna'>";
                }
            }
        }
        echo "<button type='submit'>Paso anterior</button>";
        echo "</form>";
    }

    // Paso 1: Selección de tablas
    if (!isset($_GET['tablas']) && !isset($_GET['columnas']) && !isset($_GET['filtro_valor'])) {
        echo "<h2>Selecciona las tablas</h2>";
        $resultadoTablas = $conexion->query("SHOW TABLES");
        echo "<form method='GET' action=''>";
        while ($tabla = $resultadoTablas->fetch_array()) {
            $nombreTabla = $tabla[0];
            echo "<label><input type='checkbox' name='tablas[]' value='$nombreTabla'> " . ucfirst($nombreTabla) . "</label><br>";
        }
        echo "<button type='submit'>Siguiente</button>";
        echo "<button type='submit' name='mostrar_datos'>Mostrar Datos</button>";
        echo "</form>";
    }

    // Paso 2: Selección de columnas
    if (isset($_GET['tablas']) && !isset($_GET['columnas']) && !isset($_GET['filtro_valor'])) {
        $tablasSeleccionadas = $_GET['tablas'];
        echo "<h2>Selecciona las columnas</h2>";
        echo "<form method='GET' action=''>";
        foreach ($tablasSeleccionadas as $tabla) {
            echo "<h3>Columnas de la tabla $tabla</h3>";
            $columnasResultado = $conexion->query("DESCRIBE $tabla");
            while ($columna = $columnasResultado->fetch_assoc()) {
                $nombreColumna = $columna['Field'];
                echo "<label><input type='checkbox' name='columnas[$tabla][]' value='$nombreColumna'> $nombreColumna</label><br>";
            }
            echo "<input type='hidden' name='tablas[]' value='$tabla'>";
        }
        echo "<button type='submit'>Siguiente</button>";
        echo "<button type='submit' name='mostrar_datos'>Mostrar Datos</button>";
        echo "</form>";
    }

    // Paso 3: Filtrar valores únicos
    if (isset($_GET['columnas']) && !isset($_GET['filtro_valor']) && !isset($_GET['mostrar_datos'])) {
        $tablasSeleccionadas = $_GET['tablas'];
        $columnasSeleccionadas = $_GET['columnas'];
        echo "<h2>Selecciona valores únicos</h2>";
        echo "<form method='GET' action=''>";
        foreach ($columnasSeleccionadas as $tabla => $columnas) {
            foreach ($columnas as $columna) {
                echo "<h3>Valores únicos de '$columna' en '$tabla'</h3>";
                $resultadoValores = $conexion->query("SELECT DISTINCT `$columna` FROM `$tabla`");
                while ($valor = $resultadoValores->fetch_assoc()) {
                    $valorUnico = htmlspecialchars($valor[$columna]);
                    echo "<label><input type='checkbox' name='filtro_valor[$tabla][$columna][]' value='$valorUnico'> $valorUnico</label><br>";
                }
            }
            echo "<input type='hidden' name='tablas[]' value='$tabla'>";
            foreach ($columnas as $columna) {
                echo "<input type='hidden' name='columnas[$tabla][]' value='$columna'>";
            }
        }
        echo "<button type='submit'>Aplicar Filtros</button>";
        echo "</form>";
    }

    // Paso 4: Mostrar resultados
    if (isset($_GET['mostrar_datos']) || isset($_GET['filtro_valor'])) {
        $tablasSeleccionadas = $_GET['tablas'];
        $columnasSeleccionadas = $_GET['columnas'] ?? [];
        $filtros = $_GET['filtro_valor'] ?? [];

        echo "<h2>Datos Seleccionados</h2>";
        $columnasSQL = [];
        foreach ($tablasSeleccionadas as $tabla) {
            if (isset($columnasSeleccionadas[$tabla])) {
                foreach ($columnasSeleccionadas[$tabla] as $columna) {
                    $alias = "`$tabla`.`$columna` AS `$tabla.$columna`";
                    $columnasSQL[] = $alias;
                }
            } else {
                $columnasResultado = $conexion->query("DESCRIBE $tabla");
                while ($columna = $columnasResultado->fetch_assoc()) {
                    $nombreColumna = $columna['Field'];
                    $columnasSQL[] = "`$tabla`.`$nombreColumna` AS `$tabla.$nombreColumna`";
                }
            }
        }

        $consultaSQL = "SELECT " . implode(", ", $columnasSQL) . " FROM " . implode(", ", $tablasSeleccionadas);

        if (!empty($filtros)) {
            $condiciones = [];
            foreach ($filtros as $tabla => $columnas) {
                foreach ($columnas as $columna => $valores) {
                    $valoresEscapados = array_map([$conexion, 'real_escape_string'], $valores);
                    $condiciones[] = "`$tabla`.`$columna` IN ('" . implode("','", $valoresEscapados) . "')";
                }
            }
            $consultaSQL .= " WHERE " . implode(" AND ", $condiciones);
        }

        $resultado = $conexion->query($consultaSQL);
        if ($resultado && $resultado->num_rows > 0) {
            echo "<table border='1'><tr>";
            foreach ($resultado->fetch_fields() as $columna) {
                echo "<th>{$columna->name}</th>";
            }
            echo "</tr>";

            while ($fila = $resultado->fetch_assoc()) {
                echo "<tr>";
                foreach ($fila as $valor) {
                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay datos disponibles para la selección realizada.</p>";
        }
    }

    $conexion->close();
    ?>

    <h2>Buscar en la página</h2>
    <input type="text" id="searchInput" placeholder="Buscar..." oninput="searchText()"> <!-- Cambio: oninput en lugar de onclick -->
    
    <!-- Mensaje en caso de no encontrar resultados -->
    <p id="noResultsMessage" style="color: red; display: none;">No se encontraron coincidencias.</p>

    <!-- Aquí va la tabla de datos -->
    <!-- Los datos de la tabla se generan dinámicamente a través de PHP -->

</body>
</html>
