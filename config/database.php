<?php
// config/database.php - Manejador de Base de Datos Híbrido (MySQL XAMPP + Cloud SQLite/MySQL)
require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $db_type = getenv('DB_TYPE') ?: 'mysql'; // 'mysql' o 'sqlite'
    $db_host = getenv('DB_HOST') ?: '127.0.0.1';
    $db_port = getenv('DB_PORT') ?: '3306';
    $db_name = getenv('DB_NAME') ?: 'CORES';
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

    try {
        if ($db_type === 'sqlite' || (!empty($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false && !canConnectMySQL($db_host, $db_port))) {
            // Modo SQLite para entornos en la nube sin MySQL externo
            $sqlite_path = __DIR__ . '/../database/cores.sqlite';
            $is_new = !file_exists($sqlite_path);
            $pdo = new PDO("sqlite:" . $sqlite_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            if ($is_new) {
                initializeDatabase($pdo, 'sqlite');
            }
        } else {
            // Modo MySQL (XAMPP / Servidor MySQL)
            $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
    } catch (PDOException $e) {
        // Si la base de datos MySQL aún no está creada, intentar crearla
        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            try {
                $rootPdo = new PDO("mysql:host={$db_host};port={$db_port};charset=utf8mb4", $db_user, $db_pass);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                initializeDatabase($pdo, 'mysql');
            } catch (Exception $ex) {
                return null;
            }
        } else {
            return null;
        }
    }

    return $pdo;
}

function canConnectMySQL($host, $port) {
    $fp = @fsockopen($host, (int)$port, $errno, $errstr, 1);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

function initializeDatabase($pdo, $driver = 'mysql') {
    $schema_file = __DIR__ . '/../database/schema.sql';
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        if ($driver === 'sqlite') {
            // Adaptar para SQLite si es necesario
            $sql = preg_replace('/ENGINE=InnoDB.*?utf8mb4;/i', ';', $sql);
            $sql = preg_replace('/ENUM\([^)]+\)/i', 'VARCHAR(50)', $sql);
            $sql = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $sql);
            $sql = preg_replace('/INT AUTOINCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
            $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/i', '', $sql);
            $sql = preg_replace('/USE `?CORES`?;/i', '', $sql);
        }
        $pdo->exec($sql);
    }
}
