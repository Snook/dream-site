<?PHP

/**
* As of PHP 4.2.0, there is no need to seed the random number
* generator, however, we'll do it here for portability. This will be
* needed for use with the creation of an IV for encode/decode functions.
*/
mt_srand(intval(microtime(true) * 1000000));
srand(intval(microtime(true) * 1000000));

class CCrypto {

	/**
	* The key used to encode/decode individual field data. Customize for you!
	* Keep this key a secret (keep this in a config file off the web
	* directory). Use readable characters and make at least 62 characters long.
	* The key default specified is made up of single UNIQUE characters of:
	*           "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
	*
	*Ruler-Tens:          1         2         3         4         5         6
	*Ruler-Ones: 12345678901234567890123456789012345678901234567890123456789012
	*/
	static private $_ENCRYPT_KEY =
	             "nDsI2VfeCaURkEpiSz1McjY5qLXhtr0dGPgJOxA7Nw4blFoBuZ9mHv6KQT3yW8";
	                  // /--- EACH CHARACTER MUST BE UNIQUE. NO DUPS ---/

    /**
     * stringSplit - Parses out a string into x number of byte chunk characters.
     * Example: $arr = self::stringSplit('Hi', 1);
     *
     * $arr then contains: $arr[0] = 'H'  and  $arr[1] = 'i'
     *
     * @param  string $_text Pass a text string you would like to parse out.
     * @param  int    $_chunksize The number of characters to split by.
     * @return array  Returns an array with each index containing x characters.
     * @access public
     */
    static function stringSplit($_text, $_chunksize = 1)
    {
    	$_matches = array();
        preg_match_all('/(' . str_repeat('.', $_chunksize) . ')/Uims',
                       $_text,
                       $_matches);

        return $_matches[1];
    }


    /**
     * encode - Encrypts plain text. It uses an Initialization Vector (IV)
     * integer in the range of 1-500 in order to produce more varied results.
     * You must use the same IV number when calling the decode member.
     * The encrypted text may be longer than original plain text.
     * This function requires PHP ver >= 4.2.0 because of str_rot13 function.
     *
     *
     * @param  string $_text Pass the plain text you would like to encrypt.
     * @param  int    $_IV Pass a random number between 1 and 500.
     * @param  string self::$_ENCRYPT_KEY Pass the key to use for encryption.
     * @return string Returns the plain text passed in encrypted format.
     * @access public
     */
    static function encode($_text, $_IV = 21)
    {

        if (is_numeric($_IV)) {

            $_IV = intval($_IV);

            if ($_IV < 1)
                $_IV = 1;
            else
              if ($_IV > 500)
                  $_IV = 42;

        } else {

            $_IV = 3;
        }

        $_text .= ' ';

        $_arr1 = self::stringSplit(self::$_ENCRYPT_KEY);
        $_arr2 = $_arr1;

        foreach ($_arr1 as $_i1 => $_v1) {

            foreach ($_arr2 as $_i2 => $_v2) {

                $_counter = ($_i2 + 1) + ($_i1 * strlen(self::$_ENCRYPT_KEY));

                $_array[$_counter] = $_v1 . $_v2;

                if ($_v1 == $_v2)
                    $_array[$_counter] = $_v1 . '_';
            }
        }

        $_encoded = '';
        $_count = 0;
        $_msgarr = self::stringSplit($_text);

        foreach ($_msgarr as $_mindex => $_mvalue) {

            If ($_mindex / 2 <> ceil ($_mindex / 2)) {

                $_masc = ord($_mvalue) - 31;
                $_masc = $_masc + (ceil($_count * $_IV / 3) + $_IV);
                $_count++;
                if ($_count > 12)
                    $_count = 0;
                $_encoded .= $_array[$_masc];

            } else {

                // No need to get around str_rot13 bug here since $_mvalue is
                // not being referenced after this point & will get overriden.
                $_encoded .= str_rot13($_mvalue);
            }
        }

        return $_encoded;
    }


    /**
     * decode - Decodes text that was previously encrypted with encode.
     * This function requires PHP ver >= 4.2.0 because of str_rot13 function.
     *
     * @param  string $_text Pass the encrypted text created by encode.
     * @param  int    $_IV Pass the same IV number as used in encode.
     * @param  string self::$_ENCRYPT_KEY Pass the same key used in encode.
     * @return string Returns the unencrypted plain text.
     * @access public
     */
    static function decode($_text, $_IV = 21)
    {

        $_count = 0;

        if (is_numeric($_IV)) {

            $_IV = intval($_IV);

            if ($_IV < 1)
                $_IV = 1;
            else
              if ($_IV > 500)
                  $_IV = 42;

        } else {

            $_IV = 3;
        }

        $_arr1 = self::stringSplit(self::$_ENCRYPT_KEY);
        $_arr2 = $_arr1;

        foreach ($_arr1 as $_i1 => $_v1) {

            foreach ($_arr2 as $_i2 => $_v2) {

                $_counter = ($_i2 + 1) + ($_i1 * strlen(self::$_ENCRYPT_KEY));
                $_array[$_counter] = $_v1 . $_v2;
                if ($_v1 == $_v2)
                    $_array[$_counter] = $_v1 . '_';
            }
        }

        $_array = array_flip($_array);
        $_msgarr = self::stringSplit($_text, 3);

        $_decoded = '';

        foreach ($_msgarr as $_mvalue) {

            // $_tmp_hold used to get around a possible PHP bug in versions
            // earlier than 4.3.0. The variable passed in function might change.
            $_tmp_hold  = $_mvalue;
            $_decoded  .= str_rot13(substr($_tmp_hold,0,1));

            $_ivalue = $_array[substr($_mvalue, 1, 2)];
            $_ivalue = $_ivalue - (ceil($_count * $_IV / 3) + $_IV);
            $_count++;
            if ($_count > 12)
               $_count = 0;

            $_masc = chr($_ivalue + 31);
            $_decoded .= $_masc;
        }

        return trim($_decoded);
    }

    static function genIV() {
		$iv = (int) mt_rand(1, 500);   // or use rand()
    }

}

?>