<?php
// app/auth.php - Autenticación y Control de Acceso
require_once __DIR__ . '/../config/database.php';

function getAuthUser() {
    return $_SESSION['auth_user'] ?? null;
}

function isAdmin() {
    $user = getAuthUser();
    return $user && in_array($user['role'], ['admin', 'operator']);
}

function isClient() {
    $user = getAuthUser();
    return $user && $user['role'] === 'client';
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['flash_msg'] = ['type' => 'error', 'text' => 'Acceso denegado. Se requieren permisos de administrador.'];
        header('Location: index.php?view=login&type=admin');
        exit;
    }
}

function requireClient() {
    if (!isClient() && !isAdmin()) {
        $_SESSION['flash_msg'] = ['type' => 'error', 'text' => 'Por favor inicia sesión para acceder a tu portal de cliente.'];
        header('Location: index.php?view=login&type=client');
        exit;
    }
}

function loginUser($identifier, $password, $expected_role = null) {
    $db = getDBConnection();
    if (!$db) return ['success' => false, 'message' => 'No se pudo conectar a la base de datos.'];

    // Permitir login con Correo o Documento
    $stmt = $db->prepare("
        SELECT u.*, p.name as plan_name, p.speed as plan_speed, p.price as plan_price
        FROM users u
        LEFT JOIN plans p ON u.plan_id = p.id
        WHERE u.email = ? OR u.document_number = ? OR u.client_code = ?
    ");
    $stmt->execute([$identifier, $identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Usuario no encontrado. Verifica tus datos.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Contraseña incorrecta.'];
    }

    if ($expected_role === 'admin' && !in_array($user['role'], ['admin', 'operator'])) {
        return ['success' => false, 'message' => 'Esta cuenta no tiene privilegios de administrador.'];
    }

    if ($expected_role === 'client' && $user['role'] !== 'client') {
        // Un admin también puede ver su vista si lo desea, pero notificamos si es necesario
    }

    $_SESSION['auth_user'] = [
        'id' => $user['id'],
        'client_code' => $user['client_code'],
        'name' => $user['name'],
        'email' => $user['email'],
        'document_number' => $user['document_number'],
        'phone' => $user['phone'],
        'address' => $user['address'],
        'neighborhood' => $user['neighborhood'],
        'role' => $user['role'],
        'plan_id' => $user['plan_id'],
        'plan_name' => $user['plan_name'] ?? 'Sin Plan Asignado',
        'plan_speed' => $user['plan_speed'] ?? 'N/A',
        'plan_price' => $user['plan_price'] ?? 0,
        'status' => $user['status'],
        'billing_day' => $user['billing_day'] ?? BILLING_DAY,
        'suspension_day' => $user['suspension_day'] ?? SUSPENSION_DAY,
        'collection_day' => $user['collection_day'] ?? COLLECTION_DAY
    ];

    // Registrar log
    logAuditAction('Login', 'Autenticación', "Inicio de sesión de {$user['name']} ({$user['role']})");

    return ['success' => true, 'user' => $_SESSION['auth_user']];
}

function logoutUser() {
    $user = getAuthUser();
    if ($user) {
        logAuditAction('Logout', 'Autenticación', "Cierre de sesión de {$user['name']}");
    }
    unset($_SESSION['auth_user']);
    session_destroy();
}

function logAuditAction($action, $module, $details) {
    try {
        $db = getDBConnection();
        if (!$db) return;
        $user = getAuthUser();
        $userName = $user ? $user['name'] : 'Público/Sistema';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare("INSERT INTO audit_logs (user_name, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userName, $action, $module, $details, $ip]);
    } catch (Exception $e) {
        // Ignorar error de log para no bloquear la app
    }
}
