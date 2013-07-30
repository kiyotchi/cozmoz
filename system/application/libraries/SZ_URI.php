<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ====================================================
 * Seezoo Extended URI Class
 * 
 * Seezoo fixes CGI routing from PATH_INFO on PHP works CGI mode (ex.sakura intrnet...)
 * Also, fixes CodeIgniter started from CLI routing.
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ====================================================
 */

class SZ_URI extends CI_URI
{
	protected $_get_stack;
	protected $_output_query_strings = array();

	// override constructor.
	function SZ_URI()
	{		
		parent::CI_URI();

		// hack: We can use $_GET query at segment approche
		$protocol = config_item('uri_protocol');
		if ( ($protocol === 'AUTO' || $protocol === 'PATH_INFO')
			    && config_item('enable_query_strings') === FALSE )
		{
			$this->_get_stack = ( isset($_SERVER['QUERY_STRING']) ) ? $_SERVER['QUERY_STRING'] : '';
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}
	}
		
	// override parent method
	function _fetch_uri_string()
	{
		if (config_item('cli_mode') === TRUE)
		{
			$this->uri_string = (isset($_SERVER['argv'][1]))
									  ? $_SERVER['argv'][1]
									  : '';
			return;
		}
		parent::_fetch_uri_string();
	}
	
	/**
	 * Stack to page link suffix of query string
	 * 
	 * This method uses mobile carrier only.
	 * @param string $name
	 * @param string $value
	 * @param bool $encoded
	 * @return void
	 * @access public
	 */
	public function add_query_string_suffix($name, $value, $encoded = FALSE)
	{
		// If arguments has not encoded yet, do encode
		if ( $encoded === FALSE )
		{
			$name  = rawurlencode($name);
			$value = rawurlencode($value);
		}
		$this->_output_query_strings[] = $name . '=' . $value;
	}
	
	/**
	 * Build suffix of query string
	 * @access public
	 * @return mixed string or FALSE
	 */
	public function get_query_string_suffix()
	{
		return ( count($this->_output_query_strings) > 0 )
		        ? implode('&amp;', $this->_output_query_strings)
		        : FALSE;
	}

	/**
	 * _recovery_get_query
	 *  Recovery $_GET and $_SERVER['QUERY_STRING'] from stack
	 */
	function _recovery_get_query()
	{
		$IPT = load_class('Input');
		
		$_SERVER['QUERY_STRING'] = $this->_get_stack;
		// recovery from query string
		parse_str($this->_get_stack, $_GET);
		// unset CGI key __SZREQ__ is exists
		if ( isset($_GET['__SZREQ__']) )
		{
			unset($_GET['__SZREQ__']);
		} 
		
		// and sanytize
		$_GET = $IPT->_clean_input_data($_GET);
		
		// After processed, clean stack.
		unset($this->_get_stack);
	}
}
