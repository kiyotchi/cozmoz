<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * Seezoo truly base path and file-download utilities [consider Apache mod_rewrite is ON/OFF]
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==============================================================
 */

if ( ! function_exists('file_link'))
{
	function file_link()
	{
		$CI =& get_instance();
		if ( $CI->config->item('ssl_mode') === TRUE )
		{
			return $CI->config->slash_item('ssl_base_url');
		}
		else
		{
			return $CI->config->slash_item('base_url');
		}
	}
}

if ( ! function_exists('page_link'))
{
	function page_link($path = '', $is_ssl = FALSE)
	{
		if ( $is_ssl === TRUE )
		{
			return ssl_page_link($path);
		}
		$CI =& get_instance();
		$mobile =& Mobile::get_instance();
		$suffix = ($CI->config->item('index_page') == '') ? '' : '/';
		if ( ! $mobile->is_mobile() || $CI->uri->get_query_string_suffix() === FALSE )
		{
			return $CI->config->site_url() . $suffix . $path;
		}

		$pos = strrpos($path, '?');
		if ( $pos !== FALSE )
		{
			$query = substr($path, $pos) . $CI->uri->get_query_string_suffix();
			$path  = substr($path, 0, $pos);
		}
		else
		{
			$query = '?' . $CI->uri->get_query_string_suffix();
		}

		// Y.Paku ターゲット対応
		if(stripos($path,'#') !== false) {
			$str_target = substr($path,stripos($path,'#'));
			return $CI->config->site_url() . $suffix . substr($path,0,stripos($path,'#')) . $query .'&sys='.time(). $str_target;
		}
		return $CI->config->site_url() . $suffix . $path . $query.'&sys='.time();
	}
}

if ( ! function_exists('image_link'))
{
	function image_link($path = '', $is_ssl = FALSE)
	{
		if ( $is_ssl === TRUE )
		{
			return ssl_page_link($path);
		}
		$CI =& get_instance();

		$suffix = ($CI->config->item('index_page') == '') ? '' : '/';

		return $CI->config->site_url() . $suffix . $path;
	}
}


// SSLモードであればssl_page_link()を、通常であればpage_link()を自動判定
if ( ! function_exists('get_base_link'))
{
	function get_base_link($path = '')
	{
		$CI =& get_instance();
		return ($CI->config->item('ssl_mode')) ? ssl_page_link($path) : page_link($path);
	}
}

// prepare ssl out link (https -> http)
// these function is not used currently.
if ( ! function_exists('ssl_page_link'))
{
	function ssl_page_link($path = '')
	{
		$CI =& get_instance();
		$mobile = Mobile::get_instance();
		$suffix = ($CI->config->item('index_page') == '') ? '' : '/';
		if ( ! $mobile->is_mobile() || $CI->uri->get_query_string_suffix() === FALSE )
		{
			return $CI->config->ssl_site_url() . $suffix . $path;
		}

		$pos = strrpos($path, '?');
		if ( $pos !== FALSE )
		{
			$query = substr($path, $pos) . $CI->uri->get_query_string_suffix();
			$path  = substr($path, 0, $pos);
		}
		else
		{
			$query = '?' . $CI->uri->get_query_string_suffix();
		}

		return $CI->config->ssl_site_url() . $suffix . $path . $query;
	}
}

if ( ! function_exists('ssl_file_link'))
{
	function ssl_file_link()
	{
		$CI =& get_instance();
		return $CI->config->item('ssl_base_url');
	}
}

// Do you logged in with site master users?
if ( ! function_exists('is_user_login') )
{
	function is_user_login()
	{
		$CI = & get_instance();
		$CI->load->library('session');

		return ( (int)$CI->session->userdata('user_id') > 0 ) ? TRUE : FALSE;
	}
}

// Do you logged in with members?
if ( ! function_exists('is_member_login') )
{
	function is_member_login()
	{
		$CI = & get_instance();
		$CI->load->library('session');

		return ( (int)$CI->session->userdata('member_id') > 0 ) ? TRUE : FALSE;
	}
}

/**
 * Seezoo customized force download [download_helper]
 * CodeIgniter "force_download" function in download_helper.php causes 500 internal server error
 * when download file-size over memory_limit of php.ini settings.
 * So that, if download file greater than memory_limit,
 * try split download of 4096byte / per.
 * Seezoo sometime treats bigger file ex. flv,swf,etc...
 */
if ( ! function_exists('force_download_reg'))
{
	function force_download_reg($filename = '', $filepath = '', $is_binary_data = FALSE)
	{
		// same codeigniter code:
		if ($filename == '' OR $filepath == '')
		{
			return FALSE;
		}

		// Try to determine if the filename includes a file extension.
		// We need it in order to set the MIME type
		if (FALSE === strpos($filename, '.'))
		{
			return FALSE;
		}

		// Grab the file extension
		$x = explode('.', $filename);
		$extension = end($x);

		// get apache memory_limit
		$ini_max = trim(ini_get('memory_limit'));
		switch ( strtolower($ini_max[strlen($ini_max)-1]) )
		{
			case 'g':
				$max_size = intval($ini_max * 1024 * 1024 * 1024);
				break;
			case 'm':
				$max_size = intval($ini_max * 1024 * 1024);
				break;
			case 'k':
				$max_size = intval($ini_max * 1024);
				break;
			default :
				$max_size = intval($ini_max);
		}
		$file_size = $is_binary_data ? strlen($filepath) : filesize($filepath);

		// Load the mime types
		@include(APPPATH.'config/mimes'.EXT);

		// Set a default mime if we can't find it
		if ( ! isset($mimes[$extension]))
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
		}

		// Generate the server headers
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
		{
			// add process
			$filename = mb_convert_encoding($filename, 'SHIFT_JIS', 'UTF-8');
			// add process end
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			header("Content-Length: ".$file_size);
		}
		else
		{
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			header("Content-Length: ".$file_size);
		}

		// if filesize greater than max_memory_limit, split download
		if ($ini_max > 0 && $max_size < $file_size)
		{
			flush();
			if (!$is_binary_data)
			{
				$fp = fopen($filepath, 'rb');
				while(!feof($fp))
				{
					echo fread($fp, 4096);
					flush();
				}
				fclose($fp);
			} else {
				$start = 0;
				while($start < $file_size)
				{
					if ($start + 4096 > $file_size)
					{
						echo substr($filepath, $start, $file_size - $start);
						break;
					}
					else
					{
						echo substr($filepath, $start, 4096);
					}
					flush();
				}
				exit();
			}
			exit();
		}
		else
		{
			exit(file_get_contents($filepath));
		}
	}
}
/**
 * get package_path
 */
if ( ! function_exists('package_link'))
{
	function package_link()
	{
		return file_link() . ((defined('SZ_EXT_DIR')) ? SZ_EXT_DIR : '');
	}
}

/**
 * block_exists
 */
if ( ! function_exists('block_exists') )
{
	function block_exists($block_name)
	{
		$path = 'blocks/' . $block_name;
		// Does block directory exists?
		if ( ! is_dir(SZ_EXT_PATH . $path) && ! is_dir(FCPATH . $path) )
		{
			return FALSE;
		}
		// Does registed Collections DB?
		$CI =& get_instance();

		$sql =
					'SELECT '
					.	'collection_id '
					.'FROM '
					.	'collections '
					.'WHERE '
					.	'collection_name = ? '
					.'LIMIT 1';
		$query = $CI->db->query($sql, array($block_name));

		return ($query && $query->row()) ? TRUE : FALSE;
	}
}

/**
 * page_exists
 */
if ( ! function_exists('page_exists') )
{
	function page_exists($page_path, $is_system_page = FALSE)
	{
		$CI =& get_instance();
		$sql =
					'SELECT '
					.	'PV.page_version_id, '
					.	'PV.is_system_page '
					.'FROM '
					.	'page_versions as PV '
					.'JOIN ( '
					.		'SELECT '
					.			'page_id, '
					.			'page_path '
					.		'FROM '
					.			'page_paths '
					.		'WHERE '
					.			'page_path = ? '
					.		'LIMIT 1 '
					.') as PP ON ( '
					.		'PV.page_id = PP.page_id '
					.') '
					.'LIMIT 1';
		$query = $this->db->query($sql, array($page_path));

		if (!$query || $query->row())
		{
			return FALSE;
		}
		if (!$is_system_page)
		{
			return TRUE;
		}
		else
		{
			$result = $query->row();
			return ((int)$result->is_system_page > 0) ? TRUE : FALSE;
		}
	}
}

/**
 * permission exists
 */
if ( ! function_exists('is_permission_allowed') )
{
	function is_permission_allowed($permission, $user_id)
	{
		$CI =& get_instance();

		if (!$permission
					|| empty($permission)
					|| (isset($CI->is_admin) && $CI->is_admin === TRUE)
					|| (isset($CI->is_master) && $CI->is_master === TRUE)) // case permission is NULL or empty string
		{
			return TRUE;
		}
		else
		{
			return (strpos($permission, ':' . $user_id . ':') === FALSE) ? FALSE : TRUE;
		}
	}
}

/**
 * has_permission
 */
if ( ! function_exists('has_permission'))
{
	function has_permission($permission, $user_id)
	{
		if ( !$permission)
		{
			return FALSE;
		}
		else
		{
			return (strpos($permission, ':' . $user_id . ':') === FALSE) ? FALSE : TRUE;
		}
	}
}

/**
 * method_call
 */
if ( ! function_exists('method_call') )
{
	function method_call($class, $method, $args = array())
	{
		if ( class_exists($class) )
		{
			if ( method_exists($class, $method) )
			{
				$c = new $class;
				$c->{$method}($args);
			}
		}
	}
}

/**
 * compatible PHP 5.1.x helper
 * support json_encode functions.
 */
if ( ! function_exists('json_encode'))
{
	require_once(APPPATH . 'libraries/thirdparty/JSON.php');

	function json_encode($data)
	{
		$json = new Services_JSON();
		return $json->encode($data);
	}
}

/**
 * compatible PHP 5.1.x helper
 * support json_decode functions.
 */
if ( ! function_exists('json_decode'))
{
	require_once(APPPATH . 'libraries/thirdparty/JSON.php');

	function json_decode($data)
	{
		$json = new Services_JSON();
		return $json->decode($data);
	}
}
