<?php
// config/database.php - Manejador de Base de Datos Híbrido (XAMPP Local + Cloud MySQL / SQLite)
require_once __DIR__ . '/config.php';

// Cargar archivo .env local si existe
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $val) = explode('=', $line, 2);
            $name = trim($name);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!getenv($name)) {
                putenv("$name=$val");
                $_ENV[$name] = $val;
            }
        }
    }
}

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $db_type = getenv('DB_TYPE') ?: 'mysql';
    $db_host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: '127.0.0.1');
    $db_port = getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: '3306');
    $db_name = getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'CORES');
    $db_user = getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root');
    $db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQL_PASSWORD') ?: '');

    try {
        if ($db_type === 'sqlite' || (!empty($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false && !canConnectMySQL($db_host, $db_port))) {
            // Modo SQLite de respaldo
            $sqlite_path = __DIR__ . '/../database/cores.sqlite';
            $is_new = !file_exists($sqlite_path);
            $pdo = new PDO("sqlite:" . $sqlite_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            if ($is_new) {
                initializeDatabase($pdo, 'sqlite');
            }
        } else {
            // Modo MySQL / MariaDB (Local XAMPP o Cloud MySQL)
            $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5
            ]);
        }
    } catch (PDOException $e) {
        // Auto-creación de base de datos si es local y no existe aún
        if (strpos($e->getMessage(), 'Unknown database') !== false && ($db_host === '127.0.0.1' || $db_host === 'localhost')) {
            try {
                $rootPdo = new PDO("mysql:host={$db_host};port={$db_port};charset=utf8mb4", $db_user, $db_pass);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                initializeDatabase($pdo, 'mysql');
            } catch (Exception $ex) {
                error_log("Error creando BD: " . $ex->getMessage());
                return null;
            }
        } else {
            error_log("Error de conexión a BD ({$db_host}): " . $e->getMessage());
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
    $schema_file = __DIR__ . '/../database/cores_production.sql';
    if (!file_exists($schema_file)) {
        $schema_file = __DIR__ . '/../database/schema.sql';
    }
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        if ($driver === 'sqlite') {
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
