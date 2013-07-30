<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * Seezoo file page selectable API supply
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==============================================================
 */

if ( ! function_exists('select_file'))
{
	function select_file($field_name = 'file_id', $file_id = 0, $allowed_extension = '')
	{

		$out[] = '<div class="sz_file_api_block">';
		$out[] = '<span class="sz_file_api_block_name">';
		if ((int)$file_id > 0)
		{
			$CI =& get_instance();
			$CI->load->model('file_model');
			$file = $CI->file_model->get_file_data((int)$file_id);
			if ($file)
			{
				if ($file->width > 0 && $file->height > 0)
				{
					$out[] = '<img src="' . file_link() . 'files/thumbnail/' . $file->crypt_name . '.' . $file->extension . '" alt="" />';
				}
				else
				{
					$out[] = '<img src="' . file_link() . 'images/icons/files/'
							. ((has_icon_ext($file->extension)) ? $file->extension : 'file')
							. '.png" />';
				}
				$out[] = '<span class="sz_api_selected_name">' . $file->file_name . '.' . $file->extension . '</span></span>';
				$out[] = '<input type="hidden" name="' . $field_name . '" value="' . $file->file_id .'" />';
				$out[] = '<span id="sz_allowed_extension" style="display:none">' . $allowed_extension . '</span>';
				
			}
			else
			{
				$out[] = 'ファイルを選択<span style="color:#c00">（選択されていたファイルは削除されていました）</span>';
				$out[] = '</span>';
				$out[] = '<input type="hidden" name="' . $field_name . '" value="" />';
				$out[] = '<span id="sz_allowed_extension" style="display:none">' . $allowed_extension . '</span>';
				
			}
		}
		else
		{
			$out[] = 'ファイルを選択';
			$out[] = '</span>';
			$out[] = '<input type="hidden" name="' . $field_name . '" value="" />';
			$out[] = '<span id="sz_allowed_extension" style="display:none">' . $allowed_extension . '</span>';
			
		}

		$out[] = '<a href="javascript:void(0)" class="remove_selection">&nbsp;</a>';
		$out[] = '</div>';

		return implode("\n", $out);
	}
}

if ( ! function_exists('select_file_multiple'))
{
	function select_file_multiple($field_name = 'file_ids', $file_ids = array(), $page_ids = array())
	{

		$out[] = '<div class="sz_file_api_block_multiple">画像を追加してください</div>';
		$out[] = '選択された画像：';
		$out[] = '<div id="sz_file_api_block_multiple_results" fname="' . $field_name . '">';
		
		//$out[] = '<ul class="multiple_files clearfix">';
		$out[] = '<table class="multiple_files"><tbody>';

		if (count($file_ids) > 0)
		{

			$CI =& get_instance();
			$CI->load->model('file_model');
			foreach ($file_ids as $key => $file_id)
			{
				$file = $CI->file_model->get_file_data($file_id);
				if (!$file)
				{
					continue;
				}
				$out[] = '<tr><td>';
				$out[] = '<div class="sz_file_list_sortable" style="background:url('
							 . make_file_path($file, 'thumbnail', TRUE)
							 . ') center center no-repeat;" title="'
							 . $file->file_name . $file->extension
							 . '"></div>';
				$out[] = '<input type="hidden" name="' . $field_name . '[]" value="' . $file_id . '" />';
				$out[] = '</td><td class="ipt">';
				if (isset($page_ids[$key]) && (int)$page_ids[$key] > 0 )
				{
					$out[] = select_page('page_ids[]', (int)$page_ids[$key]);
				}
				else 
				{
					$out[] = select_page('page_ids[]');
				}
				$out[] = '</td><td><p class="posrel">';
				$out[] = '<a href="javascript:void(0)" class="sz_file_multi_delete"></a>';
				$out[] = '<a href="javascript:void(0)" class="sz_file_list_sort_order_next"></a>';
				$out[] = '<a href="javascript:void(0)" class="sz_file_list_sort_order_prev"></a>';
				$out[] = '</p></td></tr>';
//				if ($file->width > 0 && $file->height > 0)
//				{
//					$out[] = '<li><div class="sz_file_list_sortable" style="background:url('
//							 . make_file_path($file, 'thumbnail', TRUE)
//							 . ') center center no-repeat;" title="'
//							 . $file->file_name . $file->extension
//							 . '"></div>';
//				}
//				else
//				{
//					$out[] = '<li><div class="sz_file_list_sortable" style="background:url('
//							 . file_link() . 'images/icons/files/' 
//							 . ((has_icon_ext($file->extension)) ? $file->extension : 'file') . '.png'
//							 . ') center center no-repeat;" title="'
//							 . $file->file_name . $file->extension
//							 . '"></div>';
//				}
//				$out[] = '<a href="javascript:void(0)" class="sz_file_multi_delete"></a>';
//				$out[] = '<a href="javascript:void(0)" class="sz_file_list_sort_order_next"></a>';
//				$out[] = '<a href="javascript:void(0)" class="sz_file_list_sort_order_prev"></a>';
//				$out[] = '<input type="hidden" name="' . $field_name . '[]" value="' . $file_id . '" /></li>';
			}

		}
		//$out[] = '</ul>';
		$out[] = '</tbody></table>';

		$out[] = '</div>';

		return implode("\n", $out);
	}
}

if ( ! function_exists('select_page'))
{
	function select_page($field_name = 'page_id', $page_id = 0)
	{
		$out[] = '<div class="sz_page_api_block">';
		$out[] = '<span class="sz_page_api_block_name">';

		if ((int)$page_id > 0)
		{
			$CI =& get_instance();
			$CI->load->model('sitemap_model');
			$page = $CI->sitemap_model->get_page_data((int)$page_id);
			if ($page)
			{
				$out[] = $page->page_title;
				$out[] = '</span>';
				$out[] = '<input type="hidden" name="' . $field_name . '" value="' . (int)$page_id . '" />';
			}
			else
			{
				$out[] = 'ページを選択<span style="color:#c00">（選択されていたページは削除されていました）</span>';
				$out[] = '</span>';
				$out[] = '<input type="hidden" name="' . $field_name . '" value="" />';
			}
		}
		else
		{
			$out[] = 'ページを選択';
			$out[] = '</span>';
			$out[] = '<input type="hidden" name="' . $field_name . '" value="" />';
		}
		$out[] = '<a href="javascript:void(0)" class="remove_selection">&nbsp;</a>';
		$out[] = '</div>';

		return implode("\n", $out);

	}
}

if ( ! function_exists('get_page_link_by_page_id'))
{
	function get_page_link_by_page_id($pid)
	{
		$CI =& get_instance();
		$sql = 'SELECT page_path FROM page_paths WHERE page_id = ? LIMIT 1';
		$query = $CI->db->query($sql, array($pid));

		$result = $query->row();
		return page_link() . $result->page_path;
	}
}

if ( ! function_exists('set_content'))
{
	function set_content()
	{
		$CI =& get_instance();
		return (isset($CI->content_data)) ? $CI->content_data : '';

	}
}

if ( ! function_exists('set_menu'))
{
	function set_menu()
	{
		$CI =& get_instance();
		return (isset($CI->menu_data)) ? $CI->menu_data : '';
	}
}

if ( ! function_exists('load_flash'))
{
	function load_flash($flash_path, $param = array())
	{
		$is_ie = (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) ? TRUE : FALSE;
		
		$param_array = array(
			//'wmode'	=> 'transparent',
			'quality'	=> 'high',
			'menu'		=> 'false',
			'scale'	=> 'noScale',
			'salign'	=> 'lt'
		);
		$param_array = array_merge($param_array, $param);
		
		if (isset($param_array['width']))
		{
			$w = $param_array['width'];
			unset($param_array['width']);
		}
		else 
		{
			$w = 600;
		}
		if (isset($param_array['height']))
		{
			$h = $param_array['height'];
			unset($param_array['height']);
		}
		else 
		{
			$h = 400;
		}
		$out = array(
			'<object width="', $w, '" height="', $h, '" style="visibility:visible;" '
		);
		if (isset($param_array['id']))
		{
			$out[] = 'id="' . $param_array['id'] . '" ';
			unset($param_array['id']);
		}
		if ($is_ie) {
			$out[] = 'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
		} else {
			$out[] = 'type="application/x-shockwave-flash" data="' . $flash_path . '">';
		}
		$swf[] = implode('', $out);
		foreach ($param_array as $key => $v)
		{
			$swf[] = '<param name="' . $key . '" value="' . $v . '" />';
		}
		if ($is_ie)
		{
			$swf[] = '<param name="movie" value="' . $flash_path . '" />';
			$swf[] = '<param name="src" value="' . $flash_path . '" />';
		}
		$swf[] = '</object>';
		return implode("\n", $swf);
	}
}

// build_javascript : パスからjavascriptタグ生成
if ( ! function_exists('build_javascript'))
{
	function build_javascript($path)
	{
		if ( ! preg_match('/^http/', $path))
		{
			if (file_exists(SZ_EXT_PATH . $path))
			{
				$path = package_link() . $path;
			}
			else if (file_exists($path))
			{
				$path = file_link() . $path;
			}
			else
			{
				return '';
			}
		}
		return '<script type="text/javascript" src="' . $path . '" charset="UTF-8"></script>';
	}
}

// build_css : パスからlinkタグ生成
if ( ! function_exists('build_css'))
{
	function build_css($path)
	{
		if ( ! preg_match('/^http/', $path))
		{
			if (file_exists(SZ_EXT_PATH . $path))
			{
				$path = package_link() . $path;
			}
			else if (file_exists($path))
			{
				$path = file_link() . $path;
			}
			else 
			{
				return '';
			}
		}
		return '<link rel="stylesheet" type="text/css" href="' . $path . '" media="all" />';
	}
}
