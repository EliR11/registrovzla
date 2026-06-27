<?php
require_once 'config.php';

$conn = getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$id = (int)$data['id'];

// Validar cédula
if (isset($data['cedula']) && !validateCedula($data['cedula'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Número de cédula inválido. Debe tener entre 7 y 10 dígitos']);
    exit;
}

// Validar edad
if (isset($data['edad']) && ($data['edad'] < 0 || $data['edad'] > 120)) {
    http_response_code(400);
    echo json_encode(['error' => 'La edad debe estar entre 0 y 120 años']);
    exit;
}

// Validar teléfono si está presente
if (!empty($data['telefono']) && !validatePhone($data['telefono'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de teléfono inválido. Ejemplo: +58 412 1234567']);
    exit;
}

// Verificar cédula duplicada (excluyendo el registro actual)
if (isset($data['cedula'])) {
    $checkStmt = $conn->prepare("SELECT id FROM personas WHERE cedula = ? AND id != ?");
    $checkStmt->bind_param("si", $data['cedula'], $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya existe otra persona con este número de cédula']);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();
}

// Construir consulta dinámica
$fields = [];
$params = [];
$types = "";

$allowedFields = [
    'localidad' => 's',
    'primerNombre' => 's',
    'segundoNombre' => 's',
    'primerApellido' => 's',
    'segundoApellido' => 's',
    'cedula' => 's',
    'edad' => 'i',
    'hospital' => 's',
    'zonaHospital' => 's',
    'telefono' => 's',
    'correo' => 's',
    'casaDestruida' => 's',
    'estado' => 's'
];

foreach ($allowedFields as $field => $type) {
    if (isset($data[$field])) {
        $fields[] = strtolower(preg_replace('/([A-Z])/', '_$1', $field)) . " = ?";
        $params[] = $data[$field];
        $types .= $type;
    }
}

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay datos para actualizar']);
    exit;
}

$params[] = $id;
$types .= "i";

$query = "UPDATE personas SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Persona actualizada correctamente'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>