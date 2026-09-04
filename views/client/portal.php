<?php
// views/client/portal.php - Panel Privado del Cliente
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/auth.php';
requireClient();

$user = getAuthUser();
$db = getDBConnection();

// Obtener datos frescos del cliente
$stmt = $db->prepare("
    SELECT u.*, p.name as plan_name, p.speed as plan_speed, p.price as plan_price, p.technology
    FROM users u
    LEFT JOIN plans p ON u.plan_id = p.id
    WHERE u.id = ?
");
$stmt->execute([$user['id']]);
$client = $stmt->fetch() ?: $user;

// Historial de pagos del cliente
$stmt_pay = $db->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY id DESC");
$stmt_pay->execute([$user['id']]);
$payments = $stmt_pay->fetchAll();

// Última factura
$stmt_inv = $db->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt_inv->execute([$user['id']]);
$last_invoice = $stmt_inv->fetch();

$is_active = ($client['status'] === 'ACTIVO');
?>

<div class="max-w-6xl mx-auto space-y-8 py-8 px-4 sm:px-6 lg:px-8">
    
    <!-- Encabezado de Bienvenida -->
    <div class="bg-gradient-to-r from-cores-navy via-slate-900 to-cores-navy rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl flex flex-wrap items-center justify-between gap-6">
        <div class="space-y-1">
            <span class="text-xs font-bold text-cores-orange uppercase tracking-widest">PORTAL DE AUTOSERVICIO</span>
            <h1 class="text-2xl sm:text-3xl font-black text-white">¡Hola, <?= htmlspecialchars($client['name']) ?>!</h1>
            <p class="text-xs text-slate-400">Código de Cliente: <strong class="text-cores-cyan font-mono"><?= htmlspecialchars($client['client_code'] ?? 'CLI-' . $client['id']) ?></strong> &bull; Condoto, Chocó</p>
        </div>
        <div>
            <?php if ($client['status'] === 'ACTIVO'): ?>
                <span class="badge-active px-4 py-2 rounded-2xl font-black text-xs inline-flex items-center gap-2 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span> SERVICIO ACTIVO
                </span>
            <?php elseif ($client['status'] === 'PENDIENTE DE PAGO'): ?>
                <span class="badge-pending px-4 py-2 rounded-2xl font-black text-xs inline-flex items-center gap-2 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> PAGO PENDIENTE
                </span>
            <?php else: ?>
                <span class="badge-suspended px-4 py-2 rounded-2xl font-black text-xs inline-flex items-center gap-2 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> SERVICIO SUSPENDIDO
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Grid de Plan Contratado y Ciclo de Facturación -->
    <div class="grid md:grid-cols-12 gap-8">
        
        <!-- Tarjeta de Plan Contratado -->
        <div class="md:col-span-6 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase">TU PLAN CONTRATADO</span>
                    <h3 class="text-xl font-black text-slate-900"><?= htmlspecialchars($client['plan_name'] ?? 'Plan Residencial') ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-cores-blue flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wifi"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Velocidad</span>
                    <div class="text-2xl font-black text-cores-blue"><?= htmlspecialchars($client['plan_speed'] ?? '30 Mbps') ?></div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Tarifa Mensual</span>
                    <div class="text-2xl font-black text-slate-900"><?= formatCOP($client['plan_price'] ?? 0) ?></div>
                </div>
            </div>

            <div class="text-xs text-slate-500 space-y-2 pt-2">
                <p><i class="fa-solid fa-location-dot text-cores-orange mr-2"></i> Dirección de servicio: <strong><?= htmlspecialchars($client['address'] . ' (' . ($client['neighborhood'] ?? 'Condoto') . ')') ?></strong></p>
                <p><i class="fa-solid fa-phone text-cores-cyan mr-2"></i> Teléfono registrado: <strong><?= htmlspecialchars($client['phone']) ?></strong></p>
            </div>
        </div>

        <!-- Tarjeta del Ciclo de Facturación del Cliente -->
        <div class="md:col-span-6 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <span class="text-xs font-bold text-slate-400 uppercase">CALENDARIO DE TU SERVICIO</span>
                <h3 class="text-xl font-black text-slate-900">Fechas Clave de Facturación</h3>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-100">
                    <div class="text-2xl font-black text-cores-blue">7</div>
                    <div class="text-[10px] font-bold text-slate-800 uppercase mt-1">DÍA 7<br>FACTURACIÓN</div>
                </div>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">
                    <div class="text-2xl font-black text-rose-600">8</div>
                    <div class="text-[10px] font-bold text-slate-800 uppercase mt-1">DÍA 8<br>SUSPENSIÓN</div>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                    <div class="text-2xl font-black text-emerald-600">22</div>
                    <div class="text-[10px] font-bold text-slate-800 uppercase mt-1">DÍA 22<br>RECAUDO</div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600 flex items-center justify-between">
                <span>Estado de pago actual:</span>
                <?php if ($last_invoice && $last_invoice['status'] === 'PAGADO'): ?>
                    <strong class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Al Día</strong>
                <?php else: ?>
                    <strong class="text-amber-600 font-bold"><i class="fa-solid fa-clock"></i> Recibo Pendiente</strong>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Historial de Pagos y Sección de Ayuda -->
    <div class="grid md:grid-cols-12 gap-8">
        
        <!-- Historial de Pagos -->
        <div class="md:col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md">
            <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-cores-blue"></i> Historial de Pagos Realizados
            </h3>

            <?php if (!empty($payments)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold uppercase text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Periodo</th>
                                <th class="px-4 py-3">Valor Pagado</th>
                                <th class="px-4 py-3">Método</th>
                                <th class="px-4 py-3">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td class="px-4 py-3 font-mono font-semibold"><?= formatDateSpanish($p['payment_date']) ?></td>
                                    <td class="px-4 py-3 font-bold text-slate-900"><?= htmlspecialchars($p['period']) ?></td>
                                    <td class="px-4 py-3 font-black text-slate-900 font-mono"><?= formatCOP($p['amount']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($p['payment_method']) ?></td>
                                    <td class="px-4 py-3 font-mono text-[11px] text-slate-400"><?= htmlspecialchars($p['reference_number'] ?? 'OK') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="py-12 text-center text-xs text-slate-400">
                    <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                    No registras pagos en el historial aún.
                </div>
            <?php endif; ?>
        </div>

        <!-- SECCIÓN DE AYUDA AL CLIENTE -->
        <div class="md:col-span-4 bg-cores-navy rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-md space-y-6 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-cores-orange/20 text-cores-orange flex items-center justify-center text-xl">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h3 class="text-xl font-black text-white">¿NECESITAS AYUDA?</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Comunícate directamente con nuestra línea de atención y soporte técnico en <strong>Condoto, Chocó</strong>.
                </p>
                <div class="pt-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">TELÉFONO DE ATENCIÓN:</span>
                    <div class="text-2xl font-black text-cores-cyan font-mono"><?= COMPANY_PHONE ?></div>
                </div>
            </div>

            <!-- Botón WhatsApp Directo con Número Exacto -->
            <a href="<?= getWhatsAppLink("Hola CORES COMUNICACIONES, soy el cliente " . $client['name'] . " (Cédula: " . $client['document_number'] . ") y solicito asistencia técnica en Condoto.") ?>" target="_blank" class="btn-cores-primary block w-full py-4 text-center text-xs font-bold shadow-lg">
                <i class="fa-brands fa-whatsapp text-base mr-2"></i> CONTACTAR POR WHATSAPP
            </a>
        </div>

    </div>

</div>
