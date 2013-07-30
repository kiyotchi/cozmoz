<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ====================================================
 * Extended CodeIgniter builtin Loader Class
 *
 * @addtionalmethods:
 * 	area : Load area data
 *   block : Load block data
 *   block_view : Load block view data
 *   template view : Load template view data
 *   sz_include : include file in template handles.
 *
 *  @package Seezoo Core
 *  @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  ====================================================
*/
class SZ_Loader extends CI_Loader
{
	protected $plugin_manager;

	function __construct()
	{
		parent::CI_Loader();

		$this->plugin_manager =& SeezooPluginManager::get_instance();
	}

	/**
	 * Load Area method
	 * @param string$area
	 * @return void
	 */
	function area($area = FALSE, $reverse_view = FALSE)
	{
		if (!$area)
		{
			show_error('undefined area name');
		}
		
		$CI =& get_instance();
		if ($CI->edit_mode === 'EDIT_SELF')
		{
			$sql = 'SELECT * FROM areas '
								. 'WHERE area_name = ? AND page_id = ? '
								. 'ORDER BY area_id DESC '
								. 'LIMIT 1';
			$query = $CI->db->query($sql, array($area, $CI->page_id, $CI->version_number));
		}
		else
		{
			$sql = 'SELECT * FROM areas '
								. 'WHERE area_name = ? AND page_id = ? '
								. 'ORDER BY area_id DESC '
								. 'LIMIT 1';
			$query = $CI->db->query($sql, array($area, $CI->page_id, $CI->version_number));
		}
		
		if ($query->num_rows() === 0)
		{
			// if record is NULL, create new record
			$area_property = array(
				'area_name'			=> $area,
				'page_id'				=> $CI->page_id,
				'created_date'		=> db_datetime()
			);
			$CI->db->insert('areas', $area_property);
			$area_property['area_id'] = $CI->db->insert_id();
		}
		else
		{
			$area_property = $query->row_array();
		}
		$area = new Area($area_property, $reverse_view);
		$area->load_blocks();
	}

	/**
	 * Load block Object
	 * @param string $cname
	 * @param int $bid
	 * @param bool $returnable
	 * @return mixed
	 */
	function block($cname = FALSE, $bid = FALSE, $returnable = FALSE, $force_view = FALSE)
	{
		if (!$cname)
		{
			return;
		}
		// to correct basename
		$cname = kill_traversal($cname);
		$plugin_prefix = '';
		$CI =& get_instance();

		if ( ! class_exists(ucfirst($cname . '_block')))
		{
			$block_path = 'blocks/' . $cname . '/' . $cname . '.php';

			if (file_exists(SZ_EXT_PATH . $block_path))
			{
				require_once(SZ_EXT_PATH . $block_path);
			}
			else if ( ($plugin = $this->plugin_manager->block_exists($cname, $force_view)) !== FALSE )
			{
				$plugin_prefix = $plugin;
				require_once(SZ_PLG_PATH . $plugin . $block_path);
			}
			else
			{
				require_once(FCPATH . $block_path);
			}
		}
		$name = ucfirst($cname) . '_block';
		$b = new $name();
		$b->collection_name = $cname;
		$b->init($bid, $cname, $plugin_prefix);

		if ($returnable === FALSE)
		{
			$this->block_view($cname . '/view', array('controller' => $b));
			//$b->load_view('view', array('controller' => $b));
		}
		else
		{
			return $b;
		}
	}

	function block_view($view, $vars = array(), $return = FALSE, $plugin_prefix = '')
	{
		// split path
		$dir = substr($view, 0, strrpos($view, '/')) . '/';
		$file = substr($view, strrpos($view, '/'));

		// carrier detection
		switch ( SZ_OUTPUT_MODE )
		{
			case 'sp':
				$prefix = 'smartphone/';
				break;
			case 'mb':
				$prefix = 'mobile/';
				break;
			default:
				$prefix = '';
		}

		// prefixed view exists?
		if ( $prefix != '' )
		{
			$ext_view = $dir . $prefix . $file;
			$view_exists = FALSE;

			if (file_exists(SZ_EXT_PATH . 'blocks/' . $ext_view.EXT))
			{
				$this->_ci_view_path = SZ_EXT_PATH . 'blocks/';
				$view_exists = TRUE;
			}
			else if ( file_exists(SZ_PLG_PATH . $plugin_prefix . 'blocks/' . $ext_view.EXT) )
			{
				$this->_ci_view_path = SZ_PLG_PATH . 'blocks/';
				$view_exists = TRUE;
			}
			else if ( file_exists(FCPATH . 'blocks/' . $ext_view.EXT))
			{
				$this->_ci_view_path = FCPATH . 'blocks/';
				$view_exists = TRUE;
			}

			if ( $view_exists === TRUE )
			{
				return $this->_ci_load(array('_ci_view' => $ext_view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
			}
		}

		// load normal view
		if ( file_exists(SZ_EXT_PATH . 'blocks/' . $view.EXT))
		{
			$this->_ci_view_path = SZ_EXT_PATH . 'blocks/';
		}
		else if ( file_exists(SZ_PLG_PATH . $plugin_prefix . 'blocks/' . $view.EXT) )
		{
			$this->_ci_view_path = SZ_PLG_PATH . $plugin_prefix . 'blocks/';
		}
		else
		{
			$this->_ci_view_path = FCPATH . 'blocks/';
		}

		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
	}

	function template_view($view, $vars = array(), $return = FALSE)
	{
		$this->_ci_view_path = 'templates/';
		$vars = $this->_merge_page_data_vars($vars);

		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
	}

	function _merge_page_data_vars($vars)
	{
		$CI =& get_instance();
		if ( ! isset($CI->page_data))
		{
			return $vars;
		}
		if (is_object($vars))
		{
			$vars = $this->_ci_object_to_array($vars);
		}
		if (! is_array($vars))
		{
			$vars = array($vars);
		}
		return array_merge($CI->page_data, $vars);
	}

	// overwrite core method
	function view($view, $vars = array(), $return = FALSE, $IM = FALSE)
	{
		$plugin = $this->plugin_manager->get_current_plugin();
		$CI     =& get_instance();
		$mode   = $CI->config->item('final_output_mode');
		if ( $mode === 'mb' )
		{
			$subdir = 'mobile_views/';
		}
		else if ( $mode === 'sp' )
		{
			$subdir = 'smartphone_views/';
		}
		else
		{
			$subdir = '';
		}

		$EXT_DIR   = SZ_EXT_PATH;
		$PLG_DIR   = SZ_PLG_PATH . $plugin . '/';
		$CORE_DIR  = APPPATH;
		$v         = $view.EXT;
		$determine = FALSE;

		// load sub-directroy ( smartphone, mobile )
		if ( $subdir !== '' )
		{
			if( $IM !== FALSE )
			{
				$this->_ci_view_path = $IM;
				$determine = TRUE;
			}
			else if ( file_exists($EXT_DIR.$subdir.$v) )
			{
				$this->_ci_view_path = $EXT_DIR.$subdir;
				$determine = TRUE;
			}
			else if ( ! empty($plugin)
			           && file_exists($PLG_DIR.$subdir.$v) )
			{
				$this->_ci_view_path = $PLG_DIR.$subdir;
				$determine = TRUE;
			}
			else if ( file_exists($CORE_DIR.$subdir.$v) )
			{
				$this->_ci_view_path = $CORE_DIR.$subdir;
				$determine = TRUE;
			}
		}

		// If sub-directory view not exists, load default view
		if ( $determine === FALSE )
		{
			$subdir = 'views/';
			if( $IM !== FALSE )
			{
				$this->_ci_view_path = $IM;
			}
			else if ( file_exists($EXT_DIR.$subdir.$v) )
			{
				$this->_ci_view_path = $EXT_DIR.$subdir;
			}
			else if ( ! empty($plugin)
			           && file_exists($PLG_DIR.$subdir.$v) )
			{
				$this->_ci_view_path = $PLG_DIR.$subdir;
			}
			else
			{
				$this->_ci_view_path = $CORE_DIR.$subdir;
			}
		}
		//var_dump($this->_ci_view_path);
//		if ( file_exists($ext_view))
//		{
//			$this->_ci_view_path = SZ_EXT_PATH . 'views/';
//		}
//		else if ( ! empty($plugin)
//		          && file_exists(SZ_PLG_PATH . $plugin . '/views/' . $view.EXT) )
//		{
//			$this->_ci_view_path = SZ_PLG_PATH . $plugin . '/views/';
//		}
//		else
//		{
//			$this->_ci_view_path = APPPATH . 'views/';
//		}

		$vars = $this->_merge_page_data_vars($vars);
		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
	}

	// load template parts
	function sz_include($file)
	{
		$CI =& get_instance();

		// this method works cms mode only.
		if ( ! isset($CI->cms_mode) ||  $CI->cms_mode !== TRUE)
		{
			return '';
		}
		$ext = 'php';
		if (($pos = strrpos($file, '.')) === FALSE)
		{
			$ext = substr($file, $pos + 1);
			$filename = substr($file, 0, $pos);
		}
		$tpl_handle = trim($CI->_rel_template_path, '/') . '/';
		$this->_ci_view_path = FCPATH . 'templates/' . $tpl_handle;
		if ($ext == 'php')
		{
			return $this->_ci_load(array('_ci_view' => $file, '_ci_vars' => array('template_path' => file_link() . 'templates/' . $tpl_handle), '_ci_return' => FALSE));
		}
		else
		{
			return $this->file($file, FALSE);
		}
	}

	/**
	 * override helper method
	 *
	 * @param $helpers
	 */
	function helper($helpers = array())
	{
		if ( ! is_array($helpers))
		{
			$helpers = array($helpers);
		}

		foreach ($helpers as $helper)
		{
			$helper = strtolower(str_replace(EXT, '', str_replace('_helper', '', $helper)).'_helper');

			if (isset($this->_ci_helpers[$helper]))
			{
				continue;
			}

			// extension helper load check at loop
			foreach ( array(SZ_EXT_PATH, APPPATH) as $extension_dir )
			{
				$ext_helper = $extension_dir.'helpers/'.config_item('subclass_prefix').$helper.EXT;
				// Is this a package extension request?
				if (file_exists($ext_helper))
				{
					$base_helper = BASEPATH.'helpers/'.$helper.EXT;

					if ( ! file_exists($base_helper))
					{
						show_error('Unable to load the requested file: helpers/'.$helper.EXT);
					}

					include_once($ext_helper);
					include_once($base_helper);

					// stack add and return;
					$this->_ci_helpers[$helper] = TRUE;
					log_message('debug', 'Helper loaded: '.$helper);
					break;
				}
			}

			// simple no-extensioned helper file check
			$loaded = FALSE;
			foreach ( $this->_get_detection_dirs() as $path )
			{
				$filename = $path.'helpers/'.$helper.EXT;
				if ( file_exists($filename) )
				{
					include_once($filename);
					$this->_ci_helpers[$helper] = TRUE;
					log_message('debug', 'Helper loaded: '.$helper);
					$loaded = TRUE;
					break;
				}
			}
/*
			$ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix').$helper.EXT;

			// package
			$pkg_ext_helper = SZ_EXT_PATH.'helpers/'.config_item('subclass_prefix').$helper.EXT;

			// Is this a package extension request?
			if (file_exists($pkg_ext_helper))
			{
				$base_helper = BASEPATH.'helpers/'.$helper.EXT;

				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}

				include_once($pkg_ext_helper);
				include_once($base_helper);
			}
			else if (file_exists(SZ_EXT_PATH.'helpers/'.$helper.EXT))
			{
				include_once(SZ_EXT_PATH.'helpers/'.$helper.EXT);
			}
			// Is this a helper extension request?
			else if (file_exists($ext_helper))
			{
				$base_helper = BASEPATH.'helpers/'.$helper.EXT;

				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}

				include_once($ext_helper);
				include_once($base_helper);
			}
			elseif (file_exists(APPPATH.'helpers/'.$helper.EXT))
			{
				include_once(APPPATH.'helpers/'.$helper.EXT);
			}
			else
			{
				if (file_exists(BASEPATH.'helpers/'.$helper.EXT))
				{
					include_once(BASEPATH.'helpers/'.$helper.EXT);
				}
				else
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}
			}

			$this->_ci_helpers[$helper] = TRUE;
*/
			if ( ! $loaded )
			{
				show_error('Unable to load the requested file: helpers/'.$helper.EXT);
			}
			//log_message('debug', 'Helper loaded: '.$helper);
		}
//		if ( ! is_array($helpers))
//		{
//			$helpers = array($helpers);
//		}
//
//		foreach ($helpers as $helper)
//		{
//			$helper = strtolower(str_replace(EXT, '', str_replace('_helper', '', $helper)).'_helper');
//
//			if (isset($this->_ci_helpers[$helper]))
//			{
//				continue;
//			}
//
//			$ext_path = SZ_EXT_PATH . 'helpers/';
//
//			if ( file_exists($ext_path . config_item('subclass_prefix') . $helper.EXT))
//			{
//				include_once($ext_path . config_item('subclass_prefix') . $helper.EXT);
//				parent::helper($helper);
////				$this->_ci_helpers[$helper] = TRUE;
////				continue;
//			}
//			else if ( file_exists($ext_path . $helper.EXT))
//			{
//				include_once($ext_path . $helper.EXT);
//
//				if ( file_exists(BASEPATH . 'helpers/' . $helper.EXT)
//								|| file_exists(APPPATH . 'helpers/' . $helper.EXT))
//				{
//					parent::helper($helper);
//				}
//				continue;
//			}
//			else
//			{
//				parent::helper($helper);
//			}
//		}
	}

	/**
	 * override model method
	 * try original plugins model file load.
	 * if file not exists, call Core Loader::model method.
	 * ### notice: plugin model file name should be differnt of Seezoo Core Model filenames.
	 */
	function model($model, $name = '', $db_conn = FALSE)
	{
		if (is_array($model))
		{
			foreach($model as $babe)
			{
				$this->model($babe);
			}
			return;
		}

		if ($model == '')
		{
			return;
		}

		// stack to original path
		$orig_model = $model;
		$orig_name = $name;

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (strpos($model, '/') === FALSE)
		{
			$path = '';
		}
		else
		{
			$x = explode('/', $model);
			$model = end($x);
			unset($x[count($x)-1]);
			$path = implode('/', $x).'/';
		}

		if ($name == '')
		{
			$name = $model;
			$orig_name = $name;
		}

		if (in_array($name, $this->_ci_models, TRUE))
		{
			return;
		}

		$CI =& get_instance();
		if (isset($CI->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		$model     = strtolower($model);
		$plugin    = $this->plugin_manager->get_current_plugin();
		$ext_model = file_exists(SZ_EXT_PATH.'models/'.$path.$model.EXT);
		$plg_model = ( ! empty($plugin) )
		               ? file_exists(SZ_PLG_PATH.$plugin.'/models/'.$path.$model.EXT)
		               : FALSE;

		if ( ! $ext_model && ! $plg_model )
		{
			parent::model($orig_model, $orig_name, $db_conn);
			return;
		}

		if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
		{
			if ($db_conn === TRUE)
				$db_conn = '';

			$CI->load->database($db_conn, FALSE, TRUE);
		}

		if ( ! class_exists('Model'))
		{
			load_class('Model', FALSE);
		}

		if ( $ext_model )
		{
			require_once(SZ_EXT_PATH.'models/'.$path.$model.EXT);
		}
		else if ( $plg_model )
		{
			require_once(SZ_PLG_PATH.$plugin.'/models/'.$path.$model.EXT);
		}

		$model = ucfirst($model);

		$CI->$name = new $model();
		$CI->$name->_assign_libraries();

		$this->_ci_models[] = $name;
	}

	// --------------------------------------------------------------------

	/**
	 * Load class (Override)
	 *
	 * This function loads the requested class.
	 * ext: also plugins directory class can load.
	 *
	 * @access	private
	 * @param 	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @param	string	an optional object name
	 * @return 	void
	 */
	function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		$class = str_replace(EXT, '', trim($class, '/'));

		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if (strpos($class, '/') !== FALSE)
		{
			// explode the path so we can separate the filename from the path
			$x = explode('/', $class);

			// Reset the $class variable now that we know the actual filename
			$class = end($x);

			// Kill the filename from the array
			unset($x[count($x)-1]);

			// Glue the path back together, sans filename
			$subdir = implode($x, '/').'/';
		}

		// stack to base class status
		$baseclass = BASEPATH.'libraries/'.ucfirst($class).EXT;

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			// loop of process
			foreach ( array(SZ_EXT_PATH, APPPATH) as $extension_dir )
			{
				$subclass = $extension_dir.'libraries/'.$subdir.config_item('subclass_prefix').$class.EXT;
				// Is this a class extension request?
				if (file_exists($subclass))
				{
					$baseclass = BASEPATH.'libraries/'.ucfirst($class).EXT;

					if ( ! file_exists($baseclass))
					{
						log_message('error', "Unable to load the requested class: ".$class);
						show_error("Unable to load the requested class: ".$class);
					}

					// Safety:  Was the class already loaded by a previous call?
					if (in_array($subclass, $this->_ci_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& get_instance();
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
							}
						}

						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}

					include_once($baseclass);
					include_once($subclass);
					$this->_ci_loaded_files[] = $subclass;

					return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
				}
			}

			// Lets search for the requested library file and load it.
			$is_duplicate = FALSE;
			foreach ($this->_get_detection_dirs() as $path)
			{
//			for ($i = 1; $i < 3; $i++)
//			{
				//$path = ($i % 2) ? APPPATH : BASEPATH;
				$filepath = $path.'libraries/'.$subdir.$class.EXT;

				// Does the file exist?  No?  Bummer...
				if ( ! file_exists($filepath))
				{
					continue;
				}

				// Safety:  Was the class already loaded by a previous call?
				if (in_array($filepath, $this->_ci_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if ( ! is_null($object_name))
					{
						$CI =& get_instance();
						if ( ! isset($CI->$object_name))
						{
							return $this->_ci_init_class($class, '', $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;
				}

				include_once($filepath);
				$this->_ci_loaded_files[] = $filepath;
				return $this->_ci_init_class($class, '', $params, $object_name);
			}
		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_ci_load_class($path, $params);
		}

		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}

	private function _get_detection_dirs()
	{
		$current = $this->plugin_manager->get_current_plugin();
		$dirs    = ( ! empty($current) )
		             ? array(SZ_EXT_PATH, SZ_PLG_PATH . $current . '/', APPPATH, BASEPATH)
		             : array(SZ_EXT_PATH, APPPATH, BASEPATH);
		return $dirs;
	}
}
