<?php
require_once 'config.php';

// Inicializar la base de datos si es necesario
initDatabase();

$conn = getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$id = (int)$data['id'];
$estado = isset($data['estado']) ? $data['estado'] : 'encontrado';

// Validar estado
if (!in_array($estado, ['desaparecido', 'encontrado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado inválido']);
    exit;
}

$stmt = $conn->prepare("UPDATE personas SET estado = ? WHERE id = ?");
$stmt->execute([$estado, $id]);

if ($stmt->rowCount() > 0) {
    // Obtener información de la persona para la notificación
    $infoStmt = $conn->prepare("SELECT primer_nombre, primer_apellido FROM personas WHERE id = ?");
    $infoStmt->execute([$id]);
    $person = $infoStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado correctamente',
        'persona' => $person
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Persona no encontrada']);
}

$conn = null;
?>
