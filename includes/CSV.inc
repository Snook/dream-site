<?php

class CSV
{
	/**
	 * Code copied from phpMyAdmin
	 *
	 */
	function makeCSVTable(&$header, &$rows, &$result, $showHeader, &$titleRows)
	{
		$fields_cnt = count($header);

		// Handles the "separator" and the optional "enclosed by" characters
		$sep = ',';
		$enc_by = '"';

		// double the "enclosed by" character
		$esc_by = $enc_by;

		$add_character = "\015\012";

		$schema_insert = '';

		if ($titleRows)
		{
			foreach ($titleRows as $trow)
			{
				$schema_insert = '';
				foreach ($trow as $j => $value)
				{
					if (!isset($value))
					{
						//$schema_insert .= 'NULL';
						$schema_insert .= '';
					}
					else if ($value == '0' || $value != '')
					{
						// loic1 : always enclose fields
						$value = preg_replace("/\015(\012)?/", "\012", $value);
						if ($enc_by == '')
						{
							$schema_insert .= $value;
						}
						else
						{
							$schema_insert .= $enc_by . str_replace($enc_by, $esc_by . $enc_by, $value) . $enc_by;
						}
					}
					else
					{
						$schema_insert .= '';
					}

					if ($j < $fields_cnt - 1)
					{
						$schema_insert .= $sep;
					}
				} // end for

				$result .= $schema_insert;
				$result .= $add_character;
			} // end for
		}

		$schema_insert = '';

		if ($showHeader)
		{
			foreach ($header as $field)
			{
				if ($enc_by == '')
				{
					$schema_insert .= $field;
				}
				else
				{
					$schema_insert .= $enc_by . str_replace($enc_by, $esc_by . $enc_by, $field) . $enc_by;
				}
				$schema_insert .= $sep;
			} // end while

			$result .= trim(substr($schema_insert, 0, -1));
			$result .= $add_character;
		}

		if (is_array($rows))
		{
			foreach ($rows as $row)
			{
				$keyIndex = 0;
				$schema_insert = '';
				foreach ($row as $j => $value)
				{
					if (!isset($value))
					{
						//$schema_insert .= 'NULL';
						$schema_insert .= '';
					}
					else if ($value == '0' || $value != '')
					{
						// loic1 : always enclose fields
						$value = preg_replace("/\015(\012)?/", "\012", $value);
						if ($enc_by == '')
						{
							$schema_insert .= $value;
						}
						else
						{
							$schema_insert .= $enc_by . str_replace($enc_by, $esc_by . $enc_by, $value) . $enc_by;
						}
					}
					else
					{
						$schema_insert .= '';
					}

					if ($keyIndex++ < $fields_cnt - 1)
					{
						$schema_insert .= $sep;
					}
				} // end for

				$result .= $schema_insert;
				$result .= $add_character;
			} // end for

			//iterate over a dataobject
		}
		else if (is_object($rows) && $header)
		{
			while ($rows->fetch())
			{
				$schema_insert = '';
				$j = 0;
				foreach ($header as $field => $label)
				{
					$value = null;
					if (isset($rows->$field))
					{
						if (method_exists($rows, 'get' . $field))
						{
							$value = $rows->{'get' . $field}();
						}
						else
						{
							// should this call toValue() ???
							$value = $rows->toValue($field);
						}
					}

					if (!isset($value))
					{
						//$schema_insert .= 'NULL';
						$schema_insert .= '';
					}
					else if ($value == '0' || $value != '')
					{
						// loic1 : always enclose fields
						$value = preg_replace("/\015(\012)?/", "\012", $value);
						if ($enc_by == '')
						{
							$schema_insert .= $value;
						}
						else
						{
							$schema_insert .= $enc_by . str_replace($enc_by, $esc_by . $enc_by, $value) . $enc_by;
						}
					}
					else
					{
						$schema_insert .= '';
					}

					if ($j < $fields_cnt - 1)
					{
						$schema_insert .= $sep;
					}

					$j++;
				} // end while

				$result .= $schema_insert;
				$result .= $add_character;
			} // end for
		}

		return true;
	} // end of the 'getTableCsv()' function

	function writeCSVFile($fileName, $header, $rows, $showHeader = true, $titleRows = false, $columnDescriptions = false)
	{

		$now = gmdate('D, d M Y H:i:s') . ' GMT';
		$mime_type = 'text/x-csv';
		$ext = 'csv';

		// send the write header statements to the browser
		header('Content-Type: ' . $mime_type);
		header('Expires: ' . $now);

		// lem9 & loic1: IE need specific headers
		$isIE = strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE');
		if ($isIE)
		{
			header('Content-Disposition: inline; filename="' . $fileName . '.' . $ext . '"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else
		{
			header('Content-Disposition: attachment; filename="' . $fileName . '.' . $ext . '"');
			header('Pragma: no-cache');
		}

		$result = '';
		CSV::makeCSVTable($header, $rows, $result, $showHeader, $titleRows);

		echo $result;
	}

	function writeCSVFileToFileSystem($fileName, $header, $rows, $titleRows = false)
	{

		$result = '';
		CSV::makeCSVTable($header, $rows, $result, true, $titleRows);

		if (!$handle = fopen($fileName, 'x'))
		{
			echo "Cannot open file ($fileName)";
			exit;
		}

		// Write $somecontent to our opened file.
		if (fwrite($handle, $result) === false)
		{
			echo "Cannot write to file ($fileName)";
			exit;
		}

		echo "Success, wrote to file ($fileName)";

		fclose($handle);
	}

}

?>