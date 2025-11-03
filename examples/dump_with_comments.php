<?php

/**
 * MySQL Schema Dumper with Comments
 * ==================================
 * 
 * Este script genera un volcado SQL (dump) del esquema de una base de datos MySQL/MariaDB,
 * asegurando que los comentarios de las tablas y columnas sean exportados correctamente.
 * 
 * Herramientas como phpMyAdmin a menudo omiten los comentarios de las tablas en sus exportaciones
 * estándar. Este script resuelve ese problema utilizando el comando `SHOW CREATE TABLE`, que
 * proporciona la definición completa y canónica de la tabla, incluyendo todos los metadatos.
 * 
 * CÓMO USAR:
 * 1. Edita las variables de configuración de la base de datos a continuación.
 * 2. Sube este script a tu servidor web o ejecútalo a través de la línea de comandos (CLI).
 *    - Vía web: Accede a la URL del script en tu navegador. El archivo SQL se descargará.
 *    - Vía CLI:  php dump_with_comments.php > tu_backup_de_esquema.sql
 */

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
$dbHost = '127.0.0.1';
$dbName = 'test'; // <-- ¡Cambia esto por el nombre de tu BD!
$dbUser = 'root';            // <-- ¡Cambia esto por tu usuario!
$dbPass = 'rootpassword';                // <-- ¡Cambia esto por tu contraseña!
$dbCharset = 'utf8mb4';

// --- LÓGICA DEL SCRIPT ---

try {
    // Conexión a la base de datos usando PDO
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    header('Content-Type: text/plain; charset=utf-8', true, 500);
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Preparar el contenido del archivo SQL
$sqlOutput = "";
$sqlOutput .= "-- Volcado de esquema para la base de datos: `$dbName`\n";
$sqlOutput .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n";
$sqlOutput .= "-- --------------------------------------------------------\n\n";

$sqlOutput .= "SET NAMES $dbCharset;\n";
$sqlOutput .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// Obtener todas las tablas de la base de datos
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Iterar sobre cada tabla para obtener su estructura
foreach ($tables as $tableName) {
    $sqlOutput .= "-- --------------------------------------------------------\n";
    $sqlOutput .= "-- Estructura para la tabla `$tableName`\n";
    $sqlOutput .= "-- --------------------------------------------------------\n\n";

    // El comando `SHOW CREATE TABLE` devuelve la estructura completa, incluyendo comentarios.
    $stmt = $pdo->query("SHOW CREATE TABLE `$tableName`");
    $createTableSql = $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'];

    $sqlOutput .= "DROP TABLE IF EXISTS `$tableName`;\n";
    $sqlOutput .= $createTableSql . ";\n\n";
}

$sqlOutput .= "SET FOREIGN_KEY_CHECKS = 1;\n";

// Enviar la salida
if (php_sapi_name() == 'cli') {
    // Si se ejecuta desde la línea de comandos, simplemente imprimir
    echo $sqlOutput;
} else {
    // Si se ejecuta desde un navegador, forzar la descarga del archivo
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $dbName . '_schema_with_comments_' . date('Y-m-d') . '.sql"');
    echo $sqlOutput;
}

exit;
