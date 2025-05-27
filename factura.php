<?php
session_start();
require '../proyecto_videojuegos/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../proyecto_videojuegos/auth/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener los productos del carrito
$stmt = $conn->prepare("SELECT carrito.*, productos.nombre, productos.precio 
    FROM carrito 
    JOIN productos ON carrito.producto_id = productos.id 
    WHERE carrito.usuario_id = ?");
$stmt->execute([$usuario_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay productos, no mostrar factura
if (count($items) === 0) {
    die("Tu carrito está vacío, no se puede generar factura.");
}

// Calcular total
$total = 0;
foreach ($items as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Simular nombre del usuario
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Cliente';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #1e1e2f, #2e2e50);
            padding: 40px;
            color: #f5f5f5;
        }
        .factura {
            max-width: 800px;
            margin: auto;
            background-color: #2a2a3d;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #00e0ff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table thead {
            background-color: #1a1a2e;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        table td {
            background-color: #2e2e44;
        }
        .total {
            text-align: right;
            font-size: 1.3em;
            font-weight: bold;
            color: #00ff88;
        }
        .gracias {
            text-align: center;
            margin-top: 30px;
            font-size: 1.1em;
            color: #ccc;
        }
        p {
            margin: 5px 0;
        }
        strong {
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="factura">
    <h2>Factura de Compra</h2>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($nombre_usuario) ?></p>
    <p><strong>Fecha:</strong> <?= date("d/m/Y H:i") ?></p>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nombre']) ?></td>
                    <td>BS<?= number_format($item['precio'], 2) ?></td>
                    <td><?= intval($item['cantidad']) ?></td>
                    <td>BS<?= number_format($item['precio'] * $item['cantidad'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="total">Total: BS<?= number_format($total, 2) ?></p>

    <p class="gracias">¡Gracias por tu compra!</p>
</div>

</body>
</html>
