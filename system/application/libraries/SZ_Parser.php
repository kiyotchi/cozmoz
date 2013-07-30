<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 *Extended CodeIgniter builtin Parser Class
 *
 * @add menthod parse_static
 *    do template parse static files
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class SZ_Parser extends CI_Parser
{
	// temporary delimiters
	var $l_delim_temp;
	var $r_delim_temp;
	var $static_methods;
	
	// vurtual static dirs
	var $virtual_dirs = array();
	var $virtual_dir_count = 0;
	var $virtual_currnet_path = '';

	function SZ_Parser()
	{
		$this->l_delim_temp = $this->l_delim;
		$this->r_delim_temp = $this->r_delim;
		$this->static_methods = new StaticV();
	}

	// add method parse static
	// template parse for static file(html or php)
	function parse_static($template, $data, $return = FALSE)
	{
		// override delimiters property
		$this->set_delimiters('<!--\s?\{\$', '\}\s?-->');

		$CI =& get_instance();

		// get template data by "Loader::file" method.
		$template = $CI->load->file($template, TRUE);

		// same parse method
		if ($template == '')
		{
			return FALSE;
		}

		foreach ($data as $key => $val)
		{
			if (is_array($val))
			{
				$template = $this->_parse_pair($key, $val, $template);
			}
			else
			{
				$template = $this->_parse_single_statics($key, (string)$val, $template);
			}
		}
		// additional, simple function replace
		$template = $this->_parse_simple_function($template);
		
		
		// get relative virtual path
		$uri = trim($CI->uri->uri_string(), '/');
		
		if (strpos($uri, '/') !== FALSE)
		{
			$this->virtual_dirs = explode('/', $uri);
			array_pop($this->virtual_dirs);
			$this->virtual_dir_count = count($this->virtual_dirs);
		}
		
		// parse filepath to static
		$template = preg_replace_callback(
										'/(src|href|action)=[\'"]([^"\'<!>]+)["\']/',
										array($this, '_correct_static_path'),
										$template
									);

		if ($return == FALSE)
		{
			$CI->output->append_output($template);
		}

		$this->_reset_delimiter();

		return $template;
	}

	// add method parse vers
	// string parse for static file(html or php)
	function parse_vars($template, $data, $return = FALSE)
	{
		// override delimiters property
		$this->set_delimiters('<!--\s?\{\$', '\}\s?-->');

		$CI =& get_instance();

		// same parse method
		if ($template == '')
		{
			return FALSE;
		}

		foreach ($data as $key => $val)
		{
			if (is_array($val))
			{
				$template = $this->_parse_pair($key, $val, $template);
			}
			else
			{
				$template = $this->_parse_single_statics($key, (string)$val, $template);
			}
		}

		// additional, simple function replace
		$template = $this->_parse_simple_function($template);

		// not implement to evil code executes like include, require...
		if ($return == FALSE)
		{
			$CI->output->append_output($template);
		}

		$this->_reset_delimiter();

		return $template;
	}

	function _reset_delimiter()
	{
		$this->set_delimiters($this->l_delim_temp, $this->r_delim_temp);
	}

	/**
	 * replace by regular exceptions
	 * @param $key
	 * @param $val
	 * @param $string
	 */
	function _parse_single_statics($key, $val, $string)
	{
		return preg_replace("|" . $this->l_delim.$key.$this->r_delim . "|", $val, $string);
	}

	/**
	 * prase variable to simple function
	 * @param string
	 */
	function _parse_simple_function($string)
	{
		if (preg_match_all("|" . $this->l_delim . '([0-9a-zA-Z_]+)' . $this->r_delim . "|", $string, $match, PREG_SET_ORDER))
		{
			foreach ($match as $value)
			{
				if (method_exists($this->static_methods, $value[1]))
				{
					$ret = $this->static_methods->{$value[1]}();
					$string = str_replace($value[0], $ret, $string);
				}
			}
		}
		return $string;
	}
	
	/**
	 * static replace callback method
	 * @param array $v
	 */
	function _correct_static_path($v)
	{
		$type = $v[1];
		$path = $v[2];
		
		
		if ( !preg_match('/\.html?$|\.php$/', $path) )
		{
			$prefix = 'statics/';
		}
		else 
		{
			$path = preg_replace('/\.html?$|\.php$/', '', $path);
			$prefix = '';
		}
		
		// correct traversal path if path includes "../"
		if (strpos($path, '../') !== FALSE)
		{
			$level = substr_count($path, '../');
			if ($this->virtual_dir_count > $level)
			{
				$rel_path_array = array_slice(
															$this->virtual_dirs,
															0,
															$this->virtual_dir_count - $level
														);
				$prefix .= implode('/', $rel_path_array) . '/';
			}
			else 
			{
				$prefix .= implode('/', $this->virtual_dirs) . '/';
			}
			$path = str_replace('../', '', $path);
		}
		else	if ($this->virtual_dir_count > 0)
		{
			$prefix .= implode('/', $this->virtual_dirs) . '/';
		} 
		
		// kill current directory relative path
		str_replace('./', '', $path);
		
		if (preg_match('/^http/', $path) && strpos($path, 'statics/') === FALSE)
		{
			$p = file_link();

			if (defined('MOVE_ORIGIN_SITE') && MOVE_ORIGIN_SITE != '')
			{
				$from = MOVE_ORIGIN_SITE;
			}
			else
			{
				$from = file_link();
			}
			return $type .'="' . str_replace($from, $p . $prefix, $path) . '"';
		}
		else if (strpos($path, 'statics/') === FALSE)
		{
			if ( preg_match('/^javascript:.+/', $path) && $type === 'href')
			{
				return $type . '="' . $path . '"';
			}
			else
			{
				return $type . '="' . file_link() . $prefix . substr($path, strrpos($path, 'statics/')) . '"';
			}
		}
		else
		{
			return $type . '="' . file_link() . $prefix . $path . '"';
		}

	}
}

