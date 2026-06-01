<?php
require 'c:/xampp/htdocs/GiaSuhai/config/database.php';
try {
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT COUNT(*) AS cnt FROM lich_hoc");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'LICH_HOC_COUNT=' . ($row['cnt'] ?? '0') . PHP_EOL;
    $stmt = $db->query("SELECT * FROM lich_hoc ORDER BY id DESC LIMIT 5");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'ERROR:' . $e->getMessage() . PHP_EOL;
}
