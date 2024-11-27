<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Registros</title>
  <style>
    /* Estilo general */
    body {
      margin: 0;
      font-family: 'Arial', sans-serif;
      display: flex;
    }

    /* Menú lateral */
    .sidebar {
      width: 250px;
      background-color: #333;
      color: white;
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
    }

    .sidebar .logo {
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      padding: 20px 0;
      background-color: #ff6f00; /* Naranja */
      color: white;
    }

    .sidebar a {
      padding: 15px 20px;
      text-decoration: none;
      color: white;
      display: block;
      font-weight: bold;
      border-left: 5px solid transparent;
    }

    .sidebar a:hover {
      background-color: #444;
      border-left: 5px solid #4caf50; /* Verde */
    }

    /* Contenido principal */
    .content {
      margin-left: 250px;
      padding: 20px;
      flex: 1;
      background-color: #f4f4f4; /* Gris claro */
    }

    .content h1 {
      margin-top: 0;
      color: #4caf50; /* Verde */
    }

    .content p {
      font-size: 18px;
      color: #333;
    }

    /* Botones y formularios */
    .form-select, .btn {
      padding: 10px;
      margin: 10px 0;
      font-size: 16px;
      border: 2px solid #ff6f00; /* Naranja */
      border-radius: 5px;
    }

    .btn {
      background-color: #4caf50; /* Verde */
      color: white;
      cursor: pointer;
      font-weight: bold;
    }

    .btn:hover {
      background-color: #ff6f00; /* Naranja */
      border-color: #4caf50;
    }

    /* Tarjetas o cuadros */
    .card {
      background-color: white;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Resaltar opciones activas */
    .sidebar a.active {
      background-color: #4caf50; /* Verde */
      border-left: 5px solid #ff6f00; /* Naranja */
    }
  </style>
</head>
<body>

  <!-- Menú lateral -->
  <div class="sidebar">
    <div class="logo">Gestión de Registros</div>
    <a href="index.php?page=inicio" class="<?= ($_GET['page'] ?? 'inicio') === 'inicio' ? 'active' : '' ?>">Inicio</a>
    <a href="index.php?page=registro" class="<?= ($_GET['page'] ?? '') === 'registro' ? 'active' : '' ?>">Registro Dinámico</a>
    <a href="index.php?page=otra-opcion" class="<?= ($_GET['page'] ?? '') === 'otra-opcion' ? 'active' : '' ?>">Otra Opción</a>
  </div>

  <!-- Contenido principal -->
  <div class="content">
    <?php
    $page = $_GET['page'] ?? 'inicio';

    switch ($page) {
      case 'inicio':
        echo '<div class="card">';
        echo '<h1>Bienvenido</h1>';
        echo '<p>Selecciona una opción del menú para continuar.</p>';
        echo '</div>';
        break;

      case 'registro':
        echo '<h1>Registro Dinámico</h1>';
        ?>
        <form method="post" action="">
          <label for="tabla">Selecciona una tabla:</label>
          <select name="tabla" id="tabla" onchange="this.form.submit()" class="form-select">
            <option value="">Selecciona una tabla</option>
            <?php foreach ($tablas as $tabla): ?>
              <option value="<?= $tabla ?>" <?= $tablaSeleccionada == $tabla ? "selected" : "" ?>><?= ucfirst($tabla) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <?php if ($tablaSeleccionada && !empty($columns)): ?>
          <h2>Insertar en <?= ucfirst($tablaSeleccionada) ?></h2>
          <form method="post" action="" enctype="multipart/form-data">
            <?php foreach ($columns as $column): ?>
              <?php
              $nombreColumna = $column['Field'];
              $tipo = $column['Type'];
              if ($nombreColumna === 'id') continue;
              ?>
              <div class="mb-3">
                <label for="<?= $nombreColumna ?>" class="form-label"><?= ucfirst($nombreColumna) ?>:</label>
                <?php if (strpos($tipo, "date") !== false): ?>
                  <input type="date" name="<?= $nombreColumna ?>" class="form-control" required>
                <?php elseif (strpos($tipo, "blob") !== false): ?>
                  <input type="file" name="<?= $nombreColumna ?>" class="form-control" required>
                <?php elseif (strpos($tipo, "int") !== false): ?>
                  <input type="number" name="<?= $nombreColumna ?>" class="form-control" required>
                <?php else: ?>
                  <input type="text" name="<?= $nombreColumna ?>" class="form-control" required>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
            <input type="hidden" name="tabla" value="<?= $tablaSeleccionada ?>">
            <button type="submit" name="insertar" class="btn">Insertar</button>
          </form>
        <?php endif;
        break;

      default:
        echo '<h1>Página no encontrada</h1>';
        break;
    }
    ?>
  </div>
  <script>
    function toggleMenu() {
      document.getElementById('sidebar').classList.toggle('open');
    }

    function loadPage(page) {
      // Cargar la página dinámica por GET
      window.location.href = "?page=" + page;
    }
  </script>

</body>
</html>
