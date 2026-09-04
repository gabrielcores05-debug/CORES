<?php
// views/admin/recaudos.php - Módulo Financiero de Recaudos
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/billing.php';

$db = getDBConnection();
$period = $_GET['period'] ?? 'month';
$metrics = getDashboardMetrics($period);

// Cálculos específicos de recaudo
$total_payments_count = (int)$db->query("SELECT COUNT(*) FROM payments WHERE status = 'PAGADO'")->fetchColumn();
$average_payment = $total_payments_count > 0 ? ($metrics['total_collected'] / $total_payments_count) : 0;
?>

<div class="space-y-8">
    
    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Módulo de Recaudos</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Análisis de ingresos financieros y recaudo por periodos.</p>
        </div>
        <div class="flex items-center gap-1.5 p-1 bg-white border border-slate-200 rounded-2xl shadow-sm text-xs font-bold">
            <a href="index.php?view=admin_recaudos&period=today" class="<?= $period === 'today' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Hoy</a>
            <a href="index.php?view=admin_recaudos&period=week" class="<?= $period === 'week' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Semana</a>
            <a href="index.php?view=admin_recaudos&period=month" class="<?= $period === 'month' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Mes</a>
            <a href="index.php?view=admin_recaudos&period=year" class="<?= $period === 'year' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Año</a>
        </div>
    </div>

    <!-- Tarjetas de Métricas Financieras -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Total Recaudado</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900"><?= formatCOP($metrics['total_collected']) ?></div>
            <span class="text-xs text-emerald-600 font-semibold mt-2 block"><i class="fa-solid fa-vault"></i> Ingreso histórico total</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Recaudo del Mes</span>
            <div class="text-2xl sm:text-3xl font-black text-cores-blue"><?= formatCOP($metrics['month_collected']) ?></div>
            <span class="text-xs text-slate-500 font-semibold mt-2 block">Mes en curso</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Número de Pagos</span>
            <div class="text-2xl sm:text-3xl font-black text-cores-orange"><?= $total_payments_count ?></div>
            <span class="text-xs text-slate-500 font-semibold mt-2 block">Transacciones procesadas</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Promedio por Pago</span>
            <div class="text-2xl sm:text-3xl font-black text-cores-cyan"><?= formatCOP($average_payment) ?></div>
            <span class="text-xs text-slate-500 font-semibold mt-2 block">Ticket promedio mensual</span>
        </div>

    </div>

    <!-- Gráfica Detallada de Ingresos -->
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-md">
        <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-cores-blue"></i> Histórico de Recaudo Mensual
        </h3>
        <div class="h-80 relative">
            <canvas id="chartRevenue"></canvas>
        </div>
    </div>

</div>
