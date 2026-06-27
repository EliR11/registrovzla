<?php
require_once 'config.php';

// Inicializar la base de datos si es necesario
initDatabase();

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
    $checkStmt->execute([$data['cedula'], $id]);
    $checkResult = $checkStmt->fetch();
    
    if ($checkResult) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya existe otra persona con este número de cédula']);
        $conn = null;
        exit;
    }
}

// Construir consulta dinámica
$fields = [];
$params = [];

$allowedFields = [
    'localidad',
    'primerNombre' => 'primer_nombre',
    'segundoNombre' => 'segundo_nombre',
    'primerApellido' => 'primer_apellido',
    'segundoApellido' => 'segundo_apellido',
    'cedula',
    'edad',
    'hospital',
    'zonaHospital' => 'zona_hospital',
    'telefono',
    'correo',
    'casaDestruida' => 'casa_destruida',
    'estado'
];

foreach ($allowedFields as $key => $field) {
    if (is_numeric($key)) {
        $fieldName = $field;
        $fieldKey = $field;
    } else {
        $fieldName = $field;
        $fieldKey = $key;
    }
    
    if (isset($data[$fieldKey])) {
        $fields[] = "$fieldName = ?";
        $params[] = $data[$fieldKey];
    }
}

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay datos para actualizar']);
    exit;
}

$params[] = $id;

$query = "UPDATE personas SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute($params);

echo json_encode([
    'success' => true,
    'message' => 'Persona actualizada correctamente'
]);

$conn = null;
?>
