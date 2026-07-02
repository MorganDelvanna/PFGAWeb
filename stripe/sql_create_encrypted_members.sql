-- SQL schema for storing encrypted member records (MariaDB 5.5 compatible)
-- Run this in your MariaDB database to create the required table.

CREATE TABLE IF NOT EXISTS encrypted_members (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid VARCHAR(36) NOT NULL,
  `type` VARCHAR(32) DEFAULT NULL,
  data MEDIUMTEXT NOT NULL,
  created_at DATETIME DEFAULT NULL,
  emailed TINYINT(1) NOT NULL DEFAULT 0,
  emailed_at DATETIME DEFAULT NULL,
  canceled_at DATETIME DEFAULT NULL,
  transactionId VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY ux_uuid (uuid),
  KEY idx_emailed (emailed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
