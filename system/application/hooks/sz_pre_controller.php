<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function set_config_status()
{
	// override config items to fix SSL port
	$config =& load_class('Config');
	
	// fix base url if yoursite enable mod_rewrite or not
	$index_page = 'index.php'; // default value

	if (isset($_SERVER['REQUEST_URI']))
	{
		$req = $_SERVER['REQUEST_URI'];
	}
	else if (isset($_SERVER['argv']) && isset($_SERVER['argv'][0]))
	{
		$req = $_SERVER['argv'][0];
	}
	else 
	{
		$req = '';
	}

	if (strpos($req, 'index.php') === FALSE
			&& file_exists(FCPATH . '.htaccess') )
	{
		// if accessed uri has not index.php, works mod_rewrite!
		$index_page = '';
	}
	
	$config->set_item('index_page', $index_page);
	
	// mobile access detection
	// mobile settings initialize
	if ( ! file_exists(FCPATH . 'files/ip_caches') )
	{
		@mkdir(FCPATH . 'files/ip_caches', 0777);
	}
	$mobile = Mobile::get_instance();
	$config->set_item('is_mobile', $mobile->is_mobile());
	$config->set_item('is_smartphone', $mobile->is_smartphone());

	// recovery get query
	$URI = load_class('URI');
	$URI->_recovery_get_query();
	
			// if opened port number eq 443, change SSL mode
	$ssl = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? TRUE : FALSE;
}
