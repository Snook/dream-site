# Obfuscate Customer Data
DROP TABLE
    IF EXISTS firstnames;

CREATE TEMPORARY TABLE firstnames (val VARCHAR(50));

INSERT INTO firstnames (val)
VALUES
('Mary'),
('Patricia'),
('Linda'),
('Barbara'),
('Elizabeth'),
('Jennifer'),
('Maria'),
('Susan'),
('Margaret'),
('Dorothy'),
('Lisa'),
('Nancy'),
('Karen'),
('Betty'),
('Helen'),
('Sandra'),
('Donna'),
('Carol'),
('Ruth'),
('Sharon'),
('Michelle'),
('Laura'),
('Sarah'),
('Kimberly'),
('Deborah'),
('Jessica'),
('Shirley'),
('Cynthia'),
('Angela'),
('Melissa'),
('Brenda'),
('Amy'),
('Anna'),
('Rebecca'),
('Virginia'),
('Kathleen'),
('Pamela'),
('Martha'),
('Debra'),
('Amanda'),
('Stephanie'),
('Carolyn'),
('Christine'),
('Marie'),
('Janet'),
('Catherine'),
('Frances'),
('Ann'),
('Joyce'),
('Diane'),
('Alice'),
('Julie'),
('Heather'),
('Teresa'),
('Doris'),
('Gloria'),
('Evelyn'),
('Jean'),
('Cheryl'),
('Mildred'),
('Katherine'),
('Joan'),
('James'),
('John'),
('Robert'),
('Michael'),
('William'),
('David'),
('Richard'),
('Charles'),
('Joseph'),
('Thomas'),
('Christopher'),
('Daniel'),
('Paul'),
('Mark'),
('Donald'),
('George'),
('Kenneth'),
('Steven'),
('Edward'),
('Brian'),
('Ronald'),
('Anthony'),
('Kevin'),
('Jason'),
('Matthew'),
('Gary'),
('Timothy'),
('Jose'),
('Larry'),
('Jeffrey'),
('Frank'),
('Scott'),
('Eric'),
('Stephen'),
('Andrew'),
('Raymond'),
('Gregory'),
('Joshua'),
('Jerry'),
('Dennis'),
('Walter'),
('Patrick'),
('Peter'),
('Harold'),
('Douglas'),
('Henry'),
('Carl'),
('Arthur'),
('Ryan'),
('Roger'),
('Joe'),
('Juan'),
('Jack'),
('Albert'),
('Jonathan'),
('Justin'),
('Terry'),
('Gerald'),
('Keith'),
('Samuel'),
('Willie'),
('Ralph'),
('Lawrence'),
('Nicholas'),
('Roy'),
('Benjamin'),
('Bruce');

DROP TABLE
    IF EXISTS lastnames;

CREATE TEMPORARY TABLE lastnames (val VARCHAR(50));

INSERT INTO lastnames (val)
VALUES
('Smith'),
('Johnson'),
('Williams'),
('Jones'),
('Brown'),
('Davis'),
('Miller'),
('Wilson'),
('Moore'),
('Taylor'),
('Anderson'),
('Thomas'),
('Jackson'),
('White'),
('Harris'),
('Martin'),
('Thompson'),
('Garcia'),
('Martinez'),
('Robinson'),
('Clark'),
('Rodriguez'),
('Lewis'),
('Lee'),
('Walker'),
('Hall'),
('Allen'),
('Young'),
('Hernandez'),
('King'),
('Wright'),
('Lopez'),
('Hill'),
('Scott'),
('Green'),
('Adams'),
('Baker'),
('Gonzalez'),
('Nelson'),
('Carter'),
('Mitchell'),
('Perez'),
('Roberts'),
('Turner'),
('Phillips'),
('Campbell'),
('Parker'),
('Evans'),
('Edwards'),
('Collins'),
('Stewart'),
('Sanchez'),
('Morris'),
('Rogers'),
('Reed'),
('Cook'),
('Morgan'),
('Bell'),
('Murphy'),
('Bailey'),
('Rivera'),
('Cooper'),
('Richardson'),
('Cox'),
('Howard'),
('Ward'),
('Torres'),
('Peterson'),
('Gray'),
('Ramirez'),
('James'),
('Watson'),
('Brooks'),
('Kelly'),
('Sanders'),
('Price'),
('Bennett'),
('Wood'),
('Barnes'),
('Ross'),
('Henderson'),
('Coleman'),
('Jenkins'),
('Perry'),
('Powell'),
('Long'),
('Patterson'),
('Hughes'),
('Flores'),
('Washington'),
('Butler'),
('Simmons'),
('Foster'),
('Gonzales'),
('Bryant'),
('Alexander'),
('Russell'),
('Griffin'),
('Diaz'),
('Hayes'),
('Myers'),
('Ford'),
('Hamilton'),
('Graham'),
('Sullivan'),
('Wallace'),
('Woods'),
('Cole'),
('West'),
('Jordan'),
('Owens'),
('Reynolds'),
('Fisher'),
('Ellis'),
('Harrison'),
('Gibson'),
('Mcdonald'),
('Cruz'),
('Marshall'),
('Ortiz'),
('Gomez'),
('Murray'),
('Freeman'),
('Wells'),
('Webb'),
('Simpson'),
('Stevens'),
('Tucker'),
('Porter'),
('Hunter'),
('Hicks'),
('Crawford'),
('Henry'),
('Boyd'),
('Mason'),
('Morales'),
('Kennedy'),
('Warren'),
('Dixon'),
('Ramos'),
('Reyes'),
('Burns'),
('Gordon'),
('Shaw'),
('Holmes'),
('Rice'),
('Robertson'),
('Hunt');

DROP TABLE
    IF EXISTS phone_nums;

CREATE TEMPORARY TABLE phone_nums (val VARCHAR(50));

INSERT INTO phone_nums (val)
VALUES
("111-111-1111"),
("222-222-2222"),
("333-333-3333"),
("444-444-4444"),
("555-555-5555"),
("666-666-6666"),
("777-777-7777"),
("888-888-8888"),
("999-999-9999");

DROP TABLE
    IF EXISTS phone_nums2;

CREATE TEMPORARY TABLE phone_nums2 (val VARCHAR(50));

INSERT INTO phone_nums2 (val)
VALUES
("111-111-1111"),
("222-222-2222"),
("333-333-3333"),
("444-444-4444"),
("555-555-5555"),
("666-666-6666"),
("777-777-7777"),
("888-888-8888"),
("999-999-9999");

UPDATE `user`
SET firstname = (
    SELECT
        val
    FROM
        firstnames
    ORDER BY
        rand()
    LIMIT 1
    ),
    lastname = (
SELECT
    val
FROM
    lastnames
ORDER BY
    rand()
    LIMIT 1
    ),
    telephone_1 = (
SELECT
    val
FROM
    phone_nums
ORDER BY
    rand()
    LIMIT 1
    ),
    telephone_2 = (
SELECT
    val
FROM
    phone_nums2
ORDER BY
    rand()
    LIMIT 1
    );

UPDATE user_preferences
SET pvalue = (
    SELECT
        val
    FROM
        phone_nums
    ORDER BY
        rand()
    LIMIT 1
    )
WHERE pkey = 'TEXT_MESSAGE_TARGET_NUMBER' AND pvalue != 'UNANSWERED';

DROP TABLE
    IF EXISTS street_nums;

CREATE TEMPORARY TABLE street_nums (val VARCHAR(50));

INSERT INTO street_nums (val)
VALUES
("12"),
("15678"),
("233"),
("4563"),
("2222"),
("19271"),
("129"),
("11111"),
("223");

DROP TABLE
    IF EXISTS street_names;

CREATE TEMPORARY TABLE street_names (val VARCHAR(50));

INSERT INTO street_names (val)
VALUES
("3rd"),
("4th"),
("Oak"),
("Elm"),
("Main"),
("255th"),
("1st"),
("Sad Sack"),
("Caviar"),
('Lachance'),
('Keaton'),
('Israel'),
('Ferrara'),
('Falcon'),
('Clemens'),
('Blocker'),
('Applegate'),
('Paz'),
('Needham'),
('Mojica'),
('Kuykendall');

DROP TABLE
    IF EXISTS street_types;

CREATE TEMPORARY TABLE street_types (val VARCHAR(50));

INSERT INTO street_types (val)
VALUES
("ST"),
("street"),
("Ave"),
("AV"),
("Avenue"),
("Blvd"),
("Way");

DROP TABLE
    IF EXISTS street_dirs;

CREATE TEMPORARY TABLE street_dirs (val VARCHAR(50));

INSERT INTO street_dirs (val)
VALUES
("N"),
("S"),
("E"),
("W"),
("SE"),
("SW"),
("NE");

UPDATE address
SET address_line1 = CONCAT(
        (
            SELECT
                val
            FROM
                street_nums
            ORDER BY
                rand()
            LIMIT 1
    ),
	" ",
	(
		SELECT
			val
		FROM
			street_names
		ORDER BY
			rand()
		LIMIT 1
	),
	" ",
	(
		SELECT
			val
		FROM
			street_types
		ORDER BY
			rand()
		LIMIT 1
	),
	" ",
	(
		SELECT
			val
		FROM
			street_dirs
		ORDER BY
			rand()
		LIMIT 1
	)
);

UPDATE orders_address
SET address_line1 = CONCAT(
        (
            SELECT
                val
            FROM
                street_nums
            ORDER BY
                rand()
            LIMIT 1
    ),
	" ",
	(
		SELECT
			val
		FROM
			street_names
		ORDER BY
			rand()
		LIMIT 1
	),
	" ",
	(
		SELECT
			val
		FROM
			street_types
		ORDER BY
			rand()
		LIMIT 1
	),
	" ",
	(
		SELECT
			val
		FROM
			street_dirs
		ORDER BY
			rand()
		LIMIT 1
	)
);

# Nullify merchant accounts accept for Mill Creek
UPDATE merchant_accounts SET ma_username = '', ma_password = '' WHERE franchise_id != 220;

# Reset user type and home store for self
UPDATE `user` SET user_type='SITE_ADMIN', home_store_id='244' WHERE id = '662598';

# update test password to ddRy123456789
UPDATE user_login SET ul_password2 = '$2a$08$J9Wstbo1vyHCKgZUIJnoWukhZ28XSpdENaHnLdeDM7CaL1FH3Hg4e', last_password_update = '2030-01-01 00:00:00' WHERE user_id = '662598';

# change private session passwords to 123456 to making things easier to test
UPDATE `session` SET session_password = '123456' WHERE session_password IS NOT NULL AND session_password != '';

# Nullify customer email addresses
UPDATE `user`, user_login SET user_login.ul_username = CONCAT(SUBSTRING_INDEX(user_login.ul_username, '@', 1), user_login.user_id, '@example.com'), user.primary_email = CONCAT(SUBSTRING_INDEX(primary_email, '@', 1), `user`.id, '@example.com') WHERE `user`.id = user_login.user_id AND `user`.user_type = 'CUSTOMER';

# Nullify stores
UPDATE store SET email_address = CONCAT(SUBSTRING_INDEX(email_address, '@', 1), '@example.com');

# Drop old database to clear old data
DROP DATABASE IF EXISTS dreamsite;

# Recreate main database
CREATE DATABASE dreamsite;

# Create list of queries to execute
#SELECT CONCAT("RENAME TABLE dreamsite_import.", t.table_name, " TO dreamsite.", t.table_name, ";") AS `rename` FROM INFORMATION_SCHEMA.TABLES t WHERE TABLE_SCHEMA = 'dreamsite_import';

drop procedure if exists `dreamsiteImport`;
DELIMITER //
CREATE PROCEDURE `dreamsiteImport` ()
BEGIN
  DECLARE a,c VARCHAR(256);
  DECLARE b INT;
  DECLARE cur1 CURSOR FOR SELECT CONCAT("RENAME TABLE dreamsite_import.", t.table_name, " TO dreamsite.", t.table_name, ";") AS `rename` FROM INFORMATION_SCHEMA.TABLES t WHERE TABLE_SCHEMA = 'dreamsite_import';
DECLARE CONTINUE HANDLER FOR NOT FOUND SET b = 1;
  DECLARE CONTINUE HANDLER FOR 1061 SET b = 0;
OPEN cur1;
SET b = 0;
  WHILE b = 0 DO
    FETCH cur1 INTO a;
    IF b = 0 THEN
      SET @c = a;
PREPARE stmt1 FROM @c;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;
END IF;
END WHILE;
CLOSE cur1;
END //
call dreamsiteImport();
