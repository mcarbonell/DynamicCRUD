<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'posts', $cache);

// Hook: Generar slug automáticamente antes de guardar
$crud->beforeSave(function($data) {
    if (isset($data['title']) && empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
    }
    return $data;
});

// Hook: Registrar en log después de crear
$crud->afterCreate(function($id, $data) {
    error_log("✓ Post creado con ID: $id - Título: {$data['title']}");
    return $data;
});

// Hook: Registrar en log después de actualizar
$crud->afterUpdate(function($id, $data) {
    error_log("✓ Post actualizado ID: $id - Título: {$data['title']}");
    return $data;
});

// Hook: Validación personalizada
$crud->afterValidate(function($data) {
    // Validación cruzada: published_at solo si status es 'published'
    if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    return $data;
});

// Hook: Auditoría antes de eliminar
$crud->beforeDelete(function($id) use ($pdo) {
    $stmt = $pdo->prepare("SELECT title FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        error_log("⚠️ Eliminando post ID: $id - Título: {$post['title']}");
    }
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        header('Location: hooks_demo.php?success=' . urlencode("Post {$action} con ID: {$result['id']}"));
        exit;
    } else {
        $error = $result['error'] ?? 'Validación fallida';
        $errors = $result['errors'] ?? [];
    }
}

// Manejar eliminación
if (isset($_GET['delete'])) {
    try {
        $crud->delete((int)$_GET['delete']);
        header('Location: hooks_demo.php?success=' . urlencode('Post eliminado correctamente'));
        exit;
    } catch (Exception $e) {
        $error = 'Error al eliminar: ' . $e->getMessage();
    }
}

$stmt = $pdo->query('SELECT p.id, p.title, p.slug, p.status, p.published_at, c.name as category 
                     FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id
                     ORDER BY p.id DESC');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Demo de Hooks (Fase 4)</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        h2 { margin-top: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; margin-right: 10px; }
        a:hover { text-decoration: underline; }
        .badge { background: #6f42c1; color: white; padding: 4px 12px; border-radius: 3px; font-size: 12px; display: inline-block; margin-bottom: 10px; }
        .nav { margin-bottom: 20px; padding: 10px 0; border-bottom: 2px solid #eee; }
        .nav a { margin-right: 15px; }
        .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin-bottom: 20px; }
        .info-box h3 { margin-top: 0; color: #1976D2; }
        .info-box ul { margin: 10px 0; padding-left: 20px; }
        .status-published { color: #28a745; font-weight: bold; }
        .status-draft { color: #6c757d; }
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <h1>🎣 DynamicCRUD - Demo de Hooks/Eventos (Fase 4)</h1>
    <p class="badge">Sistema de Hooks Implementado</p>
    
    <div class="nav">
        <a href="index.php">Usuarios</a> |
        <a href="posts.php">Posts</a> |
        <a href="categories.php">Categorías</a> |
        <a href="products.php">Productos</a> |
        <a href="contacts.php">Contactos</a> |
        <strong>Hooks Demo</strong>
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
    
    <div class="info-box">
        <h3>🎯 Hooks Activos en esta Demo:</h3>
        <ul>
            <li><strong>beforeSave</strong>: Genera automáticamente el slug desde el título</li>
            <li><strong>afterValidate</strong>: Si status es 'published', añade fecha de publicación automática</li>
            <li><strong>afterCreate</strong>: Registra en log cuando se crea un post</li>
            <li><strong>afterUpdate</strong>: Registra en log cuando se actualiza un post</li>
            <li><strong>beforeDelete</strong>: Registra en log antes de eliminar (auditoría)</li>
        </ul>
        <p><strong>💡 Tip:</strong> Revisa el archivo de log de PHP para ver los hooks en acción</p>
    </div>
    
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Post' : 'Nuevo Post' ?></h2>
            <p style="color: #666; font-size: 14px;">
                ℹ️ El campo "slug" se genera automáticamente desde el título
            </p>
            <?= $crud->renderForm($id) ?>
        </div>
        <div>
            <h2>Lista de Posts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><code><?= htmlspecialchars($post['slug'] ?? 'N/A') ?></code></td>
                        <td class="status-<?= $post['status'] ?? 'draft' ?>">
                            <?= htmlspecialchars($post['status'] ?? 'draft') ?>
                        </td>
                        <td>
                            <a href="?id=<?= $post['id'] ?>">Editar</a>
                            <a href="?delete=<?= $post['id'] ?>" onclick="return confirm('¿Eliminar este post?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="hooks_demo.php">← Crear nuevo post</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
        <h3>📚 Código de Ejemplo:</h3>
        <pre style="background: #fff; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$crud = new DynamicCRUD($pdo, 'posts');

// Generar slug automáticamente
$crud->beforeSave(function($data) {
    if (isset($data['title']) && empty($data['slug'])) {
        $data['slug'] = slugify($data['title']);
    }
    return $data;
});

// Registrar en log después de crear
$crud->afterCreate(function($id, $data) {
    error_log("Post creado: $id");
});

$crud->handleSubmission();</code></pre>
    </div>
</body>
</html>
