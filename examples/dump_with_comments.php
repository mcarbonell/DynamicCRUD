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

// 1. VOLCADO DE ESQUEMAS
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

// 2. VOLCADO DE DATOS
$sqlOutput .= "\n-- --------------------------------------------------------\n";
$sqlOutput .= "-- Volcado de datos para las tablas\n";
$sqlOutput .= "-- --------------------------------------------------------\n\n";

foreach ($tables as $tableName) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$tableName`");
        if ($stmt->fetchColumn() == 0) {
            continue; // Saltar tablas vacías
        }

        $sqlOutput .= "-- Volcando datos para la tabla `$tableName`\n";
        
        $dataStmt = $pdo->query("SELECT * FROM `$tableName`");
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            continue;
        }

        // Obtener nombres de columnas y escaparlos
        $columnNames = array_keys($rows[0]);
        $escapedColumnNames = '`' . implode('`, `', $columnNames) . '`';
        $insertPrefix = "INSERT INTO `$tableName` ($escapedColumnNames) VALUES \n";

        // Agrupar los valores en lotes para no crear INSERTs gigantes
        $chunkSize = 100; // 100 filas por sentencia INSERT
        $valueChunks = array_chunk($rows, $chunkSize, true);

        foreach ($valueChunks as $chunk) {
            $valueStrings = [];
            foreach ($chunk as $row) {
                $rowValues = [];
                foreach ($columnNames as $colName) {
                    if ($row[$colName] === null) {
                        $rowValues[] = "NULL";
                    } else {
                        // Usar PDO::quote para escapar correctamente los valores y añadir comillas
                        $rowValues[] = $pdo->quote($row[$colName]);
                    }
                }
                $valueStrings[] = "(" . implode(',', $rowValues) . ")";
            }
            $sqlOutput .= $insertPrefix . implode(",\n", $valueStrings) . ";\n\n";
        }
    } catch (\PDOException $e) {
        $sqlOutput .= "-- AVISO: No se pudieron volcar los datos de la tabla `$tableName`. Error: {$e->getMessage()}\n\n";
    }
}


$sqlOutput .= "SET FOREIGN_KEY_CHECKS = 1;\n";

// Enviar la salida
if (php_sapi_name() == 'cli') {    // Si se ejecuta desde la línea de comandos, simplemente imprimir
    echo $sqlOutput;
} else {
    // Si se ejecuta desde un navegador, forzar la descarga del archivo
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $dbName . '_schema_with_comments_' . date('Y-m-d') . '.sql"');
    echo $sqlOutput;
}

exit;
