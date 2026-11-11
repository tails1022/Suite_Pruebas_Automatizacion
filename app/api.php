<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT * FROM tareas ORDER BY id DESC");
            $tareas = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $tareas]);
            break;

        case 'get':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ?");
            $stmt->execute([$id]);
            $tarea = $stmt->fetch();
            if ($tarea) {
                echo json_encode(['success' => true, 'data' => $tarea]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Tarea no encontrada']);
            }
            break;

        case 'save':
            $id = intval($_POST['id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $prioridad = trim($_POST['prioridad'] ?? 'Media');
            $estado = trim($_POST['estado'] ?? 'Pendiente');
            $fecha_limite = trim($_POST['fecha_limite'] ?? '');

            // Validaciones funcionales de negocio
            if (empty($titulo)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El título de la tarea es obligatorio.']);
                exit();
            }

            if (empty($fecha_limite)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La fecha límite es obligatoria.']);
                exit();
            }

            if (!in_array($prioridad, ['Alta', 'Media', 'Baja'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Prioridad inválida.']);
                exit();
            }

            if (!in_array($estado, ['Pendiente', 'En Progreso', 'Completada'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Estado inválido.']);
                exit();
            }

            if ($id > 0) {
                // Actualización
                $stmt = $pdo->prepare("UPDATE tareas SET titulo = ?, descripcion = ?, prioridad = ?, estado = ?, fecha_limite = ? WHERE id = ?");
                $stmt->execute([$titulo, $descripcion, $prioridad, $estado, $fecha_limite, $id]);
                echo json_encode(['success' => true, 'message' => 'Tarea actualizada exitosamente']);
            } else {
                // Inserción
                $stmt = $pdo->prepare("INSERT INTO tareas (titulo, descripcion, prioridad, estado, fecha_limite, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titulo, $descripcion, $prioridad, $estado, $fecha_limite, $_SESSION['user_id'] ?? 1]);
                echo json_encode(['success' => true, 'message' => 'Tarea creada exitosamente']);
            }
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID inválido']);
                exit();
            }

            $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Tarea eliminada exitosamente']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
