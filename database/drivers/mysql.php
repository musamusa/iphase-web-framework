<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
class mysql extends database {
	private $query_string = null;
	private $connection;
	function mysql($config = array()){
		if(!$this->connection){
			$this->connection = mysql_connect($config['host'], $config['user'], $config['pass']);
			if(!$this->connection){
				error::raiseError("Unable to connect to MySql Server ");
			}
			if(!mysql_select_db($config['db'], $this->connection)){
				error::raiseError("Unable to connect to MySql Database ".$$config['db']);
			}
		}
	}
	function setQuery($q){
		return $this->query_string = $q;
	}
	function query($q = ''){
		if($this->query_string== null && $q == ''){
			return false;
		}
		else if($q != ''){
			return mysql_query($q, $this->connection);
		}
		else if($this->query_string != null){
			return mysql_query($this->query_string, $this->connection);
		}
		
	}
	public function getAffectedRows(){
		return mysql_affected_rows($this->_connection);
	}
	public function loadObject($className = 'stdClass')	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($object = mysql_fetch_object($cur, $className)) {
			$ret = $object;
		}
		mysql_free_result($cur);
		return $ret;
	}
	public function loadObjectList($key='', $className = 'stdClass')
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_object($cur, $className)) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result($cur);
		return $array;
	}
	function loadResult(){
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row($cur)) {
			$ret = $row[0];
		}
		mysql_free_result($cur);
		return $ret;		
	}
	function numRows(){
		$r = mysql_query($this->query_string, $this->connection);
		if (!$r) {
            return null;
        }
		return mysql_num_rows( $r );
	}
	function getNumRows(){
		if (!($cur = $this->query())) {
			return null;
		}
		return mysql_num_rows( $cur );
	}
	public function loadResultArray($numinarray = 0)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row($cur)) {
			$array[] = $row[$numinarray];
		}
		mysql_free_result($cur);
		return $array;
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @return	array
	 */
	public function loadAssoc()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($array = mysql_fetch_assoc($cur)) {
			$ret = $array;
		}
		mysql_free_result($cur);
		return $ret;
	}

	/**
	 * Load a assoc list of database rows.
	 *
	 * @param	string	The field name of a primary key.
	 * @param	string	An optional column name. Instead of the whole row, only this column value will be in the return array.
	 * @return	array	If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadAssocList($key = null, $column = null)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_assoc($cur)) {
			$value = ($column) ? (isset($row[$column]) ? $row[$column] : $row) : $row;
			if ($key) {
				$array[$row[$key]] = $value;
			} else {
				$array[] = $value;
			}
		}
		mysql_free_result($cur);
		return $array;
	}

	function getInsertId(){
		return mysql_insert_id($this->connection);
	}
	function connected(){
		if($this->connection){
			return true;
		}
		else{
			return false;
		}
	}
	public function loadRow()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row($cur)) {
			$ret = $row;
		}
		mysql_free_result($cur);
		return $ret;
	}

	/**
	 * Load a list of database rows (numeric column indexing)
	 *
	 * @param	string	The field name of a primary key
	 * @return	array	If <var>key</var> is empty as sequential list of returned records.
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 */
	public function loadRowList($key=null)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row($cur)) {
			if ($key !== null) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result($cur);
		return $array;
	}

	/**
	 * Load the next row returned by the query.
	 *
	 * @return	mixed	The result of the query as an array, false if there are no more rows, or null on an error.
	 *
	 * @since	1.6.0
	 */
	public function loadNextRow()
	{
		static $cur;

		if (!($cur = $this->query())) {
			return $this->_errorNum ? null : false;
		}

		if ($row = mysql_fetch_row($cur)) {
			return $row;
		}

		mysql_free_result($cur);
		$cur = null;

		return false;
	}

	/**
	 * Load the next row returned by the query.
	 *
	 * @param	string	The name of the class to return (stdClass by default).
	 *
	 * @return	mixed	The result of the query as an object, false if there are no more rows, or null on an error.
	 *
	 * @since	1.6.0
	 */
	public function loadNextObject($className = 'stdClass')
	{
		static $cur;

		if (!($cur = $this->query())) {
			return $this->_errorNum ? null : false;
		}

		if ($row = mysql_fetch_object($cur, $className)) {
			return $row;
		}

		mysql_free_result($cur);
		$cur = null;

		return false;
	}

	/**
	 * Inserts a row into a table based on an objects properties
	 *
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
	public function insertObject($table, &$object, $keyName = NULL)
	{
		$fmtsql = 'INSERT INTO '.$this->nameQuote($table).' (%s) VALUES (%s) ';
		$fields = array();

		foreach (get_object_vars($object) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $this->nameQuote($k);
			$values[] = $this->isQuoted($k) ? $this->Quote($v) : (int) $v;
		}
		$this->setQuery(sprintf($fmtsql, implode(",", $fields) ,  implode(",", $values)));
		if (!$this->query()) {
			return false;
		}
		$id = $this->insertid();
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	 * Description
	 *
	 * @param [type] $updateNulls
	 */
	public function updateObject($table, &$object, $keyName, $updateNulls=false)
	{
		$fmtsql = 'UPDATE '.$this->nameQuote($table).' SET %s WHERE %s';
		$tmp = array();

		foreach (get_object_vars($object) as $k => $v) {
			if (is_array($v) or is_object($v) or $k[0] == '_') { // internal or NA field
				continue;
			}

			if ($k == $keyName) {
				// PK not to be updated
				$where = $keyName . '=' . $this->Quote($v);
				continue;
			}

			if ($v === null) {
				if ($updateNulls) {
					$val = 'NULL';
				} else {
					continue;
				}
			} else {
				$val = $this->isQuoted($k) ? $this->Quote($v) : (int) $v;
			}
			$tmp[] = $this->nameQuote($k) . '=' . $val;
		}

		// Nothing to update.
		if (empty($tmp)) {
			return true;
		}

		$this->setQuery(sprintf($fmtsql, implode(",", $tmp) , $where));
		return $this->query();
	}

	/**
	 * Description
	 */
	public function insertid()
	{
		return mysql_insert_id();
	}

	/**
	 * Description
	 */
	public function getVersion()
	{
		return mysql_get_server_info($this->_connection);
	}

	
	public function getCollation ()
	{
		if ($this->hasUTF()) {
			$this->setQuery('SHOW FULL COLUMNS FROM #__content');
			$array = $this->loadAssocList();
			return $array['4']['Collation'];
		} else {
			return "N/A (mySQL < 4.1.2)";
		}
	}

	
	public function getTableList()
	{
		$this->setQuery('SHOW TABLES');
		return $this->loadResultArray();
	}

	public function getTableCreate($tables)
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery('SHOW CREATE table ' . $this->getEscaped($tblval));
			$rows = $this->loadRowList();
			foreach ($rows as $row) {
				$result[$tblval] = $row[1];
			}
		}

		return $result;
	}

	
	public function getTableFields($tables, $typeonly = true)
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery('SHOW FIELDS FROM ' . $tblval);
			$fields = $this->loadObjectList();

			if ($typeonly) {
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type);
				}
			} else {
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = $field;
				}
			}
		}

		return $result;
	}
	public function escape($data){
		return mysql_real_escape_string($data);
	}
	function getErrorMSG(){
		return mysql_error($this->connection);
	}
	function cleanErrMsg(){
		return mysql_error($this->connection);
	}
	public function getEscaped($text, $extra = false)
	{
		$result = mysql_real_escape_string($text, $this->_connection);
		if ($extra) {
			$result = addcslashes($result, '%_');
		}
		return $result;
	}
	public function quote($text, $escaped = true)
	{
		return '\''.($escaped ? $this->getEscaped($text) : $text).'\'';
	}

}
