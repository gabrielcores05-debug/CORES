<?php
// views/admin/users.php - Gestión de Clientes (+600) para CORES COMUNICACIONES S.A.S.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDBConnection();

// Filtros y Búsqueda
$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$filter_plan = $_GET['plan_id'] ?? '';

// Construir Consulta
$query = "
    SELECT u.*, p.name as plan_name, p.speed as plan_speed, p.price as plan_price,
           (SELECT status FROM invoices WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_invoice_status
    FROM users u
    LEFT JOIN plans p ON u.plan_id = p.id
    WHERE u.role = 'client'
";
$params = [];

if ($search !== '') {
    $query .= " AND (u.name LIKE ? OR u.document_number LIKE ? OR u.phone LIKE ? OR u.client_code LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($filter_status !== '') {
    $query .= " AND u.status = ?";
    $params[] = $filter_status;
}

if ($filter_plan !== '') {
    $query .= " AND u.plan_id = ?";
    $params[] = $filter_plan;
}

$query .= " ORDER BY u.id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$clients = $stmt->fetchAll();

// Obtener lista de planes para el formulario y filtros
$all_plans = $db->query("SELECT * FROM plans WHERE status = 'active'")->fetchAll();
?>

<div class="space-y-6">
    
    <!-- Encabezado y Botón Crear -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Gestión de Usuarios</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Administración de clientes conectados en Condoto, Chocó.</p>
        </div>
        <button onclick="openModal('modalNewUser')" class="btn-cores-primary px-5 py-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> REGISTRAR NUEVO CLIENTE
        </button>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="index.php" class="grid sm:grid-cols-12 gap-4 items-center">
            <input type="hidden" name="view" value="admin_users">

            <!-- Buscador -->
            <div class="sm:col-span-5 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por Nombre, Cédula o Teléfono..." class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none transition">
            </div>

            <!-- Filtro por Estado -->
            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:border-cores-blue focus:bg-white focus:outline-none transition">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" <?= $filter_status === 'ACTIVO' ? 'selected' : '' ?>>● ACTIVO</option>
                    <option value="PENDIENTE DE PAGO" <?= $filter_status === 'PENDIENTE DE PAGO' ? 'selected' : '' ?>>● PENDIENTE DE PAGO</option>
                    <option value="SUSPENDIDO" <?= $filter_status === 'SUSPENDIDO' ? 'selected' : '' ?>>● SUSPENDIDO</option>
                </select>
            </div>

            <!-- Filtro por Plan -->
            <div class="sm:col-span-3">
                <select name="plan_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:border-cores-blue focus:bg-white focus:outline-none transition">
                    <option value="">Todos los Planes</option>
                    <?php foreach ($all_plans as $pl): ?>
                        <option value="<?= $pl['id'] ?>" <?= $filter_plan == $pl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pl['name']) ?> (<?= $pl['speed'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Botón Buscar -->
            <div class="sm:col-span-1">
                <button type="submit" class="btn-cores-blue w-full py-2.5 text-xs font-bold flex items-center justify-center">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla Profesional de Clientes -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-black uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Cliente / Documento</th>
                        <th class="px-6 py-4">Teléfono / Ubicación</th>
                        <th class="px-6 py-4">Plan / Velocidad</th>
                        <th class="px-6 py-4">Tarifa (COP)</th>
                        <th class="px-6 py-4">Estado del Servicio</th>
                        <th class="px-6 py-4">Ciclo / Pago</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $c): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                
                                <!-- Nombre y Cédula -->
                                <td class="px-6 py-4">
                                    <div class="font-black text-slate-900 text-sm"><?= htmlspecialchars($c['name']) ?></div>
                                    <span class="text-[11px] font-mono text-cores-blue font-bold"><?= htmlspecialchars($c['document_type']) ?>: <?= htmlspecialchars($c['document_number']) ?></span>
                                    <span class="text-[10px] text-slate-400 block font-mono"><?= htmlspecialchars($c['client_code'] ?? 'CLI-' . $c['id']) ?></span>
                                </td>

                                <!-- Teléfono y Dirección -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 flex items-center gap-1">
                                        <i class="fa-solid fa-phone text-[10px] text-cores-cyan"></i> <?= htmlspecialchars($c['phone']) ?>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($c['neighborhood'] ?? $c['address']) ?></div>
                                </td>

                                <!-- Plan Contratado -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($c['plan_name'] ?? 'Sin Plan') ?></div>
                                    <span class="text-[10px] font-black text-cores-blue uppercase"><?= htmlspecialchars($c['plan_speed'] ?? 'N/A') ?></span>
                                </td>

                                <!-- Precio -->
                                <td class="px-6 py-4 font-black text-slate-900 font-mono">
                                    <?= formatCOP($c['plan_price'] ?? 0) ?>
                                </td>

                                <!-- Estado Visual -->
                                <td class="px-6 py-4">
                                    <?php if ($c['status'] === 'ACTIVO'): ?>
                                        <span class="badge-active px-3 py-1 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ACTIVO
                                        </span>
                                    <?php elseif ($c['status'] === 'PENDIENTE DE PAGO'): ?>
                                        <span class="badge-pending px-3 py-1 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> PENDIENTE
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-suspended px-3 py-1 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> SUSPENDIDO
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Facturación y Estado Pago -->
                                <td class="px-6 py-4">
                                    <div class="text-[11px] text-slate-700">Factura: <strong>Día <?= $c['billing_day'] ?? BILLING_DAY ?></strong></div>
                                    <?php if ($c['last_invoice_status'] === 'PAGADO'): ?>
                                        <span class="text-[10px] font-bold text-emerald-600"><i class="fa-solid fa-check"></i> Al día</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-amber-600"><i class="fa-solid fa-clock"></i> Cobro pendiente</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Botones de Acción -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <!-- Activar Usuario -->
                                        <?php if ($c['status'] !== 'ACTIVO'): ?>
                                            <form method="POST" action="index.php?action=change_user_status" class="inline">
                                                <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="new_status" value="ACTIVO">
                                                <button type="submit" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition" title="Activar Servicio">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Suspender Usuario con Confirmación -->
                                        <?php if ($c['status'] !== 'SUSPENDIDO'): ?>
                                            <form method="POST" action="index.php?action=change_user_status" class="inline" onsubmit="event.preventDefault(); confirmSuspension('<?= htmlspecialchars($c['name']) ?>', this);">
                                                <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="new_status" value="SUSPENDIDO">
                                                <button type="submit" class="p-2 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded-lg transition" title="Suspender Servicio">
                                                    <i class="fa-solid fa-pause"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- WhatsApp directo al cliente -->
                                        <a href="<?= getWhatsAppLink("Hola {$c['name']}, le contactamos de CORES COMUNICACIONES S.A.S. respecto a su servicio de Internet.") ?>" target="_blank" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition" title="Contactar por WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>

                                        <!-- Eliminar Usuario con Confirmación -->
                                        <form method="POST" action="index.php?action=delete_user" class="inline" onsubmit="event.preventDefault(); confirmDeletion('<?= htmlspecialchars($c['name']) ?>', this);">
                                            <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Eliminar Cliente">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-user-slash text-3xl mb-2 block"></i>
                                No se encontraron clientes con los filtros seleccionados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL REGISTRO DE NUEVO CLIENTE -->
<div id="modalNewUser" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 max-w-2xl w-full rounded-3xl p-6 sm:p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-black text-slate-900">Registrar Nuevo Cliente</h3>
                <p class="text-xs text-slate-500">CORES COMUNICACIONES S.A.S. &bull; Condoto, Chocó</p>
            </div>
            <button onclick="closeModal('modalNewUser')" class="text-slate-400 hover:text-slate-700 text-lg"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" action="index.php?action=save_user" class="space-y-4">
            
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre Completo *</label>
                    <input type="text" name="name" required placeholder="Nombre del cliente" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cédula o NIT *</label>
                    <input type="text" name="document_number" required placeholder="Ej: 1077000000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono / WhatsApp *</label>
                    <input type="text" name="phone" required placeholder="300 000 0000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" name="email" placeholder="cliente@correo.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barrio en Condoto *</label>
                    <input type="text" name="neighborhood" required placeholder="Claret, Cascajero, Centro, etc." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dirección de Instalación</label>
                    <input type="text" name="address" placeholder="Calle / Carrera / Manzana" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Plan de Internet *</label>
                    <select name="plan_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:border-cores-blue focus:bg-white focus:outline-none">
                        <?php foreach ($all_plans as $pl): ?>
                            <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?> (<?= $pl['speed'] ?> - <?= formatCOP($pl['price']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Estado Inicial *</label>
                    <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:border-cores-blue focus:bg-white focus:outline-none">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="PENDIENTE DE PAGO">PENDIENTE DE PAGO</option>
                        <option value="SUSPENDIDO">SUSPENDIDO</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalNewUser')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">CANCELAR</button>
                <button type="submit" class="btn-cores-primary px-6 py-2.5 text-xs font-bold">GUARDAR CLIENTE</button>
            </div>
        </form>
    </div>
</div>
