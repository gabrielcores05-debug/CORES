<?php
// index.php - Plataforma Web CORES
session_start();
require_once __DIR__ . '/config/database.php';

// Verificar conexión a base de datos
$db = getDBConnection();
$db_installed = ($db !== null);

// Manejo de Mensajes Flash
function set_flash($type, $text) {
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Helper de Auditoría
function log_audit($db, $action, $module, $details) {
    if (!$db) return;
    $user_name = $_SESSION['user']['name'] ?? 'Visitante/Sistema';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $db->prepare("INSERT INTO audit_logs (user_name, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_name, $action, $module, $details, $ip]);
}

// Router Simple
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// --- CONTROLADOR DE ACCIONES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    
    // 1. Iniciar Sesión
    if (isset($_POST['action_login'])) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $db->prepare("SELECT u.*, r.slug as role_slug, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role_slug'],
                'role_name' => $user['role_name'],
                'position' => $user['position']
            ];
            log_audit($db, 'Login', 'Autenticación', 'Inicio de sesión exitoso');
            set_flash('success', '¡Bienvenido de nuevo, ' . htmlspecialchars($user['name']) . '!');
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            set_flash('error', 'Credenciales incorrectas o usuario inactivo.');
            header('Location: index.php?page=login');
            exit;
        }
    }

    // 2. Registro de Usuario
    if (isset($_POST['action_register'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');

        if ($name && $email && $password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            try {
                $stmt = $db->prepare("INSERT INTO users (name, email, password, role_id, phone, position, status) VALUES (?, ?, ?, 3, ?, 'Usuario Registrado', 'active')");
                $stmt->execute([$name, $email, $hash, $phone]);
                log_audit($db, 'Registro', 'Usuarios', "Nuevo usuario registrado: $email");
                set_flash('success', 'Cuenta creada exitosamente. Ya puedes iniciar sesión.');
                header('Location: index.php?page=login');
                exit;
            } catch (PDOException $e) {
                set_flash('error', 'El correo ya se encuentra registrado.');
                header('Location: index.php?page=register');
                exit;
            }
        }
    }

    // 3. Crear / Editar Proyecto (CRUD)
    if (isset($_POST['action_save_project']) && isset($_SESSION['user'])) {
        if ($_SESSION['user']['role'] === 'viewer') {
            set_flash('error', 'No tienes permisos para modificar proyectos.');
            header('Location: index.php?page=projects');
            exit;
        }

        $id = intval($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = floatval($_POST['budget'] ?? 0);
        $status_p = $_POST['status'] ?? 'Planificación';
        $progress = intval($_POST['progress'] ?? 0);
        $start_date = $_POST['start_date'] ?: null;
        $end_date = $_POST['end_date'] ?: null;

        if ($id > 0) {
            // Actualizar
            $stmt = $db->prepare("UPDATE projects SET title=?, code=?, category=?, description=?, budget=?, status=?, progress=?, start_date=?, end_date=? WHERE id=?");
            $stmt->execute([$title, $code, $category, $description, $budget, $status_p, $progress, $start_date, $end_date, $id]);
            log_audit($db, 'Actualizar', 'Proyectos', "Proyecto actualizado: $title ($code)");
            set_flash('success', 'Proyecto actualizado correctamente.');
        } else {
            // Crear
            $stmt = $db->prepare("INSERT INTO projects (title, code, category, description, budget, status, progress, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $code, $category, $description, $budget, $status_p, $progress, $start_date, $end_date, $_SESSION['user']['id']]);
            log_audit($db, 'Crear', 'Proyectos', "Nuevo proyecto creado: $title ($code)");
            set_flash('success', 'Proyecto registrado exitosamente.');
        }
        header('Location: index.php?page=projects');
        exit;
    }

    // 4. Eliminar Proyecto
    if (isset($_POST['action_delete_project']) && isset($_SESSION['user'])) {
        if ($_SESSION['user']['role'] !== 'admin') {
            set_flash('error', 'Solo los administradores pueden eliminar registros.');
            header('Location: index.php?page=projects');
            exit;
        }
        $id = intval($_POST['project_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($db, 'Eliminar', 'Proyectos', "Proyecto ID #$id eliminado");
        set_flash('success', 'Proyecto eliminado del sistema.');
        header('Location: index.php?page=projects');
        exit;
    }

    // 5. Enviar Mensaje de Contacto
    if (isset($_POST['action_contact'])) {
        $c_name = trim($_POST['name'] ?? '');
        $c_email = trim($_POST['email'] ?? '');
        $c_subject = trim($_POST['subject'] ?? '');
        $c_message = trim($_POST['message'] ?? '');

        if ($c_name && $c_email && $c_message) {
            $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$c_name, $c_email, $c_subject, $c_message]);
            log_audit($db, 'Mensaje', 'Contacto', "Mensaje recibido de: $c_email");
            set_flash('success', '¡Gracias por contactarnos! Tu mensaje ha sido enviado exitosamente.');
            header('Location: index.php?page=home#contacto');
            exit;
        }
    }
}

// Cierre de Sesión
if ($page === 'logout') {
    if ($db && isset($_SESSION['user'])) {
        log_audit($db, 'Logout', 'Autenticación', 'Cierre de sesión');
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

// Exportar CSV de Reportes
if ($page === 'export_csv' && isset($_SESSION['user']) && $db) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_proyectos_cores_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    fputcsv($output, ['ID', 'Código', 'Título', 'Categoría', 'Presupuesto (COP)', 'Estado', 'Progreso (%)', 'Fecha Inicio', 'Fecha Fin']);
    
    $stmt = $db->query("SELECT id, code, title, category, budget, status, progress, start_date, end_date FROM projects ORDER BY id DESC");
    while ($row = $stmt->fetch()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

$flash = get_flash();
$current_user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CORES - Sistema de Gestión y Comunicaciones</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            900: '#78350f',
                            dark: '#0f172a',
                            darker: '#090d16'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex flex-col antialiased">

    <!-- AVISO DE BASE DE DATOS PENDIENTE -->
    <?php if (!$db_installed): ?>
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 px-4 py-3 text-center text-sm font-bold flex items-center justify-center gap-3 shadow-lg">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span>La base de datos CORES aún no está inicializada.</span>
            <a href="install.php" class="bg-slate-950 text-amber-400 hover:bg-slate-900 px-4 py-1 rounded-full text-xs font-black transition">
                <i class="fa-solid fa-bolt mr-1"></i> Instalar con 1 Clic
            </a>
        </div>
    <?php endif; ?>

    <!-- NAVBAR PRINCIPAL -->
    <nav class="bg-slate-900/90 backdrop-blur border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 font-black rounded-xl flex items-center justify-center text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 transition">
                        C
                    </div>
                    <div>
                        <span class="text-2xl font-black tracking-tight text-white">CORES<span class="text-amber-500">.</span></span>
                        <p class="text-[10px] text-slate-400 font-medium tracking-widest uppercase -mt-1">Gestión & Medios</p>
                    </div>
                </a>

                <!-- Enlaces de Navegación -->
                <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                    <a href="index.php#inicio" class="text-slate-300 hover:text-amber-400 transition">Inicio</a>
                    <a href="index.php#servicios" class="text-slate-300 hover:text-amber-400 transition">Módulos & Servicios</a>
                    <a href="index.php#nosotros" class="text-slate-300 hover:text-amber-400 transition">Institucional</a>
                    <a href="index.php#contacto" class="text-slate-300 hover:text-amber-400 transition">Contacto</a>
                </div>

                <!-- Botones de Acción / Usuario -->
                <div class="flex items-center gap-3">
                    <?php if ($current_user): ?>
                        <a href="index.php?page=dashboard" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 rounded-xl text-sm font-semibold flex items-center gap-2 transition">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span class="hidden sm:inline">Panel:</span> <?= htmlspecialchars($current_user['name']) ?>
                        </a>
                        <a href="index.php?page=logout" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-xl text-sm transition" title="Cerrar Sesión">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=login" class="px-4 py-2 text-slate-300 hover:text-white text-sm font-medium transition">
                            Iniciar Sesión
                        </a>
                        <a href="index.php?page=register" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold rounded-xl text-sm shadow-lg shadow-amber-500/20 transition transform hover:-translate-y-0.5">
                            Registrarse
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- ALERTAS FLASH -->
    <?php if ($flash): ?>
        <div class="max-w-4xl mx-auto px-4 mt-6 w-full">
            <div class="<?= $flash['type'] === 'success' ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-400' : 'bg-rose-500/10 border-rose-500/40 text-rose-400' ?> border px-5 py-4 rounded-2xl flex items-center justify-between text-sm shadow-lg">
                <div class="flex items-center gap-3">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> text-lg"></i>
                    <span><?= htmlspecialchars($flash['text']) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- CONTENIDO PRINCIPAL SEGÚN PÁGINA -->
    <main class="flex-grow">
        <?php if ($page === 'home'): ?>
            <!-- HERO SECTION -->
            <section id="inicio" class="relative py-24 lg:py-32 overflow-hidden border-b border-slate-800">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/10 via-slate-950 to-slate-950 -z-10"></div>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold uppercase tracking-wider mb-6">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Ecosistema Digital Centralizado
                    </div>
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white max-w-5xl mx-auto leading-tight">
                        Transformación, Gestión y <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600">Comunicaciones Estratégicas</span>
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-3xl mx-auto leading-relaxed">
                        Plataforma institucional <strong>CORES</strong>: articulamos proyectos, fortalecemos medios de comunicación territoriales y garantizamos el seguimiento integral de iniciativas.
                    </p>
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        <?php if ($current_user): ?>
                            <a href="index.php?page=dashboard" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold rounded-2xl shadow-xl shadow-amber-500/25 transition transform hover:-translate-y-0.5 text-base flex items-center gap-2">
                                <i class="fa-solid fa-gauge-high"></i> Ir al Panel de Control
                            </a>
                        <?php else: ?>
                            <a href="index.php?page=login" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold rounded-2xl shadow-xl shadow-amber-500/25 transition transform hover:-translate-y-0.5 text-base flex items-center gap-2">
                                <i class="fa-solid fa-right-to-bracket"></i> Acceder a la Plataforma
                            </a>
                        <?php endif; ?>
                        <a href="#servicios" class="px-8 py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold border border-slate-700 rounded-2xl transition text-base">
                            Explorar Módulos
                        </a>
                    </div>
                </div>
            </section>

            <!-- METRICAS / KPIS DESTACADOS -->
            <section class="py-16 bg-slate-900/50 border-b border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-4xl font-extrabold text-amber-400 mb-2">100%</div>
                            <div class="text-sm font-semibold text-slate-300">Trazabilidad en Línea</div>
                            <p class="text-xs text-slate-500 mt-1">Control de iniciativas en tiempo real</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-4xl font-extrabold text-amber-400 mb-2">+150</div>
                            <div class="text-sm font-semibold text-slate-300">Líderes Comunicadores</div>
                            <p class="text-xs text-slate-500 mt-1">Redes y comunidades activas</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-4xl font-extrabold text-amber-400 mb-2">3 Niveles</div>
                            <div class="text-sm font-semibold text-slate-300">Seguridad por Roles</div>
                            <p class="text-xs text-slate-500 mt-1">Admin, Operador y Consulta</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-4xl font-extrabold text-amber-400 mb-2">24/7</div>
                            <div class="text-sm font-semibold text-slate-300">Disponibilidad Web</div>
                            <p class="text-xs text-slate-500 mt-1">Acceso seguro desde cualquier lugar</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- MODULOS & SERVICIOS -->
            <section id="servicios" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Módulos del Sistema CORES</h2>
                    <p class="mt-4 text-slate-400">Arquitectura integral diseñada para la optimización de procesos y la comunicación institucional.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Card 1 -->
                    <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition group">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                            <i class="fa-solid fa-folder-tree"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Gestión de Proyectos</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">Registro, monitoreo de presupuestos, cronogramas, responsables y estados de avance porcentual.</p>
                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1">CRUD Completo &rarr;</span>
                    </div>
                    <!-- Card 2 -->
                    <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition group">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Roles y Usuarios</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">Administración granular de accesos con privilegios específicos para Administradores, Operadores y Lectores.</p>
                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1">Seguridad Avanzada &rarr;</span>
                    </div>
                    <!-- Card 3 -->
                    <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition group">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Reportes & Auditoría</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">Generación de métricas analíticas, trazabilidad de cambios por usuario y exportación de datos en formato CSV.</p>
                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1">Exportación en 1 Clic &rarr;</span>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN INSTITUCIONAL / SOBRE NOSOTROS -->
            <section id="nosotros" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 text-amber-400 text-xs font-bold uppercase tracking-widest mb-3">
                            <i class="fa-solid fa-award"></i> Marco Institucional
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight mb-6">
                            Impulsando el desarrollo y la identidad comunitaria
                        </h2>
                        <div class="space-y-6 text-slate-400 text-sm leading-relaxed">
                            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                                <h4 class="font-bold text-white text-base mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-bullseye text-amber-400"></i> Misión
                                </h4>
                                <p>Consolidar un ecosistema digital y comunicacional eficiente que permita planificar, ejecutar y evaluar iniciativas de alto impacto territorial y comunitario.</p>
                            </div>
                            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                                <h4 class="font-bold text-white text-base mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-eye text-amber-400"></i> Visión
                                </h4>
                                <p>Ser el referente tecnológico de gestión y divulgación para organizaciones y medios comunitarios, promoviendo la transparencia y la efectividad operativa.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-8 rounded-3xl border border-slate-700 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6">¿Por qué utilizar CORES?</h3>
                        <ul class="space-y-4 text-sm text-slate-300">
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check text-amber-400 mt-1"></i>
                                <span><strong>Centralización de datos:</strong> Adiós a las hojas de cálculo dispersas.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check text-amber-400 mt-1"></i>
                                <span><strong>Bitácora de auditoría:</strong> Cada cambio queda registrado con usuario, fecha e IP.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check text-amber-400 mt-1"></i>
                                <span><strong>Diseño responsive:</strong> Visualización óptima en computadores, tablets y smartphones.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check text-amber-400 mt-1"></i>
                                <span><strong>Seguridad nativa:</strong> Protección de contraseñas con cifrado bcrypt y validación estricta.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- CONTACTO -->
            <section id="contacto" class="py-24 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-extrabold text-white">Contáctanos</h2>
                    <p class="mt-2 text-slate-400 text-sm">¿Tienes preguntas o deseas implementar CORES en tu organización?</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-xl">
                    <form method="POST">
                        <div class="grid sm:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nombre Completo</label>
                                <input type="text" name="name" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Correo Electrónico</label>
                                <input type="email" name="email" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Asunto</label>
                            <input type="text" name="subject" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:border-amber-500 focus:outline-none">
                        </div>
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mensaje</label>
                            <textarea name="message" rows="4" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:border-amber-500 focus:outline-none"></textarea>
                        </div>
                        <button type="submit" name="action_contact" value="1" class="w-full py-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold rounded-xl shadow-lg transition">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Enviar Mensaje
                        </button>
                    </form>
                </div>
            </section>

        <?php elseif ($page === 'login'): ?>
            <!-- PÁGINA DE LOGIN -->
            <div class="min-h-[75vh] flex items-center justify-center p-4">
                <div class="max-w-md w-full bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-500 text-slate-950 font-black rounded-2xl text-2xl shadow-lg mb-3">C</div>
                        <h2 class="text-2xl font-bold text-white">Acceso al Sistema</h2>
                        <p class="text-xs text-slate-400 mt-1">Ingresa tus credenciales de CORES</p>
                    </div>
                    <form method="POST" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Correo Electrónico</label>
                            <input type="email" name="email" value="admin@cores.com" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Contraseña</label>
                            <input type="password" name="password" value="admin123" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:border-amber-500 focus:outline-none">
                        </div>
                        <button type="submit" name="action_login" value="1" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold rounded-xl shadow-lg transition">
                            <i class="fa-solid fa-right-to-bracket mr-2"></i> Iniciar Sesión
                        </button>
                    </form>
                    <div class="mt-6 text-center text-xs text-slate-400">
                        ¿No tienes cuenta? <a href="index.php?page=register" class="text-amber-400 hover:underline font-bold">Regístrate aquí</a>
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'register'): ?>
            <!-- PÁGINA DE REGISTRO -->
            <div class="min-h-[75vh] flex items-center justify-center p-4">
                <div class="max-w-md w-full bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-500 text-slate-950 font-black rounded-2xl text-2xl shadow-lg mb-3">C</div>
                        <h2 class="text-2xl font-bold text-white">Crear Nueva Cuenta</h2>
                        <p class="text-xs text-slate-400 mt-1">Únete a la plataforma institucional</p>
                    </div>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nombre Completo</label>
                            <input type="text" name="name" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Correo Electrónico</label>
                            <input type="email" name="email" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Teléfono</label>
                            <input type="text" name="phone" placeholder="+57 300 000 0000" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Contraseña</label>
                            <input type="password" name="password" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                        </div>
                        <button type="submit" name="action_register" value="1" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold rounded-xl shadow-lg transition mt-2">
                            <i class="fa-solid fa-user-plus mr-2"></i> Crear Cuenta
                        </button>
                    </form>
                    <div class="mt-6 text-center text-xs text-slate-400">
                        ¿Ya tienes cuenta? <a href="index.php?page=login" class="text-amber-400 hover:underline font-bold">Iniciar Sesión</a>
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'dashboard' || $page === 'projects' || $page === 'users' || $page === 'audit' || $page === 'reports'): ?>
            <!-- PANEL ADMINISTRATIVO AUTENTICADO -->
            <?php
            if (!$current_user) {
                set_flash('error', 'Debes iniciar sesión para acceder al panel.');
                header('Location: index.php?page=login');
                exit;
            }
            ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Barra de navegación interna -->
                <div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900 p-4 rounded-2xl border border-slate-800 mb-8">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-sm font-semibold">
                        <a href="index.php?page=dashboard" class="px-4 py-2 rounded-xl transition <?= $page === 'dashboard' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-300 hover:bg-slate-800' ?>">
                            <i class="fa-solid fa-chart-pie mr-1"></i> Dashboard
                        </a>
                        <a href="index.php?page=projects" class="px-4 py-2 rounded-xl transition <?= $page === 'projects' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-300 hover:bg-slate-800' ?>">
                            <i class="fa-solid fa-folder-tree mr-1"></i> Proyectos
                        </a>
                        <?php if ($current_user['role'] === 'admin'): ?>
                            <a href="index.php?page=users" class="px-4 py-2 rounded-xl transition <?= $page === 'users' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-300 hover:bg-slate-800' ?>">
                                <i class="fa-solid fa-users-gear mr-1"></i> Usuarios
                            </a>
                            <a href="index.php?page=audit" class="px-4 py-2 rounded-xl transition <?= $page === 'audit' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-300 hover:bg-slate-800' ?>">
                                <i class="fa-solid fa-clock-rotate-left mr-1"></i> Auditoría
                            </a>
                        <?php endif; ?>
                        <a href="index.php?page=reports" class="px-4 py-2 rounded-xl transition <?= $page === 'reports' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-300 hover:bg-slate-800' ?>">
                            <i class="fa-solid fa-file-lines mr-1"></i> Reportes
                        </a>
                    </div>
                    <div class="text-xs text-slate-400 flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-amber-400 font-bold uppercase">
                            <?= htmlspecialchars($current_user['role_name']) ?>
                        </span>
                    </div>
                </div>

                <?php if ($page === 'dashboard'): ?>
                    <!-- VISTA DASHBOARD -->
                    <?php
                    $total_projects = $db ? $db->query("SELECT COUNT(*) FROM projects")->fetchColumn() : 0;
                    $total_budget = $db ? $db->query("SELECT SUM(budget) FROM projects")->fetchColumn() : 0;
                    $total_users = $db ? $db->query("SELECT COUNT(*) FROM users")->fetchColumn() : 0;
                    $completed_p = $db ? $db->query("SELECT COUNT(*) FROM projects WHERE status = 'Completado'")->fetchColumn() : 0;
                    $in_progress_p = $db ? $db->query("SELECT COUNT(*) FROM projects WHERE status = 'En Progreso'")->fetchColumn() : 0;
                    $planning_p = $db ? $db->query("SELECT COUNT(*) FROM projects WHERE status = 'Planificación'")->fetchColumn() : 0;
                    ?>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Proyectos</div>
                            <div class="text-3xl font-extrabold text-white"><?= $total_projects ?></div>
                            <div class="text-xs text-amber-400 mt-2"><i class="fa-solid fa-arrow-trend-up mr-1"></i> Registros activos</div>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Presupuesto Global</div>
                            <div class="text-2xl font-extrabold text-amber-400">$<?= number_format($total_budget, 0, ',', '.') ?> COP</div>
                            <div class="text-xs text-slate-400 mt-2">Inversión acumulada</div>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Usuarios Registrados</div>
                            <div class="text-3xl font-extrabold text-white"><?= $total_users ?></div>
                            <div class="text-xs text-emerald-400 mt-2"><i class="fa-solid fa-circle-check mr-1"></i> Con acceso al sistema</div>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Completados</div>
                            <div class="text-3xl font-extrabold text-emerald-400"><?= $completed_p ?></div>
                            <div class="text-xs text-slate-400 mt-2"><?= $in_progress_p ?> en ejecución activa</div>
                        </div>
                    </div>

                    <!-- Gráficos y Acciones Rápidas -->
                    <div class="grid lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 p-6 rounded-3xl bg-slate-900 border border-slate-800">
                            <h3 class="text-lg font-bold text-white mb-4">Estado de Proyectos</h3>
                            <div class="h-64 flex items-center justify-center">
                                <canvas id="projectsChart"></canvas>
                            </div>
                        </div>
                        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
                            <h3 class="text-lg font-bold text-white mb-4">Acciones Rápidas</h3>
                            <div class="space-y-3">
                                <a href="index.php?page=projects" class="block p-4 rounded-xl bg-slate-800/80 hover:bg-slate-800 text-sm font-semibold text-white transition flex items-center justify-between">
                                    <span><i class="fa-solid fa-plus text-amber-400 mr-2"></i> Crear Proyecto</span>
                                    <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                                </a>
                                <a href="index.php?page=export_csv" class="block p-4 rounded-xl bg-slate-800/80 hover:bg-slate-800 text-sm font-semibold text-white transition flex items-center justify-between">
                                    <span><i class="fa-solid fa-file-excel text-emerald-400 mr-2"></i> Exportar a CSV</span>
                                    <i class="fa-solid fa-download text-slate-500 text-xs"></i>
                                </a>
                                <a href="index.php?page=reports" class="block p-4 rounded-xl bg-slate-800/80 hover:bg-slate-800 text-sm font-semibold text-white transition flex items-center justify-between">
                                    <span><i class="fa-solid fa-chart-column text-sky-400 mr-2"></i> Ver Reportes</span>
                                    <i class="fa-solid fa-chevron-right text-slate-500 text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <script>
                        const ctx = document.getElementById('projectsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Completados', 'En Progreso', 'Planificación'],
                                datasets: [{
                                    data: [<?= $completed_p ?>, <?= $in_progress_p ?>, <?= $planning_p ?>],
                                    backgroundColor: ['#10b981', '#f59e0b', '#64748b'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { labels: { color: '#94a3b8' } } }
                            }
                        });
                    </script>

                <?php elseif ($page === 'projects'): ?>
                    <!-- VISTA PROYECTOS -->
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-white">Módulo de Proyectos</h2>
                            <p class="text-xs text-slate-400">Listado, filtrado y administración integral</p>
                        </div>
                        <?php if ($current_user['role'] !== 'viewer'): ?>
                            <button onclick="document.getElementById('modalProject').classList.remove('hidden')" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-sm transition flex items-center gap-2">
                                <i class="fa-solid fa-plus"></i> Nuevo Proyecto
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-300">
                                <thead class="bg-slate-800/60 text-xs uppercase font-bold text-slate-400 border-b border-slate-800">
                                    <tr>
                                        <th class="px-6 py-4">Código / Título</th>
                                        <th class="px-6 py-4">Categoría</th>
                                        <th class="px-6 py-4">Presupuesto</th>
                                        <th class="px-6 py-4">Progreso</th>
                                        <th class="px-6 py-4">Estado</th>
                                        <th class="px-6 py-4 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    <?php
                                    $stmt = $db->query("SELECT * FROM projects ORDER BY id DESC");
                                    $projects = $stmt->fetchAll();
                                    foreach ($projects as $p):
                                    ?>
                                        <tr class="hover:bg-slate-800/40 transition">
                                            <td class="px-6 py-4">
                                                <span class="text-xs font-mono text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/30"><?= htmlspecialchars($p['code']) ?></span>
                                                <div class="font-bold text-white mt-1"><?= htmlspecialchars($p['title']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 font-medium"><?= htmlspecialchars($p['category']) ?></td>
                                            <td class="px-6 py-4 font-mono">$<?= number_format($p['budget'], 0, ',', '.') ?> COP</td>
                                            <td class="px-6 py-4">
                                                <div class="w-full bg-slate-800 rounded-full h-2.5 max-w-[120px] mb-1">
                                                    <div class="bg-amber-500 h-2.5 rounded-full" style="width: <?= $p['progress'] ?>%"></div>
                                                </div>
                                                <span class="text-xs text-slate-400"><?= $p['progress'] ?>%</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-xs px-2.5 py-1 rounded-full font-bold
                                                    <?= $p['status'] === 'Completado' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : ($p['status'] === 'En Progreso' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700') ?>">
                                                    <?= htmlspecialchars($p['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <?php if ($current_user['role'] === 'admin'): ?>
                                                    <form method="POST" onsubmit="return confirm('¿Seguro de eliminar este proyecto?');" class="inline">
                                                        <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
                                                        <button type="submit" name="action_delete_project" value="1" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg text-xs transition" title="Eliminar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- MODAL NUEVO PROYECTO -->
                    <div id="modalProject" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
                        <div class="bg-slate-900 border border-slate-800 max-w-xl w-full rounded-3xl p-6 sm:p-8 shadow-2xl">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-white">Nuevo Proyecto</h3>
                                <button onclick="document.getElementById('modalProject').classList.add('hidden')" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
                            </div>
                            <form method="POST" class="space-y-4">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Código</label>
                                        <input type="text" name="code" placeholder="PRJ-2026-005" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Categoría</label>
                                        <input type="text" name="category" placeholder="Tecnología / Medios" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Título del Proyecto</label>
                                    <input type="text" name="title" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Descripción</label>
                                    <textarea name="description" rows="3" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none"></textarea>
                                </div>
                                <div class="grid sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Presupuesto (COP)</label>
                                        <input type="number" step="1000" name="budget" placeholder="10000000" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Estado</label>
                                        <select name="status" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                                            <option value="Planificación">Planificación</option>
                                            <option value="En Progreso">En Progreso</option>
                                            <option value="Completado">Completado</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Progreso (%)</label>
                                        <input type="number" min="0" max="100" name="progress" value="0" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                                    <button type="button" onclick="document.getElementById('modalProject').classList.add('hidden')" class="px-5 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl text-sm">Cancelar</button>
                                    <button type="submit" name="action_save_project" value="1" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-sm transition">Guardar Proyecto</button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php elseif ($page === 'users' && $current_user['role'] === 'admin'): ?>
                    <!-- VISTA USUARIOS -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-white">Gestión de Usuarios</h2>
                        <p class="text-xs text-slate-400">Control de cuentas y asignación de roles</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                        <table class="w-full text-left text-sm text-slate-300">
                            <thead class="bg-slate-800/60 text-xs uppercase font-bold text-slate-400 border-b border-slate-800">
                                <tr>
                                    <th class="px-6 py-4">Usuario</th>
                                    <th class="px-6 py-4">Rol</th>
                                    <th class="px-6 py-4">Teléfono / Cargo</th>
                                    <th class="px-6 py-4">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php
                                $stmt = $db->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC");
                                while ($u = $stmt->fetch()):
                                ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-white"><?= htmlspecialchars($u['name']) ?></div>
                                            <div class="text-xs text-slate-400"><?= htmlspecialchars($u['email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                <?= htmlspecialchars($u['role_name']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-slate-400"><?= htmlspecialchars($u['position'] ?? 'N/A') ?></td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Activo</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($page === 'audit' && $current_user['role'] === 'admin'): ?>
                    <!-- VISTA AUDITORÍA -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-white">Bitácora de Auditoría</h2>
                        <p class="text-xs text-slate-400">Trazabilidad y registro de acciones realizadas en el sistema</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                        <table class="w-full text-left text-sm text-slate-300">
                            <thead class="bg-slate-800/60 text-xs uppercase font-bold text-slate-400 border-b border-slate-800">
                                <tr>
                                    <th class="px-6 py-4">Fecha y Hora</th>
                                    <th class="px-6 py-4">Usuario</th>
                                    <th class="px-6 py-4">Acción</th>
                                    <th class="px-6 py-4">Módulo</th>
                                    <th class="px-6 py-4">Detalles</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php
                                $stmt = $db->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 50");
                                while ($log = $stmt->fetch()):
                                ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4 font-mono text-xs text-slate-400"><?= $log['created_at'] ?></td>
                                        <td class="px-6 py-4 font-bold text-white"><?= htmlspecialchars($log['user_name']) ?></td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-800 text-amber-400"><?= htmlspecialchars($log['action']) ?></span></td>
                                        <td class="px-6 py-4 text-xs"><?= htmlspecialchars($log['module']) ?></td>
                                        <td class="px-6 py-4 text-xs text-slate-400"><?= htmlspecialchars($log['details']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($page === 'reports'): ?>
                    <!-- VISTA REPORTES -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-white">Reportes y Exportación</h2>
                            <p class="text-xs text-slate-400">Descarga resúmenes ejecutivos en CSV</p>
                        </div>
                        <a href="index.php?page=export_csv" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl text-sm transition flex items-center gap-2 shadow-lg">
                            <i class="fa-solid fa-file-csv"></i> Descargar Datos en CSV
                        </a>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 p-8 rounded-3xl text-center">
                        <div class="w-16 h-16 bg-emerald-500/10 text-emerald-400 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                            <i class="fa-solid fa-file-shield"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Exportación Directa Lista</h3>
                        <p class="text-slate-400 text-sm max-w-md mx-auto mb-6">Puedes descargar la base de datos completa de proyectos con presupuestos, avances y fechas para análisis en Excel o Power BI.</p>
                        <a href="index.php?page=export_csv" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-sm border border-slate-700 transition">
                            <i class="fa-solid fa-download text-amber-400"></i> Generar Archivo .CSV
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER INSTITUCIONAL -->
    <footer class="bg-slate-900 border-t border-slate-800 py-12 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid sm:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-amber-500 text-slate-950 font-black rounded-lg flex items-center justify-center text-sm">C</div>
                    <span class="font-extrabold text-white text-lg">CORES</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">Sistema web institucional para la gestión estratégica, administración de proyectos y fortalecimiento de medios de comunicación.</p>
            </div>
            <div>
                <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-3">Navegación</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="index.php#inicio" class="hover:text-amber-400">Inicio</a></li>
                    <li><a href="index.php#servicios" class="hover:text-amber-400">Módulos</a></li>
                    <li><a href="index.php#nosotros" class="hover:text-amber-400">Misión y Visión</a></li>
                    <li><a href="index.php#contacto" class="hover:text-amber-400">Contacto</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-3">Acceso Técnico</h4>
                <p class="text-xs text-slate-500 mb-2">Desarrollado para entorno XAMPP (PHP 8.2 + MySQL).</p>
                <a href="install.php" class="text-xs text-amber-400 hover:underline"><i class="fa-solid fa-gear mr-1"></i> Asistente de Base de Datos</a>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-600">
            &copy; <?= date('Y') ?> CORES. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>
