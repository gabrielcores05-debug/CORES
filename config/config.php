<?php
// config/config.php - Configuración Oficial de CORES COMUNICACIONES S.A.S.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Información Oficial de la Empresa
define('COMPANY_NAME', 'CORES COMUNICACIONES S.A.S.');
define('COMPANY_SLOGAN', 'CONECTAMOS TU MUNDO, IMPULSAMOS TU VIDA');
define('COMPANY_LOCATION', 'Condoto, Chocó, Colombia');
define('COMPANY_CITY', 'Condoto, Chocó');
define('COMPANY_PHONE', '3126030464');
define('COMPANY_WHATSAPP', '573126030464');
define('COMPANY_EMAIL', 'contacto@corescomunicaciones.com');
define('COMPANY_APPROX_CLIENTS', '+600');

// Reglas de Negocio del Ciclo de Facturación
define('BILLING_DAY', 7);     // Día 7: Facturación
define('SUSPENSION_DAY', 8);  // Día 8: Suspensión preventiva
define('COLLECTION_DAY', 22); // Día 22: Recaudo oficial

// Coordenadas para el mapa (Condoto, Chocó)
define('MAP_LATITUDE', 5.0939);
define('MAP_LONGITUDE', -76.6508);
define('MAP_ZOOM', 15);

// Paleta de Colores Oficial
define('COLOR_BLUE_DARK', '#0B132B');
define('COLOR_BLUE_NAVY', '#0F172A');
define('COLOR_BLUE_PRIMARY', '#0284C7');
define('COLOR_CYAN_LIGHT', '#06B6D4');
define('COLOR_ORANGE_PRIMARY', '#F97316');
define('COLOR_ORANGE_DARK', '#EA580C');

// Helper para enlace directo de WhatsApp
function getWhatsAppLink($custom_message = null) {
    $phone = COMPANY_WHATSAPP;
    if ($custom_message === null) {
        $custom_message = "Hola, me comunico desde la plataforma web de CORES COMUNICACIONES S.A.S. Deseo solicitar información sobre los servicios de Internet.";
    }
    return "https://wa.me/" . $phone . "?text=" . urlencode($custom_message);
}

// Helper para formatear moneda en Pesos Colombianos (COP)
function formatCOP($amount) {
    return '$ ' . number_format((float)$amount, 0, ',', '.') . ' COP';
}

// Helper para formato de fecha en español
function formatDateSpanish($dateString) {
    if (!$dateString) return 'N/A';
    $timestamp = strtotime($dateString);
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $dia = date('d', $timestamp);
    $mes = $meses[date('n', $timestamp) - 1];
    $anio = date('Y', $timestamp);
    return "$dia de $mes, $anio";
}
