<?php

require_once "db.php";

// Establecer tipo de contenido como JSON
header('Content-Type: application/json');

// Obtener el método HTTP y segmentar la URI
$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$recurso = $ruta[0] ?? null;
$id = $ruta[1] ?? null;

// Validar recursos disponibles
$recursosPermitidos = ['productos', 'categorias', 'promociones', 'productos-promocion'];
if (!in_array($recurso, $recursosPermitidos)) {
    http_response_code(404);
    echo json_encode([
        'error' => 'Recurso no encontrado',
        'codigo' => 404,
        'ayuda' => 'https://http.cat/404'
    ]);
    exit;
}

// Función para validar existencia de ID
function validarID($id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'error' => 'ID no proporcionado',
            'codigo' => 400,
            'ayuda' => 'https://http.cat/400'
        ]);
        exit;
    }
}

// Manejo de cada recurso
switch ($recurso) {
    // ---------------------------- CATEGORIAS ----------------------------
    case 'categorias':
        switch ($metodo) {
            case 'GET':
                if ($id) {
                    $query = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
                    $query->execute([$id]);
                    $resultado = $query->fetch(PDO::FETCH_ASSOC);

                    if ($resultado) {
                        echo json_encode($resultado);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Categoría no encontrada']);
                    }
                } else {
                    $query = $pdo->prepare("SELECT * FROM categorias");
                    $query->execute();
                    echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
                }
                break;

            case 'POST':
                $datos = json_decode(file_get_contents('php://input'), true);
                $query = $pdo->prepare("INSERT INTO categorias(nombre) VALUES(?)");
                $query->execute([$datos['nombre']]);
                http_response_code(201);
                $datos['id'] = $pdo->lastInsertId();
                echo json_encode($datos);
                break;

            case 'PUT':
                validarID($id);
                $datos = json_decode(file_get_contents('php://input'), true);
                $query = $pdo->prepare("UPDATE categorias SET id = ?, nombre = ? WHERE id = ?");
                $query->execute([$datos['id'], $datos['nombre'], $id]);
                echo json_encode($datos);
                break;

            case 'DELETE':
                validarID($id);
                $consulta = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
                $consulta->execute([$id]);
                $categoria = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$categoria) {
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'Categoría no encontrada',
                        'codigo' => 404,
                        'ayuda' => 'https://http.cat/404'
                    ]);
                    exit;
                }

                $query = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
                $query->execute([$id]);
                echo json_encode($categoria);
                break;
        }
        break;

    // ---------------------------- PRODUCTOS ----------------------------
    case 'productos':
        switch ($metodo) {
            case 'GET':
                if ($id) {
                    $query = $pdo->prepare("
                        SELECT 
                            productos.*,
                            IF(promociones.id IS NULL, 'Sin promoción', 'Con promoción') AS promocion
                        FROM productos
                        LEFT JOIN promociones ON productos.id = promociones.producto_id
                        WHERE productos.id = ?
                    ");
                    $query->execute([$id]);
                    $producto = $query->fetch(PDO::FETCH_ASSOC);

                    if ($producto) {
                        echo json_encode($producto);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Producto no encontrado']);
                    }
                } else {
                    $query = $pdo->prepare("SELECT * FROM productos");
                    $query->execute();
                    echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
                }
                break;

            case 'POST':
                $datos = json_decode(file_get_contents('php://input'), true);
                $query = $pdo->prepare("INSERT INTO productos(nombre, precio, categoria_id) VALUES(?, ?, ?)");
                $query->execute([$datos['nombre'], $datos['precio'], $datos['categoria_id']]);
                http_response_code(201);
                $datos['id'] = $pdo->lastInsertId();
                echo json_encode($datos);
                break;

            case 'PUT':
                validarID($id);
                $datos = json_decode(file_get_contents('php://input'), true);

                if (!isset($datos['nombre'], $datos['precio'], $datos['categoria_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan datos obligatorios']);
                    break;
                }

                $query = $pdo->prepare("UPDATE productos SET nombre = ?, precio = ?, categoria_id = ? WHERE id = ?");
                $query->execute([
                    $datos['nombre'],
                    $datos['precio'],
                    $datos['categoria_id'],
                    $id
                ]);

                echo json_encode([
                    'mensaje' => 'Producto actualizado correctamente',
                    'producto' => array_merge(['id' => $id], $datos)
                ]);
                break;

            case 'DELETE':
                validarID($id);
                $consulta = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
                $consulta->execute([$id]);
                $producto = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'Producto no encontrado',
                        'codigo' => 404,
                        'ayuda' => 'https://http.cat/404'
                    ]);
                    exit;
                }

                $query = $pdo->prepare("DELETE FROM productos WHERE id = ?");
                $query->execute([$id]);

                echo json_encode([
                    'mensaje' => 'Producto eliminado correctamente',
                    'producto_eliminado' => $producto
                ]);
                break;

    // ---------------------------- PROMOCIONES ----------------------------
    case 'promociones':
        switch ($metodo) {
            case 'GET':
                if ($id) {
                    $query = $pdo->prepare("SELECT * FROM promociones WHERE id = ?");
                    $query->execute([$id]);
                    $promocion = $query->fetch(PDO::FETCH_ASSOC);

                    if ($promocion) {
                        echo json_encode($promocion);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Promoción no encontrada']);
                    }
                } else {
                    $query = $pdo->prepare("SELECT * FROM promociones");
                    $query->execute();
                    echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
                }
                break;

            case 'POST':
                $datos = json_decode(file_get_contents('php://input'), true);

                // Validar campos
                if (!$datos || !isset($datos['detalle_promocion'], $datos['porcentaje_descuento'], $datos['producto_id'])) {
                    http_response_code(400);
                    echo json_encode([
                        'error' => 'Datos incompletos o mal formateados',
                        'codigo' => 400,
                        'ayuda' => 'Asegúrate de enviar JSON válido con: detalle_promocion, porcentaje_descuento, producto_id'
                    ]);
                    exit;
                }

                // Insertar en la base de datos
                $query = $pdo->prepare("INSERT INTO promociones(detalle_promocion, porcentaje_descuento, producto_id) VALUES(?, ?, ?)");
                $query->execute([
                    $datos['detalle_promocion'],
                    $datos['porcentaje_descuento'],
                    $datos['producto_id']
                ]);

                http_response_code(201);
                $datos['id'] = $pdo->lastInsertId();
                echo json_encode($datos);
                break;


            case 'PUT':
                validarID($id);

                $datos = json_decode(file_get_contents('php://input'), true);

                if (!isset($datos['detalle_promocion'], $datos['porcentaje_descuento'], $datos['producto_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan datos obligatorios.']);
                    break;
                }

                $query = $pdo->prepare("
                    UPDATE promociones 
                    SET detalle_promocion = ?, porcentaje_descuento = ?, producto_id = ? 
                    WHERE id = ?
                ");

                $exito = $query->execute([
                    $datos['detalle_promocion'],
                    $datos['porcentaje_descuento'],
                    $datos['producto_id'],
                    $id
                ]);

                if ($exito) {
                    echo json_encode(['mensaje' => 'Promoción actualizada correctamente']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al actualizar la promoción']);
                }

                break;


            case 'DELETE':
                validarID($id);
                $consulta = $pdo->prepare("SELECT * FROM promociones WHERE id = ?");
                $consulta->execute([$id]);
                $promocion = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$promocion) {
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'Promoción no encontrada',
                        'codigo' => 404,
                        'ayuda' => 'https://http.cat/404'
                    ]);
                    exit;
                }

                $query = $pdo->prepare("DELETE FROM promociones WHERE id = ?");
                $query->execute([$id]);
                echo json_encode($promocion);
                break;
        }
        break;
    }
}