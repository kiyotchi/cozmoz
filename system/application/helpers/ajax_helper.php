<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * Seezoo Ajax Utility functions
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==============================================================
 */
if ( ! function_exists('parse_path_segment'))
{
	function parse_path_segment($path, $before = FALSE)
	{
		$pos = strrpos($path, '/');

		if ($pos === FALSE)
		{
			return ($before === FALSE) ? $path : '';
		}

		if ($before === FALSE)
		{
			return substr($path, $pos + 1);
		}
		else
		{
			return substr($path, 0, $pos);
		}
	}
}

// autonavigation block format helper
if ( ! function_exists('generate_navigation_format'))
{
	function generate_navigation_format($data, $cl = '', $dp_cl = '', $current_class = '', $is_view = FALSE)
	{
		if ($data === FALSE)
		{
			return '';
		}
		$CI =& get_instance();

		$out = array('<ul class="' . $dp_cl . ((!empty($cl) ? ' ' . $cl : '')) . '">');

		foreach ($data as $key => $v)
		{
			if (is_permission_allowed($v['page']['allow_access_user'], $CI->user_id))
			{
				$out[] = '<li>';
	
				$active_class = ($v['page']['page_id'] == $CI->page_id) ? ' class="' . $current_class . '"' : '';
	
				$out[] = '<a href="' . (($is_view) ? page_link() . $v['page']['page_path'] : 'javascript:void(0)')
							. '"' . $active_class . '>' . $v['page']['page_title'] . '</a>';
				if ($v['child'] !== FALSE)
				{
					$out[] = generate_navigation_format($v['child'], '', $dp_cl);
				}
				$out[] = '</li>';
			}
		}

		$out[] = '</ul>';

		return implode("\n", $out);
	}
}

// autonavigation block for breadcrumb
if ( ! function_exists('generate_breadcrumb'))
{
	function generate_breadcrumb($data, $cl = '', $dp_cl = '', $current_class = '', $is_view = FALSE)
	{
		if (count($data) == 1)
		{
			return '';
		}
		$CI =& get_instance();
		$out = array('<ul class="' . $dp_cl . ((empty($cl) ? '' : ' ' . $cl)) . '">');
		foreach ($data as $key => $v)
		{
			if (is_permission_allowed($v['allow_access_user'], $CI->user_id))
			{
				$active_class = ($v['page_id'] == $CI->page_id) ? ' class="' . $current_class . '"' : '';
				if ($key == count($data) - 1)
				{
					$out[] = '<li class="current_page">' . $v['page_title'] . '</li>';
				}
				else
				{
					$out[] = '<li><a href="' . (($is_view) ? page_link() . $v['page_path'] : 'javascript:void(0)') . '"' . $active_class . '>' . $v['page_title'] . '</a></li>';
				}
				$out[] = '<li class="sz_bc_separator">&gt;</li>';
			}
		}
		array_pop($out);
		$out[] = '</ul>';

		return implode("\n", $out);
	}
}

// sitemap sort function
if ( ! function_exists('disp_asc'))
{
	function disp_asc($a, $b)
	{
		$ad = (int)$a['page']['display_order'];
		$bd = (int)$b['page']['display_order'];

		if ($ad == $bd)
		{
			return 0;
		}
		return ($ad < $bd) ? -1 : 1;
	}
}
if ( ! function_exists('disp_desc'))
{
	function disp_desc($a, $b)
	{
		$ad = (int)$a['page']['display_order'];
		$bd = (int)$b['page']['display_order'];

		if ($ad == $bd)
		{
			return 0;
		}
		return ($ad < $bd) ? 1 : -1;
	}
}
