CREATE TABLE IF NOT EXISTS `#__kaar_media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `subject_type` varchar(32) NOT NULL, `subject_id` bigint unsigned NOT NULL,
  `purpose` varchar(32) NOT NULL DEFAULT 'gallery', `path` varchar(500) NOT NULL, `mime_type` varchar(100) NOT NULL,
  `file_hash` char(64) NOT NULL, `alt_text` varchar(255) NOT NULL DEFAULT '', `ordering` int NOT NULL DEFAULT 0,
  `created_by` int unsigned NOT NULL DEFAULT 0, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_media_subject` (`subject_type`,`subject_id`,`purpose`,`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
-- Conditional driver/inspection columns are added by the installer repair script for MySQL/MariaDB portability.
