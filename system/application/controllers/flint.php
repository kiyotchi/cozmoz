<?php
/**
 * ===============================================================================
 *
 * Seezoo flintコントローラ
 *
 * flint.js設定オブジェクトビルドコントローラ
 * @package Flint.js
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */
class Flint extends SZ_Controller
{
	private $enable_api_cache = false;
	private $controllers_cache_expire_time = 30; // minutes

	private $filename = 'flint_config';
	private $dir_case = array(
		'l'  => 'libraries',
		'h'  => 'helpers',
		'm'  => 'modules',
		'mo' => 'models',
		'p'  => 'plugins'
	);
	
	function Flint() // same PHP4
	{
		parent::SZ_Controller(FALSE);
		$this->load->helper(array('file_helper', 'seezoo_install_helper', 'seezoo_helper'));
		$this->load->model(array('init_model', 'permission_model', 'page_model', 'install_model'));

		// 未インストール時はセッションにDBを使わない
		$this->config_path = APPPATH . 'config';
		if ( ! $this->install_model->check_is_installed($this->config_path) )
		{
			$this->config->set_item('sess_use_database', FALSE);
		}
		$this->load->library('session');

		$this->session->keep_all_flashdata();
		$this->_set_ssl_mode();
		
		// force profiler off
		$this->output->enable_profiler(FALSE);
	}

	/**
	 * flint_config
	 * load and build config object for Flint.js
	 * @access public
	 * @return string (JSON Object)
	 * @note this method should be called by 'src' attribute in <script> element only.
	 */
	function flint_config($pid, $params = array())
	{
		$this->config->load($this->filename, TRUE);
		$config                = $this->config->item('init_config', $this->filename);
		$config['siteUrl']     = ( $_SERVER['SERVER_PORT'] == 443 ) ? ssl_page_link() : page_link();
		$config['ssl_siteUrl'] = ssl_page_link();
		
		// Does token already created?
		if ( $this->session->userdata('sz_token') )
		{
			$config['sz_token'] = $this->session->userdata('sz_token');
		}
		else
		{
			$token              = sha1(microtime());
			$config['sz_token'] = $token;
			$this->session->set_userdata('sz_token', $token);
		}
		
		$config['is_login'] = ( $this->session->userdata('user_id') ) ? true : false;

		if ( $pid === 'segment' )
		{
			$config['routingMode'] = 'segment';
		}
		else if ( $pid === 'view' )
		{
			$config['routingMode'] = 'none';
		}
		else if ( $pid === 'preview' )
		{
			// if build mode is preview, keep ajax token.
			$config['sz_token']    = $this->session->userdata('sz_token');
			$config['routingMode'] = 'none';
		}
		else
		{
			$ver_mode = ( $config['is_login'] ===TRUE ) ? 'recent' : 'approve';
			$page     = $this->page_model->get_page_state($pid, $ver_mode);
			$config['version_number'] = $page->version_number;

			// allow_edit
			$config['can_edit']   = ( strpos($page->allow_edit_user, ':' . $this->session->userdata('user_id') . ':') === FALSE )
			                        ? FALSE
			                        : TRUE;

			// now editting
			$config['is_edit']    = ( $page->edit_user_id == $this->session->userdata('user_id') )
			                        ? TRUE
			                        : FALSE;
		}

		// Flint controllers detection
		if ( $config['directoryList'] === 'auto' )
		{
			$config['directoryList'] = $this->_get_controllers('./' . $config['scriptPath'] . 'controllers');
		}
		
		if ( $config['language'] === 'auto' )
		{
			$config['language'] = $this->_get_locale();
		}

		foreach ( $params as $key => $val )
		{
			$config[$key] = $val;
		}

		header('Content-Type: text/javascript');
		echo preg_replace('/[\r|\n|\r\n|\t|\s]/', '', $this->_build_config($config));
	}

	/**
	 * execute
	 * load and build config object and build core file in same file
	 * @access public
	 * @return string JavaScript strings
	 * @note this method should be called by 'src' attribute in <script> element only.
	 */
	function execute($pid)
	{

		$this->config->load($this->filename, TRUE);
		$config = $this->config->item('init_config', $this->filename);
		$config['siteUrl'] = $this->config->site_url();
		$config['sz_token'] = md5(microtime());
		$config['is_login'] = ($this->session->userdata('user_id')) ? true : false;

		if ($pid === 'segment')
		{
			$config['routingMode'] = 'segment';
		}
		else if ($pid === 'view')
		{
			$config['routingMode'] = 'none';
		}
		else
		{
			$ver_mode = ($config['is_login'] ===TRUE) ? 'recent' : 'approve';
			$page = $this->page_model->get_page_state($pid, $ver_mode);
			$config['version_number'] = $page->version_number;

			// allow_edit
			if (strpos($page->allow_edit_user, ':' . $this->session->userdata('user_id') . ':') === FALSE)
			{
				$config['can_edit'] = FALSE;
			}
			else
			{
				$config['can_edit'] = TRUE;
			}

			if ($page->edit_user_id == $this->session->userdata('user_id'))
			{
				$config['is_edit'] = TRUE;
				$config['edit_version'] = (int)$this->session->userdata('edit_version');
			}
			else
			{
				$config['is_edit'] = FALSE;
				$config['edit_version'] = false;
			}

			$config['is_arrange'] = ((int)$page->is_arranging === 1) ? TRUE : FALSE;
		}


		if ($config['directoryList'] === 'auto')
		{
			$config['directoryList'] = $this->_get_controllers('./' . $config['scriptPath'] . 'controllers');
		}


		$this->session->set_userdata('sz_token', $config['sz_token']);

		$out = array(preg_replace('/[\r|\n|\r\n|\t|\s]/', '', $this->_build_config($config)));
		$out[] = file_get_contents($config['scriptPath'] . 'flint.dev.js'); // this code should be changed [flint.min.js]

		header('Content-Type: application/javascript');
		echo implode("\n", $out);
	}

	/**
	 * auto load libraries by config
	 * @param $output
	 * @param $libs
	 * @param $dir
	 */
	function _auto_load(&$output, $libs, $dir, $suffix = '.js')
	{
		foreach ($libs as $lib)
		{
			if (file_exists($dir . $lib . $suffix))
			{
				$output[] = file_get_contents($dir . $lib . $suffix);
			}
		}
	}

	/**
	 * load_plugin
	 * load some plugins, mixed
	 * @access public
	 * @param (String) load plugin names separated ':'
	 * @return String JavaScript source
	 */
	function load_plugin($pl = FALSE)
	{
		if (!$pl) {
			die('alert("please set load plugin names");');
		}
		$pl = $this->_kill_nullbyte($pl);
		if ($this->_check_traversal($pl) === TRUE)
		{
			die('alert("disallowed characters in plugin string!")');
		}
		$this->config->load($this->filename, TRUE);
		$config = $this->config->item('init_config', $this->filename);
		$path = $config['scriptPath'];

		// already maked same load plugin?
		if ($cache = read_file($path . 'fl_cache/' . md5($pl) . '.js') && $this->enable_api_cache === TRUE)
		{
			$js = $cache;
		}
		else
		{
			// parse string api
			if (strpos($pl, ':') === FALSE)
			{
				$apis = array($pl);
			}
			else
			{
				$apis= explode(':', $pl);
			}
			$js = '';
			foreach ($apis as $val)
			{
				$dir = 'plugins';
				if (!file_exists($path . $dir . '/' .$val . '.js')) {
					continue;
				}
				$js .= $this->_get_cleand_file($path . $dir . '/' .$val . '.js');
				$js .= "\n";
			}
			// set cache
			if ($this->enable_api_cache === TRUE)
			{
				$cache_name = md5($pl);
				write_file($path . 'fl_cache/' . $cache_name . '.js', $js, 'w');
			}
		}

		header('Content-Type: application/x-javascript');
		echo $js;
		exit;
	}

	/**
	 * load_api
	 * load some apis, mixed file and compress
	 * @access public
	 * @param (String) $api (base64 encoded by flint.js
	 * @return string (JavaScript sourse)
	 */
	function load_api($api = FALSE)
	{
		if (!$api) {
			die('alert("please set load api names.");');
		}
		$api = $this->_kill_nullbyte($api);
		if ($this->_check_traversal($api) === TRUE)
		{
			die('alert("disallowed characters in api string!")');
		}
		$this->config->load($this->filename, TRUE);
		$config = $this->config->item('init_config', $this->filename);
		$path = $config['scriptPath'];

		// is already created same api_file?
		// already maked same load plugin?
		if ($cache = read_file($path . 'fl_cache/' . md5($api) . '.js') && $this->enable_api_cache === TRUE)
		{
			$js = $cache;
		}
		else
		{
			// parse string api
			if (strpos($api, ':') === FALSE)
			{
				$apis = array($api);
			}
			else
			{
				$apis= explode(':', $api);
			}
			$js = '';
			foreach ($apis as $val)
			{
				if (! preg_match('/[p|m|mo|l|h|c]{1}\-[a-zA-Z0-9]/s', $val))
				{
					exit;
				}
				list($type, $name) = explode('-', $val);
				$dir = $this->dir_case[$type];
				if (!file_exists($path . $dir . '/' .$name . '.js')) {
					continue;
				}
				$js .= $this->_get_cleand_file($path . $dir . '/' .$name . '.js');
				$js .= "\n";
			}
			// set cache
			if ($this->enable_api_cache === TRUE)
			{
				$cache_name = md5($api);
				write_file($path . 'fl_cache/' . $cache_name . '.js', $js, 'w');
			}
		}

		header('Content-Type: application/javascript');
		echo $js;
		exit;
	}

	/**
	 * load_css
	 * load some css files
	 * @access public
	 * @param (String) $css
	 * @return string (CSS sourse)
	 */
	function load_css($css = FALSE)
	{
		if (!$css) {
			exit;
		}
		$css = base64_decode($css);
		$this->config->load($this->filename, TRUE);
		$config = $this->config->item('init_config', $this->filename);
		$path = $config['scriptPath'];
		// parse string api
		if (strpos($css, ':') === FALSE)
		{
			$apis = array($css);
		}
		else
		{
			$apis= explode(':', $css);
		}
		$css = '';
//		ob_start();
		foreach ($apis as $val)
		{
			$dir = 'fl_css';
			if (!file_exists($path . $dir . '/' .$val . '.css')) {
				continue;
			}
			$css .= @file_get_contents($path . $dir . '/' .$val . '.css');
			$css .= "\n";
		}
		header('Content-Type: text/css');
		echo $css;


	}


	/**
	 * _build_config
	 * setup the config Object for Flint.js
	 * @access private
	 * @param array $config
	 * @return string $configObj ( javascript JSAOn Object )
	 */
	function _build_config($config)
	{
		foreach ($config as $key => $value)
		{
			$depth_2 = array();
			if (is_array($value))
			{
				// simple Array
				if (preg_match('/^auto/u', $key) || $key === 'usePlugins' || $key === 'directoryList')
				{
					// javascript Array
					foreach ($value as $value2)
					{
						$depth_2[] = $this->_format_string($value2);
					}
					$value = '[' . implode(',', $depth_2) . ']';
				}
				else
				{
					// javascript Object
					foreach ($value as $key2 => $value2)
					{
						$depth_2[] = $key2 . ' : ' . $this->_format_string($value2);
					}
					$value = '{' . implode(",\n" , $depth_2) . '}';
				}
				$configObj[] = $key . ' : ' . $value;
			}
			else
			{
				$configObj[] =$key . ':' . $this->_format_string($value);
			}
		}
		return 'FL_CONFIG = {' . implode(",", $configObj) . '};';
	}

	/**
	 *_format_string
	 *convert to mixed=> string for javascript Boolean
	 *@access private
	 *@param mixed $str
	 *@return mixed $res
	 */
	function _format_string($str)
	{
		if (is_bool($str))
		{
			$res = ($str === TRUE) ? 'true' : 'false';
		}
		else if (is_numeric($str))
		{
			$res = intval($str);
		}
		else if (is_string($str))
		{
			$res =  '\'' . $str . '\'';
		}
		return $res;
	}

	/**
	 * _kill_nullbyte
	 * replace a nullbyte
	 * @access private
	 * @param string $api
	 * @return string $api
	 */
	function _kill_nullbyte($api)
	{
		return str_replace('\0', '', $api);
	}

	/**
	 * _check_traversal
	 * check '..' like traversal character in api string
	 * @access ptivate
	 * @param string $api
	 * @return bool
	 */
	function _check_traversal($api)
	{
		if (strpos('..', $api) !== FALSE)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * _get_controllers
	 * check JS controller is directory?
	 * traversal 2 level ( CodeIgniter works [customized Routing] )
	 * @access private
	 * @param  String $path
	 * @return array $ret
	 */
	function _get_controllers($path)
	{
		// cache exists?
		$cache_path = FCPATH . 'files/data/' . sha1('flint_controller_directories');
		$expire = $this->controllers_cache_expire_time;
		
		if ( file_exists($cache_path) )
		{
			$data = file($cache_path);
			if ( (int)$data[0] > time() )
			{
				return json_decode($data[1]);
			}
		}
		
		$ret = array();
		$dir = opendir($path);

		if ($dir)
		{
			while(FALSE !== ($f = readdir($dir)))
			{
				if (is_dir($path . '/' . $f) && !preg_match('/^[\.]/', $f))
				{
					$ret[] = $f;
					$dir2 = opendir($path . '/' . $f);

					if ($dir2)
					{
						while(FALSE !== ($f2 = readdir($dir2)))
						{
							if (is_dir($path . '/' . $f . '/' . $f2) && !preg_match('/^[\.]/', $f2))
							{
								$ret[] = $f . '/' . $f2;
							}
						}
						closedir($dir2);
					}
				}
			}
			closedir($dir);
		}
		
		$fp = @fopen($cache_path, 'wb');
		if ( $fp )
		{
			fwrite($fp, strtotime("+{$expire} minute") . "\n" . json_encode($ret));
		}

		return $ret; // config array
	}
	
	// get locale simply browser acceptable languages
	function _get_locale()
	{
		$locale = strtolower($this->input->server('HTTP_ACCEPT_LANGUAGE'));
		$lang_list = explode(',', $locale);
		if ( ! $lang_list)
		{
			return 'japanese';
		}
		
		$lang = $lang_list[0]; // first value is Primary?
		
		return $this->_format_language($lang);
	}
	
	function _format_language($lang)
	{
		$lns = array(
			'is'		=> 'ilish',
			'it'		=> 'italy',
			'en'		=> 'english',
			'en-gb'	=> 'english',
			'en-us'	=> 'english',
			'ja'		=> 'japanese',
			'fr'		=> 'french'
		);
		
		return isset($lns[$lang]) ? $lns[$lang] : 'japanese';
	}


	function _get_cleand_file($path)
	{
		$f = @file($path, FILE_SKIP_EMPTY_LINES);
		if (!$f)
		{
			return '';
		}
		$ret = array();
		$skip = FALSE;
		foreach ($f as $line)
		{
			if (preg_match('/([\t|\s]*)?\/\*\*?(.*)?/', $line))
			{
				if (! preg_match('/\*\//', $line))
				{
					$skip = TRUE;
				}
			}
			else if (preg_match('/\*\//', $line))
			{
				$skip = FALSE;
				continue;
			}

			if ($skip === FALSE)
			{
				$ret[] = $line;
			}
		}

		return implode("", $ret);
	}
	
	/**
	 * Detection og SSL base_url 
	 * @access private
	 * @return void
	 */
	function _set_ssl_mode()
	{
		// get site data
		$this->load->model('init_model');
		$site_data = $this->init_model->get_site_info();
		
		// if opened port number eq 443, change SSL mode
		$ssl = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? TRUE : FALSE;
		
		// consider upgrade
		if ( is_object($site_data)
				&& isset($site_data->ssl_base_url)
				&& !empty($site_data->ssl_base_url))
		{
			$ssl_url = $site_data->ssl_base_url;
		}
		else
		{
			//$ssl_url = preg_replace('/^http:/', 'https:', $this->CI->config->item('base_url'));
			$ssl_url = $this->config->item('base_url');
		}
		// ssl settings write to config
		$this->config->set_item('ssl_mode', $ssl);
		$this->config->set_item('ssl_base_url', $ssl_url);
	}
}
