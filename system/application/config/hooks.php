<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

// ==================================================================
// Speed up and using memory minimize
// If request uri contains "flint",
// we set hooks pre_system, pre_controller only.
// ==================================================================

if ( isset($_SERVER['REQUEST_URI'])
		&& preg_match('/^\/flint/u', $_SERVER['REQUEST_URI']) )
{
	$hook['pre_system'] = array(
		'class'	=> 'SZ_pre_process',
		'function'	=> 'startup_js',
		'filename'	=> 'sz_pre_process.php',
		'filepath'	=> 'hooks'
	);
	$hook['pre_controller'] = array(
		'class'	=> '',
		'function'	=> 'set_config_status',
		'filename'	=> 'sz_pre_controller.php',
		'filepath'	=> 'hooks'
	);
}
// ==================================================================
// Else, startup with normal process.
// ==================================================================
else
{
	$hook['pre_system'][] = array(
		'class'	=> 'SZ_pre_process',
		'function'	=> 'startup',
		'filename'	=> 'sz_pre_process.php',
		'filepath'	=> 'hooks'
	);
	$hook['pre_controller'] = array(
		'class'	=> '',
		'function'	=> 'set_config_status',
		'filename'	=> 'sz_pre_controller.php',
		'filepath'	=> 'hooks'
	);
	$hook['post_controller_constructor'][] = array(
		'class'	=> 'PreHook',
		'function'	=> 'process',
		'filename'	=> 'pre_hook.php',
		'filepath'	=> 'hooks'
	);
	$hook['cache_override'] = array(
		'class'	=> '',
		'function'	=> 'override_cache',
		'filename'	=> 'override_cache.php',
		'filepath'	=> 'hooks'
	);
}

/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */
