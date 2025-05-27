<?php
session_start();
require_once '../db.php';

if (!isset($_GET['id'])) {
    header("Location: /admin_productos.php");
    exit;
}

$id = intval($_GET['id']);
$mensaje = '';

// Obtener datos actuales del producto
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: /admin_productos.php");
    exit;
}

// Obtener categorías para el select
$categoriaStmt = $conn->query("SELECT * FROM categorias");
$categorias = $categoriaStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];

    // Actualizar imagen solo si se sube una nueva
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $nombre_imagen = uniqid() . "." . $ext;
            $ruta = "../uploads/" . $nombre_imagen;
            move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);

            // Borrar imagen antigua
            $ruta_antigua = "../uploads/" . $producto['imagen'];
            if (file_exists($ruta_antigua)) {
                unlink($ruta_antigua);
            }

            $sql = "UPDATE productos SET nombre = ?, precio = ?, imagen = ?, categoria_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nombre, $precio, $nombre_imagen, $categoria_id, $id]);
            $mensaje = "Producto actualizado correctamente.";
        } else {
            $mensaje = "Formato de imagen no permitido.";
        }
    } else {
        // Actualizar sin cambiar imagen
        $sql = "UPDATE productos SET nombre = ?, precio = ?, categoria_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nombre, $precio, $categoria_id, $id]);
        $mensaje = "Producto actualizado correctamente.";
    }

    // Recargar datos del producto actualizado
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Producto</title>
    <link rel="stylesheet" href="/proyecto_videojuegos/stilos/style.css" />
</head>
<body>
    <h2>Editar Producto</h2>

    <?php if ($mensaje) echo "<p>$mensaje</p>"; ?>

    <form action="editar_producto.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
        <input type="text" name="nombre" placeholder="Nombre del producto" value="<?= htmlspecialchars($producto['nombre']) ?>" required />
        <input type="number" step="0.01" name="precio" placeholder="Precio" value="<?= htmlspecialchars($producto['precio']) ?>" required />
        
        <select name="categoria_id" required>
            <option value="">Seleccione categoría</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $producto['categoria_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <p>Imagen actual:</p>
        <img src="/proyecto_videojuegos/assents/<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen producto" style="max-width:150px;" />

        <p>Cambiar imagen (opcional):</p>
        <input type="file" name="imagen" accept="image/*" />

        <button type="submit">Guardar Cambios</button>
        <a href="../admin/agregar_producto.php" class="btn-volver">Agregar Nuevo Producto</a>

    </form>

    <p><a href="admin_productos.php">Volver al panel</a></p>
    <style>
         h2 {
            color: #222;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            border-radius: 10px;
            padding: 25px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        input[type="text"],
        input[type="number"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #218838;
        }

        p {
            margin-top: 15px;
        }

        .mensaje {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        img {
            max-width: 150px;
            display: block;
            margin-top: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</body>
</html>
