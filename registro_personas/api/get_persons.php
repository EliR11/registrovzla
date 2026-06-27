<?php
require_once 'config.php';

$conn = getConnection();

// Obtener el término de búsqueda si existe
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT * FROM personas";
$params = [];

if (!empty($search)) {
    $query .= " WHERE 
        localidad LIKE ? OR 
        primer_nombre LIKE ? OR 
        segundo_nombre LIKE ? OR 
        primer_apellido LIKE ? OR 
        segundo_apellido LIKE ? OR 
        cedula LIKE ? OR 
        hospital LIKE ? OR 
        zona_hospital LIKE ? OR 
        telefono LIKE ? OR 
        correo LIKE ?
    ";
    $searchTerm = "%$search%";
    $params = array_fill(0, 10, $searchTerm);
}

$query .= " ORDER BY fecha_registro DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$persons = [];
while ($row = $result->fetch_assoc()) {
    $persons[] = [
        'id' => (int)$row['id'],
        'localidad' => $row['localidad'],
        'primerNombre' => $row['primer_nombre'],
        'segundoNombre' => $row['segundo_nombre'],
        'primerApellido' => $row['primer_apellido'],
        'segundoApellido' => $row['segundo_apellido'],
        'cedula' => $row['cedula'],
        'edad' => (int)$row['edad'],
        'hospital' => $row['hospital'],
        'zonaHospital' => $row['zona_hospital'],
        'telefono' => $row['telefono'],
        'correo' => $row['correo'],
        'casaDestruida' => $row['casa_destruida'],
        'estado' => $row['estado'],
        'fechaRegistro' => $row['fecha_registro']
    ];
}

echo json_encode($persons);

$stmt->close();
$conn->close();
?>