<?php
// Calcular el total del carrito antes de imprimirlo
$carrito_total = 0;
if (isset($_SESSION['usuario_id'])) {
  $stmt = $conn->prepare("SELECT SUM(cantidad) AS total FROM carrito WHERE usuario_id = ?");
  $stmt->execute([$_SESSION['usuario_id']]);
  $carrito_total = $stmt->fetchColumn() ?? 0;
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg" style="background-color: #000;">
  <div class="container-fluid">
    <a class="navbar-brand text-white d-flex align-items-center" href="/proyecto_videojuegos/index.php">
      <i class="bi bi-lightning-fill me-2"></i> Pulsa Start
    </a>

    <!-- Botón hamburguesa -->
    <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav" aria-controls="menuNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link text-white" href="/proyecto_videojuegos/index.php">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="/proyecto_videojuegos/productos.php">Productos</a>
        </li>
      </ul>

      <div class="d-flex align-items-center">
        <a class="nav-link text-white me-3" href="/proyecto_videojuegos/carrito/ver_carrito.php">
          <i class="bi bi-cart"></i> Carrito <span id="carrito-contador" class="badge bg-light text-dark"><?= $carrito_total ?></span>
        </a>

        <?php if (isset($_SESSION['usuario_nombre'])): ?>
          <span class="text-white me-3">Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>

          <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
            <a class="nav-link text-white me-3" href="/proyecto_videojuegos/admin/agregar_producto.php">Admin</a>
          <?php endif; ?>

          <a class="nav-link text-white" href="/proyecto_videojuegos/logout.php">Cerrar sesión</a>
        <?php else: ?>
          <a class="nav-link text-white" href="/proyecto_videojuegos/auth/login.php">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<script>
  function actualizarContadorCarrito() {
    fetch('/proyecto_videojuegos/carrito/contar_carrito.php')
      .then(response => response.json())
      .then(data => {
        document.getElementById('carrito-contador').textContent = data.total;
      });
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('agregar-carrito').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const productoId = this.getAttribute('data-producto-id');

        fetch('/proyecto_videojuegos/carrito/agregar_carrito.php', {
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

    // Actualizar contador al cargar la página
    actualizarContadorCarrito();
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
