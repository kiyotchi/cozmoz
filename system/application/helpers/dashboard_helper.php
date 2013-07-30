<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * Seezoo dashboard Utility functions
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==============================================================
 */
if ( ! function_exists('build_dashboard_menu'))
{
	function build_dashboard_menu()
	{
		$CI =& get_instance();
		$sql = 'SELECT DISTINCT PP.page_path, PV.page_title, PV.page_description, PV.parent, PV.page_id, perms.allow_access_user '
				. 'FROM page_versions as PV '
				. 'LEFT OUTER JOIN page_permissions as perms USING(`page_id`) '
				. 'RIGHT OUTER JOIN page_paths as PP USING(`page_id`) '
				. 'WHERE PP.page_path LIKE ? AND PV.display_page_level = 0 '
				. 'ORDER BY display_order ASC';

		$query = $CI->db->query($sql, array('dashboard/%'));

		$ch_sql = 'SELECT PV.page_title, PV.page_description, PV.page_id, perms.allow_access_user, PP.page_path '
				. 'FROM page_versions as PV '
				. 'RIGHT OUTER JOIN page_paths as PP USING(`page_id`) '
				. 'LEFT OUTER JOIN page_permissions as perms USING(`page_id`) '
				. 'WHERE PV.parent = ? ORDER BY PV.display_order ASC';

		$child_stack = array();
		$out = array('<ul class="sideNav">');
		foreach ($query->result_array() as $v)
		{
			// page has child?
			$query2 = $CI->db->query($ch_sql, array($v['page_id']));
			$arr = array(
				'page' 	=> $v,
				'child'	=> ($query2->num_rows() > 0) ? $query2->result_array() : FALSE
			);
			$child_stack[] = $arr;
		}
		// format HTML
		foreach ($child_stack as $key => $value)
		{
			$out[] = _build_dashboard_menu_format($value['page'], $CI);

			if ($value['child'] && ($value['page']['page_id'] == $CI->page_id || $value['page']['page_id'] == $CI->parent_id))
			{
				$out[] = '<ul>';
				foreach ($value['child'] as $v)
				{
					$out[] = _build_dashboard_menu_format($v, $CI);
					$out[] = '</li>';
				}
				$out[] = '</ul>';
			}
			$out[] = '</li>';
		}
		$out[] = '</ul>';

		return implode("\n", $out);
	}
}

if ( ! function_exists('_build_dashboard_menu_format'))
{
	function _build_dashboard_menu_format($page, $CI)
	{
		$user_data = $CI->user_data;

		// are you master user?
		if ($user_data->user_id  > 1)
		{
			// you have admin_permission?
			if ($user_data->admin_flag == 0)
			{
				// this page allow_access?
				if ( has_permission($page['allow_access_user'], $user_data->user_id) === FALSE)
				{
					return '';
				}
			}
		}

		$out[] = '<li id="dashboard_page_' . $page['page_id'] . '"><a href="' . page_link() . $page['page_path'] . '"';

		if ($page['page_id'] == $CI->page_id || $page['page_id'] == $CI->parent_id)
		{
			$out[] = ' class="active"';
		}
		$out[] = '>' . $page['page_title'] . '</a>';

		return implode('', $out);
	}
}

// create sitemap
if ( ! function_exists('create_sitemap'))
{
	function create_sitemap($page, $is_open, $level = 1, $open = FALSE)
	{
		if ($page === FALSE || !is_array($page))
		{
			return;
		}
		if ($level > 1 && !$open)
		{
			$out = array('<ul style="display:none">');
		}
		else
		{
			$out = array('<ul>');
		}

		foreach ($page as $value)
		{
			$v = $value['page'];
			$ch = $value['child'];
			$out[] = '<li id="page_' . $v->page_id . '" class="sz_sortable' . (($value['child']) ? ' ch close' : '') . '">';
			$out[] = '<div class="sz_sitemap_page movable' . (((int)$v->alias_to > 0) ? ' alias' : '') . '" pid="' . $v->page_id . '" sys="' . (int)$v->is_system_page . '">';
			$out[] = _select_tree_image($value);
			$out[] = '<span pid="' . $v->page_id . '" class="ttl">';
			$out[] = $v->page_title . '<span>' . (($ch) ? '&nbsp;(' . count($ch) . ')' : '') . '</span>';
			$out[] = '</span>';
			$out[] ='</div>';
			if ($ch)
			{
				// is opened directory?
				$open = in_array($v->page_id, $is_open);

				if ($open)
				{
					$out[] = '<a href="javascript:void(0)" class="open_dir oc">&nbsp;</a>';
				}
				else
				{
					$out[] = '<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>';
				}
				$out[] = create_sitemap($ch, $is_open, ++$level, $open);
			}
			$out[] = '</li>';
		}
		$out[] = '</ul>';
		return implode("\n", $out);
	}
}

// _select tree_image
if ( ! function_exists('_select_tree_image'))
{
	function _select_tree_image($page)
	{
		$CI =& get_instance();
		$img = '<img src="' . file_link() . 'images/';
		if ($page['child'])
		{
			$img .= 'dashboard/folder.png" class="sort_page" />';
		}
		else if ($page['page']->alias_to > 0)
		{
			$img .= 'dashboard/alias.png" />';
		}
		else if ($page['page']->is_system_page > 0)
		{
			$img .= 'dashboard/system.png" class="sort_page" />';
		}
		else
		{
			$img .= 'dashboard/file.png" class="sort_page" />';
		}

		return $img;
	}
}

// make static page tree
if ( ! function_exists('build_static_page_tree'))
{
	function build_static_page_tree($statics, $directory_path = '')
	{
		ksort($statics);
		$out[] = '<ul class="static_pages">';
		$out[] = '<li class="caption">アクセスされるページパス</li>';

		$out[] = _build_static_page_tree_childs($statics, $directory_path);

		$out[] = '</ul>';
		return implode("\n", $out);
	}
}

// make child page wrapped '<li>'
if ( ! function_exists('_build_static_page_tree_childs'))
{
	function _build_static_page_tree_childs($statics, $directory_path, $count = 0)
	{
		$out = array();
		foreach ($statics as $key => $val)
		{

			if (preg_match('/^[0-9]+$/', $key) && preg_match('/.+\.html$|.+\.php$/', $val))
			{
				$out[] = '<li' . ((++$count % 2 > 0) ? ' class="odd"' : '') . '>';
				$last_segment = preg_replace("/\.html$|\.php$/", '', $val);
				$out[] = page_link() . $directory_path . $last_segment . _check_db_page_exists($directory_path . $last_segment);
				$out[] = '</li>';
			}
			else if (is_array($val) && count($val) > 0)
			{
				$out[] = _build_static_page_tree_childs($val, $directory_path . $key . '/', $count);
			}

		}
		return implode("\n", $out);
	}
}

// check use path exists in system page
if ( ! function_exists('_check_db_page_exists'))
{
	function _check_db_page_exists($path)
	{
		$CI =& get_instance();
		$sql = 'SELECT page_path_id FROM page_paths WHERE page_path = ?';
		$query = $CI->db->query($sql, array($path));

		if ($query->num_rows() > 0)
		{
			return '<span class="notice">一般ページとページパスが重複しています</span>';
		}
		else
		{
			return '';
		}
	}
}

// Make system page tree data
if ( ! function_exists('generate_system_page_tree'))
{
	function generate_system_page_tree($p)
	{
		$out = array('<li class="ch close" id="page_' . $p['page_id'] . '">');
		$out[] = '<div class="movable" pid="' . $p['page_id'] . '">';
		$out[] = '<img src="' . file_link() . 'images/config.png" /><span pid="' . $p['page_id'] . '" class="ttl">' . $p['page_title'] . '<span>';
		if ($p['childs'])
		{
			$out[] = '&nbsp;(' . count($p['childs']) . ')';
		}
		$out[] = '</span></span>';
		$out[] = '</div>';
		if ($p['childs'])
		{
			$out[] = '<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>';
		}

		$out[] = '</li>';

		return implode("\n", $out);
	}
}

// Format form questions answers
if ( ! function_exists('format_question_answer'))
{
	function format_question_answer($form, $is_escape = TRUE)
	{
		switch ($form['question_type'])
		{
			case 'text':
				return ( $is_escape ) ? prep_str($form['answer']) : $form['answer'];
			case 'textarea':
				return ( $is_escape ) ? prep_str($form['answer_text']) : $form['answer_text'];
			case 'select':
			case 'radio':
			case 'pref':
			case 'birth_year':
			case 'month':
			case 'day':
			case 'hour':
			case 'minute':
				
				// Does special format dropdown exists?
				if ( function_exists('sz_form_build_' . $form['question_type'] . '_list') )
				{
					$form['options'] = call_user_func('sz_form_build_' . $form['question_type'] . '_list');
				}
				
				$options = explode(':', $form['options']);
				if (!$options)
				{
					$options = array($form['options']);
				}
				if ( isset($options[$form['answer']]) )
				{
					return ( $is_escape ) ? prep_str($options[$form['answer']]) : $options[$form['answer']];
				}
				return '選択無し';
			case 'checkbox':
				$options = explode(':', $form['options']);
				if (!$options)
				{
					$options = array($form['options']);
				}
				$answer = explode(':', $form['answer']);
				if (!$answer)
				{
					$answer = array($form['answer']);
				}
				$ret = array();
				foreach ($answer as $v)
				{
					if ( isset($options[$v]) )
					{
						$ret[] = $options[$v];
					}
				}
				return ( $is_escape ) ? prep_str(implode('、', $ret)) : implode('、 ', $ret);
			default:
				return '';
		}
	}
}

/**
 * build child system page
 */
if ( ! function_exists('build_chidl_page'))
{
	function build_child_page($page)
	{
		$CI =& get_instance();
		$sql =
				'SELECT '
			.		'pv.page_id, '
			.		'pv.page_title, '
			.		'pp.page_path '
			.	'FROM '
			.		'page_versions as pv '
			.	'RIGHT OUTER JOIN page_paths as pp '
			.			'USING(page_id) '
			.	'JOIN ( '
			.		'SELECT '
			.			'page_id, '
			.			'MAX(version_number) as version_number '
			.		'FROM '
			.			'page_versions '
			.		'WHERE '
			.			'is_system_page = 1 '
			.		'GROUP BY '
			.			'page_id '
			.	') AS MAXPV ON ( '
			.			'pv.page_id = MAXPV.page_id '
			.		'AND '
			.			'pv.version_number = MAXPV.version_number '
			.	') '
			.	'WHERE '
			.		'pv.is_system_page = 1 '
			.	'AND	'
			.		'pv.parent = ? '
			.	'ORDER BY '
			.		'pv.display_order ASC'
			;
		$query = $CI->db->query($sql, array((int)$page->page_id));
		
		if ( ! $query || $query->num_rows() === 0 )
		{
			return '';
		}
		$child = array('<ul>');
		
		$result = $query->result();
		foreach ( $result as $key => $value )
		{
			$html = array(
				'<li id="systempage_', $value->page_id, '">',
				'<p', ((strpos($value->page_path, 'dashboard') !== FALSE) ? ' class="dashboard_page"' : ''), '>',
				anchor($value->page_path, prep_str($value->page_title)),
				'</p>',
				'<p class="configure">',
				anchor('dashboard/pages/system_page/rescan/' . $value->page_id, '更新', 'class="view"'),
				anchor('dashboard/pages/system_page/page_config/' . $value->page_id, '設定', 'class="edit"'),
				anchor('dashboard/pages/system_page/delete/' . $value->page_id, '削除', 'class="delete"'),
				'</p>',
				'<a href="#" class="arrow_u', (($key === 0) ? ' hide' : ''), '">&nbsp;</a>',
				'<a href="#" class="arrow_d', (($key === count($result) - 1) ? ' hide' : ''), '">&nbsp;</a>',
				'</li>'
			);
			$child[] = implode('', $html);
		}
		
		$child[] = '</ul>';
		
		return implode("\n", $child) . '<a href="#" class="close"></a>';
	}
}