<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\VirtualField;

// Conexión a la base de datos
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Crear instancia de DynamicCRUD para la tabla users
$crud = new DynamicCRUD($pdo, 'users');

// Agregar campo virtual: password_confirmation
$passwordConfirmation = new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    label: 'Confirmar Contraseña',
    required: true,
    validator: function($value, $allData) {
        // Validar que coincida con el campo password
        return isset($allData['password']) && $value === $allData['password'];
    },
    attributes: [
        'placeholder' => 'Repite tu contraseña',
        'minlength' => 8,
        'tooltip' => 'Debe coincidir con la contraseña ingresada arriba',
        'error_message' => 'Las contraseñas no coinciden'
    ]
);

$crud->addVirtualField($passwordConfirmation);

// Agregar campo virtual: terms_acceptance
$termsAcceptance = new VirtualField(
    name: 'terms_accepted',
    type: 'checkbox',
    label: 'Acepto los términos y condiciones',
    required: true,
    validator: function($value, $allData) {
        return $value === '1';
    },
    attributes: [
        'error_message' => 'Debes aceptar los términos y condiciones'
    ]
);

$crud->addVirtualField($termsAcceptance);

// Hook: Hashear password antes de guardar
$crud->beforeSave(function($data) {
    if (isset($data['password']) && !empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return $data;
});

// Hook: Log después de crear usuario
$crud->afterCreate(function($id, $data) {
    error_log("Nuevo usuario registrado: ID $id, Email: {$data['email']}");
});

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        echo '<div class="success-message">✅ Usuario registrado exitosamente! ID: ' . $result['id'] . '</div>';
        echo '<p><a href="?">Registrar otro usuario</a></p>';
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
    <title>Virtual Fields Demo - DynamicCRUD</title>
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
        
        .info-box ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .info-box code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Virtual Fields Demo</h1>
        <p class="subtitle">Demostración de campos virtuales: password_confirmation y terms_accepted</p>
        
        <div class="info-box">
            <h3>📋 Campos Virtuales Implementados</h3>
            <ul>
                <li><code>password_confirmation</code> - Valida que coincida con el campo password</li>
                <li><code>terms_accepted</code> - Checkbox requerido para aceptar términos</li>
            </ul>
            <p><strong>Características:</strong></p>
            <ul>
                <li>✅ No se guardan en la base de datos</li>
                <li>✅ Validación personalizada con callbacks</li>
                <li>✅ Mensajes de error personalizados</li>
                <li>✅ Soporte para tooltips y placeholders</li>
                <li>✅ Integración con sistema de hooks</li>
            </ul>
        </div>
        
        <?php echo $crud->renderForm(); ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <h3>💡 Cómo funciona</h3>
            <p>Los campos virtuales son campos que aparecen en el formulario pero no se guardan en la base de datos. Son útiles para:</p>
            <ul>
                <li><strong>Confirmación de contraseña</strong> - Validar que el usuario escribió correctamente su contraseña</li>
                <li><strong>Aceptación de términos</strong> - Requerir que el usuario acepte condiciones</li>
                <li><strong>Captcha</strong> - Validación anti-spam</li>
                <li><strong>Campos calculados</strong> - Campos que se procesan pero no se almacenan</li>
            </ul>
        </div>
    </div>
</body>
</html>
