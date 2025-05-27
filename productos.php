<?php
session_start();
require_once 'db.php';

// Cargar categorías para el filtro
$stmtCat = $conn->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Preparar consulta para productos con filtros
$condiciones = [];
$params = [];

if (!empty($_GET['busqueda'])) {
    $condiciones[] = "nombre LIKE :busqueda";
    $params[':busqueda'] = "%" . $_GET['busqueda'] . "%";
}

if (!empty($_GET['categoria'])) {
    $condiciones[] = "categoria_id = :categoria";
    $params[':categoria'] = $_GET['categoria'];
}

$where = $condiciones ? "WHERE " . implode(" AND ", $condiciones) : "";

$sql = "SELECT * FROM productos $where ORDER BY nombre";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Productos</title>
    <link rel="stylesheet" href="/proyecto_videojuegos/stilos/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .productos-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .card {
            width: 320px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);
            background-color: #fff;
        }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 200, 255, 0.35);
        }

        .card img {
            height: 200px;
            object-fit: cover;
        }

        .card-body h5 {
            font-weight: bold;
        }

        .btn-dark {
            border-radius: 8px;
        }

        .fondo-claro {
            background-color: rgb(255, 255, 255);
        }
    </style>
</head>

<body class="fondo-claro">

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Catálogo de Videojuegos</h2>

        <form method="GET" class="d-flex flex-wrap gap-3 justify-content-center mb-4">
            <input type="text" name="busqueda" class="form-control w-auto" placeholder="Buscar juego..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>" />
            <select name="categoria" class="form-select w-auto">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $cat):
                    $selected = (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'selected' : '';
                ?>
                    <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-dark">🔍 Buscar</button>
        </form>

        <div class="productos-container">
            <?php foreach ($productos as $producto): ?>
                <div class="card">
                    <img
                        src="<?= htmlspecialchars($producto['imagen'] ?? '') ?>"
                        class="card-img-top"
                        alt="<?= htmlspecialchars($producto['nombre']) ?>"
                        onerror="this.onerror=null;this.src='/proyecto_videojuegos/assets/default.jpg';" />
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
                        <p class="card-text">Precio: BS<?= number_format($producto['precio'], 2) ?></p>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-dark agregar-carrito" data-producto-id="<?= $producto['id'] ?>">añadir a carrito</button>

                            <form action="info.php" method="GET">
                                <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                                <button type="submit" class="btn btn-dark">información</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const botones = document.querySelectorAll(".agregar-carrito");

        botones.forEach(boton => {
            boton.addEventListener("click", () => {
                const productoId = boton.getAttribute("data-producto-id");

                fetch("/proyecto_videojuegos/carrito/agregar_ajax.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "producto_id=" + encodeURIComponent(productoId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        boton.textContent = "✅ Agregado";
                        boton.disabled = true;

                        // Actualizar contador del carrito
                        actualizarContadorCarrito();

                        setTimeout(() => {
                            boton.textContent = "añadir a carrito";
                            boton.disabled = false;
                        }, 2000);
                    } else {
                        alert("Error: " + data.mensaje);
                    }
                })
                .catch(error => {
                    alert("Hubo un error al añadir al carrito");
                    console.error(error);
                });
            });
        });

        function actualizarContadorCarrito() {
            fetch("/proyecto_videojuegos/carrito/obtener_total.php")
                .then(response => response.json())
                .then(data => {
                    const contador = document.getElementById("carrito-contador");
                    if (contador) {
                        contador.textContent = data.total;
                    }
                });
        }
    });
</script>

</body>

</html>