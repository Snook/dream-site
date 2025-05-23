<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Validation class
 *
 * Package to validate various datas. It includes :
 *   - numbers (min/max, decimal or not)
 *   - email (syntax, domain check)
 *   - string (predifined type alpha upper and/or lowercase, numeric,...)
 *   - date (min, max)
 *   - uri (RFC2396)
 *   - possibility valid multiple data with a single method call (::multiple)
 *
 * PHP versions 4
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Validate
 * @package    Validate
 * @author     Tomas V.V.Cox <cox@idecnet.com>
 * @author     Pierre-Alain Joye <pajoye@php.net>
 * @copyright  2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Validate.php,v 1.62 2005/05/11 17:53:20 dufuz Exp $
 * @link       http://pear.php.net/package/Validate
 */

/**
 * Methods for common data validations
 */
define('VALIDATE_NUM',          '0-9');
define('VALIDATE_SPACE',        '\s');
define('VALIDATE_ALPHA_LOWER',  'a-z');
define('VALIDATE_ALPHA_UPPER',  'A-Z');
define('VALIDATE_ALPHA',        VALIDATE_ALPHA_LOWER . VALIDATE_ALPHA_UPPER);
define('VALIDATE_EALPHA_LOWER', VALIDATE_ALPHA_LOWER . '�������������������������');
define('VALIDATE_EALPHA_UPPER', VALIDATE_ALPHA_UPPER . '�������������������������');
define('VALIDATE_EALPHA',       VALIDATE_EALPHA_LOWER . VALIDATE_EALPHA_UPPER);
define('VALIDATE_PUNCTUATION',  VALIDATE_SPACE . '\.,;\:&"\'\?\!\(\)');
define('VALIDATE_NAME',         VALIDATE_EALPHA . VALIDATE_SPACE . "'");
define('VALIDATE_STREET',       VALIDATE_NAME . "/\\��");

/**
 * Validation class
 *
 * Package to validate various datas. It includes :
 *   - numbers (min/max, decimal or not)
 *   - email (syntax, domain check)
 *   - string (predifined type alpha upper and/or lowercase, numeric,...)
 *   - date (min, max)
 *   - uri (RFC2396)
 *   - possibility valid multiple data with a single method call (::multiple)
 *
 * @category   Validate
 * @package    Validate
 * @author     Tomas V.V.Cox <cox@idecnet.com>
 * @author     Pierre-Alain Joye <pajoye@php.net>
 * @copyright  2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Validate
 */
class Validate
{
    /**
     * Validate a number
     *
     * @param string    $number     Number to validate
     * @param array     $options    array where:
     *                              'decimal'   is the decimal char or false when decimal not allowed
     *                                          i.e. ',.' to allow both ',' and '.'
     *                              'dec_prec'  Number of allowed decimals
     *                              'min'       minimun value
     *                              'max'       maximum value
     *
     * @return boolean true if valid number, false if not
     *
     * @access public
     */
    function number($number, $options = array())
    {
        $decimal = $dec_prec = $min = $max = null;
        if (is_array($options)) {
            extract($options);
        }

        $dec_prec   = $dec_prec ? "{1,$dec_prec}" : '+';
        $dec_regex  = $decimal  ? "[$decimal][0-9]$dec_prec" : '';

        if (!preg_match("|^[-+]?\s*[0-9]+($dec_regex)?\$|", $number)) {
            return false;
        }

        if ($decimal != '.') {
            $number = strtr($number, $decimal, '.');
        }

        $number = (float)str_replace(' ', '', $number);
        if ($min !== null && $min > $number) {
            return false;
        }

        if ($max !== null && $max < $number) {
            return false;
        }
        return true;
    }

    /**
     * Validate a email
     *
     * @param string    $email          URL to validate
     * @param boolean   $domain_check   Check or not if the domain exists
     *
     * @return boolean true if valid email, false if not
     *
     * @access public
     */
    function email($email, $check_domain = false)
    {
        $regex = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+'.
                 '(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|'.
                 '(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|'.
                 '([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))'.
                 '\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|'.
                 '(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|'.
                 '([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))'.
                 '\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|'.
                 '((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';
        if (preg_match($regex, $email)) {
            if ($check_domain && function_exists('checkdnsrr')) {
                list (, $domain)  = explode('@', $email);
                if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Validate a string using the given format 'format'
     *
     * @param string    $string     String to validate
     * @param array     $options    Options array where:
     *                              'format' is the format of the string
     *                                  Ex: VALIDATE_NUM . VALIDATE_ALPHA (see constants)
     *                              'min_length' minimum length
     *                              'max_length' maximum length
     *
     * @return boolean true if valid string, false if not
     *
     * @access public
     */
    function string($string, $options)
    {
        $format = null;
        $min_length = $max_length = 0;
        if (is_array($options)) {
            extract($options);
        }
        if ($format && !preg_match("|^[$format]*\$|s", $string)) {
            return false;
        }
        if ($min_length && strlen($string) < $min_length) {
            return false;
        }
        if ($max_length && strlen($string) > $max_length) {
            return false;
        }
        return true;
    }

    /**
     * Validate an URI (RFC2396)
     * This function will validate 'foobarstring' by default, to get it to validate
     * only http, https, ftp and such you have to pass it in the allowed_schemes
     * option, like this:
     * <code>
     * $options = array('allowed_schemes' => array('http', 'https', 'ftp'))
     * var_dumpn(Validate::uri('http://www.example.org'), $options);
     * </code>
     *
     * @param string    $url        URI to validate
     * @param array     $options    Options used by the validation method.
     *                              key => type
     *                              'domain_check' => boolean
     *                                  Whether to check the DNS entry or not
     *                              'allowed_schemes' => array, list of protocols
     *                                  List of allowed schemes ('http',
     *                                  'ssh+svn', 'mms')
     *
     * @return boolean true if valid uri, false if not
     *
     * @access public
     */
    function uri($url, $options = null)
    {
        $domain_check = false;
        $allowed_schemes = null;
        if (is_array($options)) {
            extract($options);
        }
        if (preg_match(
            '!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!',
            $url, $matches))
        {
            $scheme = $matches[2];
            $authority = $matches[4];
            if (is_array($allowed_schemes) &&
                !in_array($scheme,$allowed_schemes)
            ) {
                return false;
            }
            if ($domain_check && function_exists('checkdnsrr')) {
                if (!checkdnsrr($authority, 'A')) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Validate date and times. Note that this method need the Date_Calc class
     *
     * @param string    $date   Date to validate
     * @param array     $options array options where :
     *                          'format' The format of the date (%d-%m-%Y)
     *                          'min' The date has to be greater
     *                                than this array($day, $month, $year)
     *                                or PEAR::Date object
     *                          'max' The date has to be smaller than
     *                                this array($day, $month, $year)
     *                                or PEAR::Date object
     *
     * @return boolean true if valid date/time, false if not
     *
     * @access public
     */
    function date($date, $options)
    {
        $max = $min = false;
        $format = '';
        if (is_array($options)){
            extract($options);
        }
        $date_len   = strlen($format);
        for ($i = 0; $i < strlen($format); $i++) {
            $c = $format[$i];
            if ($c == '%') {
                $next = $format{$i + 1};
                switch ($next) {
                    case 'j':
                    case 'd':
                        if ($next == 'j') {
                            $day = (int)Validate::_substr($date, 1, 2);
                        } else {
                            $day = (int)Validate::_substr($date, 2);
                        }
                        if ($day < 1 || $day > 31) {
                            return false;
                        }
                        break;
                    case 'm':
                    case 'n':
                        if ($next == 'm') {
                            $month = (int)Validate::_substr($date, 2);
                        } else {
                            $month = (int)Validate::_substr($date, 1, 2);
                        }
                        if ($month < 1 || $month > 12) {
                            return false;
                        }
                        break;
                    case 'Y':
                    case 'y':
                        if ($next == 'Y') {
                            $year = Validate::_substr($date, 4);
                            $year = (int)$year?$year:'';
                        } else {
                            $year = (int)(substr(date('Y'), 0, 2) .
                                          Validate::_substr($date, 2));
                        }
                        if (strlen($year) != 4 || $year < 0 || $year > 9999) {
                            return false;
                        }
                        break;
                    case 'g':
                    case 'h':
                        if ($next == 'g') {
                            $hour = Validate::_substr($date, 1, 2);
                        } else {
                            $hour = Validate::_substr($date, 2);
                        }
                        if ($hour < 0 || $hour > 12) {
                            return false;
                        }
                        break;
                    case 'G':
                    case 'H':
                        if ($next == 'G') {
                            $hour = Validate::_substr($date, 1, 2);
                        } else {
                            $hour = Validate::_substr($date, 2);
                        }
                        if ($hour < 0 || $hour > 24) {
                            return false;
                        }
                        break;
                    case 's':
                    case 'i':
                        $t = Validate::_substr($date, 2);
                        if ($t < 0 || $t > 59) {
                            return false;
                        }
                        break;
                    default:
                        trigger_error("Not supported char `$next' after % in offset " . ($i+2), E_USER_WARNING);
                }
                $i++;
            } else {
                //literal
                if (Validate::_substr($date, 1) != $c) {
                    return false;
                }
            }
        }
        // there is remaing data, we don't want it
        if (strlen($date)) {
            return false;
        }

        if (isset($day) && isset($month) && isset($year)) {
            if (!checkdate($month, $day, $year)) {
                return false;
            }
            
            if ($min) {
                include_once 'Date/Calc.php';
                if (is_a($min, 'Date') &&
                    (Date_Calc::compareDates($day, $month, $year,
                                             $min->getDay(), $min->getMonth(), $min->getYear()) < 0))
                {
                    return false;
                } elseif (is_array($min) &&
                        (Date_Calc::compareDates($day, $month, $year,
                                             $min[0], $min[1], $min[2]) < 0))
                {
                    return false;
                }
            }

            if ($max) {
                include_once 'Date/Calc.php';
                if (is_a($max, 'Date') &&
                    (Date_Calc::compareDates($day, $month, $year,
                                             $max->getDay(), $max->getMonth(), $max->getYear()) > 0))
                {
                    return false;
                } elseif (is_array($max) &&
                        (Date_Calc::compareDates($day, $month, $year,
                                                 $max[0], $max[1], $max[2]) > 0))
                {
                    return false;
                }
            }
        }

        return true;
    }

    function _substr(&$date, $num, $opt = false)
    {
        if ($opt && strlen($date) >= $opt && preg_match('/^[0-9]{'.$opt.'}/', $date, $m)) {
            $ret = $m[0];
        } else {
            $ret = substr($date, 0, $num);
        }
        $date = substr($date, strlen($ret));
        return $ret;
    }

    function _modf($val, $div) {
        if (function_exists('bcmod')) {
            return bcmod($val, $div);
        } elseif (function_exists('fmod')) {
            return fmod($val, $div);
        }
        $r = $val / $div;
        $i = intval($r);
        return intval($val - $i * $div + .1);
    }

    /**
     * Calculates sum of product of number digits with weights
     *
     * @param string $number number string
     * @param array $weights reference to array of weights
     *
     * @returns int returns product of number digits with weights
     *
     * @access protected
     */
    function _multWeights($number, &$weights) {
        if (!is_array($weights)) {
            return -1;
        }
        $sum = 0;

        $count = min(count($weights), strlen($number));
        if ($count == 0)  { // empty string or weights array
            return -1;
        }
        for ($i = 0; $i < $count; ++$i) {
            $sum += intval(substr($number, $i, 1)) * $weights[$i];
        }

        return $sum;
    }

    /**
     * Calculates control digit for a given number
     *
     * @param string $number number string
     * @param array $weights reference to array of weights
     * @param int $modulo (optionsl) number
     * @param int $subtract (optional) number
     * @param bool $allow_high (optional) true if function can return number higher than 10
     *
     * @returns int -1 calculated control number is returned
     *
     * @access protected
     */
    function _getControlNumber($number, &$weights, $modulo = 10, $subtract = 0, $allow_high = false) {
        // calc sum
        $sum = Validate::_multWeights($number, $weights);
        if ($sum == -1) {
            return -1;
        }
        $mod = Validate::_modf($sum, $modulo);  // calculate control digit

        if ($subtract > $mod && $mod > 0) {
            $mod = $subtract - $mod;
        }
        if ($allow_high === false) {
            $mod %= 10;           // change 10 to zero
        }
        return $mod;
    }

    /**
     * Validates a number
     *
     * @param string $number number to validate
     * @param array $weights reference to array of weights
     * @param int $modulo (optionsl) number
     * @param int $subtract (optional) numbier
     *
     * @returns bool true if valid, false if not
     *
     * @access protected
     */
    function _checkControlNumber($number, &$weights, $modulo = 10, $subtract = 0) {
        if (strlen($number) < count($weights)) {
            return false;
        }
        $target_digit  = substr($number, count($weights), 1);
        $control_digit = Validate::_getControlNumber($number, $weights, $modulo, $subtract, $target_digit === 'X');

        if ($control_digit == -1) {
            return false;
        }
        if ($target_digit === 'X' && $control_digit == 10) {
            return true;
        }
        if ($control_digit != $target_digit) {
            return false;
        }
        return true;
    }

    /**
     * Bulk data validation for data introduced in the form of an
     * assoc array in the form $var_name => $value.
     * Can be used on any of Validate subpackages
     *
     * @param  array   $data     Ex: array('name' => 'toto', 'email' => 'toto@thing.info');
     * @param  array   $val_type Contains the validation type and all parameters used in.
     *                           'val_type' is not optional
     *                           others validations properties must have the same name as the function
     *                           parameters.
     *                           Ex: array('toto'=>array('type'=>'string','format'='toto@thing.info','min_length'=>5));
     * @param  boolean $remove if set, the elements not listed in data will be removed
     *
     * @return array   value name => true|false    the value name comes from the data key
     *
     * @access public
     */
    function multiple(&$data, &$val_type, $remove = false)
    {
        $keys = array_keys($data);
        $valid = array();
        foreach ($keys as $var_name) {
            if (!isset($val_type[$var_name])) {
                if ($remove) {
                    unset($data[$var_name]);
                }
                continue;
            }
            $opt = $val_type[$var_name];
            $methods = get_class_methods('Validate');
            $val2check = $data[$var_name];
            // core validation method
            if (in_array(strtolower($opt['type']), $methods)) {
                //$opt[$opt['type']] = $data[$var_name];
                $method = $opt['type'];
                unset($opt['type']);

                if (sizeof($opt) == 1) {
                    $opt = array_pop($opt);
                }
                $valid[$var_name] = call_user_func(array('Validate', $method), $val2check, $opt);

            /**
             * external validation method in the form:
             * "<class name><underscore><method name>"
             * Ex: us_ssn will include class Validate/US.php and call method ssn()
             */
            } elseif (strpos($opt['type'], '_') !== false) {
                $validateType = explode('_', $opt['type']);
                $method       = array_pop($validateType);
                $class        = implode('_', $validateType);
                $classPath    = str_replace('_', DIRECTORY_SEPARATOR, $class);
                if (!@include_once "Validate/$classPath.php") {
                    trigger_error("Validate_$class isn't installed or you may have some permissoin issues", E_USER_ERROR);
                }

                if (!class_exists("Validate_$class") ||
                    !in_array($method, get_class_methods("Validate_$class")))
                {
                    trigger_error("Invalid validation type Validate_$class::$method", E_USER_WARNING);
                    continue;
                }
                unset($opt['type']);
                if (sizeof($opt) == 1) {
                    $opt = array_pop($opt);
                }
                $valid[$var_name] = call_user_func(array("Validate_$class", $method), $data[$var_name], $opt);
            } else {
                trigger_error("Invalid validation type {$opt['type']}", E_USER_WARNING);
            }
        }
        return $valid;
    }
}

?>
