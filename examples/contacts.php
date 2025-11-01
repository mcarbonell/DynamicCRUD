<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'contacts', $cache);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        header('Location: contacts.php?success=' . urlencode("Contacto {$action} con ID: {$result['id']}"));
        exit;
    } else {
        $error = $result['error'] ?? 'Validación fallida';
        $errors = $result['errors'] ?? [];
    }
}

$stmt = $pdo->query('SELECT * FROM contacts ORDER BY id DESC');
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Contactos (Fase 3 UX)</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        h2 { margin-top: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .badge { background: #17a2b8; color: white; padding: 4px 12px; border-radius: 3px; font-size: 12px; display: inline-block; margin-bottom: 10px; }
        .nav { margin-bottom: 20px; padding: 10px 0; border-bottom: 2px solid #eee; }
        .nav a { margin-right: 15px; }
        .avatar-thumb { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <h1>🎨 DynamicCRUD - Contactos (Fase 3 UX)</h1>
    <p class="badge">Con Mejoras de Experiencia de Usuario</p>
    
    <div class="nav">
        <a href="index.php">Usuarios</a> |
        <a href="posts.php">Posts</a> |
        <a href="categories.php">Categorías</a> |
        <a href="products.php">Productos</a> |
        <strong>Contactos</strong>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            <span class="alert-icon" aria-hidden="true">✓</span>
            <span><?= htmlspecialchars($_GET['success']) ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error" role="alert">
            <span class="alert-icon" aria-hidden="true">✗</span>
            <div>
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <?php if (!empty($errors)): ?>
                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                        <?php foreach ($errors as $field => $fieldErrors): ?>
                            <?php foreach ($fieldErrors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Contacto' : 'Nuevo Contacto' ?></h2>
            <p style="color: #666; font-size: 14px;">
                ℹ️ Pasa el cursor sobre los iconos <strong>?</strong> para ver ayuda contextual
            </p>
            <?= $crud->renderForm($id) ?>
        </div>
        <div>
            <h2>Lista de Contactos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Edad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td>
                            <?php if ($contact['avatar']): ?>
                                <img src="<?= htmlspecialchars($contact['avatar']) ?>" class="avatar-thumb" alt="Avatar">
                            <?php else: ?>
                                <span style="color: #999;">Sin foto</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($contact['name']) ?></td>
                        <td><?= htmlspecialchars($contact['email']) ?></td>
                        <td><?= htmlspecialchars($contact['age'] ?? 'N/A') ?></td>
                        <td><a href="?id=<?= $contact['id'] ?>">Editar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="contacts.php">← Crear nuevo contacto</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
        <h3>✨ Características demostradas:</h3>
        <ul>
            <li>🎯 <strong>Tooltips informativos</strong> - Pasa el cursor sobre los iconos "?" para ver ayuda</li>
            <li>⚡ <strong>Validación en tiempo real</strong> - Los errores aparecen mientras escribes</li>
            <li>♿ <strong>Accesibilidad mejorada</strong> - Atributos ARIA, navegación por teclado</li>
            <li>🔄 <strong>Spinner de carga</strong> - Indicador visual al enviar el formulario</li>
            <li>📱 <strong>Diseño responsive</strong> - Se adapta a diferentes tamaños de pantalla</li>
            <li>🎨 <strong>Mensajes mejorados</strong> - Alertas con animaciones y iconos</li>
        </ul>
    </div>
</body>
</html>
