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

$stmt = $conn->prepare("DELETE FROM personas WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Persona eliminada correctamente'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Persona no encontrada']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>