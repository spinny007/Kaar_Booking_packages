CREATE TABLE IF NOT EXISTS `#__kaar_vehicle_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(190) NOT NULL,
  `alias` varchar(190) NOT NULL,
  `passenger_capacity` smallint unsigned NOT NULL DEFAULT 4,
  `luggage_capacity` smallint unsigned NOT NULL DEFAULT 2,
  `with_driver` tinyint NOT NULL DEFAULT 1,
  `self_drive` tinyint NOT NULL DEFAULT 0,
  `state` tinyint NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_vehicle_types_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_vehicles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_type_id` int unsigned NOT NULL,
  `registration_number` varchar(64) NOT NULL,
  `make` varchar(100) NOT NULL DEFAULT '', `model` varchar(100) NOT NULL DEFAULT '', `model_year` smallint unsigned DEFAULT NULL,
  `condition_status` varchar(32) NOT NULL DEFAULT 'good', `operational_status` varchar(32) NOT NULL DEFAULT 'active',
  `compliance_status` varchar(32) NOT NULL DEFAULT 'pending', `odometer_km` decimal(12,2) DEFAULT NULL,
  `state` tinyint NOT NULL DEFAULT 1, `created` datetime NOT NULL, `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_vehicle_registration` (`registration_number`), KEY `idx_kaar_vehicle_type` (`vehicle_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_drivers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `user_id` int unsigned DEFAULT NULL,
  `display_name` varchar(190) NOT NULL, `phone_masked` varchar(32) NOT NULL DEFAULT '',
  `status` varchar(32) NOT NULL DEFAULT 'pending', `compliance_status` varchar(32) NOT NULL DEFAULT 'pending',
  `online_state` varchar(16) NOT NULL DEFAULT 'offline', `created` datetime NOT NULL, `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_driver_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `user_id` int unsigned NOT NULL,
  `display_name` varchar(190) NOT NULL, `email_verified_at` datetime DEFAULT NULL,
  `kyc_status` varchar(32) NOT NULL DEFAULT 'not_started', `marketing_consent` tinyint NOT NULL DEFAULT 0,
  `created` datetime NOT NULL, `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_customer_user` (`user_id`), KEY `idx_kaar_customer_kyc` (`kyc_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_otp_challenges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `subject_type` varchar(32) NOT NULL, `subject_id` bigint unsigned NOT NULL,
  `purpose` varchar(64) NOT NULL, `destination_hash` char(64) NOT NULL, `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL, `attempts` smallint unsigned NOT NULL DEFAULT 0, `max_attempts` smallint unsigned NOT NULL DEFAULT 5,
  `consumed_at` datetime DEFAULT NULL, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_otp_lookup` (`subject_type`,`subject_id`,`purpose`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_api_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `family_id` char(36) NOT NULL, `user_id` int unsigned NOT NULL,
  `access_hash` char(64) NOT NULL, `refresh_hash` char(64) NOT NULL, `device_name` varchar(120) NOT NULL DEFAULT 'Mobile app',
  `access_expires` datetime NOT NULL, `refresh_expires` datetime NOT NULL, `last_used` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL, `revoke_reason` varchar(64) DEFAULT NULL, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_access_hash` (`access_hash`), UNIQUE KEY `idx_kaar_refresh_hash` (`refresh_hash`),
  KEY `idx_kaar_session_user` (`user_id`,`revoked_at`,`refresh_expires`), KEY `idx_kaar_session_family` (`family_id`,`revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `subject_type` varchar(32) NOT NULL, `subject_id` bigint unsigned NOT NULL,
  `document_type` varchar(64) NOT NULL, `masked_reference` varchar(100) NOT NULL DEFAULT '', `file_key` varchar(500) DEFAULT NULL,
  `file_hash` char(64) DEFAULT NULL, `mime_type` varchar(100) DEFAULT NULL, `issue_date` date DEFAULT NULL, `expiry_date` date DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'uploaded', `scan_status` varchar(32) NOT NULL DEFAULT 'pending',
  `verified_by` int unsigned DEFAULT NULL, `verified_at` datetime DEFAULT NULL, `deleted_at` datetime DEFAULT NULL,
  `created_by` int unsigned NOT NULL, `created` datetime NOT NULL, `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_document_subject` (`subject_type`,`subject_id`), KEY `idx_kaar_document_expiry` (`status`,`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_document_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `document_id` bigint unsigned NOT NULL, `reviewer_id` int unsigned NOT NULL,
  `decision` varchar(32) NOT NULL, `reason` text, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_review_document` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `title` varchar(190) NOT NULL, `alias` varchar(190) NOT NULL,
  `summary` text, `description` mediumtext, `duration_days` smallint unsigned NOT NULL DEFAULT 1, `duration_nights` smallint unsigned NOT NULL DEFAULT 0,
  `booking_mode` varchar(32) NOT NULL DEFAULT 'whole_vehicle', `vehicle_type_id` int unsigned DEFAULT NULL,
  `capacity` smallint unsigned DEFAULT NULL, `base_price` decimal(14,2) NOT NULL DEFAULT 0, `currency` char(3) NOT NULL DEFAULT 'INR',
  `sale_start` datetime DEFAULT NULL, `sale_end` datetime DEFAULT NULL, `state` tinyint NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0, `created` datetime NOT NULL, `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_package_alias` (`alias`), KEY `idx_kaar_package_state` (`state`,`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_package_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `package_id` bigint unsigned NOT NULL,
  `item_type` varchar(24) NOT NULL DEFAULT 'included', `title` varchar(255) NOT NULL, `description` text,
  `ordering` int NOT NULL DEFAULT 0, `state` tinyint NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`), KEY `idx_kaar_package_item` (`package_id`,`item_type`,`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_departures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `package_id` bigint unsigned NOT NULL, `departure_at` datetime NOT NULL, `return_at` datetime DEFAULT NULL,
  `capacity` smallint unsigned NOT NULL DEFAULT 0, `held` smallint unsigned NOT NULL DEFAULT 0, `sold` smallint unsigned NOT NULL DEFAULT 0,
  `state` tinyint NOT NULL DEFAULT 1, PRIMARY KEY (`id`), KEY `idx_kaar_departure` (`package_id`,`departure_at`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_quotes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `public_token` char(36) NOT NULL, `customer_id` bigint unsigned DEFAULT NULL,
  `product_type` varchar(40) NOT NULL, `pricing_snapshot` longtext NOT NULL, `total_amount` decimal(14,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'INR', `expires_at` datetime NOT NULL, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_quote_token` (`public_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `reference` varchar(32) NOT NULL, `source` varchar(32) NOT NULL DEFAULT 'website',
  `product_type` varchar(40) NOT NULL, `service_type` varchar(40) NOT NULL, `customer_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(190) NOT NULL, `customer_email` varchar(190) NOT NULL, `customer_phone` varchar(40) NOT NULL DEFAULT '',
  `package_id` bigint unsigned DEFAULT NULL, `departure_id` bigint unsigned DEFAULT NULL, `vehicle_type_id` int unsigned DEFAULT NULL,
  `origin_text` varchar(500) NOT NULL DEFAULT '', `destination_text` varchar(500) NOT NULL DEFAULT '', `stops_json` text,
  `departure_at` datetime NOT NULL, `return_at` datetime DEFAULT NULL, `passengers` smallint unsigned NOT NULL DEFAULT 1,
  `driver_mode` varchar(24) NOT NULL DEFAULT 'with_driver', `status` varchar(40) NOT NULL DEFAULT 'pending_admin_review',
  `payment_status` varchar(32) NOT NULL DEFAULT 'unpaid', `pricing_snapshot` longtext, `total_amount` decimal(14,2) NOT NULL DEFAULT 0,
  `currency` char(3) NOT NULL DEFAULT 'INR', `admin_notes` text, `created_by` int unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL, `modified_by` int unsigned NOT NULL DEFAULT 0, `modified` datetime DEFAULT NULL, `version` int unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_booking_reference` (`reference`),
  KEY `idx_kaar_booking_queue` (`status`,`departure_at`), KEY `idx_kaar_booking_customer` (`customer_id`,`created`), KEY `idx_kaar_booking_package` (`package_id`,`departure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_booking_resources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `booking_id` bigint unsigned NOT NULL, `resource_type` varchar(24) NOT NULL,
  `resource_id` bigint unsigned NOT NULL, `status` varchar(24) NOT NULL DEFAULT 'allocated', `hold_expires_at` datetime DEFAULT NULL,
  `created` datetime NOT NULL, PRIMARY KEY (`id`), KEY `idx_kaar_resource_booking` (`booking_id`),
  KEY `idx_kaar_resource_conflict` (`resource_type`,`resource_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_booking_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `booking_id` bigint unsigned NOT NULL, `from_status` varchar(40) DEFAULT NULL,
  `to_status` varchar(40) NOT NULL, `actor_id` int unsigned NOT NULL DEFAULT 0, `reason` text, `correlation_id` varchar(64) NOT NULL DEFAULT '', `created` datetime NOT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_history_booking` (`booking_id`,`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `booking_id` bigint unsigned NOT NULL, `provider` varchar(64) NOT NULL,
  `provider_order_id` varchar(190) NOT NULL DEFAULT '', `provider_payment_id` varchar(190) NOT NULL DEFAULT '', `idempotency_key` varchar(100) NOT NULL,
  `amount` decimal(14,2) NOT NULL, `currency` char(3) NOT NULL, `status` varchar(32) NOT NULL DEFAULT 'created', `verified_at` datetime DEFAULT NULL,
  `created` datetime NOT NULL, `modified` datetime DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_payment_idem` (`idempotency_key`), KEY `idx_kaar_payment_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_rides` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `booking_id` bigint unsigned NOT NULL, `driver_id` bigint unsigned DEFAULT NULL, `vehicle_id` bigint unsigned DEFAULT NULL,
  `dispatch_status` varchar(32) NOT NULL DEFAULT 'requested', `pickup_lat` decimal(10,7) DEFAULT NULL, `pickup_lng` decimal(10,7) DEFAULT NULL,
  `drop_lat` decimal(10,7) DEFAULT NULL, `drop_lng` decimal(10,7) DEFAULT NULL, `trip_pin_hash` varchar(255) DEFAULT NULL,
  `requested_at` datetime NOT NULL, `accepted_at` datetime DEFAULT NULL, `arrived_at` datetime DEFAULT NULL, `started_at` datetime DEFAULT NULL, `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_ride_booking` (`booking_id`), KEY `idx_kaar_ride_dispatch` (`dispatch_status`,`requested_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_driver_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `driver_id` bigint unsigned NOT NULL, `ride_id` bigint unsigned DEFAULT NULL,
  `latitude` decimal(10,7) NOT NULL, `longitude` decimal(10,7) NOT NULL, `accuracy_m` decimal(8,2) DEFAULT NULL, `recorded_at` datetime NOT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_location_driver` (`driver_id`,`recorded_at`), KEY `idx_kaar_location_ride` (`ride_id`,`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `user_id` int unsigned DEFAULT NULL, `channel` varchar(24) NOT NULL,
  `event_type` varchar(64) NOT NULL, `subject_type` varchar(32) NOT NULL DEFAULT '', `subject_id` bigint unsigned DEFAULT NULL,
  `payload_json` text NOT NULL, `status` varchar(24) NOT NULL DEFAULT 'queued', `attempts` smallint unsigned NOT NULL DEFAULT 0,
  `available_at` datetime NOT NULL, `sent_at` datetime DEFAULT NULL, `last_error` text, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_notification_queue` (`status`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_app_layouts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `platform` varchar(24) NOT NULL DEFAULT 'all', `locale` varchar(12) NOT NULL DEFAULT '*',
  `version` int unsigned NOT NULL, `status` varchar(20) NOT NULL DEFAULT 'draft', `schema_json` longtext NOT NULL,
  `published_at` datetime DEFAULT NULL, `created_by` int unsigned NOT NULL, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `idx_kaar_layout_version` (`platform`,`locale`,`version`), KEY `idx_kaar_layout_status` (`status`,`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kaar_audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT, `actor_id` int unsigned NOT NULL DEFAULT 0, `action` varchar(100) NOT NULL,
  `subject_type` varchar(32) NOT NULL, `subject_id` bigint unsigned DEFAULT NULL, `correlation_id` varchar(64) NOT NULL DEFAULT '',
  `ip_hash` char(64) DEFAULT NULL, `metadata_json` text, `created` datetime NOT NULL,
  PRIMARY KEY (`id`), KEY `idx_kaar_audit_subject` (`subject_type`,`subject_id`,`created`), KEY `idx_kaar_audit_action` (`action`,`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
