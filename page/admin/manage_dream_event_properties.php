<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('page/admin/manage_bundle.php');

class page_admin_manage_dream_event_properties extends CPageAdminOnly
{
	function runHomeOfficeManager()
	{
		$this->manageDreamEventProperties();
	}

	function runSiteAdmin()
	{
		$this->manageDreamEventProperties();
	}

	function manageDreamEventProperties()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		$tpl->assign('editDreamTasteProperties', false);
		$tpl->assign('createDreamTasteProperties', false);

		$dteProperties = DAO_CFactory::create('dream_taste_event_properties');
		$dteProperties->query("SELECT
			dtep.id,
			dtep.dream_taste_event_theme,
			dtep.default_taste_type,
			dtep.bundle_id,
			dtep.host_required,
			dtep.available_on_customer_site,
			dtep.fundraiser_value,
			dtep.password_required,
			dtep.can_rsvp_only,
			dtep.can_rsvp_upgrade,
			dtep.menu_used_with_theme,
			dtep.existing_guests_can_attend,
			dtet.title,
			dtet.theme_string,
			dtet.session_type,
			m.menu_name,
			b.bundle_type,
			b.bundle_name,
			b.number_servings_required,
			b.price
			FROM dream_taste_event_properties AS dtep
			INNER JOIN dream_taste_event_theme AS dtet ON dtet.id = dtep.dream_taste_event_theme
			INNER JOIN bundle AS b ON b.id = dtep.bundle_id AND b.is_deleted = '0'
			INNER JOIN (SELECT m.id AS menu_id, m.menu_name,
				m.menu_description,
				m.menu_start,
				m.is_active,
				m.global_menu_start_date,
				m.global_menu_end_date,
				m.display_as_coming_soon
				FROM menu AS m
				WHERE m.is_deleted = '0'
				ORDER BY m.id DESC
				LIMIT " . page_admin_manage_bundle::MENU_HISTORY_LIMIT . ") AS m ON m.menu_id = dtep.menu_id
			WHERE dtep.is_deleted = '0'
			ORDER BY dtep.id DESC");

		$dtePropertiesArray = array();
		while($dteProperties->fetch())
		{
			$dtePropertiesArray[$dteProperties->id] = clone($dteProperties);
		}

		if (isset($_GET['create']))
		{
			if (!empty($_POST['submit']) && $_POST['submit'] == 'create')
			{
				$p_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
				$p_dream_taste_event_theme = CGPC::do_clean((!empty($_POST['dream_taste_event_theme']) ? $_POST['dream_taste_event_theme'] : false), TYPE_INT);
				$p_bundle_id = CGPC::do_clean((!empty($_POST['bundle_id']) ? $_POST['bundle_id'] : false), TYPE_INT);
				$p_host_required = CGPC::do_clean((!empty($_POST['host_required']) ? $_POST['host_required'] : false), TYPE_INT);
				$p_password_required = CGPC::do_clean((!empty($_POST['password_required']) ? $_POST['password_required'] : false), TYPE_INT);
				$p_available_on_customer_site = CGPC::do_clean((!empty($_POST['available_on_customer_site']) ? $_POST['available_on_customer_site'] : false), TYPE_INT);
				$p_fundraiser_value = CGPC::do_clean((!empty($_POST['fundraiser_value']) ? $_POST['fundraiser_value'] : false), TYPE_INT);
				$p_can_rsvp_only = CGPC::do_clean((!empty($_POST['can_rsvp_only']) ? $_POST['can_rsvp_only'] : false), TYPE_INT);
				$p_can_rsvp_upgrade = CGPC::do_clean((!empty($_POST['can_rsvp_upgrade']) ? $_POST['can_rsvp_upgrade'] : false), TYPE_INT);
				$p_menu_used_with_theme = CGPC::do_clean((!empty($_POST['menu_used_with_theme']) ? $_POST['menu_used_with_theme'] : false), TYPE_INT);
				$p_existing_guests_can_attend = CGPC::do_clean((!empty($_POST['existing_guests_can_attend']) ? $_POST['existing_guests_can_attend'] : false), TYPE_INT);

				$createProperties = DAO_CFactory::create('dream_taste_event_properties');
				$createProperties->dream_taste_event_theme = $p_dream_taste_event_theme;
				$createProperties->default_taste_type = 0;
				$createProperties->menu_id = $p_menu_id;
				$createProperties->bundle_id = $p_bundle_id;
				$createProperties->host_required = $p_host_required;
				$createProperties->password_required = $p_password_required;
				$createProperties->available_on_customer_site = $p_available_on_customer_site;
				$createProperties->fundraiser_value = $p_fundraiser_value;
				$createProperties->can_rsvp_only = $p_can_rsvp_only;
				$createProperties->can_rsvp_upgrade = $p_can_rsvp_upgrade;
				$createProperties->menu_used_with_theme = $p_menu_used_with_theme;
				$createProperties->existing_guests_can_attend = $p_existing_guests_can_attend;
				$createProperties->insert();

				$tpl->setStatusMsg('Properties created.');
				CApp::bounce('?page=admin_manage_dream_event_properties');
			}

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'submit',
				CForm::css_class => 'button',
				CForm::value => 'create',
				CForm::text => 'Create Properties'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'delete',
				CForm::css_class => 'button',
				CForm::value => 'delete',
				CForm::text => 'Delete Properties'
			));

			$tpl->assign('createBundleTheme', true);
		}

		if (!empty($_POST['delete']) && $_POST['delete'] == 'delete')
		{
			$p_properties_id = CGPC::do_clean((!empty($_POST['properties_id']) ? $_POST['properties_id'] : false), TYPE_INT);

			$deleteProperties = DAO_CFactory::create('dream_taste_event_properties');
			$deleteProperties->id = $p_properties_id;

			if ($deleteProperties->find(true))
			{
				$deleteProperties->delete();
			}

			$tpl->setStatusMsg('Properties deleted.');
			CApp::bounce('?page=admin_manage_dream_event_properties');
		}

		if (!empty($_GET['edit']) && is_numeric($_GET['edit']))
		{
			$editProperties = DAO_CFactory::create('dream_taste_event_properties');
			$editProperties->id = $_GET['edit'];
			if (!$editProperties->find(true))
			{
				$tpl->setStatusMsg('Properties not found.');
				CApp::bounce('?page=admin_manage_dream_event_properties');
			}

			if (!empty($_POST['submit']) && $_POST['submit'] == 'update')
			{
				$p_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
				$p_dream_taste_event_theme = CGPC::do_clean((!empty($_POST['dream_taste_event_theme']) ? $_POST['dream_taste_event_theme'] : false), TYPE_INT);
				$p_bundle_id = CGPC::do_clean((!empty($_POST['bundle_id']) ? $_POST['bundle_id'] : false), TYPE_INT);
				$p_host_required = CGPC::do_clean((!empty($_POST['host_required']) ? $_POST['host_required'] : false), TYPE_INT);
				$p_password_required = CGPC::do_clean((!empty($_POST['password_required']) ? $_POST['password_required'] : false), TYPE_INT);
				$p_available_on_customer_site = CGPC::do_clean((!empty($_POST['available_on_customer_site']) ? $_POST['available_on_customer_site'] : false), TYPE_INT);
				$p_fundraiser_value = CGPC::do_clean((!empty($_POST['fundraiser_value']) ? $_POST['fundraiser_value'] : false), TYPE_INT);
				$p_can_rsvp_only = CGPC::do_clean((!empty($_POST['can_rsvp_only']) ? $_POST['can_rsvp_only'] : false), TYPE_INT);
				$p_can_rsvp_upgrade = CGPC::do_clean((!empty($_POST['can_rsvp_upgrade']) ? $_POST['can_rsvp_upgrade'] : false), TYPE_INT);
				$p_menu_used_with_theme = CGPC::do_clean((!empty($_POST['menu_used_with_theme']) ? $_POST['menu_used_with_theme'] : false), TYPE_INT);
				$p_existing_guests_can_attend = CGPC::do_clean((!empty($_POST['existing_guests_can_attend']) ? $_POST['existing_guests_can_attend'] : false), TYPE_INT);

				$editProperties->dream_taste_event_theme = $p_dream_taste_event_theme;
				$editProperties->default_taste_type = 0;
				$editProperties->menu_id = $p_menu_id;
				$editProperties->bundle_id = $p_bundle_id;
				$editProperties->host_required = $p_host_required;
				$editProperties->password_required = $p_password_required;
				$editProperties->available_on_customer_site = $p_available_on_customer_site;
				$editProperties->fundraiser_value = $p_fundraiser_value;
				$editProperties->can_rsvp_only = $p_can_rsvp_only;
				$editProperties->can_rsvp_upgrade = $p_can_rsvp_upgrade;
				$editProperties->menu_used_with_theme = $p_menu_used_with_theme;
				$editProperties->existing_guests_can_attend = $p_existing_guests_can_attend;
				$editProperties->update();

				$tpl->setStatusMsg('Properties updated');
			}

			$tpl->assign('editProperties', $editProperties);

			$Form->DefaultValues['dream_taste_event_theme'] = $editProperties->dream_taste_event_theme;
			$Form->DefaultValues['menu_id'] = $editProperties->menu_id;
			$Form->DefaultValues['bundle_id'] = $editProperties->bundle_id;
			$Form->DefaultValues['host_required'] = $editProperties->host_required;
			$Form->DefaultValues['password_required'] = $editProperties->password_required;
			$Form->DefaultValues['available_on_customer_site'] = $editProperties->available_on_customer_site;
			$Form->DefaultValues['fundraiser_value'] = $editProperties->fundraiser_value;
			$Form->DefaultValues['can_rsvp_only'] = $editProperties->can_rsvp_only;
			$Form->DefaultValues['can_rsvp_upgrade'] = $editProperties->can_rsvp_upgrade;
			$Form->DefaultValues['menu_used_with_theme'] = $editProperties->menu_used_with_theme;
			$Form->DefaultValues['existing_guests_can_attend'] = $editProperties->existing_guests_can_attend;

			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::value => $editProperties->id,
				CForm::name => 'properties_id'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'submit',
				CForm::css_class => 'button',
				CForm::value => 'update',
				CForm::text => 'Update Properties'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'delete',
				CForm::css_class => 'button',
				CForm::value => 'delete',
				CForm::text => 'Delete Properties'
			));
		}

		$themeArray = self::getDreamTasteThemesOptionsArray();

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'dream_taste_event_theme',
			CForm::options => $themeArray
		));

		$menuArray = CMenu::getLastXMenus(page_admin_manage_bundle::MENU_HISTORY_LIMIT);
		$menuOptionsArray = array('' => 'Select Menu');
		foreach($menuArray AS $id => $menu)
		{
			$menuOptionsArray[$id] = $menu->menu_name;
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'menu_id',
			CForm::options => $menuOptionsArray
		));

		$bundleArray = self::getBundlesOptionsArray();

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'bundle_id',
			CForm::options => $bundleArray
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'host_required',
			CForm::options => array(0 => 'No', 1 => 'Yes', 2 => 'Optional')
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'password_required',
			CForm::options => array(0 => 'No', 1 => 'Yes', 2 => 'Optional')
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'available_on_customer_site',
			CForm::options => array(0 => 'No', 1 => 'Yes')
		));

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'fundraiser_value'
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'can_rsvp_only',
			CForm::options => array(0 => 'No', 1 => 'Yes')
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'can_rsvp_upgrade',
			CForm::options => array(0 => 'No', 1 => 'Yes')
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'menu_used_with_theme',
			CForm::options => array(0 => 'No', 1 => 'Yes')
		));

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'existing_guests_can_attend',
			CForm::options => array(0 => 'No', 1 => 'Yes')
		));

		$tpl->assign('dtePropertiesArray', $dtePropertiesArray);
		$tpl->assign('form', $Form->render());
	}

	static function getDreamTasteThemesOptionsArray()
	{
		$theme = DAO_CFactory::create('dream_taste_event_theme');
		$theme->query('SELECT * FROM dream_taste_event_theme ORDER BY theme_string DESC');

		$themeArray = array('' => 'Select Theme');
		while ($theme->fetch())
		{
			$themeArray[$theme->id] = $theme->theme_string . ' (' . $theme->title . ')';
		}

		return $themeArray;
	}

	function getBundlesOptionsArray()
	{
		$bundle = DAO_CFactory::create('bundle');
		$bundle->query("SELECT
			b.id,
			b.bundle_type,
			b.bundle_name,
			b.master_menu_item,
			b.number_items_required,
			b.number_servings_required,
			b.price,
			COUNT(btmi.menu_item_id) AS menu_item_count,
			m.*,
			GROUP_CONCAT(btmi.menu_item_id) AS menu_item_ids
			FROM bundle AS b
			LEFT JOIN bundle_to_menu_item AS btmi ON btmi.bundle_id = b.id AND btmi.is_deleted = '0'
			INNER JOIN (SELECT m.id AS menu_id, m.menu_name,
				m.menu_description,
				m.menu_start,
				m.is_active,
				m.global_menu_start_date,
				m.global_menu_end_date,
				m.display_as_coming_soon
				FROM menu AS m
				WHERE m.is_deleted = '0'
				ORDER BY m.id DESC
				LIMIT " . page_admin_manage_bundle::MENU_HISTORY_LIMIT . ") AS m ON m.menu_id = b.menu_id
			WHERE b.is_deleted = '0'
			GROUP BY b.id
			ORDER BY b.menu_id DESC, b.id DESC");

		$bundleArray = array('' => 'Select Bundle');
		while ($bundle->fetch())
		{
			$bundleArray[$bundle->id] = $bundle->menu_name . ' (' . $bundle->bundle_type . ' - ' . $bundle->bundle_name . ' - ' . $bundle->price . ')';
		}

		return $bundleArray;
	}
}
?>