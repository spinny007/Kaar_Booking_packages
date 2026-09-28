CREATE TABLE IF NOT EXISTS `#__kaar_vehicle_inspections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `vehicle_id` bigint unsigned NOT NULL, `inspector_id` int unsigned NOT NULL,
  `inspection_date` date NOT NULL, `result` varchar(24) NOT NULL, `odometer_km` decimal(12,2) DEFAULT NULL,
  `checklist_json` longtext NOT NULL, `notes` text, `next_due_date` date DEFAULT NULL,
  `created` datetime NOT NULL, `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_inspection_vehicle` (`vehicle_id`,`inspection_date`), KEY `idx_kaar_inspection_due` (`result`,`next_due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_document_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT, `title` varchar(190) NOT NULL, `code` varchar(64) NOT NULL,
  `subject_type` varchar(32) NOT NULL, `expiry_required` tinyint NOT NULL DEFAULT 0,
  `required_for_compliance` tinyint NOT NULL DEFAULT 1, `state` tinyint NOT NULL DEFAULT 1, `ordering` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_document_type` (`subject_type`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__kaar_document_types` (`title`,`code`,`subject_type`,`expiry_required`,`required_for_compliance`,`state`,`ordering`) VALUES
('Address proof','address_proof','customer',0,1,1,1),('Driving licence','driving_licence','driver',1,1,1,1),
('Address proof','address_proof','driver',0,1,1,2),('Police verification','police_verification','driver',1,0,1,3),
('Registration certificate','registration_certificate','vehicle',0,1,1,1),('Commercial permit','commercial_permit','vehicle',1,1,1,2),
('Commercial insurance','commercial_insurance','vehicle',1,1,1,3),('Fitness certificate','fitness_certificate','vehicle',1,1,1,4),
('Pollution Under Control','puc','vehicle',1,1,1,5);

CREATE TABLE IF NOT EXISTS `#__kaar_companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(190) NOT NULL, `organisation_type` varchar(64) NOT NULL DEFAULT 'touring_company', `legal_type` varchar(64) NOT NULL DEFAULT '',
  `registration_number` varchar(100) DEFAULT NULL, `owner_name` varchar(190) NOT NULL DEFAULT '',
  `email` varchar(190) NOT NULL DEFAULT '', `phone` varchar(40) NOT NULL DEFAULT '', `address` text NOT NULL,
  `logo_path` varchar(500) DEFAULT NULL, `status` varchar(24) NOT NULL DEFAULT 'pending',
  `created_by` int unsigned NOT NULL DEFAULT 0, `created` datetime NOT NULL, `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_company_registration` (`registration_number`), KEY `idx_kaar_company_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Driver and vehicle company_id columns are added conditionally by script.php for MySQL/MariaDB portability.
