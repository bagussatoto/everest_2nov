<?php
$host = '192.168.5.14';
$db   = 'run_everest_modul';
$user = 'app_admin';
$pass = 'rapirapi619955';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Transactions 1022363 to 1022407 ===\n";
    $stmt = $pdo->query("SELECT id, nomer, jenis, dtime, oleh_nama FROM transaksi WHERE id BETWEEN 1022360 AND 1022410");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "ID: {$r['id']} | nomer: {$r['nomer']} | jenis: {$r['jenis']} | dtime: {$r['dtime']} | oleh: {$r['oleh_nama']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
