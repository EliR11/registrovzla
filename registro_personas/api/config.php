<?php
// ============================================
// CONFIGURACIÓN SQLITE
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configuración SQLite
define('DB_PATH', __DIR__ . '/../database.sqlite');

// Conexión a la base de datos SQLite
function getConnection() {
    try {
        $conn = new PDO('sqlite:' . DB_PATH);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Habilitar claves foráneas (buena práctica)
        $conn->exec('PRAGMA foreign_keys = ON');
        
        return $conn;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
        exit;
    }
}

// Función para sanitizar datos
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Función para validar cédula
function validateCedula($cedula) {
    return preg_match('/^[0-9]{7,10}$/', $cedula);
}

// Función para validar teléfono venezolano
function validatePhone($telefono) {
    return preg_match('/^\+?58[0-9]{10,11}$/', $telefono);
}

// Inicializar la base de datos si no existe
function initDatabase() {
    $conn = getConnection();
    
    // Crear tabla de personas
    $conn->exec("
        CREATE TABLE IF NOT EXISTS personas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            localidad TEXT NOT NULL,
            primer_nombre TEXT NOT NULL,
            segundo_nombre TEXT,
            primer_apellido TEXT NOT NULL,
            segundo_apellido TEXT,
            cedula TEXT UNIQUE NOT NULL,
            edad INTEGER NOT NULL CHECK (edad >= 0 AND edad <= 120),
            hospital TEXT,
            zona_hospital TEXT,
            telefono TEXT,
            correo TEXT,
            casa_destruida TEXT DEFAULT 'no' CHECK (casa_destruida IN ('no', 'si', 'averiada')),
            estado TEXT DEFAULT 'desaparecido' CHECK (estado IN ('desaparecido', 'encontrado')),
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Crear índices para búsquedas rápidas
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_cedula ON personas(cedula)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_nombre ON personas(primer_nombre, primer_apellido)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_estado ON personas(estado)");
    
    // Verificar si hay datos de ejemplo
    $stmt = $conn->query("SELECT COUNT(*) as total FROM personas");
    $result = $stmt->fetch();
    
    if ($result['total'] == 0) {
        // Insertar datos de ejemplo
        $conn->exec("
            INSERT INTO personas (localidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, cedula, edad, hospital, zona_hospital, telefono, correo, casa_destruida, estado)
            VALUES 
            ('Caracas', 'María', 'José', 'González', 'López', '12345678', 45, 'Hospital Universitario de Caracas', 'Pabellón A, Sala 302', '+58 412 1234567', 'maria.gonzalez@email.com', 'no', 'desaparecido'),
            ('Vargas', 'Carlos', 'Andrés', 'Mendoza', 'Rojas', '87654321', 8, 'Hospital Central de La Guaira', 'Pediatría, Cama 12', '+58 414 8765432', 'carlos.mendoza@email.com', 'si', 'desaparecido'),
            ('Miranda', 'Ana', 'María', 'Torres', 'Reyes', '98765432', 72, 'Hospital Regional de Los Teques', 'UCI, Box 5', '+58 416 5678123', 'ana.torres@email.com', 'averiada', 'encontrado')
        ");
    }
}
?>
