<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ===============================================================================
 *
 * Seezoo インストール用ヘルパ
 *
 * @package Seezoo Core
 * @author Yuta Sakurai <sakurai.yuta@gmail.com>
 *
 * ===============================================================================
 */

if ( ! function_exists('check_file_permissions') )
{
	/**
	 * 設定対象ファイルが書き込み可能かをチェック
	 * @access public
	 * @param $paths 書き込み可能かをチェックするファイルのリスト
	 * @return array
	 */
	function check_file_permissions($paths)
	{
		$return_array = array();

		foreach ($paths as $path)
		{
			$return_array[$path] =
				(file_exists($path)) ?
				is_writable($path) : FALSE;
		}

		return $return_array;
	}
}

if ( ! function_exists('get_install_filepaths') )
{
	/**
	 * インストールに書き込み権限が必要なファイルのリストを配列で取得する
	 * @access public
	 * @return array 書き込み権限が必要なファイルのリスト
	 */
	function get_install_filepaths()
	{
		return array(
			APPPATH . 'config',
			APPPATH . 'config/config.php',
			APPPATH . 'config/database.php',
			FCPATH . 'files',
			FCPATH . 'files/captcha',
			FCPATH . 'files/data',
			FCPATH . 'files/favicon',
			FCPATH . 'files/page_caches',
			FCPATH . 'files/temporary',
			FCPATH . 'files/thumbnail',
			FCPATH . 'files/upload_tmp',
		);
	}
}

if ( ! function_exists('get_seezoo_uri') )
{
	/**
	 * SeezooのトップページURIを取得する
	 * @access public
	 * @return string http://[ホスト名]/[Seezooのインストールされているパス]
	 */
	function get_seezoo_uri()
	{
		// Try to get HostName from HTTP_HOST.
		// If HTTP_HOST don't have value or set, get from SERVER_NAME.
		$server_name = ( isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] )
		                 ? $_SERVER['HTTP_HOST']
		                 : $_SERVER['SERVER_NAME'];
		$server_port = $_SERVER['SERVER_PORT'];
		
		// Does $server_name has post information?
		if ( ($point = strpos($server_name, ':')) !== -1 )
		{
			$sever_name = substr($server_name, 0, $point);
		}

		$dir = rtrim(get_seezoo_dir(), '/');

		if ($server_port == '80') {
			$uri = "http://$server_name$dir/";
		}
		else if($server_port == '443')
		{
			$uri = "https://$server_name$dir/";
		} else {
			$uri = "http://$server_name:$server_port$dir/";
		}

		return $uri;
	}
}

if ( ! function_exists('get_seezoo_dir') )
{
	/**
	 * Seezooのディレクトリパスを取得する
	 * @access public
	 * @return string Seezooのインストールされているパス
	 */
	function get_seezoo_dir()
	{
		// SCRIPT_NAME, PHP_SELFの順に変数がセットされているかを調べ、
		// 入っていたらそれを利用する。
		if ( isset($_SERVER['SCRIPT_NAME']) )
		{
			$request_uri = prep_str($_SERVER['SCRIPT_NAME']);
		}
		elseif ( isset($_SERVER['PHP_SELF']) )
		{
			$request_uri = prep_str($_SERVER['PHP_SELF']);
		}
		else
		{
			$request_uri = '';
		}

		// try explode uri of '/'
		$splitted_uri = explode('/', $request_uri);

		if ( !is_array($splitted_uri) || count($splitted_uri) === 0 )
		{
			return $request_uri;
		}

		do
		{
			$is_found_filename = preg_match(
				'/index\.php/',
				array_pop($splitted_uri)
			);
		} while ( ! $is_found_filename && count($splitted_uri) !== 0 );

		$dir = '' . implode('/', $splitted_uri);

		return $dir;
	}
}

if ( ! function_exists('get_path_icons'))
{
	/**
	 * Seezooのアイコンパスを取得する
	 * @access public
	 * @return string Seezooのアイコンが存在するパス
	 */
	function get_path_icons($path)
	{
		if (strrpos($path, '.php') !== FALSE)
		{
			$path = substr($path, strrpos($path, 'system'));
			$icon = set_install_icons('dashboard/file.png', TRUE);
		}
		else
		{
			if (strrpos($path, 'files') !== FALSE)
			{
				$path = substr($path, strrpos($path, 'files'));
			}
			else
			{
				$path = substr($path, strrpos($path, 'system'));
			}
			$icon = set_install_icons('dashboard/folder.png', TRUE);
		}
		return $icon . $path;
	}
}

if ( ! function_exists('check_mod_rewrite_loaded'))
{
	/**
	 * Apache mod_rewriteモジュールがロードされているかチェック
	 * @access public
	 * @return int
	 */
	function check_mod_rewrite_loaded()
	{
		if (function_exists('apache_get_modules'))
		{
			$apache = apache_get_modules();
			return (int)in_array('mod_rewrite', $apache);
		}
		return 2;
	}
}

if ( ! function_exists('set_install_icons'))
{
	/**
	 * インストール画面で必要なアイコンパスを生成
	 * @depend get_seezoo_dir
	 * @access public
	 * @param string $path
	 * @return sring <img>要素
	 */
	function set_install_icons($path)
	{
		return '<img src="' . get_seezoo_uri() . 'images/' . $path . '" alt="" />';
	}
}

if ( ! function_exists('publish_ticket') )
{
	/*
	 * ワンタイムチケット発行
	 * @return string
	 */
	function publish_ticket($ticket_name)
	{
		$CI =& get_instance();

		$ticket = md5(uniqid(mt_rand(), true));
		$CI->session->set_flashdata($ticket_name, $ticket);

		return $ticket;
	}
}

if ( ! function_exists('check_ticket') )
{
	/*
	 * ワンタイムチケット確認
	 * @return string
	 */
	function check_ticket($ticket_name)
	{
		$CI =& get_instance();

		$ticket = $CI->session->flashdata($ticket_name);
		if (!$CI->input->post($ticket_name) || $CI->input->post($ticket_name) != $ticket)
		{
			show_error('クッキーを有効にしてください。');
		}
		return $ticket;
	}
}

if ( ! function_exists('create_viewdata_from_validation') )
{
	/*
	 * 入力フォームの定義配列より、ユーザ入力値を配列に格納して整理する
	 * CIのform_validation::set_value()のバグ回避も兼ねる
	 * @return array
	 */
	function create_viewdata_from_validation($fields)
	{
		$CI =& get_instance();
		$data = array();

		foreach ($fields as $field) {
			$postdata = $CI->input->post($field['field']);

			$data[$field['field']]['real'] = ( empty($postdata) ) ? '' : $postdata;
			$data[$field['field']]['for_form'] = set_value($field['field']);
			$data[$field['field']]['label'] = $field['label'];
			$data[$field['field']]['rules'] = $field['rules'];
		}

		return $data;
	}
}

