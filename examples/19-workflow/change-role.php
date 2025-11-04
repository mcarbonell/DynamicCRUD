<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $allowedRoles = ['admin', 'manager', 'warehouse', 'guest'];
    $role = $_POST['role'];
    
    if (in_array($role, $allowedRoles)) {
        $_SESSION['role'] = $role;
    }
}

header('Location: index.php' . ($_GET['id'] ?? '' ? '?id=' . $_GET['id'] : ''));
exit;
