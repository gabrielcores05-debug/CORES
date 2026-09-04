<?php
// views/layouts/header.php - Encabezado Público Oficial
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/auth.php';
$authUser = getAuthUser();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= COMPANY_NAME ?> — Internet en Condoto, Chocó</title>
    <meta name="description" content="CORES COMUNICACIONES S.A.S. - Conectamos tu mundo, impulsamos tu vida. Servicios de Internet de alta velocidad en Condoto, Chocó.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cores: {
                            navy: '#0B132B',
                            dark: '#0F172A',
                            blue: '#0284C7',
                            cyan: '#06B6D4',
                            orange: '#F97316',
                            orangeDark: '#EA580C'
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Estilos Personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased flex flex-col min-h-screen">

    <!-- Topbar Informativo -->
    <div class="bg-cores-navy text-slate-300 text-xs py-2 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-cores-cyan"></i> <?= COMPANY_LOCATION ?></span>
                <span class="hidden sm:inline-block text-slate-600">|</span>
                <span class="hidden sm:flex items-center gap-1.5"><i class="fa-solid fa-users text-cores-orange"></i> <?= COMPANY_APPROX_CLIENTS ?> Clientes Conectados</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="tel:<?= COMPANY_PHONE ?>" class="hover:text-cores-cyan transition flex items-center gap-1.5">
                    <i class="fa-solid fa-phone text-cores-cyan"></i> Atención: <strong><?= COMPANY_PHONE ?></strong>
                </a>
                <a href="<?= getWhatsAppLink() ?>" target="_blank" class="text-emerald-400 hover:text-emerald-300 transition flex items-center gap-1">
                    <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- Navbar Principal -->
    <nav class="bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo Oficial -->
                <a href="index.php" class="flex items-center gap-3 group">
                    <img src="assets/img/logo.png" alt="Logo CORES COMUNICACIONES" class="h-12 w-auto object-contain group-hover:scale-105 transition">
                    <div>
                        <span class="text-xl font-black tracking-tight text-cores-dark block leading-none">CORES</span>
                        <span class="text-[10px] font-bold tracking-widest text-cores-orange uppercase block">COMUNICACIONES S.A.S.</span>
                    </div>
                </a>

                <!-- Enlaces de Navegación -->
                <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-700">
                    <a href="index.php#inicio" class="hover:text-cores-blue transition">Inicio</a>
                    <a href="index.php#planes" class="hover:text-cores-blue transition">Nuestros Planes</a>
                    <a href="index.php#nosotros" class="hover:text-cores-blue transition">¿Por qué CORES?</a>
                    <a href="index.php#ubicacion" class="hover:text-cores-blue transition">Ubicación</a>
                    <a href="index.php#contacto" class="hover:text-cores-blue transition">Contacto</a>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center gap-3">
                    <?php if ($authUser): ?>
                        <?php if ($authUser['role'] === 'admin' || $authUser['role'] === 'operator'): ?>
                            <a href="index.php?view=admin_dashboard" class="btn-cores-blue px-4 py-2.5 text-xs font-bold flex items-center gap-2">
                                <i class="fa-solid fa-gauge-high"></i> Panel Admin
                            </a>
                        <?php else: ?>
                            <a href="index.php?view=client_portal" class="btn-cores-blue px-4 py-2.5 text-xs font-bold flex items-center gap-2">
                                <i class="fa-solid fa-user-check"></i> Mi Portal
                            </a>
                        <?php endif; ?>
                        <a href="index.php?view=logout" class="p-2.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-xl text-xs transition" title="Cerrar Sesión">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    <?php else: ?>
                        <a href="index.php?view=login&type=client" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-cores-dark font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                            <i class="fa-solid fa-user text-cores-blue"></i> SOY CLIENTE
                        </a>
                        <a href="index.php?view=login&type=admin" class="btn-cores-primary px-4 py-2.5 text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-lock"></i> INGRESAR
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
