<?php
require_once 'config.php';

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
$stmt->bind_param("si", $estado, $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Obtener información de la persona para la notificación
        $infoStmt = $conn->prepare("SELECT primer_nombre, primer_apellido FROM personas WHERE id = ?");
        $infoStmt->bind_param("i", $id);
        $infoStmt->execute();
        $infoResult = $infoStmt->get_result();
        $person = $infoResult->fetch_assoc();
        $infoStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'persona' => $person
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Persona no encontrada']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>