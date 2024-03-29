<?php
require_once 'includes/CSRF.inc';

/***********************************************************************************
 * Class:    CForm
 *
 * Description:    Class for defining and standardizing form elements in HTML. The form
 *        definitions can be done in the logic/code file and then the elements
 *        passed on for use in UI design.
 *
 * Use:        $Form = new CForm;
 *        $Form->Repost = TRUE;
 *
 *        $Form->Init(    "this_form",
 *                "testCForm.php"
 *                );
 *
 *        $Form->AddElement(
 *                    array(
 *                        CForm::type=> CForm::Text,
 *                        CForm::name => "Text_YourName",
 *                        CForm::length => 20,
 *                    )
 *
 *                );
 *
 *
 * Print Properties:    echo $Form->Text_YourName
 *
 ************************************************************************************/
class CForm
{

	// Repost htmlspecialchars
	var $htmlspecialcharsOnRepost;

	// It doesn't really matter what the values are as long as they are unique

	// Element types
	const Text = 'text';
	const Search = 'search';
	const Password = 'password';
	const EMail = 'email';
	const Tel = 'tel';
	const Date = 'date';
	const Time = 'time';
	const DateTimeLocal = 'datetime-local';
	const Month = 'month';
	const Number = 'number';
	const Money = 'money';
	const File = 'file';
	const Button = 'button';
	const ButtonHidden = 'buttonhidden';
	const ButtonMultiStore = 'buttonmultistore';
	const DropDown = 'dropdown';
	const Submit = 'submit';
	const TextArea = 'textarea';
	const CheckBox = 'checkbox';
	const Hidden = 'hidden';
	const RadioButton = 'radiobutton';
	const Label = 'label';
	const StatesProvinceDropDown = 'statesdropdown';
	const StoreDropDown = 'storedropdown';
	const RegionDropDown = 'regiondropdown';
	const OpcoDropDown = 'opcodropdown';
	const StorePerStateDropDown = 'storeperstatedropdown';
	const AdminStoreDropDown = 'adminstoredropdown';
	const FranchiseDropDown = 'franchisedropdown';
	const TimezoneDropDown = 'timezonedropdown';
	const MenuItemDropDown = 'menuitemdropdown';
	const MenuItemDropDownEditable = 'menuitemdropdowneditable';
	const MenuItemPanTypeDropDown = 'menuitempantypedropdown';
	const ReferralSourceDropDown = 'referralsourcedropdown';
	// Element properties
	const id = 'id';
	const name = 'name';
	const type = 'type';
	const length = 'length'; //maxlength and size combined
	const options = 'options';
	const url = 'url';
	const value = 'value'; //deprecated except for submit buttons,hidden, and radio buttons; use DefaultValues instead
	const org_value = 'org_value';
	const checked = 'checked';
	const width = 'width';
	const height = 'height';
	const default_value = 'default'; //same as setting the DefaultValues array
	const maxlength = 'maxlength';
	const minlength = 'minlength';
	const size = 'size';
	const step = 'step';
	const pattern = 'pattern';
	const rows = 'rows';
	const cols = 'cols';
	const other = 'other';
	const stateList = 'stateList';
	const style = 'style';
	const html_entities = 'html_entities';
	const dd_type = 'dd_type'; // generic attribute for further characterizing a form element
	const dd_subtype = 'dd_subtype'; // generic attribute for further characterizing a form element
	const add_provinces = 'add_provinces';
	const attribute = 'attribute'; // array of custom attributes, such as array('data-example' => 'john')
	const gpc_type = 'gpc_type';
	const xss_filter = 'xss_filter';
	const text = 'text';
	const tel = 'tel';
	const password = 'password';
	//store drop down props
	const franchise_filter = 'franchise_filter_id';
	const allowAllOption = 'allow_all';
	const showInactiveStores = 'show_inactive';
	const omitDistributionCenters = 'omitDistributionCenters';
	const showOnlyDistibutionCenters = 'showOnlyDistibutionCenters';
	const setDefault = 'set_default';
	const intro_stores_only_filter = 'intro_stores_only_filter';
	const state_filter = 'state_filter';
	const userAccessFilter = 'user_access_filter';
	const supportsDFLFilter = 'supportsDFLFilter';

	//used by validate.js
	const required = 'required';
	const dd_required = 'dd_required';
	const minimum = 'minimum'; //min length, for passwords, etc.
	const message = 'message'; //error message for required
	const message_min = 'message_min'; //error message for minimum
	const min = 'min';
	const max = 'max';
	const number = 'number';
	const search = 'search';
	const email = 'email';
	const telephone = 'telephone';
	const date = 'date';
	const time = 'time';
	const datetimelocal = 'datetime-local';
	const month = 'month';
	const money = 'money';
	const hour = 'hour';
	const minutes = 'minutes';
	const autocomplete = 'autocomplete';
	const disabled = 'disabled';
	const optional_email = 'optional_email';
	const label = "label"; // if initial value is a label then validator will consider this a non-value
	const placeholder = "placeholder";
	const tabindex = "tabindex";
	const confirm = "confirm";
	const required_msg = "required_msg";
	const tooltip = "tooltip";
	const item_title = "item_title";

	const custom_switch = 'custom_switch';
	const css_class = 'css_class';
	const label_css_class = 'label_css_class';
	const comment = 'comment';
	const onChangeShow = 'onchangeshow';
	const hiddenUntilChange = 'hiddenuntilchange';
	const onChangeSubmit = 'onchangesubmit';
	const onChange = 'onchange';
	const onClick = 'onClick';
	const onKeyUp = 'onKeyUp';
	const readonly = 'readonly';
	const addOnClick = 'addonclick';
	const onKeyPress = 'onKeyPress';
	const onItemQtyKeyUp = 'onItemQtyKeyUp';
	const entreeID = 'entreeID';
	const servings = 'servings';
	const lastQty = 'lastQty';
	const pricing_type = 'pricing_type';
	const price = 'price';
	const is_bundle = 'is_bundle';

	const optGroupSeparator = '^^^';
	const optSubGroupSeparator = '|||';

	private $FormElements = array();
	private $HiddenElements = array();

	public $Repost = false;
	public $Bootstrap = false;
	public $ElementID = true;
	public $Post = false;
	public $FormName = "";

	private $csrf_protection = null;
	private $csrf_action_id = false;

	private $Vars = array(); //hidden fields and posted fields

	/**
	 * values to be used as defaults if the value has not been reposted
	 * note: set default values before adding elements to the form.
	 *
	 * TODO: we could make this private and just use the default_value element attribute
	 **/
	public $DefaultValues = array();

	public function __construct($csrf_action = false)
	{
		$this->csrf_action_id = $csrf_action;
		$htmlspecialcharsOnRepost = false;
	}

	static function formElement($formElement, $defaultValue = false)
	{
		$form = new CForm();
		$form->Bootstrap = true;
		$form->ElementID = false;

		// ids required by checkboxes and radios so labels can reference them
		if (in_array($formElement[CForm::type], array(CForm::CheckBox, CForm::RadioButton)))
		{
			$form->ElementID = true;
		}

		if ($defaultValue)
		{
			$form->DefaultValues[$formElement[CForm::name]] = $defaultValue;
		}

		$form->AddElement($formElement);

		$element = $form->getFormElememtHTML($formElement[CForm::name]);

		if ($formElement[CForm::type] == CForm::RadioButton)
		{
			$element = $element[$formElement[CForm::value]];
		}

		echo $element;
	}

	public function getFormElememtHTML($name)
	{
		return $this->FormElements[$name];
	}

	public function setCSRFToken()
	{

		CLog::Assert(!empty($this->csrf_action_id), "No CSRF action set");

		if (CUser::isLoggedIn())
		{
			$sessionKey = CBrowserSession::instance()->browser_session_key;
			if (empty($sessionKey))
			{
				// TODO:  not logged in so can't do much
				return;
			}

			$this->csrf_protection = new CSRF($sessionKey, $this->csrf_action_id);
		}
		else
		{
			$cartKey = CBrowserSession::instance()->getCartKey();
			if (empty($cartKey))
			{
				// TODO:  no cart so can't do much
				return;
			}

			$this->csrf_protection = new CSRF(false, $this->csrf_action_id, $cartKey);
		}

		$this->csrf_protection->csrfkey();
		// there is now a key in the database for this form
	}

	public function validate_CSRF_token()
	{
		CLog::Assert(!empty($this->csrf_action_id), "No CSRF action set");

		if (isset($_POST['dd_csrf_token']))
		{
			if (CUser::isLoggedIn())
			{
				$sessionKey = CBrowserSession::instance()->browser_session_key;

				if (empty($sessionKey))
				{
					// TODO:  not logged in so can't do much
					return false;
				}

				$this->csrf_protection = new CSRF($sessionKey, $this->csrf_action_id);
			}
			else
			{

				$cartKey = CBrowserSession::instance()->getCartKey();
				if (empty($cartKey))
				{
					// TODO:  no cart so can't do much
					return false;
				}

				$this->csrf_protection = new CSRF(false, $this->csrf_action_id, $cartKey);
			}

			$valid = $this->csrf_protection->checkcsrf($_POST['dd_csrf_token']);

			return $valid;
		}

		return false;
	}

	/***********************************************************************
	 * Function:    __CheckPost
	 *        Determines if the current page contains POST variables
	 *        and if so, sets Post to TRUE and loads the elements
	 *        into the Vars array.
	 * In:
	 * Out:
	 * Called by:    addElement()
	 * @return
	 ************************************************************************/

	private function __CheckPost($elementName, $type, $setCheckboxes = true, $gpc_type = false, $xss_filter = false)
	{
		if ((!empty($_POST)) && isset($_POST[$elementName]))
		{

			if ($gpc_type)
			{
				$_POST[$elementName] = CGPC::clean_gpc('p', $elementName, $gpc_type, $xss_filter);
			}
			else if ($xss_filter)
			{
				require_once('includes/class.inputfilter_clean.php');
				$xssFilter = new InputFilter();
				$_POST[$elementName] = $xssFilter->process($_POST[$elementName]);
			}

			if ($this->htmlspecialcharsOnRepost)
			{
				$this->Vars[$elementName] = htmlspecialchars($_POST[$elementName]);
			}
			else
			{
				$this->Vars[$elementName] = $_POST[$elementName];
			}

			//if it's a submit or checkbox, then we need to set the value to true
			if (($type == self::CheckBox || $type == self::Submit) && $setCheckboxes)
			{
				$this->Vars[$elementName] = true;
			}

			$this->Post = true;
		}
		else if (((!empty($_POST)) && $type == self::CheckBox) && $setCheckboxes)
		{
			//for checkboxes, add a false value if they aren't submitted
			$this->Vars[$elementName] = false;
			$this->Post = true;
		}
	}

	/**
	 * Clears a posted value (sets it back to the default)
	 */
	public function clearValue($name)
	{
		//check reposted vars
		if (isset($this->Vars[$name]))
		{
			unset($this->Vars[$name]);
		}
	}

	/**
	 * Returns the current for value. If the form has not been posted or the value was not sent in the post, then
	 * the default value is returned.
	 */
	public function value($name)
	{
		if (isset ($this->DefaultValues[$name]))
		{
			$value = $this->DefaultValues[$name];
		}
		else
		{
			$value = null;
		}

		//check reposted vars
		if (isset($this->Vars[$name]))
		{
			$value = $this->Vars[$name];
		}

		return $value;
	}

	/**
	 * Returns all of the current form values.
	 */
	public function values()
	{
		return array_merge($this->DefaultValues, $this->Vars);
	}

	/***********************************************************************
	 * Function:    __AddTextElements
	 *
	 * In:        array (name, length)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/

	private function __AddTextElement($a)
	{

		if ($a[self::type] == self::password)
		{
			$szBuildString = "<input type='password' name='";
		}
		else if ($a[self::type] == self::email || (array_key_exists(self::email, $a) && $a[self::email]))
		{
			$szBuildString = "<input type='email' name='";
		}
		else if ($a[self::type] == self::tel || (array_key_exists(self::telephone, $a) && $a[self::telephone]))
		{
			$szBuildString = "<input type='tel' name='";
		}
		else if ($a[self::type] == self::number || array_key_exists(self::number, $a) && $a[self::number])
		{
			$szBuildString = "<input type='number' name='";
		}
		else if ($a[self::type] == self::search || array_key_exists(self::search, $a) && $a[self::search])
		{
			$szBuildString = "<input type='search' name='";
		}
		else if ($a[self::type] == self::date || array_key_exists(self::date, $a) && $a[self::date])
		{
			$szBuildString = "<input type='date' name='";
		}
		else if ($a[self::type] == self::time || array_key_exists(self::time, $a) && $a[self::time])
		{
			$szBuildString = "<input type='time' name='";
		}
		else if ($a[self::type] == self::datetimelocal || array_key_exists(self::datetimelocal, $a) && $a[self::datetimelocal])
		{
			$szBuildString = "<input type='datetime-local' name='";
		}
		else if ($a[self::type] == self::month || array_key_exists(self::month, $a) && $a[self::month])
		{
			$szBuildString = "<input type='month' name='";
		}
		else
		{
			$szBuildString = "<input type='text' name='";
		}

		$szBuildString .= $a[self::name] . "' ";

		if ($this->ElementID)
		{
			$szBuildString .= " id='" . $a[self::name] . "' ";
		}

		if (isset($a[self::onChangeShow]))
		{
			$szBuildString .= " onchange='javascript:SHOW(\"" . $a[self::onChangeShow] . "\");' ";
		}

		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '(this.value);" ';
		}

		if (isset($a[self::css_class]))
		{
			if ($this->Bootstrap)
			{
				$a[self::css_class] = "form-control " . $a[self::css_class];
			}

			$szBuildString .= ' class="' . $a[self::css_class] . '" ';
		}
		else if ($this->Bootstrap)
		{
			$szBuildString .= ' class="form-control"';
		}

		if (array_key_exists(self::onKeyUp, $a) && $a[self::onKeyUp])
		{
			$szBuildString .= ' onkeyup="' . $a[self::onKeyUp] . '(this.value);" ';
		}

		if (array_key_exists(self::onItemQtyKeyUp, $a) && $a[self::onItemQtyKeyUp])
		{
			$szBuildString .= ' onkeyup="' . $a[self::onItemQtyKeyUp] . '(this);" ';
		}

		if (array_key_exists(self::label, $a) && $a[self::label])
		{
			$szBuildString .= ' data-label="' . $a[self::label] . '"';
		}

		if (array_key_exists(self::attribute, $a) && $a[self::attribute])
		{
			foreach ($a[self::attribute] AS $key => $value)
			{
				$szBuildString .= ' ' . $key . '="' . $value . '" ';
			}
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-required_msg="' . $a[self::required_msg] . '" ';
		}

		if (array_key_exists(self::placeholder, $a) && $a[self::placeholder])
		{
			$szBuildString .= ' placeholder="' . $a[self::placeholder] . '"';
		}

		if (array_key_exists(self::tabindex, $a) && $a[self::tabindex])
		{
			$szBuildString .= ' tabindex="' . $a[self::tabindex] . '"';
		}

		if (array_key_exists(self::step, $a) && $a[self::step])
		{
			$szBuildString .= ' step="' . $a[self::step] . '"';
		}

		if (array_key_exists(self::pattern, $a) && $a[self::pattern])
		{
			$szBuildString .= ' pattern="' . $a[self::pattern] . '"';
		}

		if (array_key_exists(self::confirm, $a) && $a[self::confirm])
		{
			$szBuildString .= ' data-confirm="' . $a[self::confirm] . '"';
		}

		if (array_key_exists(self::entreeID, $a) && $a[self::entreeID])
		{
			$szBuildString .= ' data-entreeID="' . $a[self::entreeID] . '"';
		}

		if (array_key_exists(self::servings, $a) && $a[self::servings])
		{
			$szBuildString .= ' data-servings="' . $a[self::servings] . '"';
		}

		if (array_key_exists(self::lastQty, $a))
		{
			$szBuildString .= ' data-lastQty="' . $a[self::lastQty] . '"';
		}

		if (array_key_exists(self::dd_type, $a))
		{
			$szBuildString .= " data-dd_type='" . $a[self::dd_type] . "'";
		}

		if (array_key_exists(self::dd_subtype, $a))
		{
			$szBuildString .= " data-dd_subtype='" . $a[self::dd_subtype] . "'";
		}

		if (array_key_exists(self::org_value, $a))
		{
			$szBuildString .= " data-org_value='" . $a[self::org_value] . "'";
		}

		if (array_key_exists(self::pricing_type, $a))
		{
			$szBuildString .= " data-pricing_type='" . $a[self::pricing_type] . "'";
		}

		if (array_key_exists(self::is_bundle, $a) && $a['is_bundle'])
		{
			$szBuildString .= " data-is_bundle='true'";
		}
		else if (array_key_exists(self::is_bundle, $a))
		{
			$szBuildString .= " data-is_bundle='false'";
		}

		if (array_key_exists(self::price, $a))
		{
			$szBuildString .= " data-price='" . $a[self::price] . "'";
		}

		if (array_key_exists(self::required, $a) && $a[self::required])
		{
			$szBuildString .= " required='required'";
		}

		if (array_key_exists(self::dd_required, $a) && $a[self::dd_required])
		{
			$szBuildString .= " data-dd_required='true'";
		}

		if (array_key_exists(self::item_title, $a) && $a[self::item_title])
		{
			$a[self::item_title] = htmlspecialchars($a[self::item_title]);
			$szBuildString .= ' data-item_title="' . $a[self::item_title] . '"';
		}

		if (!$this->Bootstrap && array_key_exists(self::telephone, $a) && $a[self::telephone])
		{
			$szBuildString .= " data-telephone='true'";
		}

		if (array_key_exists(self::hour, $a) && $a[self::hour])
		{
			$szBuildString .= " data-hour='true'";
		}

		if (array_key_exists(self::number, $a) && $a[self::number])
		{
			$szBuildString .= " data-number='true'";
		}

		if (array_key_exists(self::money, $a) && $a[self::money])
		{
			$szBuildString .= " data-money='true'";
		}

		if (array_key_exists(self::minutes, $a) && $a[self::minutes])
		{
			$szBuildString .= " data-minutes='true'";
		}

		if (array_key_exists(self::minimum, $a))
		{
			$szBuildString .= " data-min='" . $a[self::minimum] . "'";
		}

		if (array_key_exists(self::min, $a))
		{
			$szBuildString .= " min='" . $a[self::min] . "'";
		}

		if (array_key_exists(self::max, $a))
		{
			$szBuildString .= " max='" . $a[self::max] . "'";
		}

		if (array_key_exists(self::email, $a) && $a[self::email])
		{
			$szBuildString .= " data-email='true' ";
		}

		if (array_key_exists(self::optional_email, $a) && $a[self::optional_email])
		{
			$szBuildString .= " data-optional_email='true' ";
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		if (array_key_exists(self::onKeyPress, $a) && $a[self::onKeyPress])
		{
			$szBuildString .= ' onkeypress="' . $a[self::onKeyPress] . ';" ';
		}

		if (array_key_exists(self::maxlength, $a) && $a[self::maxlength])
		{
			$szBuildString .= " maxlength='" . $a[self::maxlength] . "'";
		}
		else if (array_key_exists(self::length, $a) && $a[self::length])
		{
			$szBuildString .= " maxlength='" . $a[self::length] . "'";
		}

		if (array_key_exists(self::minlength, $a) && $a[self::minlength])
		{
			$szBuildString .= " minlength='" . $a[self::minlength] . "'";
		}

		if (array_key_exists(self::size, $a) && $a[self::size])
		{
			$szBuildString .= " size='" . $a[self::size] . "'";
		}
		else if (array_key_exists(self::maxlength, $a) && $a[self::maxlength])
		{
			$szBuildString .= " size='" . $a[self::maxlength] . "'";
		}
		else if (array_key_exists(self::length, $a) && $a[self::length])
		{
			$szBuildString .= " size='" . $a[self::length] . "'";
		}

		if (array_key_exists(self::autocomplete, $a))
		{
			if (is_bool($a[self::autocomplete]) && !$a[self::autocomplete])
			{
				$szBuildString .= " autocomplete='off'";
			}
			else if (!empty($a[self::autocomplete]))
			{
				$szBuildString .= " autocomplete='" . $a[self::autocomplete] . "'";
			}
		}

		if (array_key_exists(self::readonly, $a) && $a[self::readonly])
		{
			$szBuildString .= " readonly='readonly' ";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= " disabled='disabled' ";
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= " style='" . $a[self::style] . "' ";
		}

		//	$szBuildString .= ' value="' . $this->value($a[self::name]) . '"';
		if ($a[self::type] == self::datetimelocal || array_key_exists(self::datetimelocal, $a) && $a[self::datetimelocal])
		{
			$szBuildString .= ' value="' . CTemplate::dateTimeFormat($this->value($a[self::name]), DATE_TIME_LOCAL) . '"';
		}
		else
		{
			$szBuildString .= ' value="' . htmlentities($this->value($a[self::name])) . '"';
		}


		$szBuildString .= " />";

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= '<div class="invalid-feedback">' . $a[self::required_msg] . '</div>';
		}

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddFileElement
	 *
	 * In:        array (name, length)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/
	private function __AddFileElement($a)
	{
		$szBuildString = '<input name="' . $a[self::name] . '"';

		if ($this->ElementID)
		{
			$szBuildString .= ' id="' . $a[self::name] . '" ';
		}

		if (array_key_exists(self::required, $a) && $a[self::required])
		{
			$szBuildString .= " required='required'";
		}
		if (array_key_exists(self::size, $a) && $a[self::size])
		{
			$szBuildString .= " size='" . $a[self::size] . "' ";
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-message="' . $a[self::required_msg] . '" ';
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= " style='" . $a[self::style] . "' ";
		}

		if (array_key_exists(self::css_class, $a) && $a[self::css_class])
		{
			$szBuildString .= " class='" . $a[self::css_class] . "' ";
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		$szBuildString .= ' type="file" />';

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddButtonElement
	 *
	 * In:        array (name, length)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/
	private function __AddButtonElement($a)
	{
		$szBuildString = '<button name="' . $a[self::name] . '"';

		if ($this->ElementID)
		{
			$szBuildString .= ' id="' . $a[self::name] . '" ';
		}

		if (array_key_exists(self::onClick, $a) && $a[self::onClick])
		{
			$szBuildString .= ' onclick="' . $a[self::onClick] . '" ';
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= " disabled='disabled' ";
		}

		if (array_key_exists(self::css_class, $a) && $a[self::css_class])
		{
			$szBuildString .= " class='" . $a[self::css_class] . "' ";
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= " style='" . $a[self::style] . "' ";
		}

		if (array_key_exists(self::value, $a) && $a[self::value])
		{
			$szBuildString .= " value='" . $a[self::value] . "' ";
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		if (array_key_exists(self::text, $a) && $a[self::text])
		{
			$szBuildString .= ">" . $a[self::text];
		}
		else
		{
			$szBuildString .= ">" . $a[self::value];
		}

		$szBuildString .= '</button>';

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddButtonMultiStoreElement
	 *
	 * In:        array (name, length)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/
	private function __AddButtonMultiStoreElement($a)
	{
		$szBuildString = '<button id="' . $a[self::name] . '_button" name="' . $a[self::name] . '_button" ';

		if ($this->ElementID)
		{
			$szBuildString .= ' data-multi_store_select_id="' . $a[self::name] . '" ';
		}

		if (array_key_exists(self::css_class, $a) && $a[self::css_class])
		{
			$szBuildString .= " class='" . $a[self::css_class] . "' ";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= ' disabled="disabled" ';
		}

		$szBuildString .= ">";

		if (array_key_exists(self::text, $a) && $a[self::text])
		{
			$szBuildString .= " " . $a[self::text] . " ";
		}

		// store count, comma separated
		$count = 0;
		if ($this->value($a[self::name]))
		{
			$count = count(explode(',', $this->value($a[self::name])));
		}

		$szBuildString .= ' (<span id="' . $a[self::name] . '-count">' . $count . '</span>) ';

		$szBuildString .= '</button>';

		$szBuildString .= '<input type="hidden" id="' . $a[self::name] . '" name="' . $a[self::name] . '" ';

		if ($this->value($a[self::name]))
		{
			$szBuildString .= ' value="' . $this->value($a[self::name]) . '" ';
		}

		$szBuildString .= ' >';

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddButtonHiddenElement
	 *
	 * In:        array (name, length)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/
	private function __AddButtonHiddenElement($a)
	{
		$szBuildString = '<button id="' . $a[self::name] . '_button" name="' . $a[self::name] . '_button" ';

		if ($this->ElementID)
		{
			$szBuildString .= ' data-button_hidden_input="' . $a[self::name] . '" ';
		}

		if (array_key_exists(self::css_class, $a) && $a[self::css_class])
		{
			$szBuildString .= " class='" . $a[self::css_class] . "' ";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= ' disabled ';
		}

		if (array_key_exists(self::readonly, $a) && $a[self::readonly])
		{
			$szBuildString .= ' readonly ';
		}

		$szBuildString .= ">";

		if (array_key_exists(self::text, $a) && $a[self::text])
		{
			$szBuildString .= " " . $a[self::text] . " ";
		}

		$szBuildString .= '</button>';

		$szBuildString .= '<input type="hidden" id="' . $a[self::name] . '" name="' . $a[self::name] . '" ';

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= ' disabled ';
		}

		if (array_key_exists(self::readonly, $a) && $a[self::readonly])
		{
			$szBuildString .= ' readonly ';
		}

		if ($this->value($a[self::name]))
		{
			$szBuildString .= ' value="' . $this->value($a[self::name]) . '" ';
		}

		$szBuildString .= ' >';

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddCheckBoxElement
	 *
	 * In:        array (name, length)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/

	private function __AddCheckBoxElement($a)
	{

		if ($this->value($a[self::name]))
		{
			$szChecked = 'checked="checked"';
		}
		else
		{
			$szChecked = "";
		}

		$szBuildString = '<input type="checkbox"';
		$szBuildString .= ' name="' . $a[self::name] . '"';

		if ($this->ElementID || $this->Bootstrap)
		{
			$szBuildString .= ' id="' . $a[self::name] . '"';
		}

		if (array_key_exists(self::onClick, $a) && $a[self::onClick])
		{
			$szBuildString .= ' onclick="' . $a[self::onClick] . '(this);" ';
		}

		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '(this);" ';
		}
		else if (isset($a[self::onChangeShow]))
		{
			$szBuildString .= " onchange='javascript:SHOW(\"" . $a[self::onChangeShow] . "\");' ";
		}

		if (array_key_exists(self::attribute, $a) && $a[self::attribute])
		{
			foreach ($a[self::attribute] AS $key => $value)
			{
				$szBuildString .= ' ' . $key . '="' . $value . '" ';
			}
		}

		if (isset($a[self::css_class]))
		{
			if ($this->Bootstrap)
			{
				$a[self::css_class] = "custom-control-input " . $a[self::css_class];
			}

			$szBuildString .= ' class="' . $a[self::css_class] . '"';
		}
		else if ($this->Bootstrap)
		{
			$szBuildString .= ' class="custom-control-input" ';
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= " style='" . $a[self::style] . "' ";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= " disabled='disabled' ";
		}

		if (array_key_exists(self::readonly, $a) && $a[self::readonly])
		{
			$szBuildString .= " readonly='readonly' ";
		}

		if (array_key_exists(self::checked, $a) && $a[self::checked])
		{
			$szBuildString .= ' checked="checked" ';
		}

		if (array_key_exists(self::org_value, $a))
		{
			$szBuildString .= " data-org_value='" . $a[self::org_value] . "'";
		}

		if (array_key_exists(self::entreeID, $a) && $a[self::entreeID])
		{
			$szBuildString .= ' data-entreeID="' . $a[self::entreeID] . '"';
		}

		if (array_key_exists(self::pricing_type, $a))
		{
			$szBuildString .= " data-pricing_type='" . $a[self::pricing_type] . "'";
		}

		if (array_key_exists(self::item_title, $a) && $a[self::item_title])
		{
			$a[self::item_title] = htmlspecialchars($a[self::item_title]);
			$szBuildString .= ' data-item_title="' . $a[self::item_title] . '"';
		}

		if (array_key_exists(self::servings, $a) && $a[self::servings])
		{
			$szBuildString .= ' data-servings="' . $a[self::servings] . '"';
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		if (array_key_exists(self::dd_type, $a) && $a[self::dd_type])
		{
			$szBuildString .= " data-dd_type='" . $a[self::dd_type] . "'";
		}

		$szBuildString .= $szChecked . " />";

		if ($this->Bootstrap || array_key_exists(self::label, $a) && $a[self::label])
		{
			$szBuildString .= ' <label';

			if (isset($a[self::label_css_class]))
			{
				if ($this->Bootstrap)
				{
					$a[self::label_css_class] = "custom-control-label " . $a[self::label_css_class];
				}

				$szBuildString .= ' class="' . $a[self::label_css_class] . '" ';
			}
			else if ($this->Bootstrap)
			{
				$szBuildString .= ' class="custom-control-label" ';
			}

			if (isset($a[self::label]))
			{
				$szBuildString .= ' for="' . $a[self::name] . '">' . $a[self::label] . ((!empty($a[self::message])) ? '<div class="text-danger collapse" data-warning_id="' . $a[self::name] . '">' . $a[self::message] . '</div>' : '') . '</label>';
			}
			else
			{
				$szBuildString .= ' for="' . $a[self::name] . '"></label>';
			}
		}

		if ($this->Bootstrap)
		{
			if (array_key_exists(self::custom_switch, $a) && $a[self::custom_switch])
			{
				$szBuildString = '<div class="custom-control custom-switch">' . $szBuildString . '</div>';
			}
			else
			{
				$szBuildString = '<div class="custom-control custom-checkbox">' . $szBuildString . '</div>';
			}
		}

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddRadioButton
	 * In:        array (name, length)
	 * Out: An array of radio button input fields
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/

	private function __AddRadioButtonElement($a)
	{

		$szChecked = '';
		if ($this->value($a[self::name]) == $a[self::value])
		{
			$szChecked = 'checked="checked"';
		}

		$radioID = $a[self::name] . $a[self::value];

		$szBuildString = "<input type='radio' name='";
		$szBuildString .= $a[self::name] . "' id='";
		$szBuildString .= $radioID . "' value='";
		$szBuildString .= $a[self::value] . "' ";

		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '(this.value);" ';
		}

		if (isset($a[self::onChangeShow]))
		{
			$szBuildString .= " onchange='javascript:SHOW(\"" . $a[self::onChangeShow] . "\");' ";
		}

		if (isset($a[self::css_class]))
		{
			if ($this->Bootstrap)
			{
				$a[self::css_class] = "custom-control-input " . $a[self::css_class];
			}

			$szBuildString .= ' class="' . $a[self::css_class] . '" ';
		}
		else if ($this->Bootstrap)
		{
			$szBuildString .= ' class="custom-control-input" ';
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= " style='" . $a[self::style] . "' ";
		}

		if (isset($a[self::required]) && $a[self::required])
		{
			$szBuildString .= " required='required' ";
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-message="' . $a[self::required_msg] . '" ';
		}

		if (isset($a[self::dd_required]) && $a[self::dd_required])
		{
			$szBuildString .= " data-dd_required='true' ";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= " disabled ";
		}

		if (array_key_exists(self::readonly, $a) && $a[self::readonly] && $this->value($a[self::name]) != $a[self::value])
		{
			$szBuildString .= " disabled readonly ";
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		$szBuildString .= $szChecked . " />";

		if ($this->Bootstrap || array_key_exists(self::label, $a) && $a[self::label])
		{
			$szBuildString .= ' <label';

			if (isset($a[self::label_css_class]))
			{
				if ($this->Bootstrap)
				{
					$a[self::label_css_class] = "custom-control-label " . $a[self::label_css_class];
				}

				$szBuildString .= ' class="' . $a[self::label_css_class] . '" ';
			}
			else if ($this->Bootstrap)
			{
				$szBuildString .= ' class="custom-control-label" ';
			}

			if (!empty($a[self::label]))
			{
				$szBuildString .= ' for="' . $radioID . '">' . $a[self::label] . '</label>';
			}
			else
			{
				$szBuildString .= ' for="' . $radioID . '"></label>';
			}
		}

		if ($this->Bootstrap)
		{
			$szBuildString = '<div class="custom-control custom-radio">' . $szBuildString . '</div>';
		}

		if (!isset($this->FormElements[$a[self::name]]))
		{
			$this->FormElements[$a[self::name]] = array();
		}

		$this->FormElements[$a[self::name]][$a[self::value]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddSubmitElement
	 *
	 * In:        array (name, value, options)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/

	private function __AddSubmitElement($a)
	{

		$szBuildString = "<input type='submit' ";

		if (isset($a[self::name]))
		{
			$szBuildString .= "name='" . $a[self::name] . "' ";
		}

		if (isset($a[self::value]))
		{
			$szBuildString .= "value='" . $a[self::value] . "' ";
		}
		else
		{
			$szBuildString .= "value='' ";
		}

		if (isset($a[self::name]) && $this->ElementID)
		{
			$szBuildString .= "id='" . $a[self::name] . "' ";
		}

		if (isset($a[self::css_class]))
		{
			$szBuildString .= "class='" . $a[self::css_class] . "' ";
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		if (isset($a[self::hiddenUntilChange]))
		{
			$szBuildString .= " style='visibility: hidden;' ";
		}

		if (isset($a[self::addOnClick]) && $a[self::addOnClick])
		{
			$szBuildString .= " onclick='_" . $a[self::name] . "Click(this.form);' ";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= " disabled='disabled' ";
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= " style='" . $a[self::style] . "' ";
		}

		$szBuildString .= " />";

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddTextAreaElement
	 *
	 * In:        array (name, value, options)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/

	private function __AddTextAreaElement($a)
	{

		$szBuildString = "<textarea ";
		$szBuildString .= " name='" . $a[self::name] . "' ";

		if ($this->ElementID)
		{
			$szBuildString .= " id='" . $a[self::name] . "' ";
		}

		if (isset($a[self::css_class]))
		{
			if ($this->Bootstrap)
			{
				$a[self::css_class] = "form-control " . $a[self::css_class];
			}

			$szBuildString .= ' class="' . $a[self::css_class] . '" ';
		}
		else if ($this->Bootstrap)
		{
			$szBuildString .= ' class="form-control" ';
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= " style='" . $a[self::style] . "' ";
		}

		if (isset($a[self::onChangeShow]))
		{
			$szBuildString .= " onchange='javascript:SHOW(\"" . $a[self::onChangeShow] . "\");' ";
		}

		if (isset($a[self::onKeyUp]))
		{
			$szBuildString .= " onkeyup='" . $a[self::onKeyUp] . "' ";
		}
		if (isset($a[self::onKeyPress]))
		{
			$szBuildString .= " onkeypress='" . $a[self::onKeyPress] . "' ";
		}

		if (array_key_exists(self::required, $a) && $a[self::required])
		{
			$szBuildString .= " required='required'";
		}
		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= ' disabled="disabled"';
		}

		if (array_key_exists(self::dd_required, $a) && $a[self::dd_required])
		{
			$szBuildString .= " data-dd_required='true'";
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		if (array_key_exists(self::org_value, $a))
		{
			$szBuildString .= " data-org_value='" . $a[self::org_value] . "'";
		}

		if (array_key_exists(self::maxlength, $a) && $a[self::maxlength])
		{
			$szBuildString .= ' maxlength="' . $a[self::maxlength] . '"';
		}

		if (array_key_exists(self::placeholder, $a) && $a[self::placeholder])
		{
			$szBuildString .= ' placeholder="' . $a[self::placeholder] . '"';
		}

		if (isset($a[self::width]))
		{
			$szBuildString .= " style='width: " . $a[self::width];
		}
		if (isset($a[self::height]))
		{
			$szBuildString .= "px; height: " . $a[self::height] . "px;'";
		}

		if (isset($a[self::required_msg]))
		{
			$messageAttr = 'data-message="' . $a[self::required_msg] . '"';
		}

		if (isset($a[self::rows]))
		{
			$szBuildString .= " rows='" . $a[self::rows] . "' ";
		}
		if (isset($a[self::cols]))
		{
			$szBuildString .= " cols='" . $a[self::cols] . "' ";
		}

		if (array_key_exists(self::attribute, $a) && $a[self::attribute])
		{
			foreach ($a[self::attribute] AS $dataAttr => $dataVal)
			{
				$szBuildString .= ' ' . $dataAttr . '="' . $dataVal . '"';
			}
		}

		$szBuildString .= ">";

		//$szBuildString .= " value='".$this->value($a[self::name])."'";
		if ($this->value($a[self::name]))
		{
			$szBuildString .= $this->value($a[self::name]);
		}
		$szBuildString .= "</textarea>";

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddHiddenElement
	 *
	 * In:        array (name, value)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/

	private function __AddHiddenElement($a)
	{

		$szBuildString = "<input type='hidden' name='";
		$szBuildString .= $a[self::name] . "'";

		if ($this->ElementID)
		{
			$szBuildString .= " id='" . $a[self::name] . "' ";
		}

		$szBuf = (isset($a[self::value]) ? $a[self::value] : $this->value($a[self::name]));

		if (isset($a[self::html_entities]))
		{
			if ($a[self::html_entities] == true)
			{
				$szBuf = htmlentities($szBuf, ENT_QUOTES);
			}
		}

		//$szBuildString .= " value='". htmlentities( (isset($a[self::value])? $a[self::value] : $this->value($a[self::name])), ENT_QUOTES ) ."'";
		$szBuildString .= " value='" . $szBuf;
		$szBuildString .= "' />";

		$this->HiddenElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddLabelElement
	 * For readonly text areas that you don't want submitted.
	 *
	 * In:        array (name, value)
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 ************************************************************************/

	private function __AddLabelElement($a)
	{

		$szBuildString = "<label name='" . $a[self::name] . "' ";

		if ($this->ElementID)
		{
			$szBuildString .= "id='" . $a[self::name] . "'>";
		}

		$szBuildString .= $a[self::value];
		$szBuildString .= "</label>";

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	/***********************************************************************
	 * Function:    __AddDropDownElement
	 *
	 * In:        array (name, array(options))
	 * Out:
	 * Called by:    AddElement()
	 * @return
	 *
	 ***********************************************************************/

	private function __AddDropDownElement($a)
	{

		if (empty ($a[self::options]))
		{
			throw new Exception("options property needs to be set.");
		}

		$szBuildString = '<select name="' . $a[self::name] . '"';

		if ($this->ElementID)
		{
			$szBuildString .= ' id="' . $a[self::name] . '" ';
		}

		if (array_key_exists(self::onChangeSubmit, $a) && $a[self::onChangeSubmit])
		{
			$szBuildString .= ' onchange="form.submit();" ';
		}
		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '(this.value);" ';
		}

		if (isset($a[self::css_class]))
		{
			if ($this->Bootstrap)
			{
				$a[self::css_class] = "custom-select " . $a[self::css_class];
			}

			$szBuildString .= ' class="' . $a[self::css_class] . '" ';
		}
		else if ($this->Bootstrap)
		{
			$szBuildString .= ' class="custom-select" ';
		}

		if (array_key_exists(self::style, $a) && $a[self::style])
		{
			$szBuildString .= ' style="' . $a[self::style] . '" ';
		}

		if (array_key_exists(self::dd_required, $a) && $a[self::dd_required])
		{
			$szBuildString .= " data-dd_required='true'";
		}

		if (array_key_exists(self::required, $a) && $a[self::required])
		{
			$szBuildString .= " required";
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-message="' . $a[self::required_msg] . '" ';
		}

		if (array_key_exists(self::dd_type, $a) && $a[self::dd_type])
		{
			$szBuildString .= " data-dd_type='" . $a[self::dd_type] . "'";
		}

		if (array_key_exists(self::org_value, $a))
		{
			$szBuildString .= " data-org_value='" . $a[self::org_value] . "'";
		}


		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= " disabled ";
		}

		if (array_key_exists(self::readonly, $a) && $a[self::readonly])
		{
			$szBuildString .= " readonly ";
		}

		if (array_key_exists(self::attribute, $a) && $a[self::attribute])
		{
			foreach ($a[self::attribute] AS $dataAttr => $dataVal)
			{
				$szBuildString .= ' ' . $dataAttr . '="' . $dataVal . '"';
			}
		}

		$szBuildString .= ">\n";

		//changed 7/18/05 ToddW
		//this needs to be able to use an associative array for the option values as well
		//as a counter if no values are specified in the array.
		//plus we may want to use an empty value, let's use the string 'null'

		//do 'null' first
		if (array_key_exists('null', $a[self::options]))
		{
			$szBuildString .= '<option value="">' . $a[self::options]["null"] . '</option>' . "\n";
		}
		else if (!$this->value($a[self::name]))
		{//make sure something is selected or set the first option as the current form value
			$optKeys = array_keys($a[self::options]);
			$this->Vars[$a[self::name]] = reset($optKeys);
		}

		$lastGroup = '';
		foreach ($a[self::options] as $optValue => $szOption)
		{
			$szOptionArray = false;

			if (is_array($szOption))
			{
				$szOptionArray = $szOption;
				$szOption = $szOption['title'];
			}

			if ($optValue !== 'null')
			{
				$currentGroup = '';
				$optionArray = explode(self::optGroupSeparator, $szOption);
				if (count($optionArray) == 2)
				{
					list($currentGroup, $szOption) = $optionArray;
				}

				if ($currentGroup != $lastGroup)
				{
					if ($currentGroup != '')
					{
						$szBuildString .= "</optgroup>\n";
					}
					$lastGroup = $currentGroup;
					$szBuildString .= '<optgroup label="' . $currentGroup . '" >' . "\n";
				}

				$disabledClause = "";
				if (strpos($szOption, "###") === 0)
				{
					$szOption = substr($szOption, 3);
					$disabledClause = " disabled ";
				}

				$inactiveClause = "";
				if (strpos($szOption, "|||") === 0)
				{
					$szOption = substr($szOption, 3);
					$inactiveClause = " style='color:gray;' ";
				}

				$currentVal = $this->value($a[self::name]);

				$selectedClause = '';
				if (isset($currentVal))
				{
					$selectedClause = ($this->value($a[self::name]) == $optValue ? 'selected ' : (array_key_exists(self::readonly, $a) && $a[self::readonly] && (($this->value($a[self::name]) != $optValue)) ? 'disabled ' : '' ));
				}

				$dataAttributes = '';

				if (!empty($szOptionArray))
				{
					if (!empty($szOptionArray['data']))
					{
						foreach ($szOptionArray['data'] AS $dataAttr => $dataVal)
						{
							$dataAttributes .= ' ' . $dataAttr . '="' . $dataVal . '"';
						}
					}

					if (!empty($szOptionArray['disabled']))
					{
						$disabledClause = " disabled='disabled' ";
					}
				}

				$szBuildString .= "<option $inactiveClause $disabledClause $dataAttributes value='$optValue' " . $selectedClause . ">$szOption</option>\n";
			}
		}
		if (($currentGroup != $lastGroup) && ($currentGroup != ''))
		{
			$szBuildString .= "</optgroup>";
		}

		$szBuildString .= "</select>";

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= '<div class="invalid-feedback">' . $a[self::required_msg] . '</div>';
		}

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	private function __AddDropDownElementWithSubGroups($a)
	{

		if (empty ($a[self::options]))
		{
			throw new Exception("options property needs to be set.");
		}

		$szBuildString = "<select name='";
		$szBuildString .= $a[self::name] . "' ";

		if ($this->ElementID)
		{
			$szBuildString .= "id='" . $a[self::name] . "' ";
		}

		if (array_key_exists(self::onChangeSubmit, $a) && $a[self::onChangeSubmit])
		{
			$szBuildString .= ' onchange="form.submit();" ';
		}
		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '(this.value);" ';
		}
		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= " disabled='disabled' ";
		}

		if (isset($a[self::css_class]))
		{
			if ($this->Bootstrap)
			{
				$a[self::css_class] = "custom-select " . $a[self::css_class];
			}

			$szBuildString .= ' class="' . $a[self::css_class] . '" ';
		}
		else if ($this->Bootstrap)
		{
			$szBuildString .= ' class="custom-select" ';
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-message="' . $a[self::required_msg] . '" ';
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= " data-tooltip='" . $a[self::tooltip] . "' ";
		}

		$szBuildString .= ">\n";

		//changed 7/18/05 ToddW
		//this needs to be able to use an associative array for the option values as well
		//as a counter if no values are specified in the array.
		//plus we may want to use an empty value, let's use the string 'null'

		//do 'null' first
		if (array_key_exists('null', $a[self::options]))
		{
			$szBuildString .= "<option value='' >" . $a[self::options]['null'] . "</option>\n";
		}

		else if (!$this->value($a[self::name]))
		{//make sure something is selected or set the first option as the current form value
			$optKeys = array_keys($a[self::options]);
			$this->Vars[$a[self::name]] = reset($optKeys);
		}

		$lastStateGroup = '';
		$lastCityGroup = '';
		$mustCloseState = false;
		$mustCloseCity = false;
		foreach ($a[self::options] as $optValue => $szOption)
		{
			if ($optValue !== 'null')
			{
				$currentStateGroup = '';
				$optionArray = explode(self::optSubGroupSeparator, $szOption);
				if (count($optionArray) == 2)
				{
					list($currentStateGroup, $szOption) = $optionArray;
				}

				$optionArray = explode(self::optGroupSeparator, $szOption);
				if (count($optionArray) == 2)
				{
					list($currentCityGroup, $szOption) = $optionArray;
				}

				if ($currentStateGroup != $lastStateGroup)
				{

					$endopt = "";
					if ($mustCloseState)
					{
						$endopt = "</optgroup></optgroup>\n";
						$mustCloseCity = false;
					}
					$lastStateGroup = $currentStateGroup;
					$szBuildString .= $endopt . '<optgroup style="background-color: #e0e0e0;" label="' . $currentStateGroup . '" >' . "\n";
					$mustCloseState = true;
				}

				if ($currentCityGroup != $lastCityGroup)
				{
					$endopt = "";
					if ($mustCloseCity)
					{
						$endopt = "</optgroup>\n";
					}

					$lastCityGroup = $currentCityGroup;
					$szBuildString .= $endopt . '<optgroup style="color: #707070;" label="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $currentCityGroup . '" >' . "\n";
					$mustCloseCity = true;
				}

				$szBuildString .= "<option style=\"color: black;\" value='$optValue' " . ($this->value($a[self::name]) == $optValue ? 'selected="selected" ' : '') . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$szOption</option>\n";
			}
		}
		if ($mustCloseCity)
		{
			$szBuildString .= "</optgroup>";
		}
		if ($mustCloseState)
		{
			$szBuildString .= "</optgroup>";
		}

		$szBuildString .= "</select>";

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	private function __AddStatesProvinceDropDown($a)
	{
		require_once('DAO/BusinessObject/CStatesAndProvinces.php');

		$szBuildString = '<select name="' . $a[self::name] . '"';

		if ($this->ElementID)
		{
			$szBuildString .= ' id="' . $a[self::name] . '"';
		}

		if (array_key_exists(self::required, $a) && $a[self::required])
		{
			$szBuildString .= ' required="required"';
		}

		if (array_key_exists(self::onChangeSubmit, $a) && $a[self::onChangeSubmit])
		{
			$szBuildString .= ' onchange="form.submit();"';
		}

		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '"';
		}

		if (isset($a[self::css_class]))
		{
			if ($this->Bootstrap)
			{
				$a[self::css_class] = "custom-select " . $a[self::css_class];
			}

			$szBuildString .= ' class="' . $a[self::css_class] . '" ';
		}
		else if ($this->Bootstrap)
		{
			$szBuildString .= ' class="custom-select" ';
		}

		if (array_key_exists(self::attribute, $a) && $a[self::attribute])
		{
			foreach ($a[self::attribute] AS $key => $value)
			{
				$szBuildString .= ' ' . $key . '="' . $value . '" ';
			}
		}

		if (array_key_exists(self::dd_required, $a) && $a[self::dd_required])
		{
			$szBuildString .= ' data-dd_required="true"';
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-message="' . $a[self::required_msg] . '" ';
		}

		if (array_key_exists(self::org_value, $a))
		{
			$szBuildString .= " data-org_value='" . $a[self::org_value] . "'";
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= ' disabled="disabled"';
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= ' data-tooltip="' . $a[self::tooltip] . '"';
		}

		$szBuildString .= '>' . "\n";

		$value = $this->value($a[self::name]);

		if (!$value)
		{
			if (array_key_exists(self::add_provinces, $a) && $a[self::add_provinces])
			{
				$szBuildString .= '<option value="" selected="selected">State/Province</option>' . "\n";
			}
			else
			{
				$szBuildString .= '<option value="" selected="selected">State</option>' . "\n";
			}
		}

		if (array_key_exists(self::other, $a) && $a[self::other])
		{
			$szBuildString .= '<option value="--">Other</option>' . "\n";
		}

		$stateArray = null;

		// stateList may be passed in to provide a drop-down with only states with active stores
		if (array_key_exists(self::stateList, $a) && count($a[self::stateList]) > 0)
		{
			$stateArray = $a[self::stateList];
		}
		else
		{
			$stateArray = CStatesAndProvinces::GetStatesArray();
		}

		if (array_key_exists(self::add_provinces, $a) && $a[self::add_provinces])
		{
			$stateArray = CStatesAndProvinces::addProvinces();
		}

		foreach ($stateArray as $abbrev => $name)
		{
			if ($abbrev == '--')
			{
				$szBuildString .= '<option value="" disabled="disabled">-----</option>' . "\n";
			}
			else if ($value == $abbrev)
			{
				$szBuildString .= '<option value="' . $abbrev . '" selected="selected">' . $name . '</option>' . "\n";
			}
			else
			{
				$szBuildString .= '<option value="' . $abbrev . '">' . $name . '</option>' . "\n";
			}
		}

		$szBuildString .= "</select>";

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= '<div class="invalid-feedback">' . $a[self::required_msg] . '</div>';
		}

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	private function __AddOpcoDropDown($a)
	{

		$szBuildString = '<select name="' . $a[self::name] . '"';

		if ($this->ElementID)
		{
			$szBuildString .= ' id="' . $a[self::name] . '"';
		}

		if (array_key_exists(self::required, $a) && $a[self::required])
		{
			$szBuildString .= ' required="required"';
		}

		if (isset($a[self::css_class]))
		{
			$szBuildString .= ' class="' . $a[self::css_class] . '"';
		}

		if (array_key_exists(self::onChangeSubmit, $a) && $a[self::onChangeSubmit])
		{
			$szBuildString .= ' onchange="form.submit();"';
		}

		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '"';
		}

		if (array_key_exists(self::attribute, $a) && $a[self::attribute])
		{
			foreach ($a[self::attribute] AS $key => $value)
			{
				$szBuildString .= ' ' . $key . '="' . $value . '" ';
			}
		}

		if (array_key_exists(self::dd_required, $a) && $a[self::dd_required])
		{
			$szBuildString .= ' data-dd_required="true"';
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-message="' . $a[self::required_msg] . '" ';
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= ' disabled="disabled"';
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= ' data-tooltip="' . $a[self::tooltip] . '"';
		}

		$szBuildString .= '>' . "\n";

		$value = $this->value($a[self::name]);

		if (!$value)
		{
			$szBuildString .= '<option value="" selected="selected">Select Opco</option>' . "\n";
		}

		$Opcos = DAO_CFactory::create('opco');
		$Opcos->query("SELECT * FROM opco WHERE active = 1");

		while ($Opcos->fetch())
		{
			if ($value == $Opcos->id)
			{
				$szBuildString .= '<option value="' . $Opcos->id . '" selected="selected">' . "#" . $Opcos->opco_number . " " . $Opcos->opco_location . '</option>' . "\n";
			}
			else
			{
				$szBuildString .= '<option value="' . $Opcos->id . '">' . "#" . $Opcos->opco_number . " " . $Opcos->opco_location . "\n";
			}
		}

		$szBuildString .= "</select>";

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	private function __AddRegionDropDown($a)
	{

		$szBuildString = '<select name="' . $a[self::name] . '"';

		if ($this->ElementID)
		{
			$szBuildString .= '  id="' . $a[self::name] . '"';
		}

		if (array_key_exists(self::required, $a) && $a[self::required])
		{
			$szBuildString .= ' required="required"';
		}

		if (isset($a[self::css_class]))
		{
			$szBuildString .= ' class="' . $a[self::css_class] . '"';
		}

		if (array_key_exists(self::onChangeSubmit, $a) && $a[self::onChangeSubmit])
		{
			$szBuildString .= ' onchange="form.submit();"';
		}

		if (array_key_exists(self::onChange, $a) && $a[self::onChange])
		{
			$szBuildString .= ' onchange="' . $a[self::onChange] . '"';
		}

		if (array_key_exists(self::attribute, $a) && $a[self::attribute])
		{
			foreach ($a[self::attribute] AS $key => $value)
			{
				$szBuildString .= ' ' . $key . '="' . $value . '" ';
			}
		}

		if (array_key_exists(self::dd_required, $a) && $a[self::dd_required])
		{
			$szBuildString .= ' data-dd_required="true"';
		}

		if (isset($a[self::required_msg]))
		{
			$szBuildString .= ' data-message="' . $a[self::required_msg] . '" ';
		}

		if (array_key_exists(self::disabled, $a) && $a[self::disabled])
		{
			$szBuildString .= ' disabled="disabled"';
		}

		if (array_key_exists(self::tooltip, $a) && $a[self::tooltip])
		{
			$szBuildString .= ' data-tooltip="' . $a[self::tooltip] . '"';
		}

		$szBuildString .= '>' . "\n";

		$value = $this->value($a[self::name]);

		if (!$value)
		{
			$szBuildString .= '<option value="" selected="selected">Select Region</option>' . "\n";
		}

		if (array_key_exists(self::other, $a) && $a[self::other])
		{
			$szBuildString .= '<option value="--">Other</option>' . "\n";
		}

		$Regions = DAO_CFactory::create('trade_area');
		$Regions->query("SELECT * FROM trade_area WHERE is_active = 1");

		while ($Regions->fetch())
		{
			if ($value == $Regions->id)
			{
				$szBuildString .= '<option value="' . $Regions->id . '" selected="selected">' . $Regions->region . '</option>' . "\n";
			}
			else
			{
				$szBuildString .= '<option value="' . $Regions->id . '">' . $Regions->region . '</option>' . "\n";
			}
		}

		$szBuildString .= "</select>";

		$this->FormElements[$a[self::name]] = $szBuildString;
	}

	private function __AddStoreDropDown($a)
	{
		require_once('DAO/BusinessObject/CStatesAndProvinces.php');

		if (!isset($a[self::name]))
		{
			$a[self::name] = 'store';
		}

		if (!isset($a[self::setDefault]) || $a[self::setDefault] !== false)
		{
			//if $default == false, then pull the default store id out of the session
			if ((!isset($this->DefaultValues[$a[self::name]])) && CBrowserSession::getCurrentStore())
			{
				$this->DefaultValues[$a[self::name]] = CBrowserSession::getCurrentStore();
			}
		}

		//build store drop down
		$StoreObj = DAO_CFactory::create('store');

		// new method of filtering per user
		if (isset($a[self::userAccessFilter]))
		{
			$addFilters = '';

			if (empty($a[self::showInactiveStores]))
			{
				$addFilters .= ' && s.active = 1 ';
			}

			if (isset($a[self::intro_stores_only_filter]) && $a[self::intro_stores_only_filter])
			{
				$addFilters .= ' && s.supports_intro_orders = 1 ';
			}

			$StoreObj->query('SELECT s.* FROM store s, user_to_store uts WHERE uts.store_id = s.id AND uts.user_id=' . $a[self::userAccessFilter] . $addFilters . ' AND uts.is_deleted = 0 ORDER BY s.state_id ASC, s.city ASC, s.store_name ASC');

			$numStores = $StoreObj->N;
		}
		else
		{
			if (isset($a[self::franchise_filter]))
			{
				$StoreObj->franchise_id = $a[self::franchise_filter];
			}

			$StoreObj->orderBy(' state_id ASC, city ASC, store_name ASC ');

			if (empty($a[self::showInactiveStores]))
			{
				$StoreObj->active = 1;
			}

			if (isset($a[self::intro_stores_only_filter]) && $a[self::intro_stores_only_filter])
			{
				$StoreObj->supports_intro_orders = 1;
			}

			$numStores = $StoreObj->find();
		}

		if ($numStores == 0)
		{
			throw new Exception('No stores found.');
		}
		else if ($numStores == 1)
		{
			//if there is only one store, then just create a read only text box
			$StoreObj->fetch();
			$this->DefaultValues[$a[self::name]] = $StoreObj->id;
			$this->AddElement(array(
				CForm::type => CForm::Label,
				CForm::name => "store",
				CForm::value => $StoreObj->store_name
			));
		}
		else
		{

			$groupByCity = false;
			//if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') === FALSE)
			//	$isSafari = false;

			//create drop down
			$options = array();
			if (!isset($a[self::franchise_filter]))
			{
				if (isset($a[self::allowAllOption]) && $a[self::allowAllOption])
				{
					$options['all'] = 'All Stores';
				}
				else
				{
					$options['null'] = 'Select Location';
				}
				//add clear button
				$showClearBtn = true;
			}

			while ($StoreObj->fetch())
			{
				if ((!isset($this->DefaultValues[$a[self::name]])) && isset($a[self::franchise_filter]))
				{
					$this->DefaultValues[$a[self::name]] = $StoreObj->id;
				}

				//				$nameNoStateAndDD = $StoreObj->city.'--'.str_replace('Dream Dinners', '', $StoreObj->store_name);
				$nameNoStateAndDD = str_replace('Dream Dinners', '', $StoreObj->store_name);
				$nameNoStateAndDD = str_replace($StoreObj->state_id . ' ', '', $nameNoStateAndDD);

				if (!$groupByCity)
				{
					$options[$StoreObj->id] = htmlspecialchars(CStatesAndProvinces::GetName($StoreObj->state_id) . self::optGroupSeparator . $nameNoStateAndDD);
				}
				else
				{
					$options[$StoreObj->id] = htmlspecialchars(CStatesAndProvinces::GetName($StoreObj->state_id) . self::optSubGroupSeparator . $StoreObj->city . self::optGroupSeparator . $nameNoStateAndDD);
				}
			}

			$a[self::options] = $options;

			if (!$groupByCity)
			{
				$this->__AddDropDownElement($a);
			}
			else
			{
				$this->__AddDropDownElementWithSubGroups($a);
			}
		}

		//if the user is just browsing through the stores, we don't want to change their homestore
		//		if ( $this->value('store') )
		//			CBrowserSession::setCurrentStore($this->value('store'));

	}

	private function __AddStorePerStateDropDown($a)
	{
		require_once('DAO/BusinessObject/CStatesAndProvinces.php');

		if (!isset($a[self::name]))
		{
			$a[self::name] = 'store_per_state';
		}

		//build store drop down
		$StoreObj = DAO_CFactory::create('store');
		if (isset($a[self::franchise_filter]))
		{
			$StoreObj->franchise_id = $a[self::franchise_filter];
		}

		if (isset($a[self::state_filter]))
		{
			$StoreObj->state_id = $a[self::state_filter];
		}

		if (isset($a[self::supportsDFLFilter]))
		{
			$StoreObj->supports_dfl_customer_site = 1;
		}

		$StoreObj->orderBy('city ASC, store_name ASC ');

		if (empty($a[self::showInactiveStores]))
		{
			$StoreObj->active = 1;
		}

		if (isset($a[self::intro_stores_only_filter]) && $a[self::intro_stores_only_filter])
		{
			$StoreObj->supports_intro_orders = 1;
		}

		$numStores = $StoreObj->find();

		if ($numStores == 0)
		{
			//throw new Exception('No stores found.');
			// May not be any stores per state
			//create drop down
			$this->AddElement(array(
				CForm::type => CForm::Label,
				CForm::name => "store",
				CForm::value => "There are no active stores for this State."
			));

			return;
			/*	}
				else if ( $numStores == 1 ) {
					//if there is only one store, then just create a read only text box
					$StoreObj->fetch();
					$this->DefaultValues[$a[self::name]] = $StoreObj->id;
					$this->AddElement(array(CForm::type=> CForm::Label,
											CForm::name => "store",
											CForm::value => $StoreObj->store_name)); */
		}
		else
		{

			//create drop down
			$options = array();
			if (!isset($a[self::franchise_filter]))
			{
				if (isset($a[self::allowAllOption]) && $a[self::allowAllOption])
				{
					$options['all'] = 'All Stores';
				}
				else
				{
					$options['null'] = 'Select Location';
				}
				//add clear button
				$showClearBtn = true;
			}

			while ($StoreObj->fetch())
			{
				if ((!isset($this->DefaultValues[$a[self::name]])) && isset($a[self::franchise_filter]))
				{
					$this->DefaultValues[$a[self::name]] = $StoreObj->id;
				}

				//				$nameNoStateAndDD = $StoreObj->city.'--'.str_replace('Dream Dinners', '', $StoreObj->store_name);
				$nameNoStateAndDD = str_replace('Dream Dinners', '', $StoreObj->store_name);
				$nameNoStateAndDD = str_replace($StoreObj->state_id . ' ', '', $nameNoStateAndDD);

				$options[$StoreObj->id] = $StoreObj->city . self::optGroupSeparator . $nameNoStateAndDD;
			}

			$a[self::options] = $options;

			$this->__AddDropDownElement($a);
		}

		//if the user is just browsing through the stores, we don't want to change their homestore
		//		if ( $this->value('store') )
		//			CBrowserSession::setCurrentStore($this->value('store'));

	}

	/**
	 * Same as __AddStoreDropDown, but also changes the home_store_id
	 * functionally almost similar to the client side versions but a few small differences.
	 */
	private function __AddAdminStoreDropDown($a)
	{

		require_once('DAO/BusinessObject/CStatesAndProvinces.php');

		if (!isset($a[self::name]))
		{
			$a[self::name] = 'store';
		}

		if (!isset($a[self::setDefault]) || $a[self::setDefault] !== false)
		{
			//if $default == false, then pull the default store id out of the session
			if ((!isset($this->DefaultValues[$a[self::name]])) && CBrowserSession::getCurrentFadminStore())
			{
				$this->DefaultValues[$a[self::name]] = CBrowserSession::getCurrentFadminStore();
			}
		}

		//build store drop down
		$StoreObj = DAO_CFactory::create('store');
		if (isset($a[self::franchise_filter]))
		{
			$StoreObj->franchise_id = $a[self::franchise_filter];
		}

		$StoreObj->orderBy(' state_id ASC, city ASC, store_name ASC');

		if (empty($a[self::showInactiveStores]))
		{
			$StoreObj->active = 1;
		}

		if (!empty($a[self::omitDistributionCenters]))
		{
			$StoreObj->store_type = CStore::FRANCHISE;
		}
		else if (!empty($a[self::showOnlyDistibutionCenters]))
		{
			$StoreObj->store_type = CStore::DISTRIBUTION_CENTER;
		}


		$numStores = $StoreObj->find();

		if ($numStores == 0)
		{
			throw new Exception('No stores found.');
		}
		else if ($numStores == 1)
		{
			//if there is only one store, then just create a read only text box
			$StoreObj->fetch();
			$this->DefaultValues[$a[self::name]] = $StoreObj->id;
			$this->AddElement(array(
				CForm::type => CForm::Label,
				CForm::name => "store",
				CForm::value => $StoreObj->store_name
			));
		}
		else
		{
			//create drop down
			$options = array();
			if (!isset($a[self::franchise_filter]))
			{
				if (isset($a[self::allowAllOption]) && $a[self::allowAllOption])
				{
					$options['all'] = 'All Stores';
				}
				else
				{
					$options['null'] = 'Select Location';
				}
				//add clear button
				$showClearBtn = true;
			}
			while ($StoreObj->fetch())
			{
				if ((!isset($this->DefaultValues[$a[self::name]])) && isset($a[self::franchise_filter]))
				{
					$this->DefaultValues[$a[self::name]] = $StoreObj->id;
				}
				$nameNoStateAndDD = str_replace('Dream Dinners', '', $StoreObj->store_name);

				$nameNoStateAndDD = $StoreObj->city . ' --- ' . str_replace($StoreObj->state_id . ' ', '', $nameNoStateAndDD);
				//$nameNoStateAndDD .= $StoreObj->city;
				$nameNoStateAndDD .= ' --- #' . $StoreObj->home_office_id;

				$inActive = "";
				if (!$StoreObj->active)
				{
					$inActive = "|||";
				}

				$options[$StoreObj->id] = htmlspecialchars(CStatesAndProvinces::GetName($StoreObj->state_id) . self::optGroupSeparator . $inActive . $nameNoStateAndDD);

				$a[self::options] = $options;
				$this->__AddDropDownElement($a);
			}

		}

		//if the site admin is switching stores, then we want to change their homestore setting
		// CES: 9/17/07 Also change the FadminStore setting in their session
		if ($this->value('store'))
		{
			CBrowserSession::setCurrentStore($this->value('store'));
			CBrowserSession::setCurrentFadminStore($this->value('store'));
		}
	}

	public function __AddFranchiseDropDown($a)
	{

		if (!isset($a[self::name]))
		{
			$a[self::name] = 'franchise_id';
		}

		$Franchise = DAO_CFactory::create('franchise');
		$Franchise->query("SELECT *
			FROM franchise
			WHERE is_deleted = '0'
			ORDER BY active DESC, franchise_name ASC");

		$franchArray = array();
		$franchArray['null'] = '----------------------- Choose a Franchise -----------------------';
		$print_inactive = false;
		$active = 1;

		while ($Franchise->fetch())
		{
			if (!$print_inactive && $Franchise->active != $active)
			{
				$franchArray['null_also'] = '----------------------- Inactive Franchise -----------------------';
				$print_inactive = true;
			}

			$franchArray[$Franchise->id] = $Franchise->franchise_name;
		}

		$a[CForm::options] = $franchArray;

		$this->__AddDropDownElement($a);
	}

	public function __AddReferralSourceDropDown($a)
	{

		$metaDataArr = form_account::$referralSourceArray;
		$optionArr = array("" => 'How did you hear about us');

		foreach ($metaDataArr as $source => $property)
		{
			$optionArr[$source] = $property['placeholder'];
		}

		$a[CForm::options] = $optionArr;
		$this->__AddDropDownElement($a);
	}

	// TODO: LMH this function has really become more specific to Admin Menu editing.. it should be moved into the menu_items.php page
	public function __AddMenuItemDropDown($a)
	{
		/*

		$MenuToMenuItems = DAO_CFactory::create('menu_to_menu_item');
		$MenuItems = DAO_CFactory::create('menu_item');
		$MenuItems->joinAdd($MenuToMenuItems, "LEFT");
		$MenuItems->selectAdd();
		$MenuItems->selectAdd("entree_id, menu_item_name, menu_to_menu_item.menu_id");
		$MenuItems->whereAdd('entree_id > 0 and menu_to_menu_item.menu_id is null');
		$MenuItems->groupBy('entree_id');
		$MenuItems->orderBy('menu_item_name DESC');
		$MenuItems->find();
		*/

		$MenuItems = DAO_CFactory::create('menu_item');

		$MenuItems->query("SELECT 
			entree_id, 
			menu_item_name, 
			menu_to_menu_item.menu_id 
			FROM menu_item 
			LEFT JOIN menu_to_menu_item ON menu_to_menu_item.menu_item_id = menu_item.id
			WHERE entree_id > 1148 
			AND menu_to_menu_item.menu_id IS NULL 
			AND menu_item.is_deleted = 0 
			GROUP BY menu_item.entree_id	
			ORDER BY menu_item.entree_id ASC");

		$MenuItemArray = array();
		$MenuItemArray[0] = '--Choose Menu Item--';

		while ($MenuItems->fetch())
		{
			$MenuItemArray[$MenuItems->entree_id] = $MenuItems->menu_item_name . " -- " . $MenuItems->entree_id;
		}

		$a[CForm::options] = $MenuItemArray;
		$this->__AddDropDownElement($a);
	}

	// TODO: LMH this function has really become more specific to Admin Menu editing.. it should be moved into the menu_items.php page
	public function __AddMenuItemDropDownEditable($a)
	{

		$Menu = DAO_CFactory::create('menu');
		$currentMenusOnly = date('Y-m') . '-01';
		$Menu->selectAdd();
		$Menu->selectAdd("id, menu_name");
		$Menu->whereAdd("menu_start > DATE_SUB('$currentMenusOnly', INTERVAL 3 MONTH)");
		$Menu->orderBy('id'); // just in case...
		$Menu->find();
		$menu_array = array();
		while ($Menu->fetch())
		{
			$menu_array[$Menu->id] = $Menu->menu_name;
		}
		$min_menu_id = key($menu_array);
		if (!isset($min_menu_id) || is_null($min_menu_id))
		{
			$min_menu_id = 0;
		}
		$MenuToMenuItems = DAO_CFactory::create('menu_to_menu_item');
		$MenuItems = DAO_CFactory::create('menu_item');
		$MenuItems->joinAdd($MenuToMenuItems, "LEFT");
		$MenuItems->selectAdd();
		$MenuItems->selectAdd("entree_id, menu_item_name, menu_to_menu_item.menu_id");
		$MenuItems->whereAdd("entree_id > 0 and (menu_to_menu_item.menu_id >= $min_menu_id OR menu_to_menu_item.menu_id is null)");
		$MenuItems->groupBy('entree_id');
		$MenuItems->orderBy('menu_id, menu_item_name ASC');
		$MenuItems->find();
		$MenuItemArray = array();
		$MenuItemArray[0] = '--Choose Menu Item--';

		while ($MenuItems->fetch())
		{
			$menu_id_var = "Un-assigned";
			if (isset($MenuItems->menu_id))
			{
				$menu_id_var = $menu_array[$MenuItems->menu_id];
			}

			$MenuItemArray[$MenuItems->entree_id] = $MenuItems->menu_item_name . " -- Entree ID: " . $MenuItems->entree_id . " -- Menu ID: " . $menu_id_var;
		}

		$a[CForm::options] = $MenuItemArray;
		$this->__AddDropDownElement($a);
	}

	public function __AddMenuItemPanTypeDropDown($a)
	{

		require_once('DAO/BusinessObject/CMenuItem.php');

		$options = CMenuItem::containerTypes();
		array_unshift($options, '--- Choose a Container Type ---');
		$options[0] = '--- Choose a Container Type ---';
		$a[CForm::options] = $options;

		$this->__AddDropDownElement($a);
	}

	public function __AddTimezoneDropDown($a)
	{

		require_once('DAO/BusinessObject/CTimezones.php');

		if (!isset($a[self::name]))
		{
			$a[self::name] = 'timezone_id';
		}

		$options = CTimezones::zones();

		if (isset($a[self::default_value]) && (!isset($this->DefaultValues[$a[self::name]])))
		{
			$this->DefaultValues[$a[self::name]] = 12; //Pacific time
		}

		$a[CForm::options] = $options;

		$this->__AddDropDownElement($a);
	}

	/***********************************************************************
	 * Function:    AddElement
	 *
	 * In:        array (type, (variable))
	 * Out:
	 * Called by:    class method
	 * @return    Exception if type not specified
	 *
	 ***********************************************************************/

	public function AddElement($a, $setCheckboxes = true)
	{

		if (empty ($a[self::type]))
		{
			throw new exception("type property needs to be set.");
		}
		if (empty ($a[self::name]))
		{
			throw new exception("name property needs to be set.");
		}

		//		if ( ($a[self::type] != self::RadioButton) &&
		//			($a[self::type] != self::CheckBox) &&
		//			($a[self::type] != self::Submit) &&
		//				isset($this->value($a[self::name])) )
		//			$this->DefaultValues[$a[self::name]] = $a[self::value];

		//set default value
		if (isset($a[self::default_value]))
		{
			$this->DefaultValues[$a[self::name]] = $a[self::default_value];
		}
		else if (!isset($this->DefaultValues[$a[self::name]]))
		{
			//add a null value
			$this->DefaultValues[$a[self::name]] = null;
		}

		$gpc_type = false;
		if (isset($a[self::gpc_type]))
		{
			$gpc_type = $a[self::gpc_type];
		}

		$xss_filter = false;
		if (isset($a[self::xss_filter]))
		{
			$xss_filter = $a[self::xss_filter];
		}

		//check for reposted value (overrides default)
		$this->__CheckPost($a[self::name], $a[self::type], $setCheckboxes, $gpc_type, $xss_filter);

		if (!$this->Bootstrap)
		{
			if (isset($a[self::css_class]))
			{
				$a[self::css_class] = "cform_input " . $a[self::css_class];
			}
			else
			{
				$a[self::css_class] = "cform_input";
			}
		}

		$toolTip = "";

		if (isset($a[self::tooltip]))
		{
			if ($a[self::tooltip] === true || $a[self::tooltip] === "")
			{
				if (isset($a[self::required_msg]))
				{
					$toolTip = $a[self::required_msg];
				}
				else if (isset($a[self::placeholder]))
				{
					$toolTip = $a[self::placeholder];
				}
			}
			else
			{
				$toolTip = $a[self::tooltip];
			}

			$a[self::tooltip] = $toolTip;
		}

		if (DEBUG)
		{
			if (isset($a[self::value]) && ($a[self::type] != self::Submit) && ($a[self::type] != self::Button) && ($a[self::type] != self::Hidden) && ($a[self::type] != self::RadioButton) && ($a[self::type] != self::Label))
			{
				throw new exception ('CForm::value deprecated for elements other than submit buttons and hidden fields');
			}
		}

		switch ($a[self::type])
		{
			case self::Text:
				$this->__AddTextElement($a);
				break;

			case self::EMail:
				$this->__AddTextElement($a);
				break;

			case self::Password:
				$this->__AddTextElement($a);
				break;

			case self::Tel:
				$this->__AddTextElement($a);
				break;

			case self::Date:
				$this->__AddTextElement($a);
				break;

			case self::Time:
				$this->__AddTextElement($a);
				break;

			case self::Number:
				$a['number'] = true;
				$this->__AddTextElement($a);
				break;

			case self::Search:
				$a['search'] = true;
				$this->__AddTextElement($a);
				break;

			case self::DateTimeLocal:
				$this->__AddTextElement($a);
				break;

			case self::Month:
				$this->__AddTextElement($a);
				break;

			case self::Money:
				$a[self::type] = self::number;
				if (empty($a[self::step]))
				{
					$a[self::step] = '0.01';
				}
				$this->__AddTextElement($a);
				break;

			case self::File:
				$this->__AddFileElement($a);
				break;

			case self::Button:
				$this->__AddButtonElement($a);
				break;

			case self::ButtonHidden:
				$this->__AddButtonHiddenElement($a);
				break;

			case self::ButtonMultiStore:
				$this->__AddButtonMultiStoreElement($a);
				break;

			//			case self::Calendar:
			//				$this->__AddCalendarElement($a);
			//				break;

			case self::DropDown:
				$this->__AddDropDownElement($a);
				break;

			case self::Submit:
				$this->__AddSubmitElement($a);
				break;

			case self::CheckBox:
				$this->__AddCheckBoxElement($a);

				break;

			case self::TextArea:
				$this->__AddTextAreaElement($a);
				break;

			case self::Hidden:
				$this->__AddHiddenElement($a);
				break;

			case self::RadioButton:
				$this->__AddRadioButtonElement($a);
				break;

			case self::StatesProvinceDropDown:
				$this->__AddStatesProvinceDropDown($a);
				break;

			case self::RegionDropDown:
				$this->__AddRegionDropDown($a);
				break;

			case self::OpcoDropDown:
				$this->__AddOpcoDropDown($a);
				break;

			case self::StoreDropDown:
				$this->__AddStoreDropDown($a);
				break;

			case self::StorePerStateDropDown:
				$this->__AddStorePerStateDropDown($a);
				break;

			case self::AdminStoreDropDown:
				$this->__AddAdminStoreDropDown($a);
				break;

			case self::ReferralSourceDropDown:
				$this->__AddReferralSourceDropDown($a);
				break;

			case self::FranchiseDropDown:
				$this->__AddFranchiseDropDown($a);
				break;

			case self::TimezoneDropDown:
				$this->__AddTimezoneDropDown($a);
				break;

			case self::MenuItemDropDown:
				$this->__AddMenuItemDropDown($a);
				break;

			case self::MenuItemDropDownEditable:
				$this->__AddMenuItemDropDownEditable($a);
				break;

			case self::MenuItemPanTypeDropDown:
				$this->__AddMenuItemPanTypeDropDown($a);
				break;

			case self::Label:
				$this->__AddLabelElement($a);
				break;

			default:
				throw new Exception("Unknown type" . $a[self::type] . ".");
				break;
		}
		/*
		$curVal = $this->value($a[self::name]);
		$messageAttr = "";

		if (isset($a[self::required_msg]))
			$messageAttr = 'data-message="' . $a[self::required_msg] . '"';

		$finalHTML = '<span class="cform_outer">';

		if (!isset($a[self::placeholder]) && isset($a[self::label]) && empty($curVal))
		{
			$finalHTML .= '<span id="' .$a[self::name] . '_lbl" class="cform_label">' . $a[self::label]. '</span>';
		}

		$finalHTML .= ($a[self::type] == self::RadioButton ? $this->FormElements[$a[self::name]][$a[self::value]] : $this->FormElements[$a[self::name]] );

		$finalHTML .= '<span ' . $messageAttr . ' id="' .$a[self::name] . '_erm" class="cform_required_msg"></span>';

		$finalHTML .= '</span>';


		if ($a[self::type] == self::RadioButton )
		{
			$this->FormElements[$a[self::name]][$a[self::value]] = $finalHTML;
		}
		else
		{
			$this->FormElements[$a[self::name]] = $finalHTML;
		}
		*/
	}


	//	/***********************************************************************
	//	* Function:	Init
	//	* In:		string	- "name"
	//	* Out:
	//	* Called by:	class method
	//	* @return
	//	*
	//	***********************************************************************/
	//

	/***********************************************************************
	 * Function:    Render
	 *    converts this form to an array of HTML snippets for use with CTemplate
	 * In:
	 * Out:
	 * Called by:    class method
	 * @return    array['hidden']
	 *            ['key'] = val;
	 *                ['key_html'] = val;
	 *
	 ***********************************************************************/
	public function Render()
	{

		$rtn = array();

		$rtn['hidden_html'] = "";
		if ($this->Repost)
		{
			foreach ($this->HiddenElements as $key => $val)
			{
				$rtn['hidden_html'] .= $val;
			}
		}

		if ($this->csrf_protection)
		{

			$rtn['hidden_html'] .= '<input type="hidden" name="dd_csrf_token" value="' . $this->csrf_protection->dd_csrf_token . '" />';
		}

		foreach ($this->values() as $key => $val)
		{
			$rtn[$key] = $this->value($key);
			if (array_key_exists($key, $this->FormElements))
			{
				$rtn[$key . '_html'] = $this->FormElements[$key];
			}
		}

		return $rtn;
	}

	//	/***********************************************************************
	//	* Function:	RenderAsTable
	//	*
	//	*		Same as Render() but lists elements inbetween $beg and $end
	//	*
	//	* In:		$beg, $end
	//	* Out:
	//	* Called by:	class method
	//	* @return	array['hidden']
	//	*			['key'] = val;
	//	*				['key_html'] = val;
	//	*
	//	***********************************************************************/
	//	public function RenderAsTable( $pre = "", $post = "", $beg = "", $end = "") {
	//
	//		$rtn = array();
	//		print_r($rtn);
	//		exit;
	//		//pass all of the default values to the template
	//		foreach ( $this->DefaultValues as $key => $val )
	//			$rtn[$key] = $this->DefaultValues[$key];
	//
	//		$rtn['hidden_html'] = "";
	//		if ($this->Repost){
	//			while(list($key, $val) = each ($this->Vars)){
	//
	//				//avoid collisions with non-hidden form elements
	//				if ( array_key_exists($key, $this->FormElements) === false ) {
	//					$rtn[$key] = $this->value($key);
	//					$rtn['hidden_html'].= "<input type='hidden' name='$key' value='$val'>\n";
	//				}
	//			}
	//		}
	//
	//		$return = $pre;
	//
	//		foreach( $this->FormElements as $key => $htmlString ) {
	//			if ( array_key_exists($key, $this->DefaultValues) !== false ) {
	//				$rtn[$key] = $this->DefaultValues[$key];
	//			}
	//			$return .= $beg.$htmlString.$end;
	//		}
	//		$return .= $post;
	//
	//		return $return;
	//	}
	//
	//

	/***********************************************************************
	 * Function:    __get
	 *        Converts elements of the object to properties. Properties
	 *        are matched by "name".
	 *
	 * In:        STRING        -    name of element
	 * Out:        STRING        -    value of property or exception
	 * Called by:    class method
	 * @return    STRING        -    value of property or exception
	 *
	 ***********************************************************************/

	public function __get($szProp)
	{
		return $this->value($szProp);
	}
}

?>