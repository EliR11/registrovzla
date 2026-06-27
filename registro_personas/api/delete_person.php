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

$stmt = $conn->prepare("DELETE FROM personas WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Persona eliminada correctamente'
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Persona no encontrada']);
}

$conn = null;
?>
