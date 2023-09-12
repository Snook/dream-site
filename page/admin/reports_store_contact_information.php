<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/CReportsSiteAdmin.inc');

class page_admin_reports_store_contact_information extends CPageAdminOnly {

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->runStoreContactInformation();
	}

	function runSiteAdmin()
	{
		$this->runStoreContactInformation();
	}

	function runStoreContactInformation()
	{
		$tpl = CApp::instance()->template();

		if (!empty($_REQUEST['export']) && $_REQUEST['export'] == 'xlsx')
		{
			if (!empty($_REQUEST['form']) && $_REQUEST['form'] == 'sci')
			{

				$storeContactInfo = DAO_CFactory::create('store_contact_information');
				$storeContactInfo->query("SELECT
					s.id,
					s.home_office_id,
					s.store_name,
					sci.timestamp_created,
					IF(sci.pkg_ship_same_as_store = 1, s.address_line1, sci.pkg_ship_address_line1) AS pkg_ship_address_line1,
					IF(sci.pkg_ship_same_as_store = 1, s.address_line2, sci.pkg_ship_address_line2) AS pkg_ship_address_line2,
					IF(sci.pkg_ship_same_as_store = 1, s.city, sci.pkg_ship_city) AS pkg_ship_city,
					IF(sci.pkg_ship_same_as_store = 1, s.state_id, sci.pkg_ship_state_id) AS pkg_ship_state_id,
					IF(sci.pkg_ship_same_as_store = 1, s.postal_code, sci.pkg_ship_postal_code) AS pkg_ship_postal_code,
					IF(sci.pkg_ship_same_as_store = 1, s.telephone_day, sci.pkg_ship_telephone_day) AS pkg_ship_telephone_day,
					sci.pkg_ship_attn,
					IF(sci.letter_ship_same_as_store = 1, s.address_line1, sci.letter_ship_address_line1) AS letter_ship_address_line1,
					IF(sci.letter_ship_same_as_store = 1, s.address_line2, sci.letter_ship_address_line2) AS letter_ship_address_line2,
					IF(sci.letter_ship_same_as_store = 1, s.city, sci.letter_ship_city) AS letter_ship_city,
					IF(sci.letter_ship_same_as_store = 1, s.state_id, sci.letter_ship_state_id) AS letter_ship_state_id,
					IF(sci.letter_ship_same_as_store = 1, s.postal_code, sci.letter_ship_postal_code) AS letter_ship_postal_code,
					IF(sci.letter_ship_same_as_store = 1, s.telephone_day, sci.letter_ship_telephone_day) AS letter_ship_telephone_day,
					sci.letter_ship_attn,
					sci.owner_1_name,
					sci.owner_1_nickname,
					sci.owner_1_address_line1,
					sci.owner_1_address_line2,
					sci.owner_1_city,
					sci.owner_1_state_id,
					sci.owner_1_postal_code,
					sci.owner_1_telephone_primary,
					sci.owner_1_telephone_secondary,
					sci.owner_1_email_address,
					sci.owner_2_name,
					sci.owner_2_nickname,
					sci.owner_2_address_line1,
					sci.owner_2_address_line2,
					sci.owner_2_city,
					sci.owner_2_state_id,
					sci.owner_2_postal_code,
					sci.owner_2_telephone_primary,
					sci.owner_2_telephone_secondary,
					sci.owner_2_email_address,
					sci.owner_3_name,
					sci.owner_3_nickname,
					sci.owner_3_address_line1,
					sci.owner_3_address_line2,
					sci.owner_3_city,
					sci.owner_3_state_id,
					sci.owner_3_postal_code,
					sci.owner_3_telephone_primary,
					sci.owner_3_telephone_secondary,
					sci.owner_3_email_address,
					sci.owner_4_name,
					sci.owner_4_nickname,
					sci.owner_4_address_line1,
					sci.owner_4_address_line2,
					sci.owner_4_city,
					sci.owner_4_state_id,
					sci.owner_4_postal_code,
					sci.owner_4_telephone_primary,
					sci.owner_4_telephone_secondary,
					sci.owner_4_email_address,
					sci.manager_1_name,
					sci.manager_1_nickname,
					sci.manager_1_telephone_primary
					FROM
					store AS s
					LEFT JOIN store_contact_information AS sci ON sci.store_id = s.id AND sci.is_deleted = '0'
					WHERE
					s.is_deleted = '0' AND
					s.active = '1'
					ORDER BY s.active DESC, sci.timestamp_created DESC, s.home_office_id ASC");

				$labels = 'ID,
					HO ID,
					Store Name,
					Updated,
					Street Address,
					Suite,
					City,
					State,
					Postal Code,
					Telephone,
					Attention,
					Street Address,
					Suite,
					City,
					State,
					Postal Code,
					Telephone,
					Attention,
					Name,
					Nickname,
					Street Address,
					Suite,
					City,
					State,
					Postal Code,
					Primary Phone,
					Secondary Phone,
					Email,
					Name,
					Nickname,
					Street Address,
					Suite,
					City,
					State,
					Postal Code,
					Primary Phone,
					Secondary Phone,
					Email,
					Name,
					Nickname,
					Street Address,
					Suite,
					City,
					State,
					Postal Code,
					Primary Phone,
					Secondary Phone,
					Email,
					Name,
					Nickname,
					Street Address,
					Suite,
					City,
					State,
					Postal Code,
					Primary Phone,
					Secondary Phone,
					Email,
					Name,
					Nickname,
					Primary Phone';

				$labelArray = array_map('trim', explode(',', $labels));

				$sectionHeader = array();
				$sectionHeader['Store Information'] = 4;
				$sectionHeader['Package Shipping Address'] = 7;
				$sectionHeader['Letter Shipping Address'] = 7;
				$sectionHeader['Owner #1'] = 10;
				$sectionHeader['Owner #2'] = 10;
				$sectionHeader['Owner #3'] = 10;
				$sectionHeader['Owner #4'] = 10;
				$sectionHeader['Manager #1'] = 3;

				$tpl->assign('sectionHeader', $sectionHeader);

				$rows = array();
				while($storeContactInfo->fetch())
				{
					$tempArray = $storeContactInfo->toArray();

					$rows[] = array_slice($tempArray, 0, count($labelArray));
				}

				if (!empty($rows))
				{


					$tpl->assign('labels', $labelArray);
					$tpl->assign('rows', $rows);

				}
				else
				{
					$tpl->setErrorMsg('Server error, no store information found.');

					CApp::bounce('/?page=admin_store_contact_information');
				}
			}
			else
			{
				if (!empty($_REQUEST) && $_REQUEST['format'] == 'owner')
				{
					$ownerContactInfo = DAO_CFactory::create('user_to_franchise');
					$ownerContactInfo->query("SELECT
						COUNT(utf.id) AS franchise_count,
						GROUP_CONCAT(utf.franchise_id) AS franchise_ids,
						utf.user_id,
						u.firstname,
						u.lastname,
						u.primary_email,
						u.telephone_1,
						u.telephone_2,
						a.address_line1,
						a.address_line2,
						a.city,
						a.state_id,
						a.postal_code
						FROM user_to_franchise AS utf
						INNER JOIN `user` AS u ON u.id = utf.user_id AND u.is_deleted = '0'
						LEFT JOIN address AS a ON a.user_id = u.id AND a.is_deleted = '0' AND a.location_type = 'BILLING' 
						WHERE utf.is_deleted = '0'
						GROUP BY utf.user_id
						ORDER BY u.lastname ASC, u.firstname ASC");

					$owners = array();
					$max_franchises = 0;
					$max_stores = 0;

					while($ownerContactInfo->fetch())
					{
						if ($ownerContactInfo->franchise_count > $max_franchises)
						{
							$max_franchises = $ownerContactInfo->franchise_count;
						}

						$owners[$ownerContactInfo->user_id]['user_id'] = $ownerContactInfo->user_id;
						$owners[$ownerContactInfo->user_id]['firstname'] = $ownerContactInfo->firstname;
						$owners[$ownerContactInfo->user_id]['lastname'] = $ownerContactInfo->lastname;
						$owners[$ownerContactInfo->user_id]['primary_email'] = $ownerContactInfo->primary_email;
						$owners[$ownerContactInfo->user_id]['telephone_1'] = $ownerContactInfo->telephone_1;
						$owners[$ownerContactInfo->user_id]['telephone_2'] = $ownerContactInfo->telephone_2;
						$owners[$ownerContactInfo->user_id]['address_line1'] = $ownerContactInfo->address_line1;
						$owners[$ownerContactInfo->user_id]['address_line2'] = $ownerContactInfo->address_line2;
						$owners[$ownerContactInfo->user_id]['city'] = $ownerContactInfo->city;
						$owners[$ownerContactInfo->user_id]['state_id'] = $ownerContactInfo->state_id;
						$owners[$ownerContactInfo->user_id]['postal_code'] = $ownerContactInfo->postal_code;
					}

					$franchiseInfo = DAO_CFactory::create('store');
					$franchiseInfo->query("SELECT
						utf.user_id,
						utf.franchise_id,
						f.franchise_name,
						COUNT(s.id) AS total_stores,
						GROUP_CONCAT(s.home_office_id) AS home_office_ids
						FROM user_to_franchise AS utf
						INNER JOIN franchise AS f ON f.id = utf.franchise_id AND f.is_deleted = '0'
						INNER JOIN `user` AS u ON u.id = utf.user_id AND u.is_deleted = '0'
						LEFT JOIN store AS s ON s.franchise_id = f.id AND s.is_deleted = '0'
						WHERE utf.is_deleted = '0'
						GROUP BY utf.user_id, utf.franchise_id");

					while($franchiseInfo->fetch())
					{
						$owners[$franchiseInfo->user_id]['franchise_' . $franchiseInfo->franchise_id . '_franchise_name'] = $franchiseInfo->franchise_name;
						$owners[$franchiseInfo->user_id]['franchise_' . $franchiseInfo->franchise_id . '_home_office_ids'] = $franchiseInfo->home_office_ids;
					}

					// setup section headers for store info
					$sectionHeader = array();
					$sectionHeader['Owner Information'] = 11;

					// setup store info labels
					$labels = 'User ID,
						Firstname,
						Lastname,
						Primary Email,
						Primary Telephone,
						Secondary Telephone,
						Street Address,
						Suite,
						City,
						State,
						Postal Code';

					// setup section headers and labels for each owner
					for ($i = 1; $i <= $max_franchises; $i++)
					{
						$sectionHeader['Entity #' . $i] = 2;

						$labels .= ',
						Entity,
						Stores';
					}

					$labelArray = array_map('trim', explode(',', $labels));

					$tpl->assign('sectionHeader', $sectionHeader);
					$tpl->assign('labels', $labelArray);
					$tpl->assign('rows', $owners);
				}
				else
				{
					$storeContactInfo = DAO_CFactory::create('store');
					$storeContactInfo->query("SELECT
						s.id,
						s.home_office_id,
						s.store_name,
						s.address_line1,
						s.address_line2,
						s.city,
						s.state_id,
						s.postal_code,
						s.telephone_day,
						s.fax,
						s.email_address,
						COUNT(utf.user_id) AS number_of_owners,
						s.pkg_ship_is_commercial,
						IF(s.pkg_ship_same_as_store = 1, s.address_line1, s.pkg_ship_address_line1) AS pkg_ship_address_line1,
						IF(s.pkg_ship_same_as_store = 1, s.address_line2, s.pkg_ship_address_line2) AS pkg_ship_address_line2,
						IF(s.pkg_ship_same_as_store = 1, s.city, s.pkg_ship_city) AS pkg_ship_city,
						IF(s.pkg_ship_same_as_store = 1, s.state_id, s.pkg_ship_state_id) AS pkg_ship_state_id,
						IF(s.pkg_ship_same_as_store = 1, s.postal_code, s.pkg_ship_postal_code) AS pkg_ship_postal_code,
						IF(s.pkg_ship_same_as_store = 1, s.telephone_day, s.pkg_ship_telephone_day) AS pkg_ship_telephone_day,
						s.pkg_ship_attn,
						s.letter_ship_is_commercial,
						IF(s.letter_ship_same_as_store = 1, s.address_line1, s.letter_ship_address_line1) AS letter_ship_address_line1,
						IF(s.letter_ship_same_as_store = 1, s.address_line2, s.letter_ship_address_line2) AS letter_ship_address_line2,
						IF(s.letter_ship_same_as_store = 1, s.city, s.letter_ship_city) AS letter_ship_city,
						IF(s.letter_ship_same_as_store = 1, s.state_id, s.letter_ship_state_id) AS letter_ship_state_id,
						IF(s.letter_ship_same_as_store = 1, s.postal_code, s.letter_ship_postal_code) AS letter_ship_postal_code,
						IF(s.letter_ship_same_as_store = 1, s.telephone_day, s.letter_ship_telephone_day) AS letter_ship_telephone_day,
						s.letter_ship_attn,
						s.manager_1_user_id,
						m1.firstname AS manager_1_firstname,
						m1.lastname AS manager_1_lastname,
						m1.telephone_1 AS manager_1_telephone_1
						FROM store AS s
						LEFT JOIN user_to_franchise AS utf ON utf.franchise_id = s.franchise_id AND utf.is_deleted = '0'
						LEFT JOIN `user` AS m1 ON m1.id = s.manager_1_user_id AND m1.is_deleted = '0'
						WHERE s.is_deleted = '0' AND s.active = '1'
						GROUP BY s.id
						ORDER BY s.active DESC, s.state_id ASC, s.city ASC");

					$stores = array();
					$max_owners = 0;

					while($storeContactInfo->fetch())
					{
						if ($storeContactInfo->number_of_owners > $max_owners)
						{
							$max_owners = $storeContactInfo->number_of_owners;
						}

						$stores[$storeContactInfo->id]['store_id'] = $storeContactInfo->id;
						$stores[$storeContactInfo->id]['home_office_id'] = $storeContactInfo->home_office_id;
						$stores[$storeContactInfo->id]['store_name'] = $storeContactInfo->store_name;
						$stores[$storeContactInfo->id]['address_line1'] = $storeContactInfo->address_line1;
						$stores[$storeContactInfo->id]['address_line2'] = $storeContactInfo->address_line2;
						$stores[$storeContactInfo->id]['city'] = $storeContactInfo->city;
						$stores[$storeContactInfo->id]['state_id'] = $storeContactInfo->state_id;
						$stores[$storeContactInfo->id]['postal_code'] = $storeContactInfo->postal_code;
						$stores[$storeContactInfo->id]['telephone_day'] = $storeContactInfo->telephone_day;
						$stores[$storeContactInfo->id]['fax'] = $storeContactInfo->fax;
						$stores[$storeContactInfo->id]['email_address'] = $storeContactInfo->email_address;
						$stores[$storeContactInfo->id]['pkg_ship_is_commercial'] = (!empty($storeContactInfo->pkg_ship_is_commercial)) ? 'Commercial' : 'Residential';
						$stores[$storeContactInfo->id]['pkg_ship_address_line1'] = $storeContactInfo->pkg_ship_address_line1;
						$stores[$storeContactInfo->id]['pkg_ship_address_line2'] = $storeContactInfo->pkg_ship_address_line2;
						$stores[$storeContactInfo->id]['pkg_ship_city'] = $storeContactInfo->pkg_ship_city;
						$stores[$storeContactInfo->id]['pkg_ship_state_id'] = $storeContactInfo->pkg_ship_state_id;
						$stores[$storeContactInfo->id]['pkg_ship_postal_code'] = $storeContactInfo->pkg_ship_postal_code;
						$stores[$storeContactInfo->id]['pkg_ship_telephone_day'] = $storeContactInfo->pkg_ship_telephone_day;
						$stores[$storeContactInfo->id]['pkg_ship_attn'] = $storeContactInfo->pkg_ship_attn;
						$stores[$storeContactInfo->id]['letter_ship_is_commercial'] = (!empty($storeContactInfo->letter_ship_is_commercial)) ? 'Commercial' : 'Residential';
						$stores[$storeContactInfo->id]['letter_ship_address_line1'] = $storeContactInfo->letter_ship_address_line1;
						$stores[$storeContactInfo->id]['letter_ship_address_line2'] = $storeContactInfo->letter_ship_address_line2;
						$stores[$storeContactInfo->id]['letter_ship_city'] = $storeContactInfo->letter_ship_city;
						$stores[$storeContactInfo->id]['letter_ship_state_id'] = $storeContactInfo->letter_ship_state_id;
						$stores[$storeContactInfo->id]['letter_ship_postal_code'] = $storeContactInfo->letter_ship_postal_code;
						$stores[$storeContactInfo->id]['letter_ship_telephone_day'] = $storeContactInfo->letter_ship_telephone_day;
						$stores[$storeContactInfo->id]['letter_ship_attn'] = $storeContactInfo->letter_ship_attn;
						$stores[$storeContactInfo->id]['manager_1_user_id'] = $storeContactInfo->manager_1_user_id;
						$stores[$storeContactInfo->id]['manager_1_firstname'] = $storeContactInfo->manager_1_firstname;
						$stores[$storeContactInfo->id]['manager_1_lastname'] = $storeContactInfo->manager_1_lastname;
						$stores[$storeContactInfo->id]['manager_1_telephone_1'] = $storeContactInfo->manager_1_telephone_1;
					}

					$ownerContactInfo = DAO_CFactory::create('user');
					$ownerContactInfo->query("SELECT
						u.id,
						s.id AS store_id,
						u.firstname,
						u.lastname,
						u.telephone_1,
						u.telephone_2,
						u.primary_email,
						a.address_line1,
						a.address_line2,
						a.city,
						a.state_id,
						a.postal_code
						FROM store AS s
						LEFT JOIN user_to_franchise AS utf ON utf.franchise_id = s.franchise_id AND utf.is_deleted = '0'
						INNER JOIN `user` AS u ON u.id = utf.user_id AND u.is_deleted = '0'
						INNER JOIN address AS a ON a.user_id = u.id AND a.is_deleted = '0'
						WHERE s.is_deleted = '0' AND s.active = '1'
						ORDER BY s.state_id ASC, s.city ASC, s.id ASC, u.lastname ASC, u.firstname ASC");

					while($ownerContactInfo->fetch())
					{
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_user_id'] = $ownerContactInfo->id;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_firstname'] = $ownerContactInfo->firstname;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_lastname'] = $ownerContactInfo->lastname;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_telephone_1'] = $ownerContactInfo->telephone_1;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_telephone_2'] = $ownerContactInfo->telephone_2;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_primary_email'] = $ownerContactInfo->primary_email;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_address_line1'] = $ownerContactInfo->address_line1;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_address_line2'] = $ownerContactInfo->address_line2;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_city'] = $ownerContactInfo->city;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_state_id'] = $ownerContactInfo->state_id;
						$stores[$ownerContactInfo->store_id]['owner_' . $ownerContactInfo->id . '_postal_code'] = $ownerContactInfo->postal_code;
					}

					// setup section headers for store info
					$sectionHeader = array();
					$sectionHeader['Store Information'] = 11;
					$sectionHeader['Package Shipping Address'] = 8;
					$sectionHeader['Letter Shipping Address'] = 8;
					$sectionHeader['Manager #1'] = 4;

					// setup store info labels
					$labels = 'ID,
						HO ID,
						Store Name,
						Street Address,
						Suite,
						City,
						State,
						Postal Code,
						Telephone,
						Fax,
						Email,
						Type,
						Street Address,
						Suite,
						City,
						State,
						Postal Code,
						Telephone,
						Attention,
						Type,
						Street Address,
						Suite,
						City,
						State,
						Postal Code,
						Telephone,
						Attention,
						User ID,
						Firstname,
						Lastname,
						Primary Phone';

					// setup section headers and labels for each owner
					for ($i = 1; $i <= $max_owners; $i++)
					{
						$sectionHeader['Owner #' . $i] = 11;

						$labels .= ',
						User ID,
						Firstname,
						Lastname,
						Primary Telephone,
						Secondary Telephone,
						Email,
						Street Address,
						Suite,
						City,
						State,
						Postal Code';
					}

					$labelArray = array_map('trim', explode(',', $labels));

					$tpl->assign('sectionHeader', $sectionHeader);
					$tpl->assign('labels', $labelArray);
					$tpl->assign('rows', $stores);
				}
			}
		}
	}
}
?>