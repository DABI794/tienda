<?php
session_start();
require '../db.php';  

// Verifica que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if (!isset($pdo) && !isset($conn)) {
    die("Error: Conexión a base de datos no definida.");
}

$db = isset($pdo) ? $pdo : $conn;

// Agregar producto al carrito
if (isset($_POST['agregar'])) {
    $producto_id = $_POST['producto_id'];

    // Busca si ya existe el producto en el carrito para ese usuario
    $stmt = $db->prepare("SELECT * FROM carrito WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$usuario_id, $producto_id]);
    $existe = $stmt->fetch();

    if ($existe) {
        // Si existe, incrementa la cantidad (usar el id de carrito)
        $stmt = $db->prepare("UPDATE carrito SET cantidad = cantidad + 1 WHERE id = ?");
        $stmt->execute([$existe['id']]);
    } else {
        // Si no existe, inserta nuevo registro
        $stmt = $db->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, 1)");
        $stmt->execute([$usuario_id, $producto_id]);
    }
    header("Location: ../carrito/ver_carrito.php");
    exit();
}

// Eliminar producto del carrito
if (isset($_POST['eliminar'])) {
    $carrito_id = $_POST['carrito_id'];
    // Verificamos que el producto pertenece al usuario antes de borrar
    $stmt = $db->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$carrito_id, $usuario_id]);
    header("Location: ../carrito/ver_carrito.php");
    exit();
}

// Obtener productos del carrito para el usuario
$stmt = $db->prepare("SELECT carrito.*, productos.nombre, productos.precio FROM carrito 
    JOIN productos ON carrito.producto_id = productos.id WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$items = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Carrito</title>
<style>
    body {
        background:rgb(255, 254, 254);
        color: #e0e0e0;
        font-family: Arial, sans-serif;
        padding: 20px;
    }
    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #0ff;
        text-shadow: 0 0 5px #0ff;
    }
    .carrito-item {
        background: #1a1a1a;
        margin-bottom: 15px;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px #0ff33a44;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .carrito-item div {
        font-size: 1.1em;
    }
    form {
        margin: 0;
    }
    button.eliminar-btn {
        background: #ff4d4d;
        border: none;
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    button.eliminar-btn:hover {
        background: #ff1a1a;
    }
    .vacío {
        text-align: center;
        font-size: 1.2em;
        color: #888;
        margin-top: 50px;
    }
</style>
</head>
<body>

<h2>Tu Carrito de Compras</h2>

<?php if (count($items) === 0): ?>
    <p class="vacío">Tu carrito está vacío.</p>
<?php else: ?>
    <?php foreach ($items as $item): ?>
        <div class="carrito-item">
            <div>
                <?= htmlspecialchars($item['nombre']) ?> 
                - BS<?= number_format($item['precio'], 2) ?> 
                x <?= intval($item['cantidad']) ?>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="carrito_id" value="<?= $item['id'] ?>" />
                <button type="submit" name="eliminar" class="eliminar-btn">Eliminar</button>
            </form>
        </div>
        
    <?php endforeach; ?>
<?php endif; ?>

<?php if (count($items) > 0): ?>
    <form action="../factura.php" method="POST" style="text-align: center; margin-top: 30px;">
        <button type="submit" name="comprar" class="btn btn-success">Finalizar compra y generar factura</button>
    </form>
<?php endif; ?>
</body>
</html>
