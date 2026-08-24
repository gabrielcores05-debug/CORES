<?php
// setup_db.php - Inicializador CLI de la base de datos CORES
$host = '127.0.0.1';
$user = 'root';
$pass = '';

echo "Conectando a MySQL en $host...\n";
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($sql);

    echo "Base de datos CORES creada e inicializada con éxito.\n";
} catch (Exception $e) {
    echo "Error al conectar/inicializar la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}
