<?php
// views/admin/payments.php - Gestión de Pagos y Facturación
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDBConnection();

// Filtros
$filter_period = $_GET['period'] ?? '';
$filter_client = $_GET['client_id'] ?? '';

$query = "
    SELECT p.*, u.name as client_name, u.document_number, u.phone, pl.name as plan_name
    FROM payments p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN plans pl ON u.plan_id = pl.id
    WHERE 1=1
";
$params = [];

if ($filter_period !== '') {
    $query .= " AND p.period = ?";
    $params[] = $filter_period;
}

if ($filter_client !== '') {
    $query .= " AND p.user_id = ?";
    $params[] = $filter_client;
}

$query .= " ORDER BY p.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Lista de clientes para registrar pago
$clients_list = $db->query("SELECT id, name, document_number, plan_id FROM users WHERE role = 'client' ORDER BY name ASC")->fetchAll();
?>

<div class="space-y-6">
    
    <!-- Encabezado y Botón Registrar Pago -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Gestión de Pagos</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Registro y conciliación de pagos de servicios de Internet.</p>
        </div>
        <button onclick="openModal('modalNewPayment')" class="btn-cores-primary px-5 py-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-receipt"></i> REGISTRAR PAGO
        </button>
    </div>

    <!-- Tabla de Pagos -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-black uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Cliente / Cédula</th>
                        <th class="px-6 py-4">Fecha de Pago</th>
                        <th class="px-6 py-4">Valor Pagado (COP)</th>
                        <th class="px-6 py-4">Método de Pago</th>
                        <th class="px-6 py-4">Periodo Facturado</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Referencia / Notas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $pay): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($pay['client_name']) ?></div>
                                    <span class="text-[11px] text-slate-500 font-mono">CC: <?= htmlspecialchars($pay['document_number']) ?></span>
                                </td>
                                <td class="px-6 py-4 font-mono font-semibold text-slate-800">
                                    <?= formatDateSpanish($pay['payment_date']) ?>
                                </td>
                                <td class="px-6 py-4 font-black text-slate-900 font-mono text-sm">
                                    <?= formatCOP($pay['amount']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px]">
                                        <i class="fa-solid fa-credit-card text-cores-blue mr-1"></i> <?= htmlspecialchars($pay['payment_method']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-700">
                                    <?= htmlspecialchars($pay['period']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge-active px-3 py-1 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i> PAGADO
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-[11px]">
                                    <div class="font-mono"><?= htmlspecialchars($pay['reference_number'] ?? 'N/A') ?></div>
                                    <span class="text-[10px] text-slate-400"><?= htmlspecialchars($pay['notes'] ?? '') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                No se registran pagos en el sistema.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL REGISTRAR PAGO -->
<div id="modalNewPayment" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 max-w-lg w-full rounded-3xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
            <h3 class="text-xl font-black text-slate-900">Registrar Pago de Servicio</h3>
            <button onclick="closeModal('modalNewPayment')" class="text-slate-400 hover:text-slate-700 text-lg"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" action="index.php?action=save_payment" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Seleccionar Cliente *</label>
                <select name="user_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:border-cores-blue focus:bg-white focus:outline-none">
                    <?php foreach ($clients_list as $cl): ?>
                        <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['name']) ?> (CC: <?= htmlspecialchars($cl['document_number']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Valor Pagado (COP) *</label>
                    <input type="number" step="1000" name="amount" required placeholder="75000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha de Pago *</label>
                    <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none font-mono">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Método de Pago *</label>
                    <select name="payment_method" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:border-cores-blue focus:bg-white focus:outline-none">
                        <option value="Efectivo">Efectivo (Sede)</option>
                        <option value="Transferencia Bancolombia">Transferencia Bancolombia</option>
                        <option value="Nequi">Nequi</option>
                        <option value="Daviplata">Daviplata</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Periodo Facturado *</label>
                    <input type="text" name="period" value="<?= date('F Y') ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Número de Referencia / Comprobante</label>
                <input type="text" name="reference_number" placeholder="Ej: TRANS-123456" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalNewPayment')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">CANCELAR</button>
                <button type="submit" class="btn-cores-primary px-6 py-2.5 text-xs font-bold">REGISTRAR PAGO</button>
            </div>
        </form>
    </div>
</div>
