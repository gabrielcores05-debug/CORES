<?php
// views/admin/settings.php - Configuración Institucional y Parámetros de Facturación
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDBConnection();
$settings = $db->query("SELECT * FROM company_settings WHERE id = 1")->fetch() ?: [
    'company_name' => COMPANY_NAME,
    'slogan' => COMPANY_SLOGAN,
    'location' => COMPANY_LOCATION,
    'phone' => COMPANY_PHONE,
    'billing_day' => BILLING_DAY,
    'suspension_day' => SUSPENSION_DAY,
    'collection_day' => COLLECTION_DAY
];
?>

<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Configuración del Sistema</h1>
        <p class="text-xs sm:text-sm text-slate-500 font-medium">Parámetros institucionales y reglas de negocio para CORES COMUNICACIONES S.A.S.</p>
    </div>

    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-md">
        <form method="POST" action="index.php?action=save_settings" class="space-y-6">
            
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-building text-cores-blue"></i> Información Institucional
            </h3>

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre de la Empresa *</label>
                    <input type="text" name="company_name" value="<?= htmlspecialchars($settings['company_name']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Eslogan Oficial *</label>
                    <input type="text" name="slogan" value="<?= htmlspecialchars($settings['slogan']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ubicación y Municipio *</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($settings['location']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono Principal y WhatsApp *</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($settings['phone']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:border-cores-blue focus:bg-white focus:outline-none font-mono">
                </div>
            </div>

            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 pt-4 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-cores-orange"></i> Reglas del Ciclo de Facturación (Días del Mes)
            </h3>

            <div class="grid sm:grid-cols-3 gap-6">
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200/60">
                    <label class="block text-xs font-bold text-cores-blue uppercase mb-1">Día de Facturación *</label>
                    <input type="number" min="1" max="31" name="billing_day" value="<?= (int)$settings['billing_day'] ?>" required class="w-full px-4 py-2 bg-white border border-sky-300 rounded-xl text-sm font-black text-cores-blue focus:outline-none font-mono">
                    <span class="text-[10px] text-slate-500 mt-1 block">Por defecto: <strong>Día 7</strong></span>
                </div>

                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/60">
                    <label class="block text-xs font-bold text-rose-700 uppercase mb-1">Día de Suspensión *</label>
                    <input type="number" min="1" max="31" name="suspension_day" value="<?= (int)$settings['suspension_day'] ?>" required class="w-full px-4 py-2 bg-white border border-rose-300 rounded-xl text-sm font-black text-rose-600 focus:outline-none font-mono">
                    <span class="text-[10px] text-slate-500 mt-1 block">Por defecto: <strong>Día 8</strong></span>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/60">
                    <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Día de Recaudo *</label>
                    <input type="number" min="1" max="31" name="collection_day" value="<?= (int)$settings['collection_day'] ?>" required class="w-full px-4 py-2 bg-white border border-emerald-300 rounded-xl text-sm font-black text-emerald-600 focus:outline-none font-mono">
                    <span class="text-[10px] text-slate-500 mt-1 block">Por defecto: <strong>Día 22</strong></span>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="btn-cores-primary px-8 py-3 text-xs font-bold">
                    GUARDAR CONFIGURACIÓN
                </button>
            </div>
        </form>
    </div>
</div>
