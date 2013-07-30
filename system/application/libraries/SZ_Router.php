<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 *Extended CodeIgniter builtin Router Class
 *
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * @additional works:
 *   load and ignite controller of "3" directory levels.
 *   this class is not return 404. finally returns default controller.
 *  =========================================================
 */

class SZ_Router extends CI_Router
{

	// CI regular routing directories;
	var $directory_reg         = array();
	var $is_packaged_directory = FALSE;

	var $directory_array       = array();
	var $directory_reg_array   = array();
	var $directory             = array();

	protected $_rel_fcpath     = '';


	function __construct()
	{
		$this->_get_rel_fcpath();

		parent::CI_Router();
	}

	/**
	 * Set the route mapping (Override)
	 *
	 * This function determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * Additional:
	 * Add route mapping for login controller to no more use "login" to security attack
	 *
	 * @override CI_Router::_set_routing
	 * @access	private
	 * @return	void
	 */
	function _set_routing()
	{
		// Are query strings enabled in the config file?
		// If so, we're done since segment based URIs are not used with query strings.
		if ($this->config->item('enable_query_strings') === TRUE AND isset($_GET[$this->config->item('controller_trigger')]))
		{
			$this->set_class(trim($this->uri->_filter_uri($_GET[$this->config->item('controller_trigger')])));

			if (isset($_GET[$this->config->item('function_trigger')]))
			{
				$this->set_method(trim($this->uri->_filter_uri($_GET[$this->config->item('function_trigger')])));
			}

			return;
		}

		// Load the routes.php file.
		@include(APPPATH.'config/routes'.EXT);
		$this->routes = ( ! isset($route) OR ! is_array($route)) ? array() : $route;
		unset($route);

		// modified : ad routes of seezoo login uri classname if defined.
		if ( defined('SEEZOO_SYSTEM_LOGIN_URI') )
		{
			$this->routes[SEEZOO_SYSTEM_LOGIN_URI . '(.*)'] = 'login$1';
		}

		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = ( ! isset($this->routes['default_controller']) OR $this->routes['default_controller'] == '') ? FALSE : strtolower($this->routes['default_controller']);

		// Fetch the complete URI string
		$this->uri->_fetch_uri_string();

		// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
		if ($this->uri->uri_string == '')
		{
			if ($this->default_controller === FALSE)
			{
				show_error("Unable to determine what should be displayed. A default route has not been specified in the routing file.");
			}

			if (strpos($this->default_controller, '/') !== FALSE)
			{
				$x = explode('/', $this->default_controller);

				$this->set_class(end($x));
				$this->set_method('index');
				$this->_set_request($x);
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
				$this->_set_request(array($this->default_controller, 'index'));
			}

			// re-index the routed segments array so it starts with 1 rather than 0
			$this->uri->_reindex_segments();

			log_message('debug', "No URI present. Default controller set.");
			return;
		}
		unset($this->routes['default_controller']);

		// Do we need to remove the URL suffix?
		$this->uri->_remove_url_suffix();

		// Compile the segments into an array
		$this->uri->_explode_segments();

		// Parse any custom routing that may exist
		$this->_parse_routes();

		// Re-index the segment array so that it starts with 1 rather than 0
		$this->uri->_reindex_segments();
	}

	function _custom_validate_request($segments, $ext_path)
	{
		$ext_path = $ext_path . 'controllers/';

		if ( file_exists($ext_path . $segments[0].EXT))
		{
			$this->set_directory_ext();
			return $segments;
		}

		if ( is_dir($ext_path . $segments[0]))
		{
			$this->set_directory_ext($segments[0]);
			//$tmp_dir = $segments[0];
			$segments = array_slice($segments, 1);

			if (count($segments) > 0)
			{
				if (is_dir($ext_path . $this->fetch_directory_reg() . $segments[0]))
				{
					// Set temp directory_name
					$tmp_dir = $this->fetch_directory_reg() . $segments[0];
					$deep_segments = array_slice($segments, 1);

					if (count($deep_segments) > 0)
					{
						if ( file_exists($ext_path . $tmp_dir . '/' . $deep_segments[0] . EXT))
						{
							$this->set_directory_ext($tmp_dir);
							return $deep_segments;
						}
						else
						{
							$this->reset_directory_ext();
							return FALSE;
						}
					}
					else
					{
						// Does the default controller exist in the sub-folder?
						if ( file_exists($ext_path.$tmp_dir.'/'.$this->default_controller.EXT))
						{
							$this->set_directory_ext($tmp_dir);
							$this->set_class($this->default_controller);
							$this->set_method('index');
							//$dir = substr($tmp_dir, 0, strrpos($tmp_dir, '/'));
							//$this->set_directory_ext($tmp_dir);
							return array();
						}
					}
				}
				else
				{
					if (file_exists($ext_path.$this->fetch_directory_reg().$segments[0].EXT))
					{
						return $segments;
					}
				}
			}
			else
			{
				if ( file_exists($ext_path . $this->fetch_directory_reg() . $this->default_controller.EXT))
				{
					$this->set_class($this->default_controller);
					$this->set_method('index');
					return array();
				}
			}
		}
		$this->reset_directory_ext();
		return FALSE;
	}

	function set_directory_ext($dir = FALSE)
	{
		// @note directory path is relative from system/application/controllers/
		//$this->directory = '../../../' . SZ_EXT_DIR . 'controllers';

		if ($dir !== FALSE)
		{
			$exp = explode('/', $dir);
			$dir = str_replace(array('/', '.'), '', end($exp));
			$this->directory[] = $dir;
			$this->directory_reg[] = $dir;
		}
	}

	function set_directory($dir)
	{
		// CodeIgniter 1.7.3 code is str_replace(array('/', '.'), '', $dir).'/'
		// but, when routing 3 levels derectory, that code is not work..
		// so that, we replace "." only
		$exp = explode('/', $dir);
		$dir = str_replace(array('/', '.'), '', end($exp));

		$this->directory[] = $dir;
		$this->directory_reg[] = $dir;
	}

	function reset_directory_ext()
	{
		$this->directory = array();
		$this->directory_reg = array();
		$this->is_packaged_directory = FALSE;
	}

	// always returns really routed directory
	function fetch_directory_reg()
	{
		return (count($this->directory_reg) === 0)
								? ''
								: (implode('/', $this->directory_reg) . '/');
	}

	function fetch_directory()
	{
		$prefix = '';
		if ($this->is_packaged_directory !== FALSE)
		{
			$prefix = $this->is_packaged_directory;//'../../../' . SZ_EXT_DIR . 'controllers/';
		}
		return (count($this->directory) === 0)
								? $prefix
								: $prefix . (implode('/', $this->directory) . '/');
	}

	/**
	 * _validate_request : override method
	 */
	function _validate_request($segments)
	{
		// First, package routing
		$customed_segments = $this->_custom_validate_request($segments, SZ_EXT_PATH);
		if ( is_array($customed_segments) )
		{
			$this->is_packaged_directory = $this->_rel_fcpath . SZ_EXT_DIR . 'controllers/';
			return $customed_segments;
		}

		// Second, plugin routing
		$plugin = SeezooPluginManager::get_instance();
		foreach ( $plugin->get_installed_plugin_names() as $plugin_path )
		{
			$plg_path = SZ_PLG_PATH . $plugin_path . '/';
			$customed_segments = $this->_custom_validate_request($segments, $plg_path);
			if ( is_array($customed_segments) )
			{
				SeezooPluginManager::set_current($plugin_path);
				$this->is_packaged_directory = $this->_rel_fcpath . SZ_PLG_DIR . $plugin_path . '/controllers/';
				return $customed_segments;
			}
		}

		// Does the requested controller exist in the root folder?
		if (file_exists(APPPATH.'controllers/'.$segments[0].EXT))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(APPPATH.'controllers/'.$segments[0]))
		{
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);

			if (count($segments) > 0)
			{
				// modified by Yoshiaki Sugimoto : search controller indexof level 3.
				if (is_dir(APPPATH . 'controllers/' . $this->fetch_directory() . $segments[0]))
				{
					// Set temp directory_name
					$tmp_dir = $this->fetch_directory() . $segments[0];
					$deep_segments = array_slice($segments, 1);

					if (count($deep_segments) > 0)
					{
						if ( file_exists(APPPATH . 'controllers/' . $tmp_dir . '/' . $deep_segments[0] . EXT))
						{
							$this->set_directory($tmp_dir);
							$segments = $deep_segments;
						}
						else
						{
							// Does the requested controller exist in the sub-folder?
							if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].EXT))
							{
								show_404($this->fetch_directory() . $segments[0]);
							}
						}
					}
					else
					{
						// Does the default controller exist in the sub-folder?
						$this->set_class($this->default_controller);
						$this->set_method('index');
						$this->set_directory($tmp_dir);

						// Does the requested controller exist in the sub-folder?
						if ( file_exists(APPPATH.'controllers/'. $tmp_dir . '/' . $this->default_controller.EXT))
						{
							return array();
						}
					}
				}
				else
				{
					if ( ! file_exists(APPPATH . 'controllers/' . $this->fetch_directory() . $segments[0].EXT))
					{
						show_404();
					}
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');

				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
				{
					$this->directory = '';
					return array();
				}
			}
			return $segments;
		}

		// stop show_404 untill do $CI->_remap
		$tmp = $segments[0];
		$segments[0] = 'page';
		$segments[1] = $tmp;

		return $segments;
	}

	/**
	 * get relative upper levels of FCPATH and controller path
	 * The result, set relative root path from controller path.
	 */
	protected function _get_rel_fcpath()
	{
		$this->_rel_fcpath = '../../../';
/*
		if ( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' )
		{
			$fcpath_exp  = explode('\\', str_replace('/', '\\', rtrim(FCPATH,  '/')));
			$ctrpath_exp = explode('\\', str_replace('/', '\\', rtrim(APPPATH .'controllers', '/')));
		}
		else
		{
			$fcpath_exp  = explode('/', trim(FCPATH,  '/'));
			$ctrpath_exp = explode('/', trim(APPPATH . 'controllers', '/'));
		}

		$diff = array_diff($ctrpath_exp, $fcpath_exp);

		if ( count($diff) === 1 )
		{
			// default setting
			$this->_rel_fcpath = '../../../';
		}
		else
		{
			foreach ( $diff as $times )
			{
				$this->_rel_fcpath .= '../';
			}
		}
*/
	}

}
