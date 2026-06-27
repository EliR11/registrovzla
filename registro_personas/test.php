<?php
// ============================================
// PRUEBA DE CONEXIÓN A LA BASE DE DATOS
// ============================================

echo "<h1>🔍 Prueba de Conexión</h1>";

// Configuración de la base de datos
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'desaparecidos_db';

// Intentar conectar
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    echo "<h2 style='color: red;'>❌ Error de conexión</h2>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>MySQL esté corriendo en XAMPP</li>";
    echo "<li>La base de datos 'desaparecidos_db' exista</li>";
    echo "<li>El usuario y contraseña sean correctos</li>";
    echo "</ul>";
    die();
}

echo "<h2 style='color: green;'>✅ Conexión exitosa</h2>";
echo "<p>Base de datos: <strong>" . $database . "</strong></p>";

// Contar registros
$result = $conn->query("SELECT COUNT(*) as total FROM personas");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>📊 Total de registros en la tabla 'personas': <strong>" . $row['total'] . "</strong></p>";
} else {
    echo "<p style='color: orange;'>⚠️ La tabla 'personas' no existe o está vacía</p>";
}

// Mostrar los primeros 5 registros
echo "<h3>📋 Últimos registros:</h3>";
$result = $conn->query("SELECT * FROM personas ORDER BY fecha_registro DESC LIMIT 5");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background: #1a237e; color: white;'>";
    echo "<th>ID</th>";
    echo "<th>Nombre</th>";
    echo "<th>Apellido</th>";
    echo "<th>Cédula</th>";
    echo "<th>Localidad</th>";
    echo "<th>Estado</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        $color = $row['estado'] == 'encontrado' ? '#e8f5e9' : '#ffebee';
        echo "<tr style='background: $color;'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['primer_nombre'] . "</td>";
        echo "<td>" . $row['primer_apellido'] . "</td>";
        echo "<td>" . $row['cedula'] . "</td>";
        echo "<td>" . $row['localidad'] . "</td>";
        echo "<td>" . ($row['estado'] == 'encontrado' ? '✅ Encontrado' : '❌ Desaparecido') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay registros en la tabla.</p>";
}

$conn->close();

echo "<br>";
echo "<hr>";
echo "<p>🌐 <a href='index.html'>Volver a la página principal</a></p>";
?>