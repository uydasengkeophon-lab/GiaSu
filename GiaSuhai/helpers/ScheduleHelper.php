<?php

/**
 * Schedule Helper Functions - Hỗ trợ cho hệ thống lịch học
 */

class ScheduleHelper
{
    // Thời gian buổi học
    const SESSION_MORNING = 1;
    const SESSION_AFTERNOON = 2;

    // Ngày trong tuần (0=Thứ 2, 6=Chủ nhật)
    const MONDAY = 0;
    const TUESDAY = 1;
    const WEDNESDAY = 2;
    const THURSDAY = 3;
    const FRIDAY = 4;
    const SATURDAY = 5;
    const SUNDAY = 6;

    /**
     * Kiểm tra có thể đăng ký trong giai đoạn nào không
     * (Chỉ cho phép từ ngày 01-07 của mỗi tháng)
     * 
     * @return bool
     */
    public static function canRegisterNow()
    {
        $day = (int)date('d');
        return $day >= 1 && $day <= 7;
    }

    /**
     * Lấy ngày cuối cùng được phép đăng ký trong tháng
     * 
     * @return int
     */
    public static function getRegistrationDeadlineDay()
    {
        return 7;
    }

    /**
     * Lấy ngày đầu tiên được phép đăng ký trong tháng
     * 
     * @return int
     */
    public static function getRegistrationStartDay()
    {
        return 1;
    }

    /**
     * Lấy tên ngày trong tuần
     * 
     * @param int $dayOfWeek (0=Thứ 2, 6=Chủ nhật)
     * @return string
     */
    public static function getDayName($dayOfWeek)
    {
        $days = [
            'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'
        ];
        return $days[$dayOfWeek] ?? '';
    }

    /**
     * Lấy tên ngày với chữ cái
     * 
     * @param int $dayOfWeek (0=Thứ 2, 6=Chủ nhật)
     * @return string
     */
    public static function getDayNameWithEnglish($dayOfWeek)
    {
        $days = [
            'Thứ 2 (Monday)',
            'Thứ 3 (Tuesday)',
            'Thứ 4 (Wednesday)',
            'Thứ 5 (Thursday)',
            'Thứ 6 (Friday)',
            'Thứ 7 (Saturday)',
            'Chủ nhật (Sunday)'
        ];
        return $days[$dayOfWeek] ?? '';
    }

    /**
     * Lấy thông tin buổi học
     * 
     * @param int $session Mã buổi học (1=sáng, 2=chiều)
     * @return array|null ['start' => '07:00:00', 'end' => '11:00:00', 'name' => 'Sáng (7-11)', 'duration' => 4]
     */
    public static function getSessionInfo($session)
    {
        $sessions = [
            1 => [
                'start' => '07:00:00',
                'end' => '11:00:00',
                'name' => 'Sáng (7:00 - 11:00)',
                'duration' => 4,
                'label' => 'Buổi Sáng'
            ],
            2 => [
                'start' => '14:00:00',
                'end' => '17:00:00',
                'name' => 'Chiều (14:00 - 17:00)',
                'duration' => 3,
                'label' => 'Buổi Chiều'
            ]
        ];

        return $sessions[$session] ?? null;
    }

    /**
     * Lấy tên buổi học
     * 
     * @param int $session Mã buổi học (1=sáng, 2=chiều)
     * @return string
     */
    public static function getSessionName($session)
    {
        $info = self::getSessionInfo($session);
        return $info ? $info['name'] : '';
    }

    /**
     * Chuyển đổi ngày trong tuần từ PHP sang hệ thống (0=Thứ 2, 6=Chủ nhật)
     * PHP: 0=Sunday, 1=Monday, ..., 6=Saturday
     * 
     * @param int $phpDayOfWeek (0-6, 0=Sunday)
     * @return int (0-6, 0=Monday)
     */
    public static function phpDayToCustomDay($phpDayOfWeek)
    {
        // PHP: 0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat
        // Custom: 0=Mon, 1=Tue, 2=Wed, 3=Thu, 4=Fri, 5=Sat, 6=Sun
        return ($phpDayOfWeek + 6) % 7;
    }

    /**
     * Chuyển đổi ngày trong tuần từ hệ thống sang PHP
     * 
     * @param int $customDayOfWeek (0-6, 0=Monday)
     * @return int (0-6, 0=Sunday)
     */
    public static function customDayToPhpDay($customDayOfWeek)
    {
        // Custom: 0=Mon, 1=Tue, 2=Wed, 3=Thu, 4=Fri, 5=Sat, 6=Sun
        // PHP: 0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat
        return ($customDayOfWeek + 1) % 7;
    }

    /**
     * Kiểm tra ngày hiện tại có phải trong giai đoạn đăng ký không
     * 
     * @return array ['can_register' => bool, 'message' => string, 'days_left' => int, 'deadline_date' => string]
     */
    public static function getRegistrationStatus()
    {
        $today = (int)date('d');
        $month = (int)date('m');
        $year = (int)date('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $startDay = self::getRegistrationStartDay();
        $deadlineDay = self::getRegistrationDeadlineDay();

        $canRegister = $today >= $startDay && $today <= $deadlineDay;
        $daysLeft = $canRegister ? ($deadlineDay - $today + 1) : 0;

        if ($canRegister) {
            $message = "✅ Đang trong giai đoạn đăng ký (Từ ngày $startDay - $deadlineDay)";
        } else {
            $nextDeadline = date('Y-m-d', strtotime("next month +6 days", mktime(0, 0, 0, $month, 1, $year)));
            $message = "❌ Ngoài giai đoạn đăng ký (Chỉ được từ ngày $startDay - $deadlineDay của mỗi tháng)";
        }

        return [
            'can_register' => $canRegister,
            'message' => $message,
            'days_left' => $daysLeft,
            'deadline_date' => date('d-m-Y', mktime(0, 0, 0, $month, $deadlineDay, $year)),
            'current_day' => $today
        ];
    }

    /**
     * Tính số ngày học trong tháng cho một lịch cố định
     * 
     * @param int $dayOfWeek (0=Thứ 2, 6=Chủ nhật)
     * @param int|null $month
     * @param int|null $year
     * @return int
     */
    public static function countSchedulesInMonth($dayOfWeek, $month = null, $year = null)
    {
        if ($month === null) {
            $month = (int)date('m');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }

        $count = 0;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
            if ($date === false) continue;

            $phpDayOfWeek = (int)$date->format('w');
            $customDayOfWeek = self::phpDayToCustomDay($phpDayOfWeek);

            if ($customDayOfWeek == $dayOfWeek) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Lấy danh sách các ngày trong tháng cho một ngày trong tuần cụ thể
     * 
     * @param int $dayOfWeek (0=Thứ 2, 6=Chủ nhật)
     * @param int|null $month
     * @param int|null $year
     * @return array
     */
    public static function getDatesInMonth($dayOfWeek, $month = null, $year = null)
    {
        if ($month === null) {
            $month = (int)date('m');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }

        $dates = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $date = \DateTime::createFromFormat('Y-m-d', $dateStr);

            if ($date === false) continue;

            $phpDayOfWeek = (int)$date->format('w');
            $customDayOfWeek = self::phpDayToCustomDay($phpDayOfWeek);

            if ($customDayOfWeek == $dayOfWeek) {
                $dates[] = [
                    'date' => $dateStr,
                    'day' => $day,
                    'php_day' => $phpDayOfWeek
                ];
            }
        }

        return $dates;
    }

    /**
     * Format thông tin buổi học dành hiển thị
     * 
     * @param int $dayOfWeek (0=Thứ 2, 6=Chủ nhật)
     * @param int $session (1=sáng, 2=chiều)
     * @return string
     */
    public static function formatScheduleDisplay($dayOfWeek, $session)
    {
        $dayName = self::getDayName($dayOfWeek);
        $sessionName = self::getSessionName($session);
        return "$dayName - $sessionName";
    }

    /**
     * Lấy màu badge cho buổi học
     * 
     * @param int $session (1=sáng, 2=chiều)
     * @return array ['bg' => string, 'color' => string]
     */
    public static function getSessionColor($session)
    {
        $colors = [
            1 => ['bg' => '#fff3e0', 'color' => '#e65100'],     // Sáng: cam
            2 => ['bg' => '#f3e5f5', 'color' => '#6a1b9a']      // Chiều: tím
        ];
        return $colors[$session] ?? ['bg' => '#f0f0f0', 'color' => '#666'];
    }
}
