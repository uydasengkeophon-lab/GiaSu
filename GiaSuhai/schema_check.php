<?php
require 'c:/xampp/htdocs/GiaSuhai/config/database.php';
try {
    $db = (new Database())->connect();
    $stmt = $db->query("SHOW COLUMNS FROM bookings LIKE 'status'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'BOOKINGS_STATUS=' . (isset($row['Type']) ? $row['Type'] : 'MISSING') . PHP_EOL;
    $stmt = $db->query("SHOW TABLES LIKE 'lich_hoc'");
    $table = $stmt->fetch(PDO::FETCH_NUM);
    echo 'LICH_HOC=' . ($table ? 'EXISTS' : 'MISSING') . PHP_EOL;
    if ($table) {
        $stmt2 = $db->query('SHOW COLUMNS FROM lich_hoc');
        while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            echo $r['Field'] . ':' . $r['Type'] . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo 'ERROR:' . $e->getMessage() . PHP_EOL;
}
