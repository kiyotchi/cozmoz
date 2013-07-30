<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ==================================================================================
 * 
 * seezoo Customized Exceptions Class
 * 
 * Insert log when erro handling
 * 
 * @package seezoo core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @copyright neo.inc
 * 
 * ==================================================================================
 */

class SZ_Exceptions extends CI_Exceptions
{
	// error level mapping for template
	private $_level_map = array(
								'error_general'	=> 'error', // basic error
								'error_db'			=> 'database', // datebase error
								'error_php'		=> 'php_error', // PHP error
							);
	// logging table
	private $_log_table = 'sz_system_logs';
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::CI_Exceptions();
	}
	
	
	/**
	 * Override show_error method
	 * @see src/system/libraries/CI_Exceptions::show_error()
	 */
	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		set_status_header($status_code);
		
		$msg = ( ! is_array($message)) ? array($message) : $message;
		if ( $status_code == 404 && isset($_SERVER['REQUEST_URI']))
		{
			$msg[] = $_SERVER['REQUEST_URI'];
		}
		
		$LOG =& load_class('Log');
		$LOG->write_db_log(
						(isset($this->_level_map[$template])) ? $this->_level_map[$template] : 'error',
						'',
						implode("\n", $msg),
						$status_code
					);
		
		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();	
		}
		ob_start();
		if ( file_exists(SZ_EXT_PATH . 'errors/' . $template.EXT) )
		{
			include(SZ_EXT_PATH . 'errors/' . $template.EXT);
		}
		else 
		{
			include(APPPATH.'errors/'.$template.EXT);
		}
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
	
	/**
	 * Override show_php_error method
	 * @see src/system/libraries/CI_Exceptions::show_php_error()
	 */
	function show_php_error($severity, $message, $filepath, $line)
	{	
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
	
		$filepath = str_replace("\\", "/", $filepath);
		
		// insert error log
		$LOG =& load_class('Log');
		$LOG->write_db_log(
						'error',
						$severity,
						$message . " file :" . $filepath . " at line : " . $line
					);
		
		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/'))
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();	
		}
		ob_start();
		include(APPPATH.'errors/error_php'.EXT);
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
}