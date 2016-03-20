<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2012 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
// No direct access
defined('BASE') or die;

class Version
{
	/** @public string Product */
	public $PRODUCT	= 'Estate Dashboard!';
	/** @public int Main Release Level */
	public $RELEASE	= '1.0';
	/** @public string Development Status */
	public $DEV_STATUS	= 'Stable';
	/** @public int Sub Release Level */
	public $DEV_LEVEL	= '1';
	/** @public int build Number */
	public $BUILD		= '2.0';
	/** @public string Codename */
	public $CODENAME	= 'phasePact';
	/** @public string Date */
	public $RELDATE	= '';
	/** @public string Time */
	public $RELTIME	= '';
	/** @public string Timezone */
	public $RELTZ		= 'GMT';
	/** @public string Copyright Text */
	public $COPYRIGHT	= 'Copyright (C) 2011 - 2012 I-Phase Limited. All rights reserved.';
	/** @public string URL */
	public $URL		= '<a href="http://www.iphtech.com">I</a> ';

	/**
	 * Method to get the long version information.
	 *
	 * @return	string	Long format version.
	 */
	public function getLongVersion()
	{
		return $this->PRODUCT .' '. $this->RELEASE .'.'. $this->DEV_LEVEL .' '
			. $this->DEV_STATUS
			.' [ '.$this->CODENAME .' ] '. $this->RELDATE .' '
			. $this->RELTIME .' '. $this->RELTZ;
	}

	/**
	 * Method to get the short version information.
	 *
	 * @return	string	Short version format.
	 */
	public function getShortVersion() {
		return $this->RELEASE .'.'. $this->DEV_LEVEL;
	}

	/**
	 * Method to get the help file version.
	 *
	 * @return	string	Version suffix for help files.
	 */
	public function getHelpVersion()
	{
		if ($this->RELEASE > '1.0') {
			return '.' . str_replace('.', '', $this->RELEASE);
		} else {
			return '';
		}
	}

	/**
	 * Compares two "A PHP standardized" version number against the current Joomla! version.
	 *
	 * @return	boolean
	 * @see		http://www.php.net/version_compare
	 */
	public function isCompatible ($minimum) {
		return (substr(Version,0,4) === substr($minimum,0,4));
	}

	/**
	 * Returns the user agent.
	 *
	 * @param	string	Name of the component.
	 * @param	bool	Mask as Mozilla/5.0 or not.
	 * @param	bool	Add version afterwards to component.
	 * @return	string	User Agent.
	 */
	public function getUserAgent($component = null, $mask = false, $add_version = true)
	{
		if ($component === null) {
			$component = 'Framework';
		}

		if ($add_version) {
			$component .= '/'.$this->RELEASE;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		if ($mask) {
			return 'Mozilla/5.0 '. $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
		}
		else {
			return $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
		}
	}
}
