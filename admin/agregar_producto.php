<?php
session_start();
require_once '../db.php';

// Obtener categorías
$stmtCat = $conn->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Agregar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $imagen_nombre = '';

    if ($nombre && $precio > 0 && $categoria_id > 0 && isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_tmp = $_FILES['imagen']['tmp_name'];
        $imagen_nombre_original = basename($_FILES['imagen']['name']);
        $ext = strtolower(pathinfo($imagen_nombre_original, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $permitidas)) {
            $imagen_nombre = uniqid() . '.' . $ext;
            $ruta_destino = "../assets/" . $imagen_nombre;

            if (move_uploaded_file($imagen_tmp, $ruta_destino)) {
                $sql = "INSERT INTO productos (nombre, precio, imagen, categoria_id) 
                        VALUES (:nombre, :precio, :imagen, :categoria_id)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':precio' => $precio,
                    ':imagen' => $imagen_nombre,
                    ':categoria_id' => $categoria_id
                ]);
                $mensaje = "Producto agregado correctamente.";
            } else {
                $mensaje = "Error al subir la imagen.";
            }
        } else {
            $mensaje = "Tipo de imagen no válido. Usa JPG, PNG o GIF.";
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}

// Eliminar producto
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = intval($_GET['eliminar_id']);

    $stmtImg = $conn->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmtImg->execute([$eliminar_id]);
    $producto = $stmtImg->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        if (!filter_var($producto['imagen'], FILTER_VALIDATE_URL)) {
            $imagen_path = "../assets/" . $producto['imagen'];
            if (file_exists($imagen_path)) {
                unlink($imagen_path);
            }
        }

          $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$eliminar_id]);

        $mensaje = "Producto eliminado correctamente.";
    }
}

// Obtener productos
$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.id DESC";
$stmt = $conn->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Panel Admin - Productos</title>
    <link rel="stylesheet" href="/proyecto_videojuegos/stilos/style.css" />
    <style>
        body {
            background:rgb(255, 248, 248);
            color:rgb(0, 0, 0);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: rgb(0, 0, 0);
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        img {
            max-width: 100px;
            height: auto;
        }

        .acciones a {
            margin-right: 10px;
            color: #007BFF;
            text-decoration: none;
        }

        .acciones a:hover {
            text-decoration: underline;
        }

        .mensaje {
            color: green;
            margin-top: 10px;
        }

        .formulario {
            margin: 30px 0;
            padding: 20px;
            background-color: rgb(152, 149, 193);
            border-radius: 10px;

        }

        .formulario input,
        .formulario select {
            padding: 8px;
            width: 100%;
            margin-bottom: 10px;
        }

        .formulario button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .formulario button:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <h2>Panel de Administración - Productos</h2>

    <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>

    <div class="formulario">
        <h3>Agregar nuevo producto</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Precio:</label>
            <input type="number" name="precio" step="0.01" required>

            <label>Imagen:</label>
            <input type="file" name="imagen" accept="image/*" required>

            <label>Categoría:</label>
            <select name="categoria_id" required>
                <option value="">Seleccionar</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Agregar Producto</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Imagen</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $prod): ?>
                <tr>
                    <td><?= htmlspecialchars($prod['nombre']) ?></td>
                    <td>BS<?= number_format($prod['precio'], 2) ?></td>
                    <td>
                        <?php if (filter_var($prod['imagen'], FILTER_VALIDATE_URL)): ?>
                            <img src="<?= htmlspecialchars($prod['imagen']) ?>" alt="Imagen producto">
                        <?php else: ?>
                            <img src="/proyecto_videojuegos/assets/<?= htmlspecialchars($prod['imagen']) ?>" alt="Imagen producto">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($prod['categoria_nombre']) ?></td>
                    <td class="acciones">
                        <a href="editar_producto.php?id=<?= $prod['id'] ?>">Editar</a>
                        <a href="../admin/agregar_producto.php?eliminar_id=<?= (int) $prod['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este producto?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html> 