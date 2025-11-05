<?php
/**
 * Admin Panel - Backend
 * 
 * Manage your blog content (posts, categories, tags)
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Admin\AdminPanel;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create admin panel
$admin = new AdminPanel($pdo, [
    'title' => 'Blog CMS Admin',
    'theme' => [
        'primary' => '#667eea',
        'sidebar_bg' => '#2d3748',
        'sidebar_text' => '#e2e8f0'
    ]
]);

// Add tables
$admin->addTable('posts', [
    'icon' => '📝',
    'label' => 'Posts',
    'description' => 'Manage blog posts'
]);

$admin->addTable('categories', [
    'icon' => '📁',
    'label' => 'Categories',
    'description' => 'Organize posts by category'
]);

$admin->addTable('tags', [
    'icon' => '🏷️',
    'label' => 'Tags',
    'description' => 'Tag your posts'
]);

$admin->addTable('comments', [
    'icon' => '💬',
    'label' => 'Comments',
    'description' => 'Manage comments'
]);

// Handle table actions
$table = $_GET['table'] ?? null;
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($table && in_array($table, ['posts', 'categories', 'tags', 'comments'])) {
    $crud = new DynamicCRUD($pdo, $table);
    
    // Configure many-to-many for posts
    if ($table === 'posts') {
        $crud->addManyToMany('tags', 'post_tags', 'post_id', 'tag_id', 'tags');
    }
    
    if ($action === 'form') {
        // Show form
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Edit ' . ucfirst($table) . '</title></head><body>';
        echo '<a href="admin.php?table=' . $table . '">← Back to list</a><br><br>';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $crud->handleSubmission();
            if ($result['success']) {
                echo '<p style="color:green">✅ Saved successfully!</p>';
                echo '<a href="admin.php?table=' . $table . '">View all ' . $table . '</a>';
            } else {
                echo '<p style="color:red">❌ Error: ' . ($result['error'] ?? 'Unknown error') . '</p>';
            }
        }
        
        echo $crud->renderForm($id);
        echo '</body></html>';
        exit;
    } elseif ($action === 'delete' && $id) {
        $crud->delete((int)$id);
        header('Location: admin.php?table=' . $table);
        exit;
    }
}

// Render admin panel
echo $admin->render();
