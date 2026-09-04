<?php
// views/layouts/footer.php - Pie de Página Oficial
require_once __DIR__ . '/../../config/config.php';
?>
    <!-- FOOTER INSTITUCIONAL -->
    <footer class="bg-cores-navy text-slate-400 text-sm border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Columna 1: Identidad -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="assets/img/logo.png" alt="CORES Logo" class="h-12 w-auto object-contain">
                        <div>
                            <span class="text-xl font-black text-white tracking-tight block">CORES COMUNICACIONES S.A.S.</span>
                            <span class="text-xs font-bold text-cores-orange tracking-wider uppercase block">"<?= COMPANY_SLOGAN ?>"</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-md">
                        Empresa prestadora de servicios de Internet de alta velocidad en <strong>Condoto, Chocó</strong>. Conectamos hogares, comercios e instituciones con tecnología de fibra óptica y soporte técnico cercano.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="tel:<?= COMPANY_PHONE ?>" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-cores-blue text-white flex items-center justify-center transition">
                            <i class="fa-solid fa-phone"></i>
                        </a>
                        <a href="<?= getWhatsAppLink() ?>" target="_blank" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-emerald-600 text-white flex items-center justify-center transition">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                        <a href="mailto:<?= COMPANY_EMAIL ?>" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-cores-orange text-white flex items-center justify-center transition">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                    </div>
                </div>

                <!-- Columna 2: Enlaces Rápidos -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-4">Navegación</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="index.php#inicio" class="hover:text-cores-cyan transition">INICIO</a></li>
                        <li><a href="index.php#planes" class="hover:text-cores-cyan transition">PLANES DE INTERNET</a></li>
                        <li><a href="index.php#nosotros" class="hover:text-cores-cyan transition">¿POR QUÉ CORES?</a></li>
                        <li><a href="index.php#ubicacion" class="hover:text-cores-cyan transition">UBICACIÓN CONDOTO</a></li>
                        <li><a href="index.php#contacto" class="hover:text-cores-cyan transition">CONTACTO</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Información y Accesos -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-4">Contacto Directo</h4>
                    <div class="space-y-3 text-xs">
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-cores-orange"></i>
                            <span><?= COMPANY_LOCATION ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-cores-cyan"></i>
                            <span>Teléfono: <strong><?= COMPANY_PHONE ?></strong></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-emerald-400"></i>
                            <span>WhatsApp: <strong><?= COMPANY_PHONE ?></strong></span>
                        </p>
                        <div class="pt-2">
                            <a href="index.php?view=login&type=client" class="text-cores-cyan hover:underline font-bold block mb-1">
                                &rarr; Portal del Cliente (Consultar Pagos)
                            </a>
                            <a href="index.php?view=login&type=admin" class="text-cores-orange hover:underline font-bold block">
                                &rarr; Acceso Administrativo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Línea inferior -->
            <div class="border-t border-slate-800/80 mt-12 pt-8 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-400">
                <p>&copy; <?= date('Y') ?> <strong><?= COMPANY_NAME ?></strong>. Todos los derechos reservados. Condoto, Chocó, Colombia.</p>
                <p class="text-[11px] text-slate-400">"<?= COMPANY_SLOGAN ?>"</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
