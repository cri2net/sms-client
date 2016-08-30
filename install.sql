
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

