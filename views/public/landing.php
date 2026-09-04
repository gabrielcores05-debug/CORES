<?php
// views/public/landing.php - Página Comercial Oficial de CORES COMUNICACIONES S.A.S.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';

$db = getDBConnection();
$plans = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM plans WHERE status = 'active' ORDER BY price ASC");
        $plans = $stmt->fetchAll();
    } catch (Exception $e) {
        $plans = [];
    }
}
?>

<!-- ======================================================== -->
<!-- 1. HERO SECTION PRINCIPAL -->
<!-- ======================================================== -->
<section id="inicio" class="relative bg-cores-navy bg-hero-glow text-white py-20 sm:py-28 overflow-hidden border-b border-slate-800">
    <!-- Efectos decorativos de red y fibra -->
    <div class="absolute inset-0 opacity-20 pointer-events-none">
        <div class="absolute top-10 left-10 w-96 h-96 bg-cores-blue rounded-full filter blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-cores-orange rounded-full filter blur-3xl opacity-30 animate-pulse"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            
            <!-- Texto del Hero -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                
                <!-- Badge Institucional -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-cores-cyan text-xs font-bold uppercase tracking-widest shadow-inner">
                    <span class="w-2.5 h-2.5 rounded-full bg-cores-orange animate-ping"></span>
                    <?= COMPANY_NAME ?> &bull; Condoto, Chocó
                </div>

                <!-- Eslogan Oficial -->
                <div class="text-cores-orange font-bold text-sm tracking-widest uppercase">
                    "<?= COMPANY_SLOGAN ?>"
                </div>

                <!-- Título Principal -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Internet que te conecta con <span class="text-gradient-cores">lo que realmente importa.</span>
                </h1>

                <!-- Descripción Oficial -->
                <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Disfruta una conexión rápida y estable para estudiar, trabajar, entretenerte y mantenerte conectado con los tuyos en <strong>Condoto, Chocó</strong>.
                </p>

                <!-- Botones de Acción -->
                <div class="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-4">
                    <a href="#planes" class="btn-cores-primary px-8 py-4 text-sm font-black flex items-center gap-2">
                        <i class="fa-solid fa-wifi"></i> CONOCE NUESTROS PLANES
                    </a>
                    <a href="index.php?view=login&type=client" class="btn-cores-blue px-7 py-4 text-sm font-bold flex items-center gap-2">
                        <i class="fa-solid fa-user-circle"></i> SOY CLIENTE
                    </a>
                    <a href="#contacto" class="px-7 py-4 bg-slate-800/80 hover:bg-slate-700 text-white font-bold text-sm rounded-2xl border border-slate-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-headset text-cores-cyan"></i> CONTÁCTANOS
                    </a>
                </div>

                <!-- Características clave rápidas -->
                <div class="pt-6 grid grid-cols-3 gap-4 border-t border-slate-800/80 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-cores-orange text-base"></i>
                        <span>100% Fibra Óptica</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-cores-cyan text-base"></i>
                        <span>Alta Estabilidad</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone-volume text-emerald-400 text-base"></i>
                        <span>Soporte Local</span>
                    </div>
                </div>
            </div>

            <!-- Visual / Logo Hero -->
            <div class="lg:col-span-5 flex justify-center">
                <div class="relative w-full max-w-md p-8 rounded-3xl bg-slate-900/70 border border-slate-800 shadow-2xl backdrop-blur-xl text-center">
                    <div class="w-48 h-48 mx-auto mb-6 relative flex items-center justify-center">
                        <div class="absolute inset-0 bg-cores-blue/20 rounded-full animate-pulse-ring"></div>
                        <img src="assets/img/logo.png" alt="CORES COMUNICACIONES S.A.S." class="w-40 h-40 object-contain relative z-10 drop-shadow-2xl">
                    </div>
                    <div class="space-y-2">
                        <div class="text-2xl font-black text-white">CORES COMUNICACIONES</div>
                        <div class="text-xs font-bold text-cores-orange tracking-widest uppercase">CONECTIVIDAD EN CONDOTO</div>
                        <p class="text-xs text-slate-400 pt-2">Línea directa de atención:</p>
                        <a href="tel:<?= COMPANY_PHONE ?>" class="text-xl font-extrabold text-cores-cyan hover:underline inline-block">
                            <?= COMPANY_PHONE ?>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ======================================================== -->
<!-- 2. SECCIÓN +600 CLIENTES CONECTADOS -->
<!-- ======================================================== -->
<section class="py-16 bg-white border-b border-slate-200 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-slate-900 via-cores-navy to-slate-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl border border-slate-800">
            <div class="grid md:grid-cols-12 gap-8 items-center text-center md:text-left">
                
                <!-- Número Protagonista -->
                <div class="md:col-span-5 border-b md:border-b-0 md:border-r border-slate-800 pb-6 md:pb-0 md:pr-8">
                    <div class="text-6xl sm:text-7xl font-black text-gradient-orange tracking-tight leading-none mb-2">
                        <?= COMPANY_APPROX_CLIENTS ?>
                    </div>
                    <div class="text-xl sm:text-2xl font-extrabold text-white tracking-tight uppercase">
                        CLIENTES CONECTADOS
                    </div>
                </div>

                <!-- Mensaje de Confianza Local -->
                <div class="md:col-span-7 space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cores-blue/20 text-cores-cyan text-xs font-bold uppercase">
                        <i class="fa-solid fa-location-dot"></i> <?= COMPANY_CITY ?>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-bold text-white leading-snug">
                        Conectando nuestra comunidad con calidad y compromiso.
                    </h3>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Somos la red que impulsa los hogares, comercios e instituciones de Condoto, Chocó. Con un servicio cercano, soporte presencial y tecnología de vanguardia.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ======================================================== -->
<!-- 3. SECCIÓN ¿POR QUÉ ELEGIR CORES? -->
<!-- ======================================================== -->
<section id="nosotros" class="py-24 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-bold text-cores-orange uppercase tracking-widest">NUESTROS BENEFICIOS</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                ¿POR QUÉ ELEGIR CORES?
            </h2>
            <p class="text-slate-600 text-sm sm:text-base">
                Conoce las ventajas de contar con el proveedor de Internet que comprende las necesidades reales de nuestra comunidad en Condoto.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Beneficio 1: Buena velocidad -->
            <div class="card-tech p-8 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 text-cores-blue flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Buena velocidad de conexión</h3>
                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                    Navega a altas velocidades constantes para transferir archivos pesados, ver contenido multimedia y navegar sin demoras.
                </p>
            </div>

            <!-- Beneficio 2: Conexión estable -->
            <div class="card-tech p-8 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-cores-orange flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Conexión estable</h3>
                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                    Infraestructura moderna de fibra óptica que minimiza caídas y mantiene la continuidad de tu servicio las 24 horas.
                </p>
            </div>

            <!-- Beneficio 3: Hogares y Negocios -->
            <div class="card-tech p-8 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-cyan-50 text-cores-cyan flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-house-laptop"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Servicio para hogares y negocios</h3>
                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                    Planes a la medida para familias de todos los tamaños y soluciones de conectividad para el comercio local.
                </p>
            </div>

            <!-- Beneficio 4: Atención y Soporte Cercano -->
            <div class="card-tech p-8 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-hand-holding-hand"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Atención al cliente y soporte cercano</h3>
                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                    Equipo técnico local en Condoto disponible para resolver dudas, mantenimientos e instalaciones de forma oportuna.
                </p>
            </div>

            <!-- Beneficio 5: Empresa Local -->
            <div class="card-tech p-8 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-building-flag"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Empresa local de Condoto</h3>
                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                    Invertimos en el desarrollo de nuestra región, generando empleo y conectando el territorio chocoano.
                </p>
            </div>

            <!-- Beneficio 6: Estudio, Trabajo y Streaming -->
            <div class="card-tech p-8 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-tv"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Estudio, teletrabajo y entretenimiento</h3>
                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                    Baja latencia ideal para videollamadas de estudio, trabajo remoto, plataformas de streaming y juegos online.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- ======================================================== -->
<!-- 4. SECCIÓN DE PLANES DE INTERNET -->
<!-- ======================================================== -->
<section id="planes" class="py-24 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-bold text-cores-blue uppercase tracking-widest">TARIFAS Y VELOCIDADES</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                NUESTROS PLANES
            </h2>
            <p class="text-slate-600 text-sm sm:text-base">
                Planes dinámicos con velocidad simétrica y tecnología de fibra óptica en Condoto, Chocó.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 items-stretch">
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $plan): 
                    $isFeatured = (bool)$plan['is_featured'];
                    $features = array_map('trim', explode(',', $plan['features']));
                    $planMsg = "Hola CORES COMUNICACIONES, deseo contratar el {$plan['name']} ({$plan['speed']}) por " . formatCOP($plan['price']) . " mensuales para Condoto.";
                ?>
                    <div class="<?= $isFeatured ? 'card-tech-featured' : 'card-tech' ?> p-8 flex flex-col justify-between">
                        
                        <?php if ($isFeatured): ?>
                            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-cores-orange text-white text-[10px] font-black uppercase px-4 py-1 rounded-full shadow-md">
                                PLAN MÁS POPULAR
                            </div>
                        <?php endif; ?>

                        <div>
                            <!-- Encabezado del Plan -->
                            <div class="mb-4">
                                <h3 class="text-xl font-bold text-slate-900"><?= htmlspecialchars($plan['name']) ?></h3>
                                <span class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($plan['technology'] ?? 'Fibra Óptica') ?></span>
                            </div>

                            <!-- Velocidad Destacada -->
                            <div class="py-4 my-4 border-y border-slate-100 flex items-baseline gap-2">
                                <span class="text-4xl font-black text-cores-blue tracking-tight"><?= htmlspecialchars($plan['speed']) ?></span>
                            </div>

                            <!-- Precio Mensual -->
                            <div class="mb-6">
                                <div class="text-2xl font-black text-slate-900"><?= formatCOP($plan['price']) ?></div>
                                <span class="text-[11px] text-slate-500">Tarifa mensual fija</span>
                            </div>

                            <!-- Lista de Características -->
                            <ul class="space-y-3 text-xs text-slate-600 mb-8">
                                <?php foreach ($features as $feat): ?>
                                    <li class="flex items-start gap-2.5">
                                        <i class="fa-solid fa-circle-check text-cores-orange text-sm mt-0.5"></i>
                                        <span><?= htmlspecialchars($feat) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="space-y-2.5 pt-4 border-t border-slate-100">
                            <a href="<?= getWhatsAppLink($planMsg) ?>" target="_blank" class="<?= $isFeatured ? 'btn-cores-primary' : 'btn-cores-blue' ?> block w-full py-3.5 text-center text-xs font-bold">
                                <i class="fa-brands fa-whatsapp mr-1.5"></i> CONTRATAR
                            </a>
                            <a href="<?= getWhatsAppLink("Hola, deseo más información sobre el " . $plan['name']) ?>" target="_blank" class="block w-full py-2.5 text-center text-xs text-slate-600 hover:text-cores-blue font-semibold">
                                MÁS INFORMACIÓN &rarr;
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-4 text-center py-12 text-slate-500">
                    <p>No se encontraron planes configurados actualmente en la base de datos.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- ======================================================== -->
<!-- 5. SECCIÓN UBICACIÓN (CONDOTO, CHOCÓ) -->
<!-- ======================================================== -->
<section id="ubicacion" class="py-24 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-bold text-cores-cyan uppercase tracking-widest">COBERTURA TERRITORIAL</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                ESTAMOS EN CONDOTO, CHOCÓ
            </h2>
            <p class="text-slate-600 text-sm sm:text-base">
                "Conectamos nuestra comunidad desde Condoto, Chocó."
            </p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8 items-center">
            
            <!-- Mapa Interactivo Leaflet -->
            <div class="lg:col-span-8 bg-white p-4 rounded-3xl border border-slate-200 shadow-lg">
                <div id="map"></div>
                <div class="pt-4 px-2 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                    <span><i class="fa-solid fa-map-pin text-cores-orange mr-1"></i> Marcador: Sede Operativa Condoto</span>
                    <span class="font-semibold text-slate-700">Coordenadas: 5.0939° N, 76.6508° W</span>
                </div>
            </div>

            <!-- Mapa Territorial & Información -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-md">
                    <h3 class="font-bold text-slate-900 text-base mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-map text-cores-blue"></i> Mapa del Municipio de Condoto
                    </h3>
                    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 p-2 mb-4">
                        <img src="assets/img/condoto_map.png" alt="Mapa Municipal de Condoto Chocó" class="w-full h-auto object-contain max-h-48 mx-auto">
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Despliegue progresivo de fibra óptica en los principales barrios y zonas comerciales de Condoto.
                    </p>
                </div>

                <div class="bg-cores-navy text-white p-6 rounded-3xl border border-slate-800 shadow-md space-y-3">
                    <div class="text-xs font-bold text-cores-orange uppercase">ATENCIÓN PRESENCIAL</div>
                    <div class="text-sm font-bold text-white"><?= COMPANY_NAME ?></div>
                    <p class="text-xs text-slate-400">Condoto, Chocó, Colombia</p>
                    <div class="pt-2">
                        <a href="<?= getWhatsAppLink() ?>" target="_blank" class="btn-cores-primary px-4 py-2 text-xs font-bold inline-flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp"></i> Consultar Cobertura
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ======================================================== -->
<!-- 6. SECCIÓN DE CONTACTO -->
<!-- ======================================================== -->
<section id="contacto" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-bold text-cores-orange uppercase tracking-widest">ATENCIÓN INMEDIATA</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                CONTÁCTANOS
            </h2>
            <p class="text-slate-600 text-sm sm:text-base">
                Estamos listos para asesorarte y llevar la mejor conexión a tu hogar o empresa.
            </p>
        </div>

        <div class="grid lg:grid-cols-12 gap-12">
            
            <!-- Datos de Contacto Directos -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="card-tech p-8 space-y-6">
                    <div>
                        <div class="text-xl font-black text-slate-900"><?= COMPANY_NAME ?></div>
                        <span class="text-xs font-bold text-cores-blue"><?= COMPANY_CITY ?></span>
                    </div>

                    <div class="space-y-4 text-sm">
                        <!-- Teléfono Principal -->
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="w-10 h-10 rounded-xl bg-sky-100 text-cores-blue flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 font-bold uppercase block">TELÉFONO DE ATENCIÓN</span>
                                <a href="tel:<?= COMPANY_PHONE ?>" class="text-lg font-extrabold text-slate-900 hover:text-cores-blue transition">
                                    <?= COMPANY_PHONE ?>
                                </a>
                            </div>
                        </div>

                        <!-- Botón WhatsApp Directo -->
                        <a href="<?= getWhatsAppLink() ?>" target="_blank" class="block w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white text-center font-bold rounded-2xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                            <i class="fa-brands fa-whatsapp text-lg mr-2"></i> CONTACTAR POR WHATSAPP (<?= COMPANY_PHONE ?>)
                        </a>

                        <!-- Soporte al Cliente -->
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/60 text-xs text-amber-900 space-y-1">
                            <strong class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-clock"></i> Horarios de Atención:</strong>
                            <p>Lunes a Sábado: 8:00 AM - 6:00 PM</p>
                            <p>Condoto, Chocó, Colombia</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Formulario de Mensajes -->
            <div class="lg:col-span-7">
                <div class="card-tech p-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-envelope text-cores-orange"></i> Envíanos un Mensaje
                    </h3>
                    
                    <form method="POST" action="index.php?action=send_contact" class="space-y-5">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nombre Completo *</label>
                                <input type="text" name="name" required placeholder="Tu nombre" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Teléfono / WhatsApp *</label>
                                <input type="text" name="phone" required placeholder="300 000 0000" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Correo Electrónico</label>
                                <input type="email" name="email" placeholder="correo@ejemplo.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Asunto *</label>
                                <input type="text" name="subject" required placeholder="Solicitud de instalación" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Mensaje o Barrio en Condoto *</label>
                            <textarea name="message" rows="4" required placeholder="Indícanos tu dirección o consulta..." class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:border-cores-blue focus:bg-white focus:outline-none transition"></textarea>
                        </div>

                        <button type="submit" class="btn-cores-primary w-full py-4 text-sm font-bold flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> ENVIAR SOLICITUD
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>
</section>
