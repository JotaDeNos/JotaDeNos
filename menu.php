<?php
// Incluir el archivo de conexión a la base de datos si lo necesitas
// include('conexion.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Registros</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      margin: 0;
      padding: 0;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #FF821B;
      color: white;
      padding: 10px 20px;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
    }

    .menu-button {
      background-color: transparent;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
    }

    .container {
      margin: 100px auto 20px auto;
      max-width: 800px;
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: -250px;
      width: 250px;
      height: 100%;
      background-color: #333;
      color: white;
      padding: 20px;
      transition: 0.3s;
    }

    .sidebar.open {
      left: 0;
    }

    .sidebar button {
      width: 100%;
      padding: 10px;
      background-color: #FF821B;
      color: white;
      border: none;
      margin-bottom: 10px;
      cursor: pointer;
    }

    .sidebar button:hover {
      background-color: #0db882;
    }

    .carousel {
      display: flex;
      overflow: hidden;
      position: relative;
      margin-top: 20px;
    }

    .carousel img {
      width: 100%;
      transition: opacity 1s ease-in-out;
    }

    .carousel img.previous {
      opacity: 0;
    }

    .carousel img.active {
      opacity: 1;
    }
  </style>
</head>
<body>
  <div class="header">
    <button class="menu-button" onclick="toggleMenu()">☰</button>
    <h1>Gestión de Registros</h1>
  </div>

  <div id="sidebar" class="sidebar">
    <h3>Menú</h3>
    <button onclick="loadPage('inicio')">Inicio</button>
    <button onclick="loadPage('registro')">Registro Dinámico</button>
  </div>

  <div id="content" class="container">
    <?php
    // Incluir contenido según la selección
    if (!isset($_GET['page']) || $_GET['page'] == 'inicio') {
        include('inicio.php');
    } elseif ($_GET['page'] == 'registro') {
        include('registro.php');
    }
    ?>
  </div>

  <script>
    function toggleMenu() {
      document.getElementById('sidebar').classList.toggle('open');
    }

    function loadPage(page) {
      window.location.href = "?page=" + page;
    }
  </script>
</body>
</html>
