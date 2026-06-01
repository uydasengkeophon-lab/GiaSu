-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 08, 2026 lúc 04:54 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `giasu`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','approved','rejected','cancelled') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `study_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

INSERT INTO `bookings` (`id`, `tutor_id`, `student_id`, `created_at`, `status`, `amount`, `study_date`, `start_time`, `end_time`) VALUES
(1, 2, 5, '2026-01-01 03:00:00', 'pending', 100000.00, '2026-01-05', '08:00:00', '10:00:00'),
(2, 3, 7, '2026-01-02 03:00:00', 'paid', 200000.00, '2026-01-06', '09:00:00', '11:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Test', 'test@gmail.com', 'Hello', '2026-01-01 03:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `author` varchar(100) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `news`
--

INSERT INTO `news` (`id`, `title`, `image`, `content`, `created_at`, `author`, `tutor_id`, `status`) VALUES
(1, 'Khai giảng', NULL, 'Mở lớp mới', '2026-02-01 10:00:00', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`id`, `user_id`, `full_name`, `phone`, `grade_level`, `address`) VALUES
(2, 5, 'uida', '1234567', 'lớp11', 'điện biên'),
(3, 7, 'Mone', '55555', 'lớp11', 'điện biên'),
(4, 8, 'Thanh', NULL, NULL, NULL),
(5, 34, 'abc', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tutors`
--

CREATE TABLE `tutors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subjects` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tutors`
--

INSERT INTO `tutors` (`id`, `user_id`, `full_name`, `phone`, `subjects`, `bio`, `hourly_rate`, `avatar`) VALUES
(2, 4, 'Quảng Thi Mai', '02343234', 'C++', 'dạy vui vẻ', 100000.00, 'img1.jpg'),
(3, 6, 'Ui Da', '082893324', 'CSDL', 'dạy cơ bản', 200000.00, 'img2.jpg'),
(4, 9, 'Minh Jun', '0946003946', 'Web', 'dạy HTML CSS JS', 120000.00, 'img3.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','tutor','student') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '123456', 'admin@appco.com', 'admin', '2025-12-07 23:58:57'),
(4, 'Mai', '123456', 'mai@gmail.com', 'tutor', '2025-12-08 00:08:45'),
(5, 'uida', '12345', 'somphoy.k61cntta@utb.edu.vn', 'student', '2025-12-08 00:38:53'),
(6, 'Ui', '12345', 'Ui@gmile.com', 'tutor', '2025-12-08 17:48:11'),
(7, 'Mone', '11111', 'Mone@gimile.com', 'student', '2025-12-08 17:57:48'),
(8, 'thanh', '11111', 'thanh@gmile.com', 'student', '2025-12-08 19:17:58'),
(9, 'minh', '22222', 'minh@gmail.com', 'tutor', '2025-12-09 17:16:55'),
(10, 'Nga', '12345', 'Nga@gm.com', 'student', '2025-12-28 19:37:54'),
(11, 'minhhieu1709', 'minhhieu1709', 'minhhieu17092003@gmail.com', 'student', '2025-12-30 00:07:00'),
(12, 'man', '11111', 'man@gmali.com', 'student', '2025-12-30 00:36:01'),
(13, 'mon', '123456', 'mone@gmail.com', 'student', '2025-12-30 00:51:15'),
(16, 'am', '123456', 'am@gmail.com', 'student', '2026-01-19 02:07:40'),
(17, 'tar', '123456', 'tar@gmail.com', 'student', '2026-03-11 07:19:45'),
(18, 'keo', '123455', 'k@gmail.com', 'student', '2026-03-16 01:26:04'),
(19, 'pu', '12345', 'pu@dmail.com', 'student', '2026-03-19 20:58:03'),
(20, 'nut', '12345', 'nut@gii.xn--cm-2ya', 'student', '2026-03-19 22:59:23'),
(21, 'OUY', '12345', 'o@gmail.com', 'student', '2026-03-25 00:34:17'),
(22, 'na', '12345', 'n@gmail.com', 'student', '2026-03-25 01:36:07'),
(23, 'mb', '12345', 'm@gg.com', 'student', '2026-03-25 01:56:54'),
(24, 'eo', '12345', 'e@gi.com', 'student', '2026-03-25 02:24:33'),
(29, 'thi', '12345', 'thi@gmil.com', 'student', '2026-03-29 21:05:31'),
(30, 'dung', '12345', 'd@gmile.com', 'student', '2026-04-04 08:46:55'),
(31, 'chon', '12345', 'chon@gmile.com', 'student', '2026-04-04 09:14:43'),
(32, 'vi', '12345', 'v@g.com', 'student', '2026-04-07 19:32:43'),
(33, 'an', '12345', 'a@h.com', 'student', '2026-04-07 20:42:30'),
(34, 'abc', '12345678', 'abc@utb.edu.vn', 'student', '2026-04-08 14:48:42');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `tutors`
--
ALTER TABLE `tutors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `tutors`
--
ALTER TABLE `tutors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tutors`
--
ALTER TABLE `tutors`
  ADD CONSTRAINT `tutors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
