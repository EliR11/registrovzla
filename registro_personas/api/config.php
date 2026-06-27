<?php
// Configuración para SQLite
define('DB_PATH', __DIR__ . '/../database.sqlite');

function getConnection() {
    try {
        $conn = new PDO('sqlite:' . DB_PATH);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
        exit;
    }
}

// Inicializar la base de datos si no existe
function initDatabase() {
    $conn = getConnection();
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
}
?>
