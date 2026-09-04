<?php
// index.php - Enrutador Maestro y Controlador Principal de CORES COMUNICACIONES S.A.S.
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/billing.php';

$db = getDBConnection();
$view = $_GET['view'] ?? 'home';
$action = $_GET['action'] ?? '';

// ========================================================
// CONTROLADOR DE ACCIONES (POST / GET EXPORTS)
// ========================================================

// 1. INICIAR SESIÓN
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_type = $_POST['role_type'] ?? 'client';

    $result = loginUser($identifier, $password, $role_type);

    if ($result['success']) {
        if ($result['user']['role'] === 'admin' || $result['user']['role'] === 'operator') {
            header('Location: index.php?view=admin_dashboard');
        } else {
            header('Location: index.php?view=client_portal');
        }
        exit;
    } else {
        $_SESSION['flash_msg'] = ['type' => 'error', 'text' => $result['message']];
        header("Location: index.php?view=login&type=$role_type");
        exit;
    }
}

// 2. CERRAR SESIÓN
if ($view === 'logout') {
    logoutUser();
    header('Location: index.php');
    exit;
}

// 3. REGISTRAR O EDITAR CLIENTE (ADMIN)
if ($action === 'save_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $name = trim($_POST['name'] ?? '');
    $doc = trim($_POST['document_number'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? ($doc . '@corescomunicaciones.com'));
    $neighborhood = trim($_POST['neighborhood'] ?? 'Condoto');
    $address = trim($_POST['address'] ?? 'Condoto, Chocó');
    $plan_id = intval($_POST['plan_id'] ?? 1);
    $status = $_POST['status'] ?? 'ACTIVO';
    $password = password_hash('cliente123', PASSWORD_BCRYPT);
    $code = 'CLI-' . rand(1000, 9999);

    if ($db && $name && $doc && $phone) {
        try {
            $stmt = $db->prepare("
                INSERT INTO users (client_code, name, document_type, document_number, email, phone, address, neighborhood, role, plan_id, status, password, installation_date)
                VALUES (?, ?, 'CC', ?, ?, ?, ?, ?, 'client', ?, ?, ?, ?)
            ");
            $stmt->execute([$code, $name, $doc, $email, $phone, $address, $neighborhood, $plan_id, $status, $password, date('Y-m-d')]);
            $new_user_id = $db->lastInsertId();

            // Generar primera factura de muestra
            $plan_price = (float)$db->query("SELECT price FROM plans WHERE id = $plan_id")->fetchColumn();
            $inv_num = 'FAC-' . date('Y-m') . '-' . str_pad($new_user_id, 4, '0', STR_PAD_LEFT);
            $stmt_inv = $db->prepare("
                INSERT INTO invoices (invoice_number, user_id, plan_id, period, amount, issue_date, due_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')
            ");
            $stmt_inv->execute([$inv_num, $new_user_id, $plan_id, date('F Y'), $plan_price, date('Y-m-07'), date('Y-m-08')]);

            logAuditAction('Crear', 'Usuarios', "Nuevo cliente registrado: $name ($doc)");
            $_SESSION['flash_msg'] = ['type' => 'success', 'text' => "Cliente $name registrado exitosamente."];
        } catch (Exception $e) {
            $_SESSION['flash_msg'] = ['type' => 'error', 'text' => 'Error al registrar cliente: ' . $e->getMessage()];
        }
    }
    header('Location: index.php?view=admin_users');
    exit;
}

// 4. CAMBIAR ESTADO DE USUARIO (ACTIVAR / SUSPENDER)
if ($action === 'change_user_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? 'ACTIVO';

    if ($db && $user_id > 0) {
        $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        logAuditAction('Estado', 'Usuarios', "Estado cambiado a $new_status para usuario ID #$user_id");
        $_SESSION['flash_msg'] = ['type' => 'success', 'text' => "Estado de cliente actualizado a $new_status."];
    }
    header('Location: index.php?view=admin_users');
    exit;
}

// 5. ELIMINAR USUARIO
if ($action === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($db && $user_id > 0) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'client'");
        $stmt->execute([$user_id]);
        logAuditAction('Eliminar', 'Usuarios', "Cliente ID #$user_id eliminado del sistema");
        $_SESSION['flash_msg'] = ['type' => 'success', 'text' => 'Cliente eliminado de la base de datos.'];
    }
    header('Location: index.php?view=admin_users');
    exit;
}

// 6. REGISTRAR PAGO
if ($action === 'save_payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $user_id = intval($_POST['user_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
    $payment_method = $_POST['payment_method'] ?? 'Efectivo';
    $period = $_POST['period'] ?? date('F Y');
    $reference = trim($_POST['reference_number'] ?? 'REC-' . rand(10000, 99999));

    if ($db && $user_id > 0 && $amount > 0) {
        $stmt = $db->prepare("
            INSERT INTO payments (user_id, amount, payment_date, payment_method, period, status, reference_number, registered_by)
            VALUES (?, ?, ?, ?, ?, 'PAGADO', ?, ?)
        ");
        $stmt->execute([$user_id, $amount, $payment_date, $payment_method, $period, $reference, $_SESSION['auth_user']['name']]);

        // Actualizar estado de usuario y factura
        $db->prepare("UPDATE users SET status = 'ACTIVO' WHERE id = ?")->execute([$user_id]);
        $db->prepare("UPDATE invoices SET status = 'PAGADO' WHERE user_id = ? AND period = ?")->execute([$user_id, $period]);

        logAuditAction('Pago', 'Recaudos', "Pago de " . formatCOP($amount) . " registrado para cliente ID #$user_id");
        $_SESSION['flash_msg'] = ['type' => 'success', 'text' => 'Pago registrado y acreditado exitosamente.'];
    }
    header('Location: index.php?view=admin_payments');
    exit;
}

// 7. CREAR PLAN DE INTERNET
if ($action === 'save_plan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $speed = trim($_POST['speed'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $features = trim($_POST['features'] ?? '');
    $is_featured = intval($_POST['is_featured'] ?? 0);

    if ($db && $name && $code && $speed) {
        $stmt = $db->prepare("
            INSERT INTO plans (name, code, speed, price, features, is_featured, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$name, $code, $speed, $price, $features, $is_featured]);
        logAuditAction('Crear', 'Planes', "Nuevo plan creado: $name ($speed)");
        $_SESSION['flash_msg'] = ['type' => 'success', 'text' => 'Nuevo plan de Internet publicado.'];
    }
    header('Location: index.php?view=admin_plans');
    exit;
}

// 8. GUARDAR CONFIGURACIÓN INSTITUCIONAL
if ($action === 'save_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $company_name = trim($_POST['company_name'] ?? COMPANY_NAME);
    $slogan = trim($_POST['slogan'] ?? COMPANY_SLOGAN);
    $location = trim($_POST['location'] ?? COMPANY_LOCATION);
    $phone = trim($_POST['phone'] ?? COMPANY_PHONE);
    $billing_day = intval($_POST['billing_day'] ?? 7);
    $suspension_day = intval($_POST['suspension_day'] ?? 8);
    $collection_day = intval($_POST['collection_day'] ?? 22);

    if ($db) {
        $stmt = $db->prepare("
            UPDATE company_settings
            SET company_name=?, slogan=?, location=?, phone=?, billing_day=?, suspension_day=?, collection_day=?
            WHERE id = 1
        ");
        $stmt->execute([$company_name, $slogan, $location, $phone, $billing_day, $suspension_day, $collection_day]);
        logAuditAction('Configuración', 'Sistema', 'Parámetros institucionales actualizados');
        $_SESSION['flash_msg'] = ['type' => 'success', 'text' => 'Configuración actualizada correctamente.'];
    }
    header('Location: index.php?view=admin_settings');
    exit;
}

// 9. ENVIAR FORMULARIO DE CONTACTO
if ($action === 'send_contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Solicitud');
    $message = trim($_POST['message'] ?? '');

    if ($db && $name && $phone && $message) {
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        logAuditAction('Contacto', 'Web', "Mensaje recibido de $name ($phone)");
        $_SESSION['flash_msg'] = ['type' => 'success', 'text' => '¡Gracias por contactar a CORES COMUNICACIONES! Te responderemos a la brevedad.'];
    }
    header('Location: index.php#contacto');
    exit;
}

// 10. EXPORTACIONES A CSV (EXCEL)
if ($action === 'export_csv_clients') {
    requireAdmin();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=clientes_cores_condoto_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Código', 'Nombre', 'Documento', 'Teléfono', 'Barrio / Dirección', 'Plan', 'Velocidad', 'Tarifa (COP)', 'Estado']);
    $stmt = $db->query("
        SELECT u.client_code, u.name, u.document_number, u.phone, u.neighborhood, p.name as plan_name, p.speed, p.price, u.status
        FROM users u LEFT JOIN plans p ON u.plan_id = p.id
        WHERE u.role = 'client'
        ORDER BY u.id ASC
    ");
    while ($r = $stmt->fetch()) {
        fputcsv($output, $r);
    }
    fclose($output);
    exit;
}

if ($action === 'export_csv_payments') {
    requireAdmin();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=recaudos_cores_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['ID Pago', 'Cliente', 'Cédula', 'Valor (COP)', 'Fecha', 'Método', 'Periodo', 'Comprobante', 'Registrado Por']);
    $stmt = $db->query("
        SELECT p.id, u.name, u.document_number, p.amount, p.payment_date, p.payment_method, p.period, p.reference_number, p.registered_by
        FROM payments p JOIN users u ON p.user_id = u.id
        ORDER BY p.id DESC
    ");
    while ($r = $stmt->fetch()) {
        fputcsv($output, $r);
    }
    fclose($output);
    exit;
}

// ========================================================
// ENRUTADOR DE VISTAS (PÚBLICAS, ADMIN Y CLIENTE)
// ========================================================

$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

// Vistas Administrativas
if (strpos($view, 'admin_') === 0) {
    require_once __DIR__ . '/views/layouts/admin_header.php';
    
    // Mensaje Flash en Admin
    if ($flash) {
        $color = $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800';
        echo "<div class='mb-6 p-4 rounded-2xl border $color text-xs font-bold flex items-center justify-between shadow-sm'>
                <span>" . htmlspecialchars($flash['text']) . "</span>
                <button onclick='this.parentElement.remove()'><i class='fa-solid fa-xmark'></i></button>
              </div>";
    }

    if ($view === 'admin_dashboard') {
        require_once __DIR__ . '/views/admin/dashboard.php';
    } elseif ($view === 'admin_users') {
        require_once __DIR__ . '/views/admin/users.php';
    } elseif ($view === 'admin_payments') {
        require_once __DIR__ . '/views/admin/payments.php';
    } elseif ($view === 'admin_recaudos') {
        require_once __DIR__ . '/views/admin/recaudos.php';
    } elseif ($view === 'admin_plans') {
        require_once __DIR__ . '/views/admin/plans.php';
    } elseif ($view === 'admin_reports') {
        require_once __DIR__ . '/views/admin/reports.php';
    } elseif ($view === 'admin_settings') {
        require_once __DIR__ . '/views/admin/settings.php';
    }

    require_once __DIR__ . '/views/layouts/admin_footer.php';
    exit;
}

// Vistas Públicas y de Clientes
require_once __DIR__ . '/views/layouts/header.php';

if ($flash) {
    $color = $flash['type'] === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700' : 'bg-rose-500/10 border-rose-500/30 text-rose-700';
    echo "<div class='max-w-7xl mx-auto px-4 mt-4 w-full'>
            <div class='p-4 rounded-2xl border $color text-xs font-bold flex items-center justify-between shadow-sm bg-white'>
                <span>" . htmlspecialchars($flash['text']) . "</span>
                <button onclick='this.parentElement.parentElement.remove()'><i class='fa-solid fa-xmark'></i></button>
            </div>
          </div>";
}

if ($view === 'login') {
    require_once __DIR__ . '/views/auth/login.php';
} elseif ($view === 'client_portal') {
    require_once __DIR__ . '/views/client/portal.php';
} else {
    require_once __DIR__ . '/views/public/landing.php';
}

require_once __DIR__ . '/views/layouts/footer.php';
