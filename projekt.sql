-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Lis 10, 2025 at 03:35 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projekt`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, 1, 'Dodał nowe zlecenie serwisowe', '2025-11-08 21:48:05'),
(2, 2, 'Dodał nowe zlecenie serwisowe', '2025-11-08 21:54:25'),
(3, 2, 'Zaktualizował status zlecenia #2', '2025-11-08 21:54:37'),
(4, 2, 'Zaktualizował status zlecenia #2', '2025-11-08 21:54:39'),
(5, 4, 'Wylogowanie administratora', '2025-11-09 12:59:25'),
(6, 4, 'Usunięto użytkownika ID: 5', '2025-11-09 13:04:44'),
(7, 4, 'Wylogowanie administratora', '2025-11-09 13:04:58'),
(8, 4, 'Wylogowanie administratora', '2025-11-09 13:10:59'),
(9, 1, 'Dodał nowe zlecenie serwisowe', '2025-11-09 13:15:23'),
(10, 1, 'Zaktualizował status zlecenia #3', '2025-11-09 13:15:31'),
(11, 1, 'Zaktualizował status zlecenia #3', '2025-11-09 13:15:34'),
(12, 4, 'Usunięto zlecenie ID: 3', '2025-11-09 13:16:03'),
(13, 4, 'Wylogowanie administratora', '2025-11-09 13:16:08'),
(14, 4, 'Wylogowanie administratora', '2025-11-10 14:30:16');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_model` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('do_zrobienia','zrobione') DEFAULT 'do_zrobienia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `user_id`, `car_model`, `description`, `image`, `status`, `created_at`) VALUES
(1, 1, 'Renault Twingo', 'JEBANY DPF', NULL, 'do_zrobienia', '2025-11-08 21:48:05'),
(2, 2, 'Renault Twingo', 'DPF KURWA I ELEKTRYKA', NULL, 'do_zrobienia', '2025-11-08 21:54:25');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `auth_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `auth_code`, `created_at`) VALUES
(1, 'KacperLiseq', '$2y$10$MABcjXtDJuWpYQV1fkBU0O20YXThntxAJ5GzEj7xCcoT9kMAcNMhG', 'user', '890008', '2025-11-08 21:47:31'),
(2, 'Karol Krawlet', '$2y$10$SeBHRiXKZtqTp1QGQnCfxORDfxlriMuu65U95Hxfk470C65KKv0ai', 'user', '258806', '2025-11-08 21:53:33'),
(4, 'admin', '$2y$10$UyMKyZkANadP1KsDFgwt1etqgSfmznrPUYTa2oyJZVnCZLY5wGsPm', 'admin', '213489', '2025-11-09 12:53:54');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
