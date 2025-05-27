<?php
require 'db.php';
include 'includes/navbar.php';

if (!isset($_GET['id'])) {
  die("Producto no encontrado.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
  die("Producto inválido.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($producto['nombre']) ?> - Detalles</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/proyecto_videojuegos/stilos/style.css">
  <style>
    body {
      background: linear-gradient(to right, #e0f7fa, #f1f8e9);
    }

    .producto-card {
      background-color: #fff;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }

    .producto-card:hover {
      transform: translateY(-5px);
    }

    .producto-img {
      max-height: 400px;
      object-fit: cover;
      border-radius: 1rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-dark {
      transition: background-color 0.3s, transform 0.2s;
    }

    .btn-dark:hover {
      background-color: #0d6efd;
      transform: scale(1.05);
    }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-10 producto-card">
      <div class="row align-items-center">
        <div class="col-md-6 text-center mb-4 mb-md-0">
          <img
            src="<?= htmlspecialchars($producto['imagen'] ?? '') ?>"
            class="img-fluid producto-img"
            alt="<?= htmlspecialchars($producto['nombre']) ?>"
            onerror="this.onerror=null;this.src='/proyecto_videojuegos/assets/default.jpg';" />
        </div>
        <div class="col-md-6">
          <h1 class="fw-bold"><?= htmlspecialchars($producto['nombre']) ?></h1>
          <p class="text-muted">
            <?= htmlspecialchars($producto['descripcion'] ?? 'Descripción no disponible.') ?>
          </p>
          <p class="fs-4 fw-semibold text-success">BS<?= number_format($producto['precio'], 2) ?></p>

          <form method="POST" action="/proyecto_videojuegos/carrito/ver_carrito.php" class="d-inline">
            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
            <button type="submit" name="agregar" class="btn btn-dark shadow-sm me-2"> Añadir a carrito</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
