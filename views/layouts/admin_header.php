<?php
// views/layouts/admin_header.php - Estructura y Sidebar Administrativo
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/auth.php';
requireAdmin();

$user = getAuthUser();
$current_view = $_GET['view'] ?? 'admin_dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Control — <?= COMPANY_NAME ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased flex min-h-screen">

    <!-- SIDEBAR ADMINISTRATIVO -->
    <aside class="w-64 bg-cores-navy text-slate-300 flex-shrink-0 hidden lg:flex flex-col justify-between border-r border-slate-800">
        <div>
            <!-- Header del Sidebar con Logo Oficial -->
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <img src="assets/img/logo.png" alt="CORES Logo" class="h-10 w-auto object-contain">
                <div>
                    <span class="text-lg font-black text-white block leading-none">CORES</span>
                    <span class="text-[9px] font-bold tracking-widest text-cores-orange uppercase block">CENTRO DE CONTROL</span>
                </div>
            </div>

            <!-- Navegación de Módulos -->
            <nav class="p-4 space-y-1.5 text-xs font-bold">
                <a href="index.php?view=admin_dashboard" class="<?= $current_view === 'admin_dashboard' ? 'bg-cores-blue text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fa-solid fa-chart-pie text-sm"></i> DASHBOARD
                </a>
                <a href="index.php?view=admin_users" class="<?= $current_view === 'admin_users' ? 'bg-cores-blue text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fa-solid fa-users text-sm"></i> USUARIOS (+600)
                </a>
                <a href="index.php?view=admin_payments" class="<?= $current_view === 'admin_payments' ? 'bg-cores-blue text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fa-solid fa-money-bill-wave text-sm"></i> PAGOS
                </a>
                <a href="index.php?view=admin_recaudos" class="<?= $current_view === 'admin_recaudos' ? 'bg-cores-blue text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fa-solid fa-sack-dollar text-sm"></i> RECAUDOS
                </a>
                <a href="index.php?view=admin_plans" class="<?= $current_view === 'admin_plans' ? 'bg-cores-blue text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fa-solid fa-wifi text-sm"></i> PLANES
                </a>
                <a href="index.php?view=admin_reports" class="<?= $current_view === 'admin_reports' ? 'bg-cores-blue text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fa-solid fa-file-invoice-dollar text-sm"></i> REPORTES
                </a>
                <a href="index.php?view=admin_settings" class="<?= $current_view === 'admin_settings' ? 'bg-cores-blue text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-4 py-3 rounded-xl transition">
                    <i class="fa-solid fa-sliders text-sm"></i> CONFIGURACIÓN
                </a>
            </nav>
        </div>

        <!-- Footer del Sidebar -->
        <div class="p-4 border-t border-slate-800 space-y-2">
            <a href="index.php" target="_blank" class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-800/60 hover:bg-slate-800 text-slate-300 text-xs transition">
                <span><i class="fa-solid fa-globe text-cores-cyan mr-2"></i> Ver Web Pública</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
            <a href="index.php?view=logout" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold transition">
                <i class="fa-solid fa-right-from-bracket"></i> SALIR
            </a>
        </div>
    </aside>

    <!-- ÁREA DE CONTENIDO PRINCIPAL -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Topbar Administrativa -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-8">
            <!-- Botón menú móvil -->
            <div class="flex items-center gap-4">
                <div class="text-sm font-black text-slate-800 uppercase tracking-tight">
                    <?= COMPANY_NAME ?> &bull; <span class="text-cores-blue font-bold">Condoto, Chocó</span>
                </div>
            </div>

            <!-- Usuario y Accesos -->
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <span class="text-xs font-bold text-slate-900 block"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="text-[10px] font-bold text-cores-orange uppercase tracking-wider block">ADMINISTRADOR</span>
                </div>
                <div class="w-9 h-9 rounded-full bg-cores-blue text-white flex items-center justify-center font-black text-xs shadow-md">
                    <?= strtoupper(substr($user['name'], 0, 2)) ?>
                </div>
            </div>
        </header>

        <!-- Contenido Específico -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-8">
