<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ==================================================================================
 * 
 * seezoo Customized Logging Class
 * 
 * Insert log to database some case
 * 
 * @package seezoo core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @copyright neo.inc
 * 
 * ==================================================================================
 */

class SZ_Log extends CI_Log
{
	// logging table
	private $_log_table = 'sz_system_logs';
		
	function __construct()
	{
		parent::CI_Log();
	}
	
	/**
	 * judge logging
	 * @param $type
	 * @access private
	 * @return bool
	 */
	function _ignore_case($type, $code)
	{
		// Flint.js ignittions error is not logging...
		if ( isset($_SERVER['REQUEST_URI'])
				&& strpos($_SERVER['REQUEST_URI'], 'flint') !== FALSE )
		{
			return TRUE;
		}
		
		// judge system logging_level
		$level = (defined('SZ_LOGGING_LEVEL')) ? SZ_LOGGING_LEVEL : 2; // DEFAULT debug
		
		switch ( $level )
		{
			case 0:
				return TRUE; // not logging
			case 1:
				return ( $type === 'mail' || $code == 404 ) ? FALSE : TRUE;
			default:
				return FALSE;
		}
		
	}
	
	/**
	 * private get instance
	 * @access private
	 */
	function _get_db_instance()
	{
		if ( ! class_exists('CI_DB') )
		{
			require_once(BASEPATH.'database/DB'.EXT);
		}
		$db =& DB();
		return $db;
	}
	
	/**
	 * Insert log to database
	 * @param $type
	 * @param $severity
	 * @param $value
	 */
	function write_db_log($type, $severity = '', $value, $status_code = FALSE)
	{
		if ( $this->_ignore_case($type, $status_code) === TRUE )
		{
			return;
		}
		$record = array(
			'log_type'		=> $type,
			'severity'		=> $severity,
			'log_text'		=> $value,
			'logged_date'	=> date('Y-m-d H:i:s', time())
		);
		
		$db = $this->_get_db_instance();
		$db->insert($this->_log_table, $record);
	}
	
	function write_mail_log($subject, $to, $body, $is_success = FALSE)
	{
		$log_body = array(
						($is_success) ? 'メール送信成功' : 'メール送信失敗',
						'送信先：' . $to,
						'件名：' . $subject,
						'本文：',
						$body
					);
		$this->write_db_log('mail', '', implode("\n", $log_body));
	}
}
