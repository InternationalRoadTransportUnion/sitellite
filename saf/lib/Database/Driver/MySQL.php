<?php
//
// +----------------------------------------------------------------------+
// | Sitellite Content Management System                                  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2010 Sitellite.org Community                           |
// +----------------------------------------------------------------------+
// | This software is released under the GNU GPL License.                 |
// | Please see the accompanying file docs/LICENSE for licensing details. |
// |                                                                      |
// | You should have received a copy of the GNU GPL License               |
// | along with this program; if not, visit www.sitellite.org.            |
// | The license text is also available at the following web site         |
// | address: <http://www.sitellite.org/index/license                     |
// +----------------------------------------------------------------------+
// | Authors: John Luxford <john.luxford@gmail.com>                       |
// +----------------------------------------------------------------------+
//
// MySQL_Driver is the Database driver for the MySQL database system.
//

/**
	 * MySQL_Driver is the Database driver for the MySQL database system.
	 * MySQL_Query provides all of the MySQL-specific functionality
	 * for Query objects.  It is accessed through the Database class API.
	 * 
	 * New in 1.2:
	 * - Updated to work with saf.Database 2.0
	 * 
	 * New in 1.4:
	 * - Fixed a bug that caused the error () and errno () methods to return
	 *   blank strings.
	 * 
	 * New in 1.6:
	 * - Removed the fetchXML() method, since it isn't necessary and simply inherits
	 *   from the base Query class.
	 * 
	 * <code>
	 * <?php
	 * 
	 * $q = new MySQL_Query ("select * from table");
	 * if ($q->execute ()) {
	 * 	while ($row = $q->fetch ()) {
	 * 		// do something with $row
	 * 	}
	 * 	$q->free ();
	 * } else {
	 * 	// query failed
	 * }
	 * 
	 * ? >
	 * </code>
	 * 
	 * @package	Database
	 * @author	John Luxford <john.luxford@gmail.com>
	 * @license	http://www.sitellite.org/index/license	GNU GPL License
	 * @version	1.6, 2002-07-09, $Id: MySQL.php,v 1.4 2007/09/01 17:02:17 lux Exp $
	 * @access	public
	 * 
	 */

class MySQL_Query extends Query {
	/**
	 * Contains the SQL query to be executed.
	 * 
	 * @access	public
	 * 
	 */
	var $sql = '';

	/**
	 * Contains the result identifier for the current execution.
	 * 
	 * @access	public
	 * 
	 */
	var $result = '';

	/**
	 * Currently unused.
	 * 
	 * @access	public
	 * 
	 */
	var $field = '';

	/**
	 * The resource ID for the database connection to be queried.
	 * 
	 * @access	public
	 * 
	 */
	var $connection = '';

	var $_fetchModeFunctions = array (
		DB_FETCHMODE_ASSOC		=> 'mysql_fetch_array',
		DB_FETCHMODE_OBJECT		=> 'mysql_fetch_object',
	);

	/**
	 * Constructor method.
	 * 
	 * $sql is the SQL query you wish to execute with this object.
	 * $conn is the database connection resource to be queried.
	 * 
	 * @access	public
	 * @param	string	$sql
	 * @param	resource	$conn
	 * 
	 */
	function MySQL_Query ($sql = '', &$conn, $cache = 0) {
		if (version_compare (phpversion (), '7', '>=')) {
			$this->_fetchModeFunctions = array (
				DB_FETCHMODE_ASSOC		=> 'mysqli_fetch_array',
				DB_FETCHMODE_OBJECT		=> 'mysqli_fetch_object',
			);
		} else {
			$this->_fetchModeFunctions = array (
				DB_FETCHMODE_ASSOC		=> 'mysql_fetch_array',
				DB_FETCHMODE_OBJECT		=> 'mysql_fetch_object',
			);
		}
		parent::Query ($sql, $conn, $cache);
	}

	/**
	 * Executes the current SQL query.
	 * 
	 * $values is an array of values to substitute.  The syntax for denoting
	 * bind_values in the SQL query is a double-questionmark (??).
	 * 
	 * Returns the new SQL query.  Note: does not modify the actual $sql property.
	 * 
	 * @access	public
	 * @param	array	$values
	 * @return	resource
	 * 
	 */
	function execute () {
		// call bind_values
		$this->bind_values (func_get_args ());

		if (parent::execute ()) {
			// if parent::execute () returns true, then we're using the cached copy,
			// so no need to query the database
			return true;
		}

		// query the database
		$this->result = @mysql_query ($this->tmp_sql, $this->connection);
		return $this->result;
	}

	/**
	 * Returns the name of the specified column in the table currently being queried.
	 * 
	 * $num is the column number.  Note: Some database systems begin with
	 * column 0, while others with 1.
	 * 
	 * @access	public
	 * @param	integer	$num
	 * @return	string
	 * 
	 */
	function field ($num = 0) {
		if ($this->result) {
			if (! is_array ($this->fields)) {
				return @mysql_field_name ($this->result, $num);
			} else {
				return $this->fields[$num];
			}
		} else {
			return 0;
		}
	}

	/**
	 * Returns the number of rows affected or found by the current query.
	 * 
	 * @access	public
	 * @return	integer
	 * 
	 */
	function rows () {
		if ($this->result) {
			if (empty ($this->rows)) {
				return @mysql_num_rows ($this->result);
			} else {
				return $this->rows;
			}
		} else {
			return 0;
		}
	}

	/**
	 * Returns the row ID generated by the database during the previous
	 * SQL insert query.
	 * 
	 * @access	public
	 * @return	integer
	 * 
	 */
	function lastid () {
		if ($this->result) {
			return @mysql_insert_id ($this->connection);
		} else {
			return 0;
		}
	}

	/**
	 * Returns the next row of data from the current query, always in the
	 * form of an object, with each column as its properties.
	 * 
	 * @access	public
	 * @return	object
	 * 
	 */
	function fetch ($offset = 0, $limit = 0) {
		if ($this->result) {
			if ($limit > 0) {
				$res = array ();
				$c = 0;
				while ($row = $this->fetch ()) {
					if ($c < $offset) {
						$c++;
						continue;
					} elseif ($c >= $offset + $limit) {
						break;
					}
					$res[] = $row;
					$c++;
				}
				return $res;
			}
			switch ($this->cache_action) {
				case 0:
					//print_r ($this);
					return @$this->_fetchModeFunctions[db_fetch_mode ()] ($this->result);
				case 1:
					//print_r ($this);
					return parent::fetch ();
				case 2:
					//print_r ($this);
					$this->_row = @$this->_fetchModeFunctions[db_fetch_mode ()] ($this->result);
					return parent::fetch ();
				case 3:
					//print_r ($this);
					$this->_row = @$this->_fetchModeFunctions[db_fetch_mode ()] ($this->result);
					return parent::fetch ();
			}
		} else {
			//print_r ($this);
			return 0;
		}
	}

	/**
	 * Tells the database system to let go of its data from the previous
	 * query.
	 * 
	 * @access	public
	 * 
	 */
	function free () {
		parent::free ();
		if ($this->result) {
			return @mysql_free_result ($this->result);
		}
	}

	/**
	 * Issues a mysql "begin" statement, beginning a transaction.
	 * 
	 * @access	public
	 * 
	 */
	function begin () {
		@mysql_query ("begin", $this->connection);
	}

	/**
	 * Issues a mysql "commit" statement, committing the current transaction.
	 * 
	 * @access	public
	 * 
	 */
	function commit () {
		@mysql_query ("commit", $this->connection);
	}

	/**
	 * Issues a mysql "rollback" statement, rolling back the current transaction.
	 * 
	 * @access	public
	 * 
	 */
	function rollback () {
		@mysql_query ("rollback", $this->connection);
	}

	/**
	 * Returns the database error number in case of error.
	 * 
	 * @access	public
	 * @return	integer
	 * 
	 */
	function errno () {
		return @mysql_errno ($this->connection);
	}

	/**
	 * Returns the database error message in case of error.
	 * 
	 * @access	public
	 * @return	string
	 * 
	 */
	function error () {
		return @mysql_error ($this->connection);
	}
}

/**
	 * MySQL_Driver provides all of the MySQL-specific functionality
	 * for Database objects.  It is accessed through the Database class API.
	 * 
	 * New in 1.2:
	 * - Added a close() method.
	 * 
	 * <code>
	 * <?php
	 * 
	 * $db = new MySQL_Driver ("name", "host", "user", "pass", 1);
	 * 
	 * if ($db->connection) {
	 * 	// connection worked
	 * } else {
	 * 	// connection failed
	 * }
	 * 
	 * ? >
	 * </code>
	 * 
	 * @author	John Luxford <john.luxford@gmail.com>
	 * @license	http://www.sitellite.org/index/license	GNU GPL License
	 * @version	1.2, 2002-07-16, $Id: MySQL.php,v 1.4 2007/09/01 17:02:17 lux Exp $
	 * @access	public
	 * 
	 */

class MySQL_Driver {
	/**
	 * Contains the database connection resource.
	 * 
	 * @access	public
	 * 
	 */
	var $connection;

	/**
	 * Contains the name of the database being used.
	 * 
	 * @access	public
	 * 
	 */
	var $name;

	/**
	 * Contains the name of the database host.
	 * 
	 * @access	public
	 * 
	 */
	var $host;

	/**
	 * Contains the username used to connect to the current database.
	 * 
	 * @access	public
	 * 
	 */
	var $user;

	/**
	 * Contains the password used to connect to the current database.
	 * 
	 * @access	public
	 * 
	 */
	var $pass;

	/**
	 * A 1 or 0 (true or false, and true by default), specifying whether
	 * to establish a persistent connection or not.
	 * 
	 * @access	public
	 * 
	 */
	var $persistent;

	/**
	 * Contains connection error information.
	 *
	 * @access	 public
	 *
	 */
	var $error = false;

	/**
	 * Constructor method.
	 * 
	 * @access	public
	 * @param	string	$name
	 * @param	string	$host
	 * @param	string	$user
	 * @param	string	$pass
	 * @param	boolean	$persistent
	 * 
	 */
	function MySQL_Driver ($name = "", $host = "", $user = "", $pass = "", $persistent = 1) {
		$this->name = $name;
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->persistent = $persistent;
		$this->connect ();
	}

	/**
	 * Establishes a connection with the database
	 * 
	 * @access	public
	 * 
	 */
	function connect () {
		if ($this->persistent) {
			$this->connection = @mysql_pconnect ($this->host, $this->user, $this->pass);
		} else {
			$this->connection = @mysql_connect ($this->host, $this->user, $this->pass);
		}
		if (! $this->connection) {
			$this->error = 'Database connection failed.';
			return false;
		}
		if (! @mysql_select_db ($this->name, $this->connection)) {
			$this->error = 'Database connection established, but permission denied.';
			return false;
		}
		return true;
	}

	/**
	 * Creates and returns a Query object.
	 * 
	 * $sql is the SQL query you wish to execute with this object.
	 * 
	 * @access	public
	 * @param	string	$sql
	 * @return	object
	 * 
	 */
	function query ($sql = "") {
		// inherits full functionality from Database class
	}

	/**
	 * Disconnects from the database.  This method is usually unnecessary
	 * since connections should be automatically terminated or should persist after
	 * the script finishes executing, depending on your settings.
	 * 
	 * @access	public
	 * @return	boolean
	 * 
	 */
	function close () {
		if (@mysql_close ($this->connection)) {
			$this->connection = false;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retrieves a list of tables in the database and places them in the
	 * $tables property, which is an array.  Returns the list of names on
	 * success and false on failure.
	 * 
	 * @access	public
	 * @return	array
	 * 
	 */
	function getTables (&$db) {
		$res = $db->fetch ('show tables');
		if (! $res) {
			return false;
		}
		if (is_object ($res)) {
			$res = array ($res);
		}
		$tables = array ();
		foreach ($res as $obj) {
			$obj_vars = array_values (get_object_vars ($obj));
			$tables[] = array_shift ($obj_vars);
		}
		return $tables;
	}

	// PEAR::DB emulation methods below

	function raiseError ($msg) {
		$this->error = $msg;
		return false;
	}

	function quote ($str) {
		switch (strtolower (gettype ($str))) {
			case 'null':
				return 'NULL';
			case 'integer':
			case 'double':
				return $str;
			case 'string':
			default:
				if (gettype ($str) != 'string') {
					$str = (string) $str;
				}
				if (function_exists ('mysql_real_escape_string')) {
					return "'" . mysql_real_escape_string ($str, $this->connection) . "'";
				} else {
					return "'" . mysql_escape_string ($str) . "'";
			}
		}
	}

	function createSequence ($seq_name) {
		$seqname = db_get_sequence_name ($seq_name);
		$res = db_execute ("CREATE TABLE ${seqname} ".
							'(id INTEGER UNSIGNED AUTO_INCREMENT NOT NULL,'.
							' PRIMARY KEY(id))');
		if (! $res) {
			return false;
		}
		// insert yields value 1, nextId call will generate ID 2
		db_execute ("INSERT INTO ${seqname} VALUES(0)");
		return 1;
	}

	function dropSequence ($seq_name) {
		$seqname = db_get_sequence_name ($seq_name);
		return db_execute ("DROP TABLE ${seqname}");
	}

	function nextId ($seq_name) {
		$seqname = db_get_sequence_name ($seq_name);
		do {
			$repeat = 0;
//			$this->pushErrorHandling(PEAR_ERROR_RETURN);
			$result = db_execute ("UPDATE ${seqname} ".
								   'SET id=LAST_INSERT_ID(id+1)');
//			$this->popErrorHandling();
			if ($result) {
				/** COMMON CASE **/
				$id = mysql_insert_id ($this->connection);
				if ($id != 0) {

					return $id;
				}
				/** EMPTY SEQ TABLE **/
				// Sequence table must be empty for some reason, so fill it and return 1
				// Obtain a user-level lock
				$result = db_single ("SELECT GET_LOCK('${seqname}_lock',10)");
				if (! $result) {
					return false;
				}
//				if (DB::isError($result)) {
//					return $this->raiseError($result);
//				}
				if ($result == 0) {
					// Failed to get the lock, bail with a DB_ERROR_NOT_LOCKED error
//					return $this->mysqlRaiseError(DB_ERROR_NOT_LOCKED);
					$this->error = 'Failed to obtain a db lock';
					return false;
				}

				// add the default value
				$result = db_execute ("REPLACE INTO ${seqname} VALUES (0)");
				if (! $result) {
					return false;
				}
//				if (DB::isError($result)) {
//					return $this->raiseError($result);
//				}

				// Release the lock
				$result = db_single ("SELECT RELEASE_LOCK('${seqname}_lock')");
				if (! $result) {
					return false;
				}
//				if (DB::isError($result)) {
//					return $this->raiseError($result);
//				}
				// We know what the result will be, so no need to try again
				return 1;

			/** ONDEMAND TABLE CREATION **/
			} elseif ($ondemand && DB::isError($result) &&
				$result->getCode() == DB_ERROR_NOSUCHTABLE)
			{
				$result = $this->createSequence($seq_name);
				// Since createSequence initializes the ID to be 1,
				// we do not need to retrieve the ID again (or we will get 2)
				if (! $result) {
					return false;
//				if (DB::isError($result)) {
//					return $this->raiseError($result);
				} else {
					// First ID of a newly created sequence is 1
					return 1;
				}

			/** BACKWARDS COMPAT **/
/*			} elseif (DB::isError($result) &&
					  $result->getCode() == DB_ERROR_ALREADY_EXISTS)
			{
				// see _BCsequence() comment
				$result = $this->_BCsequence($seqname);
				if (DB::isError($result)) {
					return $this->raiseError($result);
				}
				$repeat = 1;
*/			}
		} while ($repeat);

//		return $this->raiseError($result);
		return false;
	}

}

/**
	 * MySQL_DatabaseTable provides all of the MySQL-specific functionality
	 * for the saf.Database.Table package.
	 * 
	 * <code>
	 * <?php
	 * 
	 * $dt = new MySQL_DatabaseTable ($db, 'tablename', 'pkeycolumn');
	 * 
	 * if ($res = $dt->fetchAll ()) {
	 * 	// do something with $res
	 * } else {
	 * 	echo $dt->error;
	 * }
	 * 
	 * ? >
	 * </code>
	 * 
	 * @author	John Luxford <john.luxford@gmail.com>
	 * @license	http://www.sitellite.org/index/license	GNU GPL License
	 * @version	1.0, 2002-08-07, $Id: MySQL.php,v 1.4 2007/09/01 17:02:17 lux Exp $
	 * @access	public
	 * 
	 */

class MySQL_DatabaseTable extends DatabaseTable {
	/**
	 * Contains a key/value list of database types (regular
	 * expressions are used here to save repeating ourselves) and their
	 * corresponding MailForm widget types.
	 * 
	 * @access	public
	 * 
	 */
	var $typemap = array (
		'(var)?char\(([0-9]+)\)'	=> 'text',
		'text'						=> 'textarea',
		'mediumtext'				=> 'textarea',
		'longtext'					=> 'textarea',
		'date'						=> 'date',
		'time'						=> 'time',
		'datetime'					=> 'datetime',
		'timestamp\([0-9]+\)'			=> 'datetime',
		'enum\((.+)\)'				=> 'select',
		'set\((.+)\)'				=> 'multiple',
		'int\(([0-9]+)\)'			=> 'text',
		'blob'						=> 'textarea',
		'.*'							=> 'text',
	);

	/**
	 * Gets all the info it can from the database about this table and
	 * its columns, stores it in the $info property, and parses it into an
	 * associative array ($columns) of MailForm widgets.
	 * 
	 * @access	public
	 * @return	boolean
	 * 
	 */
	function getInfo () {
		if (! $this->info) {
			$res = $this->db->fetch ('show columns from ' . $this->name);
			if (! $res) {
				$this->error = $this->db->error;
				return false;
			}
			if (is_object ($res)) {
				$res = array ($res);
			}
			$this->info = $res;
		}

		// typemap

		foreach ($this->info as $obj) {
			// parse each column into an object
			$col = new StdClass;

			$col->name = $obj->Field;

			foreach ($this->typemap as $regex => $type) {
				if (preg_match ('/^' . $regex . '$/', $obj->Type, $regs)) {
					$col->type = $type;

					if ($type == 'select' || $type == 'multiple') {
						// parse $regs[1] for enum values
						$val = $regs[1];
						$val = preg_replace ('/^\'/', '', $val);
						$val = preg_replace ('/\'$/', '', $val);
						$vals = preg_split ('/\',\'/', $val);
						$col->value = array ();
						foreach ($vals as $val) {
							$col->value[$val] = $val;
						}
					} elseif ($type == 'text') {
						// parse $regs[1] for maxlength of text widget
						if (strstr ($regs[0], 'char')) {
							$col->length = $regs[2];
						} elseif (strstr ($regs[0], 'int')) {
							$col->numeric = true;
						}
					} else {
						if (strstr ($regs[0], 'longtext')) {
							$col->length = 4294967295;
						} elseif (strstr ($regs[0], 'mediumtext')) {
							$col->length = 16777215;
						} elseif (strstr ($regs[0], 'tinytext')) {
							$col->length = 255;
						} elseif (strstr ($regs[0], 'text')) {
							$col->length = 65535;
						}
					}

					break;
				}
			}

			if (! is_array ($this->db->tables)) {
				$this->db->getTables ();
			}

			/*if (preg_match ('/^(' . join ('|', $this->db->tables) . ')_id$/i', $col->name, $regs)) {
				$col->type = 'ref';
				$col->table = $regs[1];
				if ($col->table == $col->name) {
					$col->self_ref = true;
				}
			}*/

			if ($obj->Extra == 'auto_increment') {
				$col->type = 'hidden';
				$col->autonum = true;
			}

			if ($obj->Null == 'YES') {
				$col->nullable = true;
			}

			if (! empty ($obj->Default)) {
				$col->default_value = $obj->Default;
			}

			$this->columns[$col->name] = $this->_makeWidget ($col);
		}
		return true;
	}

	/**
	 * Gets the name of the primary key field for this table using the
	 * results of getInfo() (see below).  Used by the constructor method to
	 * set the $pkey property if a primary key column was not specified.
	 * Returns false on failure.
	 * 
	 * @access	public
	 * @return	string
	 * 
	 */
	function getPkey () {
		if ($this->pkey) {
			return $this->pkey;
		}

		if (! $this->info) {
			if (! $this->getInfo ()) {
				return false;
			}
		}

		// we got $this->info

		foreach ($this->info as $obj) {
			if ($obj->Key == 'PRI') {
				return $obj->Field;
			}
		}

		return false;
	}

	/**
	 * Uses the global $tables array (Sitellite CMS specific) to get
	 * the primary key, referencing column, display column, and a true or false
	 * self-reference value, when given a Ref column name and its corresponding
	 * table.
	 * 
	 * @access	public
	 * @param	string	$name
	 * @param	string	$table
	 * @return	boolean
	 * 
	 */
	function getRefInfo ($name, $table) {
		// returns array of pkey, refcol, displaycol, and self_ref

		global $tables;

		$ret = array ();
		$ret[] = $tables[$table]->primary_key;
		$ret[] = $name;

		$dcols = array ();
		if ($tables[$table]->columns[$tables[$table]->primary_key]->type != 'hidden') {
			$dcols[] = $tables[$table]->primary_key;
		}
		foreach ($tables[$table]->columns as $c) {
			if ($c->name != $tables[$table]->primary_key) {
				if (! preg_match ('/^(hidden|file|password)$/', $c->type)) {
					$dcols[] = $c->name;
					if (count ($dcols) >= 2) {
						break;
					}
				}
			}
		}
		if (count ($dcols) == 0) {
			$ret[] = $tables[$table]->primary_key;
		} else {
			// note: this line uses a MySQL-specific function
			$ret[] = 'CONCAT(' . join (', ", ", ', $dcols) . ')';
		}


		if ($this->name == $table) {
			$ret[] = true;
		} else {
			$ret[] = false;
		}

		return $ret;
	}
}



?>