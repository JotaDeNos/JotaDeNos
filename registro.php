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
      // Excluimos la columna 'id' del formulario
      if ($nombreColumna === 'id') {
        continue;
      }
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
    <button type="submit" name="insertar" class="btn btn-primary">Insertar</button>
  </form>
<?php endif; ?>
