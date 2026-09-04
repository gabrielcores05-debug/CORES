<?php
// views/admin/plans.php - Administración de Planes de Internet
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDBConnection();
$plans = $db->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Planes de Internet</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Administra las tarifas y velocidades ofrecidas en la web comercial.</p>
        </div>
        <button onclick="openModal('modalNewPlan')" class="btn-cores-primary px-5 py-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> CREAR PLAN
        </button>
    </div>

    <!-- Grid de Planes -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($plans as $p): ?>
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-cores-blue uppercase"><?= htmlspecialchars($p['code']) ?></span>
                        <span class="badge-active px-2.5 py-0.5 rounded-full text-[10px] font-bold">Activo</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-1"><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="text-3xl font-black text-cores-blue my-3"><?= htmlspecialchars($p['speed']) ?></div>
                    <div class="text-xl font-black text-slate-900 mb-4"><?= formatCOP($p['price']) ?></div>
                    <p class="text-xs text-slate-500 leading-relaxed"><?= htmlspecialchars($p['features']) ?></p>
                </div>
                <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-semibold"><?= htmlspecialchars($p['technology'] ?? 'Fibra Óptica') ?></span>
                    <span class="text-xs font-bold text-cores-orange"><?= $p['is_featured'] ? 'Destacado' : '' ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL CREAR PLAN -->
<div id="modalNewPlan" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 max-w-lg w-full rounded-3xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
            <h3 class="text-xl font-black text-slate-900">Crear Plan de Internet</h3>
            <button onclick="closeModal('modalNewPlan')" class="text-slate-400 hover:text-slate-700 text-lg"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="index.php?action=save_plan" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre del Plan *</label>
                <input type="text" name="name" required placeholder="Plan Fibra Hogar Max" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Código Único *</label>
                    <input type="text" name="code" required placeholder="PLN-80M" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Velocidad *</label>
                    <input type="text" name="speed" required placeholder="80 Mbps" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tarifa Mensual (COP) *</label>
                    <input type="number" step="1000" name="price" required placeholder="85000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">¿Destacado en Web?</label>
                    <select name="is_featured" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:border-cores-blue focus:bg-white focus:outline-none">
                        <option value="0">No</option>
                        <option value="1">Sí (Destacado)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Características (separadas por coma) *</label>
                <textarea name="features" rows="3" required placeholder="Fibra Óptica, Streaming 4K, Soporte Local" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none"></textarea>
            </div>
            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalNewPlan')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">CANCELAR</button>
                <button type="submit" class="btn-cores-primary px-6 py-2.5 text-xs font-bold">GUARDAR PLAN</button>
            </div>
        </form>
    </div>
</div>
