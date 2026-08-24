-- Esquema de Base de Datos para el Sistema CORES
-- Compatible con MySQL / MariaDB (XAMPP)

CREATE DATABASE IF NOT EXISTS `CORES` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `CORES`;

-- 1. Tabla de Roles
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Usuarios
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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Proyectos / Iniciativas Institucionales (Módulo Core)
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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Auditoría / Historial de Cambios (Trazabilidad)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_name` VARCHAR(100) NULL,
    `action` VARCHAR(50) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla de Mensajes de Contacto (Landing Page)
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserción de Datos Iniciales (Roles)
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrador', 'admin', 'Control total del sistema, usuarios, configuraciones y auditoría'),
(2, 'Operador', 'operator', 'Gestión de proyectos, registros y generación de reportes'),
(3, 'Consulta', 'viewer', 'Acceso de solo lectura a proyectos y reportes')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Inserción de Usuario Administrador Inicial
-- Contraseña: admin123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `phone`, `position`, `status`) VALUES
(1, 'Administrador CORES', 'admin@cores.com', '$2y$10$gPsp0dD0i0r68aYm.yR85OB3hN6kUk9v5TkhO0eH4FhHq8zMhFZeO', 1, '+57 300 000 0000', 'Director General', 'active')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

-- Inserción de Proyectos de Prueba Iniciales
INSERT INTO `projects` (`id`, `title`, `code`, `category`, `description`, `budget`, `status`, `progress`, `start_date`, `end_date`, `created_by`) VALUES
(1, 'Implementación de Red de Comunicaciones Afro', 'PRJ-2026-001', 'Infraestructura', 'Despliegue y articulación de canales digitales comunitarios para transmisión y cobertura regional.', 45000000.00, 'En Progreso', 65, '2026-01-15', '2026-06-30', 1),
(2, 'Plataforma Web de Gestión Institucional CORES', 'PRJ-2026-002', 'Tecnología', 'Desarrollo del ecosistema digital centralizado para registro, trazabilidad y control de proyectos.', 28000000.00, 'En Progreso', 85, '2026-02-01', '2026-04-15', 1),
(3, 'Taller de Capacitación en Medios y Liderazgo', 'PRJ-2026-003', 'Capacitación', 'Formación de 120 líderes comunitarios en producción audiovisual y comunicación estratégica.', 15000000.00, 'Completado', 100, '2026-01-10', '2026-02-28', 1),
(4, 'Estudio Diagnóstico de Cobertura y Necesidades', 'PRJ-2026-004', 'Investigación', 'Levantamiento de información territorial sobre acceso a conectividad y medios de difusión local.', 12000000.00, 'Planificación', 20, '2026-03-01', '2026-05-30', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);
