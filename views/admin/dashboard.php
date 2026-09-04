<?php
// views/admin/dashboard.php - Centro de Control Principal para CORES COMUNICACIONES S.A.S.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/billing.php';

$period = $_GET['period'] ?? 'month';
$custom_start = $_GET['start_date'] ?? null;
$custom_end = $_GET['end_date'] ?? null;

$metrics = getDashboardMetrics($period, $custom_start, $custom_end);
$calendar = getBillingCalendarStatus();
$alerts = getSystemAlerts();
?>

<div class="space-y-8">
    
    <!-- Encabezado del Dashboard con Filtros de Periodo -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Centro de Control CORES</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Resumen general de operaciones, clientes y recaudo en Condoto, Chocó.</p>
        </div>

        <!-- Filtros de Recaudo por Periodo -->
        <div class="flex items-center gap-1.5 p-1 bg-white border border-slate-200 rounded-2xl shadow-sm text-xs font-bold">
            <a href="index.php?view=admin_dashboard&period=today" class="<?= $period === 'today' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Hoy</a>
            <a href="index.php?view=admin_dashboard&period=week" class="<?= $period === 'week' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Semana</a>
            <a href="index.php?view=admin_dashboard&period=month" class="<?= $period === 'month' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Mes</a>
            <a href="index.php?view=admin_dashboard&period=year" class="<?= $period === 'year' ? 'bg-cores-blue text-white' : 'text-slate-600 hover:text-slate-900' ?> px-3 py-1.5 rounded-xl transition">Año</a>
        </div>
    </div>

    <!-- SECCIÓN DINERO RECAUDADO (TARJETAS DESTACADAS) -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Recaudo del Mes -->
        <div class="bg-gradient-to-br from-cores-navy to-slate-900 rounded-3xl p-6 text-white border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-cores-blue/20 rounded-full blur-2xl"></div>
            <div class="text-xs font-bold text-cores-cyan uppercase tracking-widest mb-1">RECAUDO DEL MES (<?= $calendar['current_month'] ?>)</div>
            <div class="text-3xl sm:text-4xl font-black text-white tracking-tight my-2">
                <?= formatCOP($metrics['month_collected']) ?>
            </div>
            <div class="text-xs text-slate-300 flex items-center gap-1.5 pt-2 border-t border-slate-800">
                <i class="fa-solid fa-calendar-check text-cores-orange"></i>
                <span>Ciclo mensual en curso</span>
            </div>
        </div>

        <!-- Total Recaudado Acumulado -->
        <div class="bg-gradient-to-br from-cores-orange to-cores-orangeDark rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/20 rounded-full blur-2xl"></div>
            <div class="text-xs font-bold text-white/80 uppercase tracking-widest mb-1">TOTAL RECAUDADO (HISTÓRICO)</div>
            <div class="text-3xl sm:text-4xl font-black text-white tracking-tight my-2">
                <?= formatCOP($metrics['total_collected']) ?>
            </div>
            <div class="text-xs text-white/90 flex items-center gap-1.5 pt-2 border-t border-white/20">
                <i class="fa-solid fa-vault"></i>
                <span>Suma total de pagos registrados</span>
            </div>
        </div>

        <!-- Recaudo del Periodo Seleccionado -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-md sm:col-span-2 lg:col-span-1">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">RECAUDO FILTRADO (<?= strtoupper($period) ?>)</div>
            <div class="text-3xl sm:text-4xl font-black text-cores-blue tracking-tight my-2">
                <?= formatCOP($metrics['period_collected']) ?>
            </div>
            <div class="text-xs text-slate-600 flex items-center gap-1.5 pt-2 border-t border-slate-100">
                <i class="fa-solid fa-filter text-cores-blue"></i>
                <span>Filtro activo: <?= ucfirst($period) ?></span>
            </div>
        </div>

    </div>

    <!-- TARJETAS DE USUARIOS Y PAGOS (ESTADÍSTICAS REALES) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <!-- Total de Usuarios -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm text-center">
            <span class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Total Usuarios</span>
            <div class="text-2xl font-black text-slate-900"><?= $metrics['total_users'] ?></div>
            <span class="text-[10px] text-slate-400 font-semibold">Registrados</span>
        </div>

        <!-- Usuarios Activos -->
        <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm text-center">
            <span class="text-[11px] font-bold text-emerald-600 uppercase block mb-1">Activos</span>
            <div class="text-2xl font-black text-emerald-600"><?= $metrics['active_users'] ?></div>
            <span class="text-[10px] text-emerald-500 font-semibold">● Servicio OK</span>
        </div>

        <!-- Usuarios Pendientes de Pago -->
        <div class="bg-white p-5 rounded-2xl border border-amber-100 shadow-sm text-center">
            <span class="text-[11px] font-bold text-amber-600 uppercase block mb-1">Pendientes Pago</span>
            <div class="text-2xl font-black text-amber-600"><?= $metrics['pending_users'] ?></div>
            <span class="text-[10px] text-amber-500 font-semibold">● Por cobrar</span>
        </div>

        <!-- Usuarios Suspendidos -->
        <div class="bg-white p-5 rounded-2xl border border-rose-100 shadow-sm text-center">
            <span class="text-[11px] font-bold text-rose-600 uppercase block mb-1">Suspendidos</span>
            <div class="text-2xl font-black text-rose-600"><?= $metrics['suspended_users'] ?></div>
            <span class="text-[10px] text-rose-500 font-semibold">● En corte</span>
        </div>

        <!-- Pagos Realizados -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm text-center">
            <span class="text-[11px] font-bold text-cores-blue uppercase block mb-1">Pagos Hechos</span>
            <div class="text-2xl font-black text-cores-blue"><?= $metrics['paid_invoices_count'] ?></div>
            <span class="text-[10px] text-slate-400 font-semibold">Facturas pagadas</span>
        </div>

        <!-- Pagos Pendientes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm text-center">
            <span class="text-[11px] font-bold text-cores-orange uppercase block mb-1">Pagos Pendientes</span>
            <div class="text-2xl font-black text-cores-orange"><?= $metrics['pending_invoices_count'] ?></div>
            <span class="text-[10px] text-slate-400 font-semibold">Facturas abiertas</span>
        </div>

    </div>

    <!-- CALENDARIO DE CORES Y REGLAS DE NEGOCIO -->
    <div class="grid lg:grid-cols-12 gap-8">
        
        <!-- Widget Calendario de Facturación Oficial -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-cores-blue"></i> CALENDARIO DE FACTURACIÓN
                    </h3>
                    <p class="text-xs text-slate-500">Reglas clave del ciclo operativo de CORES COMUNICACIONES.</p>
                </div>
                <div class="text-xs font-bold px-3 py-1 bg-slate-100 rounded-full text-slate-700">
                    Hoy: <strong>Día <?= $calendar['current_day'] ?></strong> (<?= $calendar['current_month'] ?>)
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4 text-center">
                
                <!-- Día 7: Facturación -->
                <div class="p-5 rounded-2xl border <?= $calendar['current_day'] === 7 ? 'bg-sky-50 border-cores-blue ring-2 ring-cores-blue' : 'bg-slate-50 border-slate-200' ?>">
                    <div class="text-3xl font-black text-cores-blue mb-1">7</div>
                    <div class="text-xs font-bold text-slate-800 uppercase">FACTURACIÓN</div>
                    <p class="text-[11px] text-slate-500 mt-1">Generación y corte mensual de recibos.</p>
                </div>

                <!-- Día 8: Suspensión -->
                <div class="p-5 rounded-2xl border <?= $calendar['current_day'] === 8 ? 'bg-rose-50 border-rose-500 ring-2 ring-rose-500' : 'bg-slate-50 border-slate-200' ?>">
                    <div class="text-3xl font-black text-rose-600 mb-1">8</div>
                    <div class="text-xs font-bold text-slate-800 uppercase">SUSPENSIÓN</div>
                    <p class="text-[11px] text-slate-500 mt-1">Revisión de pendientes y alertas de mora.</p>
                </div>

                <!-- Día 22: Recaudo -->
                <div class="p-5 rounded-2xl border <?= $calendar['current_day'] === 22 ? 'bg-emerald-50 border-emerald-500 ring-2 ring-emerald-500' : 'bg-slate-50 border-slate-200' ?>">
                    <div class="text-3xl font-black text-emerald-600 mb-1">22</div>
                    <div class="text-xs font-bold text-slate-800 uppercase">RECAUDO</div>
                    <p class="text-[11px] text-slate-500 mt-1">Día central de recaudo y conciliación.</p>
                </div>

            </div>

            <!-- Resumen de próximo hito -->
            <div class="mt-6 p-4 rounded-2xl bg-cores-navy text-white flex items-center justify-between text-xs">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-bell text-cores-orange"></i>
                    <strong><?= $calendar['next_event'] ?></strong>
                </span>
                <?php if ($calendar['days_left'] > 0): ?>
                    <span class="text-cores-cyan font-bold">(Faltan <?= $calendar['days_left'] ?> días)</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ALERTAS DEL DASHBOARD -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md">
            <h3 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-cores-orange"></i> ALERTAS DEL SISTEMA
            </h3>
            
            <div class="space-y-3">
                <?php if (!empty($alerts)): ?>
                    <?php foreach ($alerts as $alt): ?>
                        <div class="p-4 rounded-2xl border text-xs flex items-start gap-3 
                            <?= $alt['type'] === 'danger' ? 'bg-rose-50 border-rose-200 text-rose-900' : ($alt['type'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-sky-50 border-sky-200 text-sky-900') ?>">
                            <i class="fa-solid <?= $alt['icon'] ?> text-base mt-0.5"></i>
                            <div>
                                <strong class="block font-bold mb-0.5"><?= $alt['title'] ?></strong>
                                <span><?= $alt['message'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-6 text-center text-xs text-slate-400">
                        <i class="fa-solid fa-check-circle text-2xl text-emerald-500 mb-2 block"></i>
                        No hay alertas críticas pendientes en este momento.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- SECCIÓN DE 4 GRÁFICAS MODERNAS -->
    <div class="grid lg:grid-cols-2 gap-8">
        
        <!-- Gráfica 1: Recaudo por Mes -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-md">
            <h3 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-column text-cores-blue"></i> 1. Recaudo por Mes (COP)
            </h3>
            <div class="h-64 relative">
                <canvas id="chartRevenue"></canvas>
            </div>
        </div>

        <!-- Gráfica 2: Usuarios por Estado -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-md">
            <h3 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-cores-orange"></i> 2. Usuarios por Estado
            </h3>
            <div class="h-64 relative">
                <canvas id="chartUserStatus"></canvas>
            </div>
        </div>

        <!-- Gráfica 3: Facturas Pagadas vs Pendientes -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-md">
            <h3 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-cores-cyan"></i> 3. Pagos Realizados vs Pendientes
            </h3>
            <div class="h-64 relative">
                <canvas id="chartInvoices"></canvas>
            </div>
        </div>

        <!-- Gráfica 4: Crecimiento de Usuarios -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-md">
            <h3 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-arrow-trend-up text-emerald-600"></i> 4. Crecimiento de Usuarios
            </h3>
            <div class="h-64 relative">
                <canvas id="chartGrowth"></canvas>
            </div>
        </div>

    </div>

</div>
