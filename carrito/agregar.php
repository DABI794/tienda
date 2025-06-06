<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => false, 'mensaje' => 'No has iniciado sesión']);
    exit();
}


$usuario_id = $_SESSION['usuario_id'];
$producto_id = $_POST['producto_id'] ?? null;

if (!$producto_id) {
    die("Producto inválido.");
}

// Verificar si ya existe el producto en el carrito
$stmt = $conn->prepare("SELECT * FROM carrito WHERE usuario_id = ? AND producto_id = ?");
$stmt->execute([$usuario_id, $producto_id]);
$existe = $stmt->fetch();

if ($existe) {
    // Incrementar cantidad
    $stmt = $conn->prepare("UPDATE carrito SET cantidad = cantidad + 1 WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$usuario_id, $producto_id]);
} else {
    // Insertar nuevo
    $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, 1)");
    $stmt->execute([$usuario_id, $producto_id]);
}
exit();
