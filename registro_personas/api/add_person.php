<?php
require_once 'config.php';

// Inicializar la base de datos si es necesario
initDatabase();

$conn = getConnection();

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

// Validar campos requeridos
$required = ['localidad', 'primerNombre', 'primerApellido', 'cedula', 'edad'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "El campo $field es requerido"]);
        exit;
    }
}

// Validar cédula
if (!validateCedula($data['cedula'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Número de cédula inválido. Debe tener entre 7 y 10 dígitos']);
    exit;
}

// Validar edad
if ($data['edad'] < 0 || $data['edad'] > 120) {
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

// Verificar cédula duplicada
$checkStmt = $conn->prepare("SELECT id FROM personas WHERE cedula = ?");
$checkStmt->execute([$data['cedula']]);
$checkResult = $checkStmt->fetch();

if ($checkResult) {
    http_response_code(409);
    echo json_encode(['error' => 'Ya existe una persona con este número de cédula']);
    $conn = null;
    exit;
}

// Preparar y ejecutar inserción
$query = "INSERT INTO personas (
    localidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
    cedula, edad, hospital, zona_hospital, telefono, correo, casa_destruida, estado
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);

$localidad = sanitize($data['localidad']);
$primerNombre = sanitize($data['primerNombre']);
$segundoNombre = isset($data['segundoNombre']) ? sanitize($data['segundoNombre']) : null;
$primerApellido = sanitize($data['primerApellido']);
$segundoApellido = isset($data['segundoApellido']) ? sanitize($data['segundoApellido']) : null;
$cedula = sanitize($data['cedula']);
$edad = (int)$data['edad'];
$hospital = isset($data['hospital']) ? sanitize($data['hospital']) : null;
$zonaHospital = isset($data['zonaHospital']) ? sanitize($data['zonaHospital']) : null;
$telefono = isset($data['telefono']) ? sanitize($data['telefono']) : null;
$correo = isset($data['correo']) ? sanitize($data['correo']) : null;
$casaDestruida = isset($data['casaDestruida']) ? $data['casaDestruida'] : 'no';
$estado = 'desaparecido';

$stmt->execute([
    $localidad,
    $primerNombre,
    $segundoNombre,
    $primerApellido,
    $segundoApellido,
    $cedula,
    $edad,
    $hospital,
    $zonaHospital,
    $telefono,
    $correo,
    $casaDestruida,
    $estado
]);

$id = $conn->lastInsertId();
echo json_encode([
    'success' => true,
    'id' => $id,
    'message' => 'Persona registrada correctamente'
]);

$conn = null;
?>
