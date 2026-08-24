<?php
// install.php - Asistente de Instalación Automática CORES
session_start();

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_db'])) {
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Crear base de datos
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `CORES` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `CORES`;");

        // Crear tablas
        $sql = "
        CREATE TABLE IF NOT EXISTS `roles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(50) NOT NULL UNIQUE,
            `slug` VARCHAR(50) NOT NULL UNIQUE,
            `description` VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(120) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role_id` INT NOT NULL,
            `phone` VARCHAR(20) NULL,
            `position` VARCHAR(100) NULL,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `projects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(200) NOT NULL,
            `code` VARCHAR(50) NOT NULL UNIQUE,
            `category` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `budget` DECIMAL(15, 2) DEFAULT 0.00,
            `status` ENUM('Planificación', 'En Progreso', 'Completado', 'Suspendido') DEFAULT 'Planificación',
            `progress` INT DEFAULT 0,
            `start_date` DATE NULL,
            `end_date` DATE NULL,
            `created_by` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `audit_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_name` VARCHAR(100) NULL,
            `action` VARCHAR(50) NOT NULL,
            `module` VARCHAR(50) NOT NULL,
            `details` TEXT NULL,
            `ip_address` VARCHAR(45) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `contact_messages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(120) NOT NULL,
            `subject` VARCHAR(150) NOT NULL,
            `message` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- Inserción de Roles
        INSERT IGNORE INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
        (1, 'Administrador', 'admin', 'Control total del sistema y auditoría'),
        (2, 'Operador', 'operator', 'Gestión de proyectos y reportes'),
        (3, 'Consulta', 'viewer', 'Acceso de lectura a información');

        -- Inserción de Usuario Admin (admin@cores.com / admin123)
        INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `phone`, `position`, `status`) VALUES
        (1, 'Administrador CORES', 'admin@cores.com', '$2y$10\$gPsp0dD0i0r68aYm.yR85OB3hN6kUk9v5TkhO0eH4FhHq8zMhFZeO', 1, '+57 300 000 0000', 'Coordinador General', 'active');

        -- Inserción de Proyectos de Ejemplo
        INSERT IGNORE INTO `projects` (`id`, `title`, `code`, `category`, `description`, `budget`, `status`, `progress`, `start_date`, `end_date`, `created_by`) VALUES
        (1, 'Red de Medios Comunitarios y Digitales', 'PRJ-2026-001', 'Comunicaciones', 'Fortalecimiento de canales informativos y producción audiovisual territorial.', 35000000.00, 'En Progreso', 70, '2026-01-10', '2026-06-30', 1),
        (2, 'Ecosistema de Gestión de Proyectos CORES', 'PRJ-2026-002', 'Tecnología', 'Despliegue del portal web y panel administrativo de control de procesos.', 25000000.00, 'En Progreso', 90, '2026-02-01', '2026-04-30', 1),
        (3, 'Capacitación en Liderazgo y Medios Étnicos', 'PRJ-2026-003', 'Educación', 'Programa de formación técnica para 150 jóvenes comunicadores.', 18000000.00, 'Completado', 100, '2026-01-15', '2026-02-28', 1);

        -- Log Inicial de Auditoría
        INSERT INTO `audit_logs` (`user_name`, `action`, `module`, `details`, `ip_address`) VALUES
        ('Sistema', 'Instalación', 'Base de Datos', 'Inicialización exitosa del sistema CORES', '127.0.0.1');
        ";

        $pdo->exec($sql);
        $status = 'success';
        $message = '¡Base de datos CORES instalada correctamente con usuario y datos de prueba!';
    } catch (PDOException $e) {
        $status = 'error';
        $message = 'Error en la instalación: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador CORES - Sistema Web</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-500 text-slate-950 rounded-2xl mb-4 font-black text-2xl shadow-lg">
            C
        </div>
        <h1 class="text-2xl font-bold mb-2">Instalador del Sistema</h1>
        <p class="text-slate-400 text-sm mb-6">Inicializa la base de datos MySQL en XAMPP con 1 solo clic.</p>

        <?php if ($status === 'success'): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 p-5 rounded-2xl mb-6 text-sm">
                <i class="fa-solid fa-circle-check text-2xl mb-2 block"></i>
                <?= htmlspecialchars($message) ?>
                <div class="mt-4 pt-3 border-t border-emerald-500/30 text-xs text-left">
                    <p><strong>Admin:</strong> admin@cores.com</p>
                    <p><strong>Clave:</strong> admin123</p>
                </div>
            </div>
            <a href="index.php" class="block w-full py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-lg transition">
                <i class="fa-solid fa-arrow-right mr-2"></i> Ir a la Plataforma CORES
            </a>
        <?php else: ?>
            <?php if ($status === 'error'): ?>
                <div class="bg-rose-500/10 border border-rose-500/40 text-rose-400 p-4 rounded-2xl mb-6 text-sm text-left">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> <?= htmlspecialchars($message) ?>
                    <p class="mt-2 text-xs text-slate-300">Asegúrate de que <strong>MySQL</strong> esté iniciado (Running) en el panel de control de XAMPP.</p>
                </div>
            <?php endif; ?>

            <form method="POST">
                <button type="submit" name="install_db" value="1" class="w-full py-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-database mr-2"></i> Inicializar Base de Datos
                </button>
            </form>
            <a href="index.php" class="inline-block mt-4 text-xs text-slate-400 hover:text-amber-400">Ir al portal principal</a>
        <?php endif; ?>
    </div>
</body>
</html>
