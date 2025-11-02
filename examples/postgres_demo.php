<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

// Conexión a PostgreSQL
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=test', 'postgres', 'postgres');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<div class="success-message">✅ Conectado a PostgreSQL</div>';
} catch (PDOException $e) {
    die('<div class="error-message">❌ Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Crear instancia de DynamicCRUD
$crud = new DynamicCRUD($pdo, 'users');

// Hook: Auto-generate slug
$crud->beforeSave(function($data) {
    if (isset($data['name'])) {
        error_log("Guardando usuario: {$data['name']}");
    }
    return $data;
});

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        echo '<div class="success-message">✅ Usuario guardado exitosamente! ID: ' . $result['id'] . '</div>';
        echo '<p><a href="?">Crear otro usuario</a> | <a href="?id=' . $result['id'] . '">Editar este usuario</a></p>';
    } else {
        echo '<div class="error-message">❌ Error: ';
        if (isset($result['errors'])) {
            echo '<ul>';
            foreach ($result['errors'] as $field => $error) {
                echo '<li><strong>' . htmlspecialchars($field) . ':</strong> ' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
        } else {
            echo htmlspecialchars($result['error']);
        }
        echo '</div>';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PostgreSQL Demo - DynamicCRUD</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .error-message ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            margin-top: 0;
            color: #1976D2;
        }
        
        .info-box code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .db-badge {
            display: inline-block;
            background: #336791;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="db-badge">🐘 PostgreSQL</span>
        <h1>PostgreSQL Demo</h1>
        <p class="subtitle">DynamicCRUD funcionando con PostgreSQL usando el Adapter Pattern</p>
        
        <div class="info-box">
            <h3>🎯 Características Demostradas</h3>
            <ul>
                <li><strong>Adapter Pattern</strong> - Abstracción de base de datos</li>
                <li><strong>Auto-detección</strong> - Detecta automáticamente MySQL o PostgreSQL</li>
                <li><strong>Schema Analysis</strong> - Lee estructura desde INFORMATION_SCHEMA</li>
                <li><strong>Foreign Keys</strong> - Detecta relaciones automáticamente</li>
                <li><strong>Metadata</strong> - Soporta JSON en COMMENT ON COLUMN</li>
            </ul>
            <p><strong>Conexión:</strong> <code>pgsql:host=localhost;dbname=test</code></p>
        </div>
        
        <?php echo $crud->renderForm($_GET['id'] ?? null); ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <h3>💡 Diferencias PostgreSQL vs MySQL</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f5f5f5;">
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Característica</th>
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">MySQL</th>
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">PostgreSQL</th>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Identificadores</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>`backticks`</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>"double quotes"</code></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Auto-increment</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>AUTO_INCREMENT</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>SERIAL</code></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Tipos de datos</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>VARCHAR, INT</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>VARCHAR, INTEGER</code></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">ENUM</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">Nativo</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">CHECK constraint</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Comentarios</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>COMMENT 'text'</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code>COMMENT ON COLUMN</code></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
