<?php
require_once('phplib/DB/DataObject.php');
require_once('includes/DAO/CFactory.php');

/**
 * DreamSite base class for data access objects
 */
class DAO extends DB_DataObject
{

	static private $_fieldDescriptions = null;
	protected $_fetchResult = null;

	var $_joinAdd = array();  // array (joinAs => tableName)
	var $_query_string;

	function __construct()
	{
		$this->is_deleted = 0;
	}

	/**
	 * Returns the current datetime
	 */
	static function now()
	{
		return date("Y-m-d H:i:s", time());
	}

	private static $unneeded = array(
		'__table',
		'N',
		'_database_dsn',
		'_DB_DataObject_version',
		'_database_dsn_md5',
		'_database',
		'_query',
		'_query_string',
		'_DB_resultid',
		'_resultFields',
		'_link_loaded',
		'_join',
		'_joinAdd',
		'_lastError',
		'is_deleted',
		'_fetchResult'
	);

	public static function getCompressedArrayFromDAO($dataObj, $includeNullValues = false, $setNullValueToZero = false)
	{
		$retVal = array();
		foreach ($dataObj as $field => $value)
		{

			if ($includeNullValues)
			{
				if (!in_array($field, self::$unneeded))
				{
					if (is_null($value))
					{
						$value = 0;
					}
					$retVal[$field] = $value;
				}
			}
			else
			{
				if (isset($value) && !in_array($field, self::$unneeded))
				{
					$retVal[$field] = $value;
				}
			}
		}

		return $retVal;
	}

	/**
	 * This is a hack to override DB_DataObject:insert() to handle our timestamps correctly.
	 *
	 * @throws exception
	 */
	function insert($ignoreAffectedRows = false)
	{
		$this->validate();

		global $_DB_DATAOBJECT;

		// we need to write to the connection (For nextid) - so us the real
		// one not, a copied on (as ret-by-ref fails with overload!)

		if (!isset($_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5]))
		{
			$this->_connect();
		}

		$quoteIdentifiers = !empty($_DB_DATAOBJECT['CONFIG']['quote_identifiers']);

		$DB = $_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5];

		$items = $this->table();

		if (!$items)
		{
			$this->raiseError("insert:No table definition for {$this->tableName()}", DB_DATAOBJECT_ERROR_INVALIDCONFIG);

			return false;
		}
		$options = $_DB_DATAOBJECT['CONFIG'];

		$leftq = '';
		$rightq = '';

		$seqKeys = isset($_DB_DATAOBJECT['SEQUENCE'][$this->_database][$this->tableName()]) ? $_DB_DATAOBJECT['SEQUENCE'][$this->_database][$this->tableName()] : $this->sequenceKey();

		$key = isset($seqKeys[0]) ? $seqKeys[0] : false;
		$useNative = isset($seqKeys[1]) ? $seqKeys[1] : false;
		$seq = isset($seqKeys[2]) ? $seqKeys[2] : false;

		$dbtype = $_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5]->dsn["phptype"];

		// nativeSequences or Sequences..

		// big check for using sequences

		if (($key !== false) && !$useNative)
		{

			if (!$seq)
			{
				$keyvalue = $DB->nextId($this->tableName());
			}
			else
			{
				$f = $DB->getOption('seqname_format');
				$DB->setOption('seqname_format', '%s');
				$this->$key = $DB->nextId($seq);
				$DB->setOption('seqname_format', $f);
			}
		}

		// if we haven't set disable_null_strings to "full"
		$ignore_null = !isset($options['disable_null_strings']) || !is_string($options['disable_null_strings']) || strtolower($options['disable_null_strings']) !== 'full';

		foreach ($items as $k => $v)
		{

			// if we are using autoincrement - skip the column...
			if ($key && ($k == $key) && $useNative)
			{
				continue;
			}

			// ***********
			// Added ToddW
			// insert the current timestamp for the create time
			if ($k == 'timestamp_created')
			{
				if ($leftq)
				{
					$leftq .= ', ';
					$rightq .= ', ';
				}

				$leftq .= ($quoteIdentifiers ? ($DB->quoteIdentifier($k) . ' ') : "$k ");
				$rightq .= 'Now() ';
				continue;
			}

			$current_user = CUser::getCurrentUser();
			if (!empty($current_user->id))
			{
				if ($k == 'created_by')
				{
					if ($leftq)
					{
						$leftq .= ', ';
						$rightq .= ', ';
					}

					$leftq .= ($quoteIdentifiers ? ($DB->quoteIdentifier($k) . ' ') : "$k ");
					$rightq .= $current_user->id . ' ';
					continue;
				}
			}
			// ************

			// Ignore variables which aren't set to a value
			if ((!isset($this->$k) || ($v == 1 && $this->$k === '')) && $ignore_null)
			{
				continue;
			}

			if (!isset($this->$k))
			{
				continue;
			}

			// ***
			//CHANGED
			//ToddW
			//only ignore the timestamps called 'timestamp_updated'
			//insert Now() to the 'timestamp_created' fields

			// don't insert data into mysql timestamps
			// use query() if you really want to do this!!!!
			if ($k == 'timestamp_updated')
			{
				continue;
			}

			if ($leftq)
			{
				$leftq .= ', ';
				$rightq .= ', ';
			}

			$leftq .= ($quoteIdentifiers ? ($DB->quoteIdentifier($k) . ' ') : "$k ");

			if (is_object($this->$k) && is_a($this->$k, 'DB_DataObject_Cast'))
			{
				$value = $this->$k->toString($v, $dbtype);
				if (PEAR::isError($value))
				{
					$this->raiseError($value->getMessage(), DB_DATAOBJECT_ERROR_INVALIDARGS);

					return false;
				}
				$rightq .= $value;
				continue;
			}

			if ((strtolower($this->$k) === 'null') && !($v & DB_DATAOBJECT_NOTNULL))
			{
				$rightq .= " NULL ";
				continue;
			}
			// DATE is empty... on a col. that can be null..
			// note: this may be useful for time as well..
			if (!$this->$k && (($v & DB_DATAOBJECT_DATE) || ($v & DB_DATAOBJECT_TIME)) && !($v & DB_DATAOBJECT_NOTNULL))
			{

				$rightq .= " NULL ";
				continue;
			}

			if ($v & DB_DATAOBJECT_STR)
			{
				$rightq .= $DB->quoteSmart((string)(($v & DB_DATAOBJECT_BOOL) ? (int)(bool)$this->$k : $this->$k)) . " ";
				continue;
			}
			if (is_numeric($this->$k))
			{
				$rightq .= " {$this->$k} ";
				continue;
			}
			// at present, we only cast to integers
			// - V2 may store additional data about float/int
			$rightq .= ' ' . intval($this->$k) . ' ';
		}

		// not sure why we let empty insert here.. - I guess to generate a blank row..

		if ($leftq || $useNative)
		{
			$table = ($quoteIdentifiers ? $DB->quoteIdentifier($this->tableName()) : $this->tableName());

			$r = $this->_query("INSERT INTO $table ($leftq) VALUES ($rightq) ", $ignoreAffectedRows);

			if (PEAR::isError($r))
			{
				$this->raiseError($r);
				if (!defined('DD_SERVER_NAME') || DD_SERVER_NAME != 'LIVE')
				{
					require_once("includes/CLog.inc");
					CLog::RecordNew(CLog::ERROR, "INSERT ERROR: " . $this->_lastError->userinfo, "", "", true);
				}

				return false;
			}

			if ($r < 1)
			{
				return 0;
			}

			// now do we have an integer key!

			if ($key && $useNative)
			{
				switch ($dbtype)
				{
					case 'mysql':
					case 'mysqli':
						$method = "{$dbtype}_insert_id";
						$this->$key = $method($_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5]->connection);
						break;

					case 'mssql':
						// note this is not really thread safe - you should wrapp it with
						// transactions = eg.
						// $db->query('BEGIN');
						// $db->insert();
						// $db->query('COMMIT');

						$mssql_key = $DB->getOne("SELECT @@IDENTITY");
						if (PEAR::isError($mssql_key))
						{
							$this->raiseError($r);

							return false;
						}
						$this->$key = $mssql_key;
						break;

					case 'pgsql':
						if (!$seq)
						{
							$seq = $DB->getSequenceName($this->tableName());
						}
						$pgsql_key = $DB->getOne("SELECT last_value FROM " . $seq);
						if (PEAR::isError($pgsql_key))
						{
							$this->raiseError($r);

							return false;
						}
						$this->$key = $pgsql_key;
						break;
				}
			}

			if (isset($_DB_DATAOBJECT['CACHE'][strtolower(get_class($this))]))
			{
				$this->_clear_cache();
			}
			if ($key)
			{
				return $this->$key;
			}

			return true;
		}
		$this->raiseError("insert: No Data specified for query", DB_DATAOBJECT_ERROR_NODATA);

		return false;
	}

	/**
	 * Overridden to fill in the updated_by field
	 * @throws exception
	 */
	public function update($dataObject = false, $allowTimestampUpdate = false)
	{
		$this->validate();

		$current_user = CUser::getCurrentUser();
		//may want to revisit this if we cache the original object's data for conditional updating
		if (array_key_exists('updated_by', $this->table()) && !empty($current_user->id))
		{
			$this->updated_by = $current_user->id;
		}

		global $_DB_DATAOBJECT;
		// connect will load the config!
		$this->_connect();

		$original_query = $this->_query;

		$items = $this->table();

		// only apply update against sequence key if it is set?????

		$seq = $this->sequenceKey();
		if ($seq[0] !== false)
		{
			$keys = array($seq[0]);
			if (!isset($this->{$keys[0]}) && $dataObject !== true)
			{
				$this->raiseError("update: trying to perform an update without
						the key set, and argument to update is not
						DB_DATAOBJECT_WHEREADD_ONLY
					" . print_r(array(
						'seq' => $seq,
						'keys' => $keys
					), true), DB_DATAOBJECT_ERROR_INVALIDARGS);

				return false;
			}
		}
		else
		{
			$keys = $this->keys();
		}

		if (!$items)
		{
			$this->raiseError("update:No table definition for {$this->tableName()}", DB_DATAOBJECT_ERROR_INVALIDCONFIG);

			return false;
		}

		$settings = '';
		$this->_connect();

		$DB = $_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5];
		$dbtype = $DB->dsn["phptype"];
		$quoteIdentifiers = !empty($_DB_DATAOBJECT['CONFIG']['quote_identifiers']);
		$options = $_DB_DATAOBJECT['CONFIG'];

		$ignore_null = !isset($options['disable_null_strings']) || !is_string($options['disable_null_strings']) || strtolower($options['disable_null_strings']) !== 'full';

		foreach ($items as $k => $v)
		{

			if ((!isset($this->$k) || ($v == 1 && $this->$k === '')) && $ignore_null)
			{
				continue;
			}

			// ignore stuff thats

			// don't write things that haven't changed..
			if (($dataObject !== false) && isset($dataObject->$k) && ($dataObject->$k === $this->$k))
			{
				continue;
			}

			// - don't write keys to left.!!!
			if (in_array($k, $keys))
			{
				continue;
			}

			// don't insert data into mysql timestamps
			// use query() if you really want to do this!!!!

			// CES - need to update time_created when order object is activated
			if ($v & DB_DATAOBJECT_MYSQLTIMESTAMP)
			{
				if (!$allowTimestampUpdate)
				{
					continue;
				}
				else
				{
					if ($k == 'timestamp_created')
					{
						if ($settings)
						{
							$settings .= ', ';
						}
						$settings .= 'timestamp_created = Now() ';
						continue;
					}
				}
			}

			//skip created_by in an update call
			if ($k == 'created_by')
			{
				continue;
			}

			if ($settings)
			{
				$settings .= ', ';
			}

			$kSql = ($quoteIdentifiers ? $DB->quoteIdentifier($k) : $k);

			if (is_object($this->$k) && is_a($this->$k, 'DB_DataObject_Cast'))
			{
				$value = $this->$k->toString($v, $DB);
				if (PEAR::isError($value))
				{
					$this->raiseError($value->getMessage(), DB_DATAOBJECT_ERROR_INVALIDARGS);

					return false;
				}
				$settings .= "$kSql = $value ";
				continue;
			}

			// special values ... at least null is handled...
			if (!($v & DB_DATAOBJECT_NOTNULL) && DB_DataObject::_is_null($this, $k))
			{
				$settings .= "$kSql = NULL ";
				continue;
			}
			// DATE is empty... on a col. that can be null..
			// note: this may be usefull for time as well..
			if (!$this->$k && (($v & DB_DATAOBJECT_DATE) || ($v & DB_DATAOBJECT_TIME)) && !($v & DB_DATAOBJECT_NOTNULL))
			{

				$settings .= "$kSql = NULL ";
				continue;
			}

			if ($v & DB_DATAOBJECT_STR)
			{
				$settings .= "$kSql = " . $this->_quote((string)(($v & DB_DATAOBJECT_BOOL) ? // this is thanks to the braindead idea of postgres to
						// use t/f for boolean.
						(($this->$k === 'f') ? 0 : (int)(bool)$this->$k) : $this->$k)) . ' ';
				continue;
			}
			if (is_numeric($this->$k))
			{
				$settings .= "$kSql = {$this->$k} ";
				continue;
			}
			// at present, we only cast to integers
			// - V2 may store additional data about float/int
			$settings .= "$kSql = " . intval($this->$k) . ' ';
		}

		if (!empty($_DB_DATAOBJECT['CONFIG']['debug']))
		{
			$this->debug("got keys as " . serialize($keys), 3);
		}
		if ($dataObject !== true)
		{
			$this->_build_condition($items, $keys);
		}
		else
		{
			// prevent wiping out of data!
			if (empty($this->_query['condition']))
			{
				$this->raiseError("update: global table update not available
						do \$do->whereAdd('1=1'); if you really want to do that.
					", DB_DATAOBJECT_ERROR_INVALIDARGS);

				return false;
			}
		}

		// echo " $settings, $this->condition ";
		if ($settings && isset($this->_query) && $this->_query['condition'])
		{

			$table = ($quoteIdentifiers ? $DB->quoteIdentifier($this->tableName()) : $this->tableName());

			$r = $this->_query("UPDATE $table SET $settings {$this->_query['condition']} ");

			// restore original query conditions.
			$this->_query = $original_query;

			if (PEAR::isError($r))
			{
				$this->raiseError($r);

				if (!defined('DD_SERVER_NAME') || DD_SERVER_NAME != 'LIVE')
				{
					require_once("includes/CLog.inc");
					CLog::RecordNew(CLog::ERROR, "UPDATE ERROR: " . $this->_lastError->userinfo, "", "", true);
				}

				return false;
			}
			if ($r < 1)
			{
				return 0;
			}

			$this->_clear_cache();

			return $r;
		}
		// restore original query conditions.
		$this->_query = $original_query;

		// if you manually specified a dataobject, and there where no changes - then it's ok..
		if ($dataObject !== false)
		{
			return true;
		}

		$this->raiseError("update: No Data specified for query $settings , {$this->_query['condition']}", DB_DATAOBJECT_ERROR_NODATA);

		return false;
	}

	/**
	 * sends query to database - this is the private one that must work
	 *   - internal functions use this rather than $this->query()
	 *
	 * @param string $string
	 *
	 * @access private
	 * @return mixed none or PEAR_Error
	 */
	function _query($string, $ignoreAffectedRows = false)
	{
		$this->_query_string = $string;

		return parent::_query($string, $ignoreAffectedRows);
	}

	/**
	 * Loads a text resource from the (rsc/dao.ini) template folder with text labels for each field in this object.
	 *
	 * input: $field is the name of the db field, or an array of fields
	 * output: a string label for $field, or an array of all the labels in this obejct if no $field is passed in
	 */
	public function getFieldLabel($field = false, $table = false)
	{

		if ((!$field) || !strlen(trim($field)))
		{
			return null;
		}

		if (self::$_fieldDescriptions === null)
		{
			self::$_fieldDescriptions = parse_ini_file('rsc/dao.ini', true);
		}

		if (!$table)
		{

			//try to get the table name from the full path (table.field) notation
			$fullPath = explode('.', $field);
			if (count($fullPath) > 1)
			{
				$table = trim($fullPath[0]);
				$field = trim($fullPath[1]);
			}
			else
			{ //else use $this's table
				$table = $this->__table;
			}
		}

		if ($field && !is_array($field))
		{
			if (array_key_exists($table, self::$_fieldDescriptions) && array_key_exists($field, self::$_fieldDescriptions[$table]))
			{
				return self::$_fieldDescriptions[$table][$field];
			}
			else
			{
				return $field;
			}
		}

		else if (array_key_exists($table, self::$_fieldDescriptions))
		{
			return self::$_fieldDescriptions[$table];
		}

		return null;
	}

	/**
	 * Returns an array of field labels given a list of fully qualified field names:
	 * eg. $fieldList = 'tbl_dd_user.id, tbl_dd_user.primary_email, tbl_dd_user.firstname, tbl_dd_user.lastname, tbl_dd_user.telephone_1'
	 * @throws exception
	 */
	public function getFieldLabels($fieldList)
	{

		$labels = array();

		foreach (explode(',', $fieldList) as $field)
		{
			$alias = explode(' ', trim($field));
			$fullPath = explode('.', $alias[0]);

			if (count($alias) == 3) //e.g. user.id as "id"
			{
				$key = trim(trim($alias[2], "'"), '"');
			} //use the alias as the key
			else if (count($fullPath) > 1)
			{
				$key = trim($fullPath[1]);
			} //use the column name as the key without the table prefix
			else
			{
				$key = trim($field);
			} //use the $field name as the key

			if ($key == '*')
			{
				if ((count($fullPath) == 1) || ((count($fullPath) > 1) && ($fullPath[0] == $this->__table)))
				{
					foreach ($this->table() as $key => $type)
					{
						if ($key != 'is_deleted')
						{
							$labels[$key] = $this->getFieldLabel($key);
						}
					}
				}
				else
				{
					throw new exception ('TODO: all labels from joined table');
				}
			}
			else
			{
				$labels [$key] = $this->getFieldLabel(trim($alias[0]));
			}
		}

		return $labels;
	}

	/**
	 * Called by insert and update to validate the object before sending to the db.
	 *
	 *
	 * CES 11/14/14 : This hides a more fleshed out routine in the base class. See notes in DataObject.php
	 * Might be nice to merge this and create our own supoer-validator
	 *
	 * @throws exception
	 * @access public
	 */
	function validate()
	{
		//for inserts, check required field list
		if (empty($this->id))
		{
			foreach ($this->table() as $field => $type)
			{
				if (($field != 'id') && ($field != 'timestamp_created') && ($field != 'timestamp_updated') && ($field != 'created_by') && ($field != 'updated_by') && ($field != 'is_deleted') //always left out of CUser updates
					&& ($type & DB_DATAOBJECT_NOTNULL))
				{
					if ((!isset($this->$field)) || ($this->$field === null))
					{
						throw new exception($field . ':: ' . $this->$field . ' is required by ' . $this->__table);
					}
				}
			}
		}
	}

	function unsetProperty($propertyName)
	{
		if (property_exists($this, $propertyName))
		{
			unset($this->{$propertyName});
		}
	}

	function find_includeDeleted($n = false)
	{
		$this->unsetProperty('is_deleted');

		return parent::find($n);
	}

	/**
	 * For finding the rows within a particular letter group.
	 * @throws Exception
	 */
	function whereAddFirstCharLike($columnName, $matchValues, $logic = 'AND', $not = false)
	{
		$columnName = "LEFT($columnName, 1)";

		if (!$matchValues)
		{
			throw new Exception('$matchValues is undefined in DAO::whereAddIn(), probably trying to do a query on an empty set of alpha groups or on an empty set of records');
		}

		if (!is_array($matchValues))
		{
			$matchValues = explode(',', $matchValues);
		}

		//strip any single quotes
		$unquoted = array();
		foreach ($matchValues as $letter)
		{
			$unquoted[] = trim($letter, "'");
		}

		$myStr = '';
		if ($not)
		{
			foreach ($unquoted as $letter)
			{
				if (strlen($myStr) > 0)
				{
					$myStr .= ' AND ';
				}

				$myStr .= "($columnName NOT LIKE '$letter%') ";
			}

			$this->whereAdd(" (($myStr) OR $columnName IS NULL) ", $logic);
		}
		else
		{
			foreach ($unquoted as $letter)
			{
				if (strlen($myStr) > 0)
				{
					$myStr .= ' OR ';
				}

				$myStr .= "($columnName LIKE '$letter%') ";
			}
			$this->whereAdd("($myStr)", $logic);
		}
	}

	/*
	 * Override to use the DD factory
	 */
	/**
	 * @throws Exception
	 */
	function factory($table = '')
	{
		return DAO_CFactory::create($table);
	}

	/**
	 * support updating is_deleted if forceDelete is false and
	 * the object contains the 'is_deleted' field
	 *
	 */
	function delete($useWhere = false, $forceDelete = false)
	{
		if ((!$forceDelete) && array_key_exists('is_deleted', $this->table()))
		{
			// don't update without an id
			if (!$this->id)
			{
				return false;
			}

			$this->is_deleted = 1;

			$updatedByClause = "";
			if (array_key_exists('updated_by', $this->table()) && @CUser::getCurrentUser()->id)
			{
				$this->updated_by = CUser::getCurrentUser()->id;
				$updatedByClause = ", updated_by = " . $this->updated_by . " ";
			}

			$r = $this->query('UPDATE ' . $this->__table . ' SET is_deleted = 1 ' . $updatedByClause . ' WHERE id = ' . $this->id);
			if ($r)
			{
				return 1;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return parent::delete($useWhere);
		}
	}

	function fetch()
	{
		$res = parent::fetch();

		/**
		 *
		 * On fetch, loop over any tables added via self::joinAdd()
		 * Build data objects per table from temporary select statements
		 * labeled as join__tablename_column and remove them from the primary object
		 *
		 */
		if (!empty($this->_joinAdd))
		{
			foreach ($this->_joinAdd as $joinAs => $table)
			{
				$DAO_table = DAO_CFactory::create($table);

				$hasData = false;

				foreach ($DAO_table->table() as $column => $value)
				{
					if (property_exists($this, "join__" . $joinAs . "_" . $column))
					{
						$DAO_table->$column = $this->{"join__" . $joinAs . "_" . $column};

						$hasData = true;

						$this->unsetProperty("join__" . $joinAs . "_" . $column);
					}
				}

				if ($hasData)
				{
					$this->{"DAO_" . $joinAs} = $DAO_table;
				}
			}
		}

		return $res;
	}

	function joinAdd($obj = false, $joinType = 'INNER', $joinAs = false, $joinCol = false, $joinSubDAO = false)
	{
		/**
		 * Removes the is_deleted condition on an outer join
		 * @throws exception
		 */
		if ($joinType == 'LEFT')
		{
			if (array_key_exists('is_deleted', $this->table()) && array_key_exists('is_deleted', $obj->table()))
			{
				if ($this->is_deleted == $obj->is_deleted)
				{
					$obj->unsetProperty('is_deleted');
				}
				else
				{
					throw new exception("joined objects' is_deleted field does not match");
				}
			}
		}

		/**
		 * AddS all joined table columNs to the select statement as join__tablename_column
		 * which on self::fetch() will be placed into data objects attached to the main object
		 *
		 */
		if ($joinSubDAO)
		{
			// (no value resets the join)
			if ($obj === false)
			{
				$this->_joinAdd = array();
				$joinTableName = false;
			}
			else
			{
				$joinTableName = $obj->tableName();
			}

			if (!empty($joinTableName))
			{
				if (is_array($joinType))
				{
					$joinAs = isset($joinType['joinAs']) ? $joinType['joinAs'] : $joinAs;
				}

				if ($joinAs)
				{
					$joinTableName = $joinAs;
				}

				$this->_joinAdd[$joinTableName] = $obj->tableName();

				if (!empty($obj->_joinAdd))
				{
					$this->_joinAdd = array_merge($this->_joinAdd, $obj->_joinAdd);

					foreach ($obj->_joinAdd as $thisJoinTableName => $tableName)
					{
						foreach (DAO_CFactory::create($tableName)->table() as $column => $value)
						{
							parent::selectAdd($thisJoinTableName . '.' . $column . ' AS join__' . $thisJoinTableName . '_' . $column);
						}
					}
				}

				foreach ($obj->table() as $column => $value)
				{
					parent::selectAdd($joinTableName . '.' . $column . ' AS join__' . $joinTableName . '_' . $column);
				}
			}
		}

		return parent::joinAdd($obj, $joinType, $joinAs, $joinCol);
	}

	/**
	 *
	 * Supplementary function for self::joinAdd, automatically sets useWhereAsOn to true.
	 *
	 * useWhereAsOn can still be manually passed in as false just as you would for self::joinAdd
	 *
	 */
	function joinAddWhereAsOn($obj = false, $joinType = 'INNER', $joinAs = false, $joinCol = false, $joinSubDAO = true)
	{
		if (is_array($joinType))
		{
			$joinType['useWhereAsOn'] = (isset($joinType['useWhereAsOn']) ? $joinType['useWhereAsOn'] : true);
		}
		else
		{
			$joinType = array(
				'joinType' => $joinType,
				'useWhereAsOn' => true
			);
		}

		return self::joinAdd($obj, $joinType, $joinAs, $joinCol, $joinSubDAO);
	}

	/**
	 * Clones an object and removes unneeded properties. Also removes unneeded properties of sub-objects
	 */
	function cloneObj($clean = true, $cleanSubObj = true)
	{
		$cloneObj = clone $this;

		if ($clean && method_exists($cloneObj, 'unsetProperty'))
		{
			foreach (self::$unneeded as $key)
			{
				$cloneObj->unsetProperty($key);
			}

			if ($cleanSubObj)
			{
				foreach ($cloneObj as $property)
				{
					if (is_object($property) && method_exists($property, 'unsetProperty'))
					{
						foreach (self::$unneeded as $key)
						{
							$property->unsetProperty($key);
						}
					}
				}
			}
		}

		return $cloneObj;
	}

	function getFetchResult()
	{
		return $this->_fetchResult;
	}

	function getFetchAllResult()
	{
		$this->fetchAll();

		return $this->getFetchResult();
	}

}

?>