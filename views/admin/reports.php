<?php
// views/admin/reports.php - Módulo de Reportes y Exportación
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/billing.php';

$metrics = getDashboardMetrics();
?>

<div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Reportes y Exportación</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Generación de archivos e informes ejecutivos de CORES COMUNICACIONES S.A.S.</p>
        </div>
        <a href="index.php?action=export_csv_clients" class="btn-cores-primary px-5 py-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-file-excel"></i> EXPORTAR LISTA DE CLIENTES (.CSV)
        </a>
    </div>

    <!-- Opciones de Descarga de Reportes -->
    <div class="grid md:grid-cols-3 gap-6">
        
        <!-- Reporte 1: Clientes y Estados -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-md space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-cores-blue flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">Base de Clientes (+600)</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Descarga la lista de usuarios con Cédula, Teléfono, Barrio, Plan y Estado de servicio actual.
            </p>
            <a href="index.php?action=export_csv_clients" class="btn-cores-blue block w-full py-2.5 text-center text-xs font-bold">
                <i class="fa-solid fa-download mr-1"></i> Descargar Clientes (.CSV)
            </a>
        </div>

        <!-- Reporte 2: Histórico de Pagos -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-md space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">Historial de Recaudos</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Descarga el consolidado de pagos realizados con fechas, valores, métodos y comprobantes.
            </p>
            <a href="index.php?action=export_csv_payments" class="btn-cores-primary block w-full py-2.5 text-center text-xs font-bold">
                <i class="fa-solid fa-download mr-1"></i> Descargar Pagos (.CSV)
            </a>
        </div>

        <!-- Reporte 3: Cartera Pendiente -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-md space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">Cartera y Pendientes</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Reporte de clientes pendientes de pago para el ciclo operativo del Día 8 (Suspensión preventiva).
            </p>
            <a href="index.php?action=export_csv_debtors" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold block w-full text-center text-xs rounded-xl transition">
                <i class="fa-solid fa-download mr-1"></i> Descargar Pendientes (.CSV)
            </a>
        </div>

    </div>
</div>
