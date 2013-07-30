<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ====================================================
 * Seezoo Pre process hook Class
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ====================================================
 */

class SZ_pre_process
{
	protected $mobile;
	
	/**
	 * Main startup function
	 * @access public
	 */
	public function startup()
	{
		$this->_load_depend_scripts();
		$this->_pre_process();
		$this->_fix_ie();
		$this->_check_denied_ip();
		$this->_convert_input_encoding();
	}
	
	/**
	 * Startup function on Flint execute
	 * @access public
	 */
	public function startup_js()
	{
		$this->_load_depend_scripts(TRUE);
		
		//MobileConfig::set_cache_dir(FCPATH . 'files/ip_caches/');
		// set dete.timezone to avoid STRICT ERROR and speed up!
		$timezone = (defined('SEEZOO_TIMEZONE') && SEEZOO_TIMEZONE !== '')
		              ? SEEZOO_TIMEZONE
		              : ini_get('date.timezone');
		// set
		ini_set('date.timezone', $timezone);
	
		// added default internal encoding
		if (config_item('charset'))
		{
			mb_internal_encoding(config_item('charset'));
		}
	}
	
	
	// private functions --------------------------------------------------
	
	
	/**
	 * Main preprocess
	 * mobile access detection, define system debug level,
	 * and define database prefix.
	 * @access private
	 */
	protected function _pre_process()
	{	
		// Mobile detection and load emoji library 
		//MobileConfig::set_cache_dir(FCPATH . 'files/ip_caches/');
		//MobileConfig::set_detection_mode(FALSE);
		$this->mobile = Mobile::get_instance();

		// detect or get dete.timezone to avoid STRICT ERROR and speed up!
		$timezone = (defined('SEEZOO_TIMEZONE') && SEEZOO_TIMEZONE !== '')
		              ? SEEZOO_TIMEZONE
		              : ini_get('date.timezone');
		// and set
		ini_set('date.timezone', $timezone);
		
		// append login URI define
		if ( ! defined('SEEZOO_SYSTEM_LOGIN_URI') )
		{
			define('SEEZOO_SYSTEM_LOGIN_URI', 'login');
		}
		
		// global define database prefix from DB settings
		include(APPPATH . 'config/database.php');
		define('SZ_DB_PREFIX', $db['default']['dbprefix']);	
	
		// add default internal encoding
		if (config_item('charset'))
		{
			mb_internal_encoding(config_item('charset'));
		}

		SeezooOptions::init('common');
		// define logging, debugging level OGP settings
		if ( config_item('seezoo_installed') === TRUE )
		{
			$db     =& DB();
			$query  = $db->query('SELECT * FROM site_info LIMIT 1');
			$result = $query->row();
			SeezooOptions::set('site_info', $result);
			
			define('SZ_LOGGING_LEVEL', (int)$result->log_level);
			if ( isset($result->debug_level) )
			{
				define('SZ_DEBUG_LEVEL', (int)$result->debug_level);
			}
			else
			{
				define('SZ_DEBUG_LEVEL', 0);
			}
			
			$query  = $db->query('SELECT * FROM sz_ogp_data LIMIT 1');
			if ( $query && $query->row() )
			{
				$result = $query->row();
				SeezooOptions::set('ogp_data', $result);
			}
		}
		else
		{
			define('SZ_LOGGING_LEVEL', 0);
			define('SZ_DEBUG_LEVEL', 1);
		}
		
		// change error reporting level
		error_reporting(( SZ_DEBUG_LEVEL === 0 ) ? 0 : E_ALL);
	}
	
	/**
	 * IE fix constant define
	 * @access private
	 */
	protected function _fix_ie()
	{
		// advance user agent (need CSS fixer) define
		$ad_ua = FALSE;
		$png   = '.png';
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			if (preg_match('/msie 6.0/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$ad_ua = 'ie6'; // Internet Explorer 6
				$png   = '.gif';
			}
			else if (preg_match('/msie 7.0/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$ad_ua = 'ie7'; // Internet Explorer 7
			}
		}
		define('ADVANCE_UA', $ad_ua);
		define('PNG', $png);
	}
	
	/**
	 * Convert Input variables
	 * @access private
	 */
	protected function _convert_input_encoding()
	{
		// TODO: convert globals enough?
		if ($this->mobile->is_mobile()
		      && strpos(SEEZOO_CONVERT_MOBILE_CARRIERS, $this->mobile->carrier()) !== FALSE
		      && defined('SEEZOO_MOBILE_STRING_ENCODING') )
		{
			$_GET    = $this->_convert_globals($_GET,    config_item('charset'), SEEZOO_MOBILE_STRING_ENCODING);
			$_POST   = $this->_convert_globals($_POST,   config_item('charset'), SEEZOO_MOBILE_STRING_ENCODING);
			$_COOKIE = $this->_convert_globals($_COOKIE, config_item('charset'), SEEZOO_MOBILE_STRING_ENCODING);
		}
		else if ( defined('SEEZOO_SERVER_ENCODING')
		             && SEEZOO_SERVER_ENCODING !== ''
		             && strtolower(SEEZOO_SERVER_ENCODING) !== strtolower(config_item('charset')) )
		{
			$_GET    = $this->_convert_globals($_GET,    config_item('charset'), SEEZOO_SERVER_ENCODING);
			$_POST   = $this->_convert_globals($_POST,   config_item('charset'), SEEZOO_SERVER_ENCODING);
			$_COOKIE = $this->_convert_globals($_COOKIE, config_item('charset'), SEEZOO_SERVER_ENCODING);
		}
	}
	
	/**
	 * Check access remote IP is denied?
	 * @access private
	 */
	protected function _check_denied_ip()
	{
		$IPT =& load_class('Input');
		
		// access IP check
		if ( file_exists(APPPATH . 'config/denied_ips.txt' ) )
		{
			// load a deny ips to array
			$deny_list = file(APPPATH . 'config/denied_ips.txt');
			
			// get remote IP and hostname
			$ip   = $IPT->ip_address();
			$host = gethostbyaddr($ip);
			
			foreach ( $deny_list as $deny )
			{
				// allow *, and replace * to .* for regex
				$deny = '/' . str_replace('\*', '.*', preg_quote($deny)) . '/';
				// If your host or IP matched, Good-bye!
				if ( preg_match($deny, $ip) || preg_match($deny, $host) )
				{
					$LOG =& load_class('Log');
					$LOG->write_db_log('access_denied', '', "Access_denied. Accessed from:\nIP:{$ip}\nhost:{$host}");
					show_404();
				}
			}
		}
	}
	
	/**
	 * Load seezoo depends script files
	 * @access private
	 * @param bool $exec_flint
	 */
	protected function _load_depend_scripts($exec_flint = FALSE)
	{
		// Seezoo settings constants include
		require_once(APPPATH . 'config/seezoo.config.php');
		
		// Seezoo custom Class include
		require_once(APPPATH . 'libraries/seezoo/SeezooPluginManager.php');
		require_once(APPPATH . 'libraries/seezoo/SeezooUtility.php');
		require_once(APPPATH . 'libraries/seezoo/SeezooOptions.php');
		
		if ( $exec_flint === FALSE )
		{
			// Original Library Class include
			require_once(APPPATH . 'libraries/seezoo/Area.php');
			require_once(APPPATH . 'libraries/seezoo/Block.php');
			require_once(APPPATH . 'libraries/statics/StaticV.php');
		}
		
		require_once(APPPATH . 'libraries/mobile.php');
	}
	
	/**
	 * Variable converts
	 * @access private
	 * @param array $globals
	 * @param string $to
	 * @param string $from
	 */
	protected function _convert_globals($globals, $to, $from)
	{
		foreach ( $globals as $key => $value )
		{
			if ( is_array($value) )
			{
				$value = $this->_convert_globals($value, $to, $from);
			}
			else
			{
				if ( mb_check_encoding($value, $from) === TRUE )
				{
					$value = mb_convert_encoding($value, $to, $from);
				}
			}
			$globals[$key] = $value;
		}
		return $globals;
	}
}