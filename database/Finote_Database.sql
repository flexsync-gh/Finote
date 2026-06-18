-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Jun 2026 pada 15.01
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web2`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `saving_goals`
--

CREATE TABLE `saving_goals` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `current_amount` decimal(15,2) DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `saving_transactions`
--

CREATE TABLE `saving_transactions` (
  `id` int(11) NOT NULL,
  `saving_goal_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `goalid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `transaction_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `accountid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_date` date NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `transactions`
--
DELIMITER $$
CREATE TRIGGER `trg_after_delete_transaction` AFTER DELETE ON `transactions` FOR EACH ROW BEGIN
    IF OLD.type = 'income' THEN
        UPDATE accounts
        SET balance = balance - OLD.amount
        WHERE id = OLD.accountid;
    ELSEIF OLD.type = 'expense' THEN
        UPDATE accounts
        SET balance = balance + OLD.amount
        WHERE id = OLD.accountid;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_insert_transaction` AFTER INSERT ON `transactions` FOR EACH ROW BEGIN
    IF NEW.type = 'income' THEN
        UPDATE accounts
        SET balance = balance + NEW.amount
        WHERE id = NEW.accountid;
    ELSEIF NEW.type = 'expense' THEN
        UPDATE accounts
        SET balance = balance - NEW.amount
        WHERE id = NEW.accountid;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_transaction` AFTER UPDATE ON `transactions` FOR EACH ROW BEGIN
    IF OLD.type = 'income' AND NEW.type = 'expense' THEN
    UPDATE accounts
    SET balance = balance - OLD.amount
    WHERE id = OLD.accountid;

    ELSEIF OLD.type = 'expense' AND NEW.type = 'income' THEN
        UPDATE accounts
        SET balance = balance + OLD.amount
        WHERE id = OLD.accountid;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(70) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(300) NOT NULL,
  `phonenumber` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_budget_vs_pengeluaran`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_budget_vs_pengeluaran` (
`userid` int(11)
,`nama_user` varchar(70)
,`nama_kategori` varchar(100)
,`budget` decimal(15,2)
,`total_pengeluaran` decimal(37,2)
,`sisa_budget` decimal(38,2)
,`status_budget` varchar(15)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_laporan_transaksi`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_laporan_transaksi` (
`id_transaksi` int(11)
,`userid` int(11)
,`nama_user` varchar(70)
,`nama_akun` varchar(100)
,`nama_kategori` varchar(100)
,`type` varchar(50)
,`amount` decimal(15,2)
,`description` text
,`transaction_date` date
,`status_transaksi` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_ringkasan_keuangan_user`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_ringkasan_keuangan_user` (
`userid` int(11)
,`nama_user` varchar(70)
,`total_income` decimal(37,2)
,`total_expense` decimal(37,2)
,`saldo_bersih` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_budget_vs_pengeluaran`
--
DROP TABLE IF EXISTS `v_budget_vs_pengeluaran`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_budget_vs_pengeluaran`  AS SELECT `b`.`userid` AS `userid`, `u`.`name` AS `nama_user`, `c`.`name` AS `nama_kategori`, `b`.`amount` AS `budget`, coalesce(sum(`t`.`amount`),0) AS `total_pengeluaran`, `b`.`amount`- coalesce(sum(`t`.`amount`),0) AS `sisa_budget`, CASE WHEN coalesce(sum(`t`.`amount`),0) > `b`.`amount` THEN 'Melebihi Budget' WHEN coalesce(sum(`t`.`amount`),0) >= `b`.`amount` * 0.8 THEN 'Hampir Habis' ELSE 'Aman' END AS `status_budget` FROM (((`budgets` `b` join `users` `u` on(`b`.`userid` = `u`.`id`)) join `categories` `c` on(`b`.`categoryid` = `c`.`id`)) left join `transactions` `t` on(`t`.`userid` = `b`.`userid` and `t`.`categoryid` = `b`.`categoryid` and `t`.`type` = 'expense' and month(`t`.`transaction_date`) = `b`.`month` and year(`t`.`transaction_date`) = `b`.`year`)) GROUP BY `b`.`userid`, `u`.`name`, `c`.`name`, `b`.`amount` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_laporan_transaksi`
--
DROP TABLE IF EXISTS `v_laporan_transaksi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_laporan_transaksi`  AS SELECT `t`.`id` AS `id_transaksi`, `u`.`id` AS `userid`, `u`.`name` AS `nama_user`, `a`.`name` AS `nama_akun`, `c`.`name` AS `nama_kategori`, `t`.`type` AS `type`, `t`.`amount` AS `amount`, `t`.`description` AS `description`, `t`.`transaction_date` AS `transaction_date`, `hitung_status_transaksi`(`t`.`type`,`t`.`amount`) AS `status_transaksi` FROM (((`transactions` `t` join `users` `u` on(`t`.`userid` = `u`.`id`)) join `accounts` `a` on(`t`.`accountid` = `a`.`id`)) join `categories` `c` on(`t`.`categoryid` = `c`.`id`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_ringkasan_keuangan_user`
--
DROP TABLE IF EXISTS `v_ringkasan_keuangan_user`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ringkasan_keuangan_user`  AS SELECT `u`.`id` AS `userid`, `u`.`name` AS `nama_user`, coalesce(sum(case when `t`.`type` = 'income' then `t`.`amount` else 0 end),0) AS `total_income`, coalesce(sum(case when `t`.`type` = 'expense' then `t`.`amount` else 0 end),0) AS `total_expense`, coalesce(sum(case when `t`.`type` = 'income' then `t`.`amount` else -`t`.`amount` end),0) AS `saldo_bersih` FROM (`users` `u` left join `transactions` `t` on(`t`.`userid` = `u`.`id`)) GROUP BY `u`.`id`, `u`.`name` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_account_user` (`userid`);

--
-- Indeks untuk tabel `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_budget_user` (`userid`),
  ADD KEY `fk_budget_category` (`categoryid`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category_user` (`userid`);

--
-- Indeks untuk tabel `saving_goals`
--
ALTER TABLE `saving_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_savinggoal_user` (`userid`);

--
-- Indeks untuk tabel `saving_transactions`
--
ALTER TABLE `saving_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_saving_transaction_goal` (`saving_goal_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transaction_user` (`userid`),
  ADD KEY `fk_transaction_account` (`accountid`),
  ADD KEY `fk_transaction_category` (`categoryid`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phonenumber` (`phonenumber`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `saving_goals`
--
ALTER TABLE `saving_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `saving_transactions`
--
ALTER TABLE `saving_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_account_user` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `fk_budget_category` FOREIGN KEY (`categoryid`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_budget_user` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_user` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `saving_goals`
--
ALTER TABLE `saving_goals`
  ADD CONSTRAINT `fk_savinggoal_user` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `saving_transactions`
--
ALTER TABLE `saving_transactions`
  ADD CONSTRAINT `fk_saving_transaction_goal` FOREIGN KEY (`saving_goal_id`) REFERENCES `saving_goals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_account` FOREIGN KEY (`accountid`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_category` FOREIGN KEY (`categoryid`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_user` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
