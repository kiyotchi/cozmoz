<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ===============================================================================
 * Seezoo Extend Config class
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * @see http://d.hatena.ne.jp/Kenji_s/20110117/1295260998 thanks!
 * ===============================================================================
 */

class SZ_Config extends CI_Config
{
	// ショッピングモジュール用
	//protected $prefix_list = array('product', 'order', 'sales', 'customer', 'mailmagazine', 'collaboration', 'top', 'contact', 'basic', 'siteconfig', 'coupon');
	
	function __construct()
	{
		parent::CI_Config();
		
		// =================== Server environment detection ==================
		
		$CGI = FALSE;
		$CLI = FALSE;
		
		// Does your server has native PATH_INFO?
		$PATHINFO = ( isset($_SERVER['PATH_INFO']) )
		             ? TRUE
		             : FALSE;
		
		// request from path_info?
		if ( strpos(PHP_SAPI, 'cgi') !== FALSE )
		{
			// Case: Sakura Internet, etc...
			$CGI = TRUE;
			
			if (isset($_GET['__SZREQ__']))
			{
				$_SERVER['PATH_INFO'] = $_GET['__SZREQ__'];
				unset($_GET['__SZREQ__']);
				$this->set_item('uri_protocol', 'PATH_INFO');
			}
		}
		else if ( strpos(PHP_SAPI, 'cli') !== FALSE && isset($_SERVER['argv']) )
		{
			$CLI = TRUE;
			chdir(FCPATH);
		}
		
		$this->set_item('cgi_mode', $CGI);
		$this->set_item('cli_mode', $CLI);
		$this->set_item('has_path_info', $PATHINFO);
	}
	
	/**
	 * SSL Site URL
	 *
	 * @access	public
	 * @param	string	the URI string
	 * @return	string
	 */
	function ssl_site_url($uri = '')
	{
		if (is_array($uri))
		{
			$uri = implode('/', $uri);
		}
		
		$CI =& get_instance();
		$base = '';
		//if ( $CI->uri->segment(1) === 'dashboard')
		//{
		//	if ( in_array($CI->uri->segment(2), $this->prefix_list) )
		//	{
		//		$base = 'dashboard/';
		//	}
		//}

		if ($uri == '')
		{
			return $this->slash_item('ssl_base_url').$this->item('index_page').$base;
		}
		else
		{
			$suffix = ($this->item('url_suffix') == FALSE) ? '' : $this->item('url_suffix');
			return $this->slash_item('ssl_base_url').$this->slash_item('index_page').$base.trim($uri, '/').$suffix; 
		}
	}
	
// ==================== modified method for Disney couture =======================

	/**
	 * Site URL
	 *
	 * @access	public
	 * @param	string	the URI string
	 * @return	string
	 *
	function site_url($uri = '')
	{
		if (is_array($uri))
		{
			$uri = implode('/', $uri);
		}
		
		$CI =& get_instance();
		$base = '';
		if ( $CI->uri->segment(1) === 'dashboard')
		{
			if ( in_array($CI->uri->segment(2), $this->prefix_list) )
			{
				$base = 'dashboard/';
			}
		}

		if ($uri == '')
		{
			
			return $this->slash_item('base_url').$this->item('index_page') . $base;
		}
		else
		{
			$suffix = ($this->item('url_suffix') == FALSE) ? '' : $this->item('url_suffix');
			return $this->slash_item('base_url').$this->slash_item('index_page').$base.trim($uri, '/').$suffix;
		}
	}
	 */
}
