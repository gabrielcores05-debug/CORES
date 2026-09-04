<?php
// app/billing.php - Lógica de Negocio y Reglas de Facturación para CORES COMUNICACIONES S.A.S.
require_once __DIR__ . '/../config/database.php';

// Obtener todas las métricas del Dashboard en tiempo real desde la Base de Datos (MySQL / MariaDB)
function getDashboardMetrics($period = 'month', $custom_start = null, $custom_end = null) {
    $metrics = [
        'total_users' => 0,
        'active_users' => 0,
        'suspended_users' => 0,
        'pending_users' => 0,
        'paid_invoices_count' => 0,
        'pending_invoices_count' => 0,
        'total_collected' => 0.0,
        'period_collected' => 0.0,
        'month_collected' => 0.0
    ];

    try {
        $db = getDBConnection();
        if (!$db) {
            return $metrics;
        }

        // 1. Conteo de usuarios por estado
        $metrics['total_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
        $metrics['active_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND status = 'ACTIVO'")->fetchColumn();
        $metrics['suspended_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND status = 'SUSPENDIDO'")->fetchColumn();
        $metrics['pending_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND status = 'PENDIENTE DE PAGO'")->fetchColumn();

        // 2. Conteo de facturas
        $metrics['paid_invoices_count'] = (int)$db->query("SELECT COUNT(*) FROM invoices WHERE status = 'PAGADO'")->fetchColumn();
        $metrics['pending_invoices_count'] = (int)$db->query("SELECT COUNT(*) FROM invoices WHERE status IN ('PENDIENTE', 'VENCIDO')")->fetchColumn();

        // 3. Recaudo Total Acumulado (histórico real)
        $metrics['total_collected'] = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'PAGADO'")->fetchColumn();

        // 4. Recaudo del Mes Actual (Compatible con MySQL y MariaDB)
        $current_year = (int)date('Y');
        $current_month = (int)date('m');

        $stmt_m = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'PAGADO' AND YEAR(payment_date) = ? AND MONTH(payment_date) = ?");
        $stmt_m->execute([$current_year, $current_month]);
        $metrics['month_collected'] = (float)$stmt_m->fetchColumn();

        // 5. Recaudo según filtro de periodo (Día, Semana, Mes, Año, Personalizado)
        $metrics['period_collected'] = $metrics['month_collected'];

        if ($period === 'today') {
            $today = date('Y-m-d');
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'PAGADO' AND DATE(payment_date) = ?");
            $stmt->execute([$today]);
            $metrics['period_collected'] = (float)$stmt->fetchColumn();
        } elseif ($period === 'week') {
            $week_start = date('Y-m-d', strtotime('monday this week'));
            $week_end = date('Y-m-d', strtotime('sunday this week'));
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'PAGADO' AND payment_date BETWEEN ? AND ?");
            $stmt->execute([$week_start, $week_end]);
            $metrics['period_collected'] = (float)$stmt->fetchColumn();
        } elseif ($period === 'year') {
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'PAGADO' AND YEAR(payment_date) = ?");
            $stmt->execute([$current_year]);
            $metrics['period_collected'] = (float)$stmt->fetchColumn();
        } elseif ($period === 'custom' && $custom_start && $custom_end) {
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'PAGADO' AND payment_date BETWEEN ? AND ?");
            $stmt->execute([$custom_start, $custom_end]);
            $metrics['period_collected'] = (float)$stmt->fetchColumn();
        }

    } catch (PDOException $e) {
        error_log("Error en getDashboardMetrics: " . $e->getMessage());
    }

    return $metrics;
}

// Analizar el estado actual respecto al calendario de facturación oficial
function getBillingCalendarStatus() {
    $current_day = (int)date('j');
    $current_month_name = date('F');
    $meses = [
        'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
        'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
        'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
        'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
    ];
    $month_es = $meses[$current_month_name] ?? $current_month_name;

    $is_billing_day = ($current_day === BILLING_DAY);
    $is_suspension_day = ($current_day === SUSPENSION_DAY);
    $is_collection_day = ($current_day === COLLECTION_DAY);

    // Próximo hito operativo
    if ($current_day < BILLING_DAY) {
        $next_event = "Próximo Hito: Facturación (Día " . BILLING_DAY . ")";
        $days_left = BILLING_DAY - $current_day;
    } elseif ($current_day === BILLING_DAY) {
        $next_event = "¡Hoy es Día de Facturación!";
        $days_left = 0;
    } elseif ($current_day === SUSPENSION_DAY) {
        $next_event = "¡Hoy es Día de Suspensión Preventiva!";
        $days_left = 0;
    } elseif ($current_day < COLLECTION_DAY) {
        $next_event = "Próximo Hito: Recaudo (Día " . COLLECTION_DAY . ")";
        $days_left = COLLECTION_DAY - $current_day;
    } elseif ($current_day === COLLECTION_DAY) {
        $next_event = "¡Hoy es Día de Recaudo Oficial!";
        $days_left = 0;
    } else {
        $next_event = "Próximo Ciclo: Facturación del próximo mes (Día " . BILLING_DAY . ")";
        $days_left = (date('t') - $current_day) + BILLING_DAY;
    }

    return [
        'current_day' => $current_day,
        'current_month' => $month_es,
        'billing_day' => BILLING_DAY,
        'suspension_day' => SUSPENSION_DAY,
        'collection_day' => COLLECTION_DAY,
        'is_billing_day' => $is_billing_day,
        'is_suspension_day' => $is_suspension_day,
        'is_collection_day' => $is_collection_day,
        'next_event' => $next_event,
        'days_left' => $days_left
    ];
}

// Generar Alertas Dinámicas para el Administrador según reglas y BD
function getSystemAlerts() {
    $alerts = [];
    $cal = getBillingCalendarStatus();
    $metrics = getDashboardMetrics();

    // Alerta de fecha de negocio
    if ($cal['is_billing_day']) {
        $alerts[] = [
            'type' => 'info',
            'icon' => 'fa-file-invoice',
            'title' => '¡Hoy es Día 7: Facturación!',
            'message' => 'Se ha iniciado el ciclo mensual de facturación para los usuarios de Condoto.'
        ];
    } elseif ($cal['is_suspension_day']) {
        $alerts[] = [
            'type' => 'warning',
            'icon' => 'fa-triangle-exclamation',
            'title' => '¡Hoy es Día 8: Suspensión Preventiva!',
            'message' => 'Revisa la lista de usuarios con pagos pendientes antes de aplicar suspensiones.'
        ];
    } elseif ($cal['is_collection_day']) {
        $alerts[] = [
            'type' => 'success',
            'icon' => 'fa-sack-dollar',
            'title' => '¡Hoy es Día 22: Recaudo Oficial!',
            'message' => 'Jornada central de recaudo y conciliación de pagos en sede y medios digitales.'
        ];
    }

    // Alertas por datos de usuarios
    if ($metrics['pending_users'] > 0) {
        $alerts[] = [
            'type' => 'warning',
            'icon' => 'fa-clock',
            'title' => 'Usuarios Pendientes de Pago',
            'message' => "Hay {$metrics['pending_users']} usuario(s) que aún no han registrado su pago este mes."
        ];
    }

    if ($metrics['suspended_users'] > 0) {
        $alerts[] = [
            'type' => 'danger',
            'icon' => 'fa-ban',
            'title' => 'Servicios Suspendidos',
            'message' => "Actualmente {$metrics['suspended_users']} cliente(s) se encuentran en estado SUSPENDIDO."
        ];
    }

    return $alerts;
}
