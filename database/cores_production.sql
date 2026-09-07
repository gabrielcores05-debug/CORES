-- ========================================================
-- CORES COMUNICACIONES S.A.S. - BASE DE DATOS DE PRODUCCIÓN
-- Ubicación: Condoto, Chocó, Colombia
-- Teléfono: 3126030464
-- ========================================================

-- 1. Tabla de Configuración Institucional
CREATE TABLE IF NOT EXISTS `company_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(150) NOT NULL DEFAULT 'CORES COMUNICACIONES S.A.S.',
    `slogan` VARCHAR(255) NOT NULL DEFAULT 'CONECTAMOS TU MUNDO, IMPULSAMOS TU VIDA',
    `location` VARCHAR(150) NOT NULL DEFAULT 'Condoto, Chocó, Colombia',
    `phone` VARCHAR(20) NOT NULL DEFAULT '3126030464',
    `whatsapp` VARCHAR(20) NOT NULL DEFAULT '573126030464',
    `billing_day` INT NOT NULL DEFAULT 7,
    `suspension_day` INT NOT NULL DEFAULT 8,
    `collection_day` INT NOT NULL DEFAULT 22,
    `approx_clients` VARCHAR(50) NOT NULL DEFAULT '+600',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Planes de Internet
CREATE TABLE IF NOT EXISTS `plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `speed` VARCHAR(50) NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `technology` VARCHAR(50) DEFAULT 'Fibra Óptica',
    `features` TEXT NOT NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Usuarios (Administradores y Clientes)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_code` VARCHAR(50) NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `document_type` VARCHAR(20) DEFAULT 'CC',
    `document_number` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `phone` VARCHAR(30) NOT NULL,
    `address` VARCHAR(200) NOT NULL DEFAULT 'Condoto, Chocó',
    `neighborhood` VARCHAR(100) NULL,
    `role` ENUM('admin', 'operator', 'client') DEFAULT 'client',
    `plan_id` INT NULL,
    `status` ENUM('ACTIVO', 'PENDIENTE DE PAGO', 'SUSPENDIDO') DEFAULT 'ACTIVO',
    `password` VARCHAR(255) NOT NULL,
    `billing_day` INT DEFAULT 7,
    `suspension_day` INT DEFAULT 8,
    `collection_day` INT DEFAULT 22,
    `installation_date` DATE NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Facturas Mensuales
CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `plan_id` INT NULL,
    `period` VARCHAR(50) NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `issue_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `status` ENUM('PAGADO', 'PENDIENTE', 'VENCIDO') DEFAULT 'PENDIENTE',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla de Pagos y Recaudos
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT NULL,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_method` VARCHAR(50) DEFAULT 'Efectivo',
    `period` VARCHAR(50) NOT NULL,
    `status` ENUM('PAGADO', 'PENDIENTE') DEFAULT 'PAGADO',
    `reference_number` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `registered_by` VARCHAR(100) DEFAULT 'Sistema',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla de Mensajes de Contacto
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `subject` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('unread', 'read', 'contacted') DEFAULT 'unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabla de Auditoría
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_name` VARCHAR(120) NULL,
    `action` VARCHAR(50) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- INSERCIÓN DE DATOS INICIALES (SEMILLA OFICIAL)
-- ========================================================

-- Configuración Institucional
INSERT INTO `company_settings` (`id`, `company_name`, `slogan`, `location`, `phone`, `whatsapp`, `billing_day`, `suspension_day`, `collection_day`, `approx_clients`) VALUES
(1, 'CORES COMUNICACIONES S.A.S.', 'CONECTAMOS TU MUNDO, IMPULSAMOS TU VIDA', 'Condoto, Chocó, Colombia', '3126030464', '573126030464', 7, 8, 22, '+600')
ON DUPLICATE KEY UPDATE `company_name` = VALUES(`company_name`);

-- Planes de Internet Oficiales
INSERT INTO `plans` (`id`, `name`, `code`, `speed`, `price`, `technology`, `features`, `is_featured`, `status`) VALUES
(1, 'Plan Hogar Básico', 'PLN-30M', '30 Mbps', 55000.00, 'Fibra Óptica', 'Internet 100% Fibra Óptica, Descarga y subida estable, Conexión para 3 a 5 dispositivos, Ideal para navegación y estudio, Soporte técnico local en Condoto', 0, 'active'),
(2, 'Plan Hogar Plus', 'PLN-50M', '50 Mbps', 75000.00, 'Fibra Óptica', 'Internet 100% Fibra Óptica, Streaming en Full HD sin cortes, Conexión para 6 a 8 dispositivos, Ideal para teletrabajo y clases virtuales, Soporte prioritario', 1, 'active'),
(3, 'Plan Familia Conectada', 'PLN-100M', '100 Mbps', 95000.00, 'Fibra Óptica', 'Ultra velocidad de descarga, Streaming 4K simultáneo, Conexión para +10 dispositivos, Baja latencia para videojuegos online, Router Wi-Fi Doble Banda', 0, 'active'),
(4, 'Plan Comercio / Empresarial', 'PLN-200M', '200 Mbps', 150000.00, 'Fibra Óptica', 'Canal de datos dedicado para negocios, Máxima estabilidad garantizada, Múltiples puntos de venta y cámaras de seguridad, IP y soporte preferencial inmediato', 0, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Usuarios Administradores y Clientes
-- Admin: admin@cores.com / admin123
-- Cliente: 1077100001 / cliente123
INSERT INTO `users` (`id`, `client_code`, `name`, `document_type`, `document_number`, `email`, `phone`, `address`, `neighborhood`, `role`, `plan_id`, `status`, `password`, `installation_date`) VALUES
(1, 'ADM-001', 'Administrador CORES', 'CC', '1077000000', 'admin@cores.com', '3126030464', 'Sede Principal, Condoto', 'Centro', 'admin', NULL, 'ACTIVO', '$2y$10$oumZ8yXbTcuNv0kf6kcWUO9ZRuwQ6U1SxS0RofGtJv89ifcFZQHTC', '2025-01-01'),
(2, 'CLI-1001', 'Gabriel Perea Mosquera', 'CC', '1077100001', 'gabriel.cliente@gmail.com', '3126030464', 'Calle 10 # 5-24', 'Barrio Claret', 'client', 2, 'ACTIVO', '$2y$10$Ix3yDvhwqOTfH/MKpHSaceFj6IQWFaefHEHLHZDLyrBaYog7e61SW', '2025-03-10'),
(3, 'CLI-1002', 'Luz Marina Córdoba', 'CC', '1077100002', 'luz.marina@gmail.com', '3145551234', 'Carrera 4 # 12-10', 'Barrio Cascajero', 'client', 1, 'ACTIVO', '$2y$10$Ix3yDvhwqOTfH/MKpHSaceFj6IQWFaefHEHLHZDLyrBaYog7e61SW', '2025-04-15'),
(4, 'CLI-1003', 'Carlos Mario Valencia', 'CC', '1077100003', 'carlos.valencia@gmail.com', '3117894561', 'Sector El Comercio', 'Centro', 'client', 3, 'PENDIENTE DE PAGO', '$2y$10$Ix3yDvhwqOTfH/MKpHSaceFj6IQWFaefHEHLHZDLyrBaYog7e61SW', '2025-06-01'),
(5, 'CLI-1004', 'Droguería San Juan Condoto', 'NIT', '900554433', 'contacto@drogueriasanjuan.com', '3209876543', 'Av. Principal # 8-30', 'Centro', 'client', 4, 'ACTIVO', '$2y$10$Ix3yDvhwqOTfH/MKpHSaceFj6IQWFaefHEHLHZDLyrBaYog7e61SW', '2025-02-20'),
(6, 'CLI-1005', 'Yesenia Rentería Palacios', 'CC', '1077100005', 'yesenia.renteria@gmail.com', '3156677889', 'Calle 15 # 3-12', 'Barrio San José', 'client', 2, 'SUSPENDIDO', '$2y$10$Ix3yDvhwqOTfH/MKpHSaceFj6IQWFaefHEHLHZDLyrBaYog7e61SW', '2025-05-18'),
(7, 'CLI-1006', 'Jhonatan Blandón Moreno', 'CC', '1077100006', 'jhonatan.blandon@gmail.com', '3134455667', 'Carrera 7 # 9-45', 'Barrio Claret', 'client', 1, 'ACTIVO', '$2y$10$Ix3yDvhwqOTfH/MKpHSaceFj6IQWFaefHEHLHZDLyrBaYog7e61SW', '2025-07-22')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Facturas Iniciales
INSERT INTO `invoices` (`id`, `invoice_number`, `user_id`, `plan_id`, `period`, `amount`, `issue_date`, `due_date`, `status`) VALUES
(1, 'FAC-2026-08-001', 2, 2, 'Agosto 2026', 75000.00, '2026-08-07', '2026-08-08', 'PAGADO'),
(2, 'FAC-2026-08-002', 3, 1, 'Agosto 2026', 55000.00, '2026-08-07', '2026-08-08', 'PAGADO'),
(3, 'FAC-2026-08-003', 4, 3, 'Agosto 2026', 95000.00, '2026-08-07', '2026-08-08', 'PENDIENTE'),
(4, 'FAC-2026-08-004', 5, 4, 'Agosto 2026', 150000.00, '2026-08-07', '2026-08-08', 'PAGADO'),
(5, 'FAC-2026-08-005', 6, 2, 'Agosto 2026', 75000.00, '2026-08-07', '2026-08-08', 'VENCIDO'),
(6, 'FAC-2026-08-006', 7, 1, 'Agosto 2026', 55000.00, '2026-08-07', '2026-08-08', 'PAGADO'),
(7, 'FAC-2026-09-001', 2, 2, 'Septiembre 2026', 75000.00, '2026-09-07', '2026-09-08', 'PAGADO'),
(8, 'FAC-2026-09-002', 3, 1, 'Septiembre 2026', 55000.00, '2026-09-07', '2026-09-08', 'PAGADO'),
(9, 'FAC-2026-09-003', 4, 3, 'Septiembre 2026', 95000.00, '2026-09-07', '2026-09-08', 'PENDIENTE'),
(10, 'FAC-2026-09-004', 5, 4, 'Septiembre 2026', 150000.00, '2026-09-07', '2026-09-08', 'PAGADO'),
(11, 'FAC-2026-09-005', 6, 2, 'Septiembre 2026', 75000.00, '2026-09-07', '2026-09-08', 'VENCIDO'),
(12, 'FAC-2026-09-006', 7, 1, 'Septiembre 2026', 55000.00, '2026-09-07', '2026-09-08', 'PAGADO')
ON DUPLICATE KEY UPDATE `invoice_number` = VALUES(`invoice_number`);

-- Pagos Iniciales
INSERT INTO `payments` (`id`, `invoice_id`, `user_id`, `amount`, `payment_date`, `payment_method`, `period`, `status`, `reference_number`, `registered_by`) VALUES
(1, 1, 2, 75000.00, '2026-08-15', 'Transferencia Bancolombia', 'Agosto 2026', 'PAGADO', 'TRANS-88291', 'Administrador'),
(2, 2, 3, 55000.00, '2026-08-20', 'Efectivo', 'Agosto 2026', 'PAGADO', 'REC-00124', 'Administrador'),
(3, 4, 5, 150000.00, '2026-08-22', 'Nequi', 'Agosto 2026', 'PAGADO', 'NEQ-99382', 'Administrador'),
(4, 6, 7, 55000.00, '2026-08-22', 'Efectivo', 'Agosto 2026', 'PAGADO', 'REC-00125', 'Administrador'),
(5, 7, 2, 75000.00, '2026-09-02', 'Transferencia Bancolombia', 'Septiembre 2026', 'PAGADO', 'TRANS-89100', 'Administrador'),
(6, 8, 3, 55000.00, '2026-09-03', 'Daviplata', 'Septiembre 2026', 'PAGADO', 'DAV-11029', 'Administrador'),
(7, 10, 5, 150000.00, '2026-09-04', 'Transferencia Bancolombia', 'Septiembre 2026', 'PAGADO', 'TRANS-89211', 'Administrador'),
(8, 12, 7, 55000.00, '2026-09-04', 'Efectivo', 'Septiembre 2026', 'PAGADO', 'REC-00130', 'Administrador')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);

-- Auditoría Inicial
INSERT INTO `audit_logs` (`user_name`, `action`, `module`, `details`, `ip_address`) VALUES
('Sistema', 'Producción', 'Base de Datos', 'Inicialización oficial del sistema CORES COMUNICACIONES S.A.S. en producción', '127.0.0.1');
