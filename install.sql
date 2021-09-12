
-- Не забываем добавлять к имени таблицы префикс своего проекта

-- version 1.0.0
CREATE TABLE IF NOT EXISTS `sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('new','sending','complete','cancel','fail') NOT NULL DEFAULT 'new',
  `to` varchar(100) NOT NULL,
  `created_at` double NOT NULL,
  `updated_at` double NOT NULL,
  `send_at` double DEFAULT NULL,
  `min_sending_time` double NOT NULL,
  `replace_data` text,
  `raw_text` text,
  `processing` varchar(50) DEFAULT NULL,
  `processing_data` mediumtext,
  `processing_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`min_sending_time`),
  KEY `processing` (`processing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- version 1.0.1
ALTER TABLE `sms`
  ADD COLUMN `additional` MEDIUMTEXT NULL AFTER `processing_status`;

-- version 1.1.1
ALTER TABLE `sms`
  ADD COLUMN alfaname VARCHAR(50) NULL AFTER additional;

-- version 1.4.6
ALTER TABLE sms
  CHANGE `status` `status` ENUM('new','sending','complete','cancel','fail') CHARSET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'new' NOT NULL,
  CHANGE `to` `to` VARCHAR(100) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  CHANGE `replace_data` `replace_data` TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHANGE `raw_text` `raw_text` TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHANGE `processing` `processing` VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHANGE `processing_data` `processing_data` MEDIUMTEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHANGE `processing_status` `processing_status` VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHANGE `additional` `additional` MEDIUMTEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHANGE `alfaname` `alfaname` VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  CHARSET=utf8mb4, COLLATE=utf8mb4_general_ci;
