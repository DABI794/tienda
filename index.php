<?php
require 'db.php';
include '../proyecto_videojuegos/includes/navbar.php';

$stmt = $conn->query("SELECT * FROM productos");
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/proyecto_videojuegos/stilos/style.css" />
  <title>Tienda</title>
  <style>
    .card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 200, 255, 0.35);
    }
  </style>
</head>
<body>

<div class="container mt-4">
  <div class="row">
    <?php foreach ($productos as $producto): ?>
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm rounded">
          <?php
          $img_url = !empty($producto['imagen_url']) ? htmlspecialchars($producto['imagen_url']) : '/proyecto_videojuegos/assets/default.jpg';
          ?>
          <img
            src="<?= htmlspecialchars($producto['imagen'] ?? '') ?>"
            class="card-img-top"
            alt="<?= htmlspecialchars($producto['nombre']) ?>"
            onerror="this.onerror=null;this.src='/proyecto_videojuegos/assets/default.jpg';" />
          <div class="card-body text-center">
            <h5 class="card-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
            <p class="card-text">BS<?= number_format($producto['precio'], 2) ?></p>
            <button class="btn btn-dark btn-sm me-2 agregar-carrito" data-producto-id="<?= (int)$producto['id'] ?>">Añadir a carrito</button>
            <a href="info.php?id=<?= (int)$producto['id'] ?>" class="btn btn-outline-dark btn-sm mt-2">Información</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AJAX para carrito -->
<script>
  function actualizarContadorCarrito() {
    fetch('/proyecto_videojuegos/carrito/obtener_total.php')
      .then(response => response.json())
      .then(data => {
        document.getElementById('carrito-contador').textContent = data.total;
      });
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.agregar-carrito').forEach(function(btn) {
      btn.addEventListener('click', function(event) {
        event.preventDefault();
        const productoId = this.getAttribute('data-producto-id');

        fetch('/proyecto_videojuegos/carrito/agregar_ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'producto_id=' + encodeURIComponent(productoId)
        })
        .then(response => response.json())
        .then(data => {
          if (data.exito) {
            actualizarContadorCarrito();
          } else {
            alert(data.mensaje || 'Hubo un error al agregar al carrito.');
          }
        });
      });
    });

    actualizarContadorCarrito();
  });
</script>

</body>
</html>
