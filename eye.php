<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Avanzada de Tablas</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .form-inline {
            display: flex;
            align-items: center;
        }
        .form-inline input, .form-inline select, .form-inline button {
            margin-right: 5px;
        }
        .hidden {
            display: none;
        }
        .highlight {
            background-color: yellow;
        }
        .collapsible {
            cursor: pointer;
            background-color: #f2f2f2;
            padding: 8px;
            border: 1px solid black;
        }
        .content {
            display: none;
            padding: 10px;
            border: 1px solid black;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <h1>Gestión Avanzada de Tablas con Filtros y Edición</h1>

    <!-- Buscador de texto -->
    <div>
        <label for="searchInput">Buscar: </label>
        <input type="text" id="searchInput" placeholder="Escribe para buscar...">
    </div>

    <?php
    function conectarBD() {
        $conexion = new mysqli("localhost", "root", "", "gestion_docentes");
        if ($conexion->connect_error) {
            die("Error de conexión: " . $conexion->connect_error);
        }
        return $conexion;
    }

    $conexion = conectarBD();

    // Obtener tablas disponibles
    $consultaTablas = $conexion->query("SHOW TABLES");
    $tablasDisponibles = [];
    if ($consultaTablas) {
        while ($fila = $consultaTablas->fetch_array()) {
            $tablasDisponibles[] = $fila[0];
        }
    }

    // Formulario para seleccionar tabla
    echo "<form method='GET' action='' style='margin-bottom: 20px;'>";
    echo "<label for='tabla'>Seleccionar tabla: </label>";
    echo "<select name='tabla' id='tabla'>";
    foreach ($tablasDisponibles as $tabla) {
        $seleccionada = isset($_GET['tabla']) && $_GET['tabla'] === $tabla ? "selected" : "";
        echo "<option value='$tabla' $seleccionada>" . ucfirst($tabla) . "</option>";
    }
    echo "</select>";
    echo "<button type='submit'>Filtrar</button>";
    echo "</form>";

    // Verificar si se seleccionó una tabla
    if (isset($_GET['tabla']) && in_array($_GET['tabla'], $tablasDisponibles)) {
        $tablaSeleccionada = $_GET['tabla'];

        // Obtener columnas y valores únicos
        $columnas = [];
        $valoresUnicos = [];
        $columnasConsulta = $conexion->query("SHOW COLUMNS FROM $tablaSeleccionada");
        if ($columnasConsulta) {
            while ($columna = $columnasConsulta->fetch_assoc()) {
                $columnas[] = $columna['Field'];
                $valoresConsulta = $conexion->query("SELECT DISTINCT " . $columna['Field'] . " FROM $tablaSeleccionada");
                $valoresUnicos[$columna['Field']] = $valoresConsulta ? array_column($valoresConsulta->fetch_all(MYSQLI_ASSOC), $columna['Field']) : [];
            }
        }

        // Filtros avanzados con encabezados interactivos
        echo "<div style='margin-bottom: 20px;'>";
        foreach ($columnas as $columna) {
            echo "<div class='collapsible'>" . ucfirst($columna) . "</div>";
            echo "<div class='content'>";
            echo "<form method='GET' action=''>";
            echo "<input type='hidden' name='tabla' value='" . htmlspecialchars($tablaSeleccionada) . "'>";
            foreach ($valoresUnicos[$columna] as $valor) {
                $checked = isset($_GET[$columna]) && in_array($valor, $_GET[$columna]) ? "checked" : "";
                echo "<label><input type='checkbox' name='{$columna}[]' value='$valor' $checked> $valor</label><br>";
            }
            echo "<button type='submit'>Aplicar Filtro</button>";
            echo "</form>";
            echo "</div>";
        }
        echo "</div>";

        // Construir cláusula WHERE
        $filtros = [];
        foreach ($columnas as $columna) {
            if (!empty($_GET[$columna])) {
                $valoresFiltrados = array_map(fn($valor) => "'" . $conexion->real_escape_string($valor) . "'", $_GET[$columna]);
                $filtros[] = "$columna IN (" . implode(", ", $valoresFiltrados) . ")";
            }
        }
        $whereSQL = !empty($filtros) ? "WHERE " . implode(" AND ", $filtros) : "";

        // Consultar datos filtrados
        $sql = "SELECT * FROM $tablaSeleccionada $whereSQL";
        $resultado = $conexion->query($sql);

        // Mostrar datos
        if ($resultado && $resultado->num_rows > 0) {
            echo "<table id='dataTable'>";
            echo "<tr>";
            foreach ($columnas as $columna) {
                echo "<th>" . htmlspecialchars($columna) . "</th>";
            }
            echo "<th>Acciones</th>";
            echo "</tr>";

            while ($fila = $resultado->fetch_assoc()) {
                echo "<tr>";
                echo "<form method='POST' action='' class='form-inline'>";
                foreach ($columnas as $columna) {
                    echo "<td><span class='editable'>" . htmlspecialchars($fila[$columna]) . "</span>";
                    echo "<input type='text' class='hidden editor' name='datos[$columna]' value='" . htmlspecialchars($fila[$columna]) . "'></td>";
                }
                echo "<td>";
                echo "<input type='hidden' name='tabla' value='" . htmlspecialchars($tablaSeleccionada) . "'>";
                echo "<input type='hidden' name='clave_primaria' value='" . htmlspecialchars(json_encode($fila)) . "'>";
                echo "<button type='button' class='edit-button'>Editar</button>";
                echo "<button type='submit' name='accion' value='actualizar' class='hidden save-button'>Guardar</button>";
                echo "<button type='button' class='hidden cancel-button'>Cancelar</button>";
                echo "<button type='submit' name='accion' value='eliminar' class='delete-button' onclick='return confirmarEliminacion();'>Eliminar</button>";
                echo "</td>";
                echo "</form>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No se encontraron resultados.</p>";
        }
    }

    $conexion->close();
    ?>

    <script>
        // Confirmar eliminación
        function confirmarEliminacion() {
            return confirm("¿Estás seguro de que deseas eliminar este registro?");
        }

        // Desplegar contenido interactivo
        document.querySelectorAll('.collapsible').forEach(collapsible => {
            collapsible.addEventListener('click', () => {
                const content = collapsible.nextElementSibling;
                content.style.display = content.style.display === "block" ? "none" : "block";
            });
        });

        // Botones de edición
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', () => {
                const row = button.closest('tr');
                row.querySelectorAll('.editable').forEach(el => el.classList.add('hidden'));
                row.querySelectorAll('.editor').forEach(el => el.classList.remove('hidden'));
                row.querySelector('.edit-button').classList.add('hidden');
                row.querySelector('.save-button').classList.remove('hidden');
                row.querySelector('.cancel-button').classList.remove('hidden');
            });
        });

        document.querySelectorAll('.cancel-button').forEach(button => {
            button.addEventListener('click', () => {
                const row = button.closest('tr');
                row.querySelectorAll('.editable').forEach(el => el.classList.remove('hidden'));
                row.querySelectorAll('.editor').forEach(el => el.classList.add('hidden'));
                row.querySelector('.edit-button').classList.remove('hidden');
                row.querySelector('.save-button').classList.add('hidden');
                row.querySelector('.cancel-button').classList.add('hidden');
            });
        });

        // Buscar en la tabla
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#dataTable tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let matchFound = false;

                cells.forEach(cell => {
                    const text = cell.textContent || cell.innerText;
                    if (text.toLowerCase().includes(searchTerm)) {
                        matchFound = true;
                        cell.classList.add('highlight');
                    } else {
                        cell.classList.remove('highlight');
                    }
                });

                row.style.display = matchFound ? '' : 'none';
            });
        });
    </script>
</body>
</html>
