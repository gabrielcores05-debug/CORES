<?php
// views/auth/login.php - Acceso a la Plataforma (Administración y Clientes)
require_once __DIR__ . '/../../config/config.php';

$activeTab = $_GET['type'] ?? 'client';
?>
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-100">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 space-y-6">
        
        <!-- Logo y Encabezado -->
        <div class="text-center space-y-2">
            <a href="index.php" class="inline-block">
                <img src="assets/img/logo.png" alt="CORES Logo" class="h-16 w-auto mx-auto object-contain">
            </a>
            <h2 class="text-2xl font-black text-slate-900">Ingreso a la Plataforma</h2>
            <p class="text-xs text-slate-500 font-medium"><?= COMPANY_NAME ?></p>
        </div>

        <!-- Selector de Pestañas (Admin / Cliente) -->
        <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-2xl text-xs font-bold text-center">
            <a href="index.php?view=login&type=client" class="<?= $activeTab === 'client' ? 'bg-white text-cores-blue shadow-sm' : 'text-slate-500 hover:text-slate-900' ?> py-2.5 rounded-xl transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-user"></i> Portal Cliente
            </a>
            <a href="index.php?view=login&type=admin" class="<?= $activeTab === 'admin' ? 'bg-cores-navy text-white shadow-sm' : 'text-slate-500 hover:text-slate-900' ?> py-2.5 rounded-xl transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-shield-halved"></i> Administración
            </a>
        </div>

        <?php if ($activeTab === 'client'): ?>
            <!-- FORMULARIO PORTAL CLIENTE -->
            <form method="POST" action="index.php?action=login" class="space-y-4">
                <input type="hidden" name="role_type" value="client">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Número de Documento (Cédula o NIT)</label>
                    <div class="relative">
                        <i class="fa-solid fa-id-card absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                        <input type="text" name="identifier" value="1077100001" required placeholder="Ej: 1077100001" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contraseña</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                        <input type="password" name="password" value="cliente123" required placeholder="Tu contraseña" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1 flex items-center justify-between">
                        <span>Clave por defecto: <strong>cliente123</strong></span>
                    </div>
                </div>

                <button type="submit" class="btn-cores-blue w-full py-3.5 text-sm font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> INGRESAR AL PORTAL
                </button>
            </form>

            <div class="bg-sky-50 border border-sky-200/70 p-4 rounded-2xl text-xs text-sky-900 space-y-1">
                <strong class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-info text-cores-blue"></i> ¿Eres nuevo cliente en Condoto?</strong>
                <p>Tu usuario inicial es tu número de cédula y tu clave provisional es <code>cliente123</code>.</p>
                <a href="<?= getWhatsAppLink("Hola, soy cliente de CORES en Condoto y necesito ayuda para acceder a mi portal.") ?>" target="_blank" class="text-cores-blue font-bold hover:underline inline-block pt-1">
                    Solicitar ayuda por WhatsApp &rarr;
                </a>
            </div>

        <?php else: ?>
            <!-- FORMULARIO ADMINISTRACIÓN -->
            <form method="POST" action="index.php?action=login" class="space-y-4">
                <input type="hidden" name="role_type" value="admin">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Electrónico / Usuario Admin</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                        <input type="text" name="identifier" value="admin@cores.com" required placeholder="admin@cores.com" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contraseña de Administrador</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                        <input type="password" name="password" value="admin123" required placeholder="Tu contraseña" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                    </div>
                </div>

                <button type="submit" class="btn-cores-primary w-full py-3.5 text-sm font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-shield-halved"></i> ACCEDER AL CENTRO DE CONTROL
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center pt-2">
            <a href="index.php" class="text-xs font-semibold text-slate-500 hover:text-cores-blue transition">
                &larr; Volver a la página principal
            </a>
        </div>

    </div>
</div>
