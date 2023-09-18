CREATE TABLE `customer_referral_credit` (
	`id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` mediumint(11) unsigned NOT NULL,
	`credit_state` enum('AVAILABLE','EXPIRED','CONSUMED') NOT NULL DEFAULT 'AVAILABLE',
	`dollar_value` decimal(6,2) NOT NULL DEFAULT '0.00',
	`order_id` mediumint(11) DEFAULT NULL,
	`parent_of_partial` mediumint(11) DEFAULT NULL,
	`original_amount` decimal(6,2) DEFAULT NULL,
	`expiration_date` datetime DEFAULT NULL,
	`timestamp_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`timestamp_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_by` int(11) unsigned DEFAULT NULL,
	`created_by` int(11) unsigned DEFAULT NULL,
	`is_deleted` tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `IDX_user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;