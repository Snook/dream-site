ALTER TABLE `dreamsite`.`store`
    ADD COLUMN `bio_store_name` varchar(255) NULL AFTER `core_pricing_tier`,
	ADD COLUMN `bio_primary_party_name` varchar(255) NULL AFTER `bio_store_name`,
    ADD COLUMN `bio_primary_party_title` varchar(255) NULL AFTER `bio_primary_party_name`,
    ADD COLUMN `bio_primary_party_story` TEXT NULL AFTER `bio_primary_party_title`,
    ADD COLUMN `bio_secondary_party_name` varchar(255) NULL AFTER `bio_primary_party_story`,
    ADD COLUMN `bio_secondary_party_title` varchar(255) NULL AFTER `bio_secondary_party_name`,
    ADD COLUMN `bio_secondary_party_story` TEXT NULL AFTER `bio_secondary_party_title`,
    ADD COLUMN `bio_team_description` TEXT NULL AFTER `bio_secondary_party_story`,
    ADD COLUMN `bio_store_hours` TEXT NULL AFTER `bio_team_description`;

ALTER TABLE `dreamsite`.`store`
    ADD COLUMN `bio_store_holiday_hours` TEXT NULL AFTER `bio_store_hours`;






