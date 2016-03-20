<?php
defined('BASE') or die;
class database{
	public $name = '';
	protected $_nameQuote = null;

	/**
	 * UTF-8 support
	 */
	protected $_utf = 0;
	

	/**
	 * The fields that are to be quote
	 *
	 * @var array
	 * @since	1.5
	 */
	protected $_quoted = null;

	/**
	 *  Legacy compatibility
	 *
	 * @var bool
	 * @since	1.5
	 */
	protected $_hasQuoted = null;

	function __construct(){
		$this->_quoted			= array();
		$this->_hasQuoted		= false;
	}
	
	public static function getInstance(){
		if(!class_exists('config')){
			return;
		}
		$config = Factory::getConfig();
		if($config->driver == 'none'){
			return;
		}
		$params = array();
		$params['host'] = $config->host;
		$params['user'] = $config->user;
		$params['pass'] = $config->password;
		$params['db'] = $config->db;
		static $instance;
		if(!$instance){
			//if($config->driver != '' ){	$driver = $config->driver;}	else{$driver = "mysql";	}
			$driver = array_key_exists("driver", $config)? $config->driver : "mysql";
			$driver = preg_replace('/[^A-Z0-9_\.-]/i', '', $driver);
			require_once(LIBRARIES.DS."iphase".DS."database".DS."drivers".DS.$driver.".php");
			$instance = new $driver($params);
		}
		return $instance;
	}
	public function addQuoted($quoted)
	{
		if (is_string($quoted)) {
			$this->_quoted[] = $quoted;
		} else {
			$this->_quoted = array_merge($this->_quoted, (array)$quoted);
		}
		$this->_hasQuoted = true;
	}
	public function splitSql($queries)
	{
		$start = 0;
		$open = false;
		$open_char = '';
		$end = strlen($queries);
		$query_split = array();

		for ($i = 0; $i < $end; $i++) {
			$current = substr($queries,$i,1);
			if (($current == '"' || $current == '\'')) {
				$n = 2;

				while(substr($queries,$i - $n + 1, 1) == '\\' && $n < $i) {
					$n ++;
				}

				if ($n%2==0) {
					if ($open) {
						if ($current == $open_char) {
							$open = false;
							$open_char = '';
						}
					} else {
						$open = true;
						$open_char = $current;
					}
				}
			}

			if (($current == ';' && !$open)|| $i == $end - 1) {
				$query_split[] = substr($queries, $start, ($i - $start + 1));
				$start = $i + 1;
			}
		}

		return $query_split;
	}



	/**
	 * Checks if field name needs to be quoted
	 *
	 * @param	string	The field name
	 * @return	bool
	 */
	public function isQuoted($fieldName)
	{
		if ($this->_hasQuoted) {
			return in_array($fieldName, $this->_quoted);
		} else {
			return true;
		}
	}


	public function nameQuote($s)
	{
		$q = $this->_nameQuote;

		if (strlen($q) == 1) {
			return $q.$s.$q;
		} else {
			return $q{0}.$s.$q{1};
		}
	}
}

