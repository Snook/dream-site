<?php

define('TYPE_NOCLEAN', 'TYPE_NOCLEAN');            // no change
define('TYPE_BOOL', 'TYPE_BOOL');                // force boolean
define('TYPE_INT', 'TYPE_INT');                    // force integer
define('TYPE_UINT', 'TYPE_UINT');                // force unsigned integer
define('TYPE_NUM', 'TYPE_NUM');                    // force number
define('TYPE_UNUM', 'TYPE_UNUM');                // force unsigned number
define('TYPE_UNIXTIME', 'TYPE_UNIXTIME');        // force unix datestamp (unsigned integer)
define('TYPE_STR', 'TYPE_STR');                    // force trimmed string
define('TYPE_NOTRIM', 'TYPE_NOTRIM');            // force string - no trim
define('TYPE_NOHTML', 'TYPE_NOHTML');            // force trimmed string with HTML made safe
define('TYPE_ARRAY', 'TYPE_ARRAY');                // force array
define('TYPE_FILE', 'TYPE_FILE');                // force file
define('TYPE_BINARY', 'TYPE_BINARY');            // force binary string
define('TYPE_NOHTMLCOND', 'TYPE_NOHTMLCOND');    // force trimmed string with HTML made safe if determined to be unsafe
define('TYPE_EMAIL', 'TYPE_EMAIL');    // force trimmed string with HTML made safe if determined to be unsafe
define('TYPE_POSTAL_CODE', 'TYPE_POSTAL_CODE');    // force trimmed string with returning only valid postal code characters
define('TYPE_DATE_YYYYMMDD', 'TYPE_DATE_YYYYMMDD');
define('TYPE_STR_SIMPLE', 'TYPE_STR_SIMPLE'); //same as TYPE_STR but without safeSQL();

class CGPC
{

	// get post vars
	public static $GPC = array(
		'g' => null,
		'p' => null,
		'r' => null
	);

	public static function gpc_vars($unsetGlobals = false)
	{

		foreach ($_GET AS $k => $v)
		{
			self::$GPC['g'][$k] = $v;

			if ($unsetGlobals)
			{
				unset($_GET[$k]);
			}
		}

		foreach ($_POST AS $k => $v)
		{
			self::$GPC['p'][$k] = $v;

			if ($unsetGlobals)
			{
				unset($_POST[$k]);
			}
		}

		foreach ($_REQUEST AS $k => $v)
		{
			self::$GPC['r'][$k] = $v;

			if ($unsetGlobals)
			{
				unset($_REQUEST[$k]);
			}
		}
	}

	/**
	 * Makes a single GPC variable safe to use and returns it
	 *
	 * @param    array    The source array containing the data to be cleaned
	 * @param    string    The name of the variable in which we are interested
	 * @param    integer    The type of the variable in which we are interested
	 *
	 * @return    mixed
	 */
	static function clean_gpc($source, $varname, $vartype = TYPE_NOCLEAN, $xss_filter = false)
	{
		$data = false;

		switch ($source)
		{
			case 'g':
				$data = (isset(self::$GPC['g'][$varname]) ? self::$GPC['g'][$varname] : null);
				break;
			case 'p':
				$data = (isset(self::$GPC['p'][$varname]) ? self::$GPC['p'][$varname] : null);
				break;
			default:
				$data = (isset(self::$GPC['r'][$varname]) ? self::$GPC['r'][$varname] : null);
				break;
		}

		return self::do_clean($data, $vartype, $xss_filter);
	}

	static function do_clean($data, $type, $xss_filter = false)
	{
		if ($data === false)
		{
			return false;
		}

		if ($xss_filter)
		{
			require_once('includes/class.inputfilter_clean.php');
			$xssFilter = new InputFilter();
			$data = $xssFilter->process($data);
		}

		$booltypes = array(
			'1',
			'yes',
			'y',
			'true'
		);

		if (isset($data))
		{
			switch ($type)
			{
				case TYPE_INT:
					$data = intval($data);
					break;
				case TYPE_UINT:
					$data = ($data = intval($data)) < 0 ? 0 : $data;
					break;
				case TYPE_NUM:
					$data = strval($data) + 0;
					break;
				case TYPE_UNUM:
					$data = strval($data) + 0;
					$data = ($data < 0) ? 0 : $data;
					break;
				case TYPE_BINARY:
					$data = strval($data);
					break;
				case TYPE_STR:
					$data = trim(strval($data));
					$data = filter_var($data, FILTER_SANITIZE_STRING);
                    $_db = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
                    mysqli_select_db($_db, DB_DATABASE);
                    $data = CGPC::safeSQL($data, $_db);
					break;
                case TYPE_STR_SIMPLE:
                    $data = trim(strval($data));
                    $data = filter_var($data, FILTER_SANITIZE_STRING);
                    break;
				case TYPE_EMAIL:
					$data = trim(strval($data));
					$data = filter_var($data, FILTER_SANITIZE_EMAIL);
					break;
				case TYPE_NOTRIM:
					$data = strval($data);
					break;
				case TYPE_NOHTML:
					$data = htmlspecialchars(trim(strval($data)));
					break;
				case TYPE_BOOL:
					$data = in_array(strtolower($data), $booltypes) ? 1 : 0;
					break;
				case TYPE_ARRAY:
					$data = (is_array($data)) ? $data : array();
					break;
				case TYPE_POSTAL_CODE:
					$data = trim(strval($data));
					$data = preg_replace("/[^A-Z0-9 \-]/i", "", $data);
					break;
				case TYPE_DATE_YYYYMMDD:
					$data = trim(strval($data));
					if (!preg_match('/[1-2][0-9][0-9][0-9]\-[0-1][0-9]\-[0-3][0-9]$/', $data))
					{
						$data = null;
					}
					break;
				case TYPE_NOHTMLCOND:
				{
					$data = trim(strval($data));
					if (strcspn($data, '<>"') < strlen($data) || (strpos($data, '&') !== false && !preg_match('/&(#[0-9]+|amp|lt|gt|quot);/si', $data)))
					{
						// data is not htmlspecialchars because it still has characters or entities it shouldn't
						$data = htmlspecialchars($data);
					}
				}
					break;
				case TYPE_FILE:
				{
					// perhaps redundant :p
					if (is_array($data))
					{
						if (is_array($data['name']))
						{
							$files = count($data['name']);
							for ($index = 0; $index < $files; $index++)
							{
								$data['name']["$index"] = trim(strval($data['name']["$index"]));
								$data['type']["$index"] = trim(strval($data['type']["$index"]));
								$data['tmp_name']["$index"] = trim(strval($data['tmp_name']["$index"]));
								$data['error']["$index"] = intval($data['error']["$index"]);
								$data['size']["$index"] = intval($data['size']["$index"]);
							}
						}
						else
						{
							$data['name'] = trim(strval($data['name']));
							$data['type'] = trim(strval($data['type']));
							$data['tmp_name'] = trim(strval($data['tmp_name']));
							$data['error'] = intval($data['error']);
							$data['size'] = intval($data['size']);
						}
					}
					else
					{
						$data = array(
							'name' => '',
							'type' => '',
							'tmp_name' => '',
							'error' => 0,
							'size' => 4,
							// UPLOAD_ERR_NO_FILE
						);
					}
				}
					break;
				case TYPE_UNIXTIME:
				{
					// TODO
				}
					break;
				case TYPE_NOCLEAN:
				{
				}
					break;
				default:
					// trigger_error
			}
		}

		// strip out characters that really have no business being in non-binary data
		switch ($type)
		{
			case TYPE_STR:
			case TYPE_NOTRIM:
			case TYPE_NOHTML:
			case TYPE_NOHTMLCOND:
				$data = str_replace(chr(0), '', $data);
		}

		return $data;
	}

//following functions taken from class.inputfilter_clean.php for cleaning $_POST, $_GET, $_REQUEST variables from pages
    static function safeSQL($source, &$connection)
    {
        if (is_array($source))
        {
            foreach ($source as $key => $value)
            {
                if (is_string($value))
                {
                    $source[$key] = $source->quoteSmart(CGPC::decode($value), $connection);
                }
            }

            return $source;
        }
        else if (is_string($source))
        {
            if (is_string($source))
            {
                return CGPC::quoteSmart(CGPC::decode($source), $connection);
            }
        }
        else
        {
            return $source;
        }
    }

    static function quoteSmart($source, &$connection)
    {
        if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
        {
            $source = stripslashes($source);
        }
        $source = CGPC::escapeString($source, $connection);

        return $source;
    }

	static  function escapeString($string, $connection)
    {
    	$string = mysqli_real_escape_string($connection, $string);
        return $string;
    }

    static function decode($source)
    {
        $source = html_entity_decode($source, ENT_QUOTES, "ISO-8859-1");

        /*
        $source = @preg_replace('/&#(\d+);/me', "chr(\\1)", $source);
        $source = @preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $source);
        */

        // Convert decimal
        $source = preg_replace_callback('/&#(\d+);/m', function ($m)
        {
            return utf8_encode(chr($m[1]));
        }, $source);

        // Convert hex
        $source = preg_replace_callback('/&#x([a-f0-9]+);/mi', function ($m)
        {
            return utf8_encode(chr('0x' . $m[1]));
        }, $source);

        return $source;
    }
}

function connect()
{

    $_db = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
    mysqli_select_db($_db, DB_DATABASE);

    return $_db;
}
?>