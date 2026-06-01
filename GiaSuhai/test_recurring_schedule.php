<?php
/**
 * Test File for Recurring Schedule Feature
 * 
 * File này để test các chức năng của lịch cố định
 * Truy cập qua: http://localhost/GiaSuhai/test_recurring_schedule.php
 */

require_once 'config/database.php';
require_once 'models/Schedule.php';
require_once 'helpers/ScheduleHelper.php';

echo "<h1>🧪 Test Recurring Schedule Feature</h1>";
echo "<hr>";

try {
    $database = new Database();
    $conn = $database->connect();
    $schedule = new Schedule($conn);

    // Test 1: Check Registration Status
    echo "<h2>Test 1: Registration Status</h2>";
    $status = ScheduleHelper::getRegistrationStatus();
    echo "<pre>";
    print_r($status);
    echo "</pre>";
    echo "<p>Can Register Now: " . ($status['can_register'] ? '✅ Yes' : '❌ No') . "</p>";

    // Test 2: Get Session Info
    echo "<h2>Test 2: Session Information</h2>";
    $morning = ScheduleHelper::getSessionInfo(1);
    $afternoon = ScheduleHelper::getSessionInfo(2);
    echo "<h3>Buổi Sáng (Session 1):</h3>";
    echo "<pre>";
    print_r($morning);
    echo "</pre>";
    echo "<h3>Buổi Chiều (Session 2):</h3>";
    echo "<pre>";
    print_r($afternoon);
    echo "</pre>";

    // Test 3: Day Names
    echo "<h2>Test 3: Day Names</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Day Code</th><th>Name (Vietnamese)</th><th>Name with English</th></tr>";
    for ($i = 0; $i <= 6; $i++) {
        $name = ScheduleHelper::getDayName($i);
        $nameEng = ScheduleHelper::getDayNameWithEnglish($i);
        echo "<tr><td>$i</td><td>$name</td><td>$nameEng</td></tr>";
    }
    echo "</table>";

    // Test 4: Count Schedules in Current Month
    echo "<h2>Test 4: Count Schedules in Month</h2>";
    echo "<p>Số lần mỗi ngày xuất hiện trong tháng hiện tại:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Day</th><th>Count</th></tr>";
    for ($i = 0; $i <= 6; $i++) {
        $name = ScheduleHelper::getDayName($i);
        $count = ScheduleHelper::countSchedulesInMonth($i);
        echo "<tr><td>$name ($i)</td><td>$count</td></tr>";
    }
    echo "</table>";

    // Test 5: Get Dates for Monday in Current Month
    echo "<h2>Test 5: All Mondays in Current Month</h2>";
    $mondays = ScheduleHelper::getDatesInMonth(0); // 0 = Monday
    echo "<p>Tất cả các Thứ 2 trong tháng " . date('m/Y') . ":</p>";
    echo "<ul>";
    foreach ($mondays as $date) {
        echo "<li>{$date['date']} (Day {$date['day']})</li>";
    }
    echo "</ul>";

    // Test 6: Generate Schedules for Current Month
    echo "<h2>Test 6: Generate Schedules from Recurring</h2>";
    $recurringSchedules = $schedule->getAllRecurringSchedules();
    echo "<p>Số lịch cố định hiện tại: " . count($recurringSchedules) . "</p>";
    
    if (count($recurringSchedules) > 0) {
        echo "<p>Top 3 lịch cố định:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Gia sư</th><th>Học viên</th><th>Môn</th><th>Ngày</th><th>Buổi</th></tr>";
        $count = 0;
        foreach ($recurringSchedules as $rec) {
            if ($count >= 3) break;
            $dayName = ScheduleHelper::getDayName($rec['thu_trong_tuan']);
            $sessionName = ScheduleHelper::getSessionName($rec['phien_hoc']);
            echo "<tr>";
            echo "<td>{$rec['id']}</td>";
            echo "<td>{$rec['tutor_name']}</td>";
            echo "<td>{$rec['student_name']}</td>";
            echo "<td>{$rec['mon_hoc']}</td>";
            echo "<td>$dayName</td>";
            echo "<td>$sessionName</td>";
            echo "</tr>";
            $count++;
        }
        echo "</table>";

        echo "<p>Sinh lịch cho tháng hiện tại từ lịch cố định:</p>";
        $generated = $schedule->generateMonthSchedulesFromRecurring();
        echo "<p>Số buổi học được sinh: " . count($generated) . "</p>";
        
        if (count($generated) > 0) {
            echo "<p>Top 5 buổi học:</p>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Ngày</th><th>Gia sư</th><th>Học viên</th><th>Môn</th><th>Giờ</th></tr>";
            $count = 0;
            foreach ($generated as $sched) {
                if ($count >= 5) break;
                $time = $sched['gio_bat_dau'] . ' - ' . $sched['gio_ket_thuc'];
                echo "<tr>";
                echo "<td>{$sched['ngay']}</td>";
                echo "<td>{$sched['tutor_name']}</td>";
                echo "<td>{$sched['student_name']}</td>";
                echo "<td>{$sched['mon_hoc']}</td>";
                echo "<td>$time</td>";
                echo "</tr>";
                $count++;
            }
            echo "</table>";
        }
    } else {
        echo "<p>⚠️ Chưa có lịch cố định nào. Hãy tạo lịch cố định để xem kết quả.</p>";
    }

    // Test 7: Test Day Conversion
    echo "<h2>Test 7: Day Conversion (PHP ↔ Custom)</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>PHP Day</th><th>Name</th><th>Custom Day</th></tr>";
    for ($phpDay = 0; $phpDay <= 6; $phpDay++) {
        $customDay = ScheduleHelper::phpDayToCustomDay($phpDay);
        $phpNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $name = $phpNames[$phpDay];
        echo "<tr><td>$phpDay ($name)</td><td>" . ScheduleHelper::getDayName($customDay) . "</td><td>$customDay</td></tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h2>✅ All Tests Completed!</h2>";

} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        h1, h2, h3 {
            color: #333;
        }
        table {
            background: white;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th {
            background: #2196F3;
            color: white;
            padding: 10px;
        }
        td {
            padding: 8px;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        pre {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        p {
            line-height: 1.6;
        }
    </style>
</head>
</html>
