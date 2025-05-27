<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['total' => 0]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT SUM(cantidad) AS total FROM carrito WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total = (int) $stmt->fetchColumn();

echo json_encode(['total' => $total]);

exit();
