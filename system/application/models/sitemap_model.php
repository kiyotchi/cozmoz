<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * サイトマップ管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Sitemap_model extends Model
{
	protected $ignore_controllers = array(
		'page',
		'flint',
		'ajax',
		'gadget_ajax',
		'action',
		'install',
		'welcome'
	);

	function __construct()
	{
		parent::Model();
	}

	function get_all_page()
	{
		$parent = 1;
		$return = array();

		// get_top page
		$sql = 'SELECT '
			.		'page_id, '
			.		'page_title '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = 1 '
			.	'ORDER BY version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql);

		$return['page'] = $query->row();
		$return['child'] = $this->_get_child_page(1);

		return $return;
	}

	function _get_child_page($pid)
	{
		$sql = 'SELECT '
			.		'DISTINCT page_id, '
			.		'page_title, '
			.		'alias_to, '
			.		'is_system_page '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'parent = ? '
//			.		'AND is_system_page = 0 '
			.	'ORDER BY '
			.		'display_order ASC, '
			.		'version_number DESC'
			;
		$query = $this->db->query($sql, array((int)$pid));
		$ret = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $value)
			{
				$ret[$value->page_id]['page'] = $value;
				$ret[$value->page_id]['child'] = $this->_get_child_page($value->page_id);
			}
			return $ret;
		}
		return FALSE;
	}

	function get_page_structures_all()
	{
		// get all pages
		$sql = 'SELECT '
			.		'DISTINCT(`page_id`) page_id, '
			.		'page_title, '
			.		'display_page_level, '
			.		'parent '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'is_system_page = 0 '
			.	'ORDER BY display_order ASC'
			;
		$query = $this->db->query($sql);

		// select stack to parent pages
		$stack = array();
		$pl_stack = array();
		$ret = array();
		$max_depth = 0;

		foreach ($query->result() as $value)
		{
			if ((int)$value->page_id === 1)
			{
				// page_id = 1 is top page
				$home = $value;
				$pl_stack[1] = array(1);
				continue;
			}
			if ((int)$value->parent > 0)
			{
				echo $value->parent . "\n";
				$stack[$value->parent][] = $value;
			}
			// set page_level stacks
			if (array_key_exists($value->display_page_level + 1, $pl_stack))
			{
				$pl_stack[$value->display_page_level + 1][] = $value->page_id;
			}
			else
			{
				$pl_stack[$value->display_page_level + 1] = array($value->page_id);
			}
			$max_depth = ($max_depth < $value->display_page_level) ? $value->display_page_level : $max_depth;
		}

		// extract stacks and format display
		foreach ($pl_stack as  $key => $v)
		{
			foreach ($v as $l)
			{
				if (!array_key_exists($l, $stack))
				{
					continue;
				}
				$ret['level_' . $key][] =  $stack[$l];
			}
		}
		$ret['home'] = $home;

		return array($ret, $max_depth);
	}
	
	// フロント側のシステムページリストを取得
	function get_frontend_system_pages($is_all = FALSE)
	{
		$sql =
			'SELECT '
			.	'pv.page_id, '
			.	'pv.page_title, '
			.	'pv.parent, '
			.	'pv.is_ssl_page, '
			.	'pp.page_path '
			.'FROM page_versions as pv '
			.'RIGHT OUTER JOIN '
			.	'page_paths as pp '
			.'ON (pv.page_id = pp.page_id) '
			.'JOIN ( '
			.	'SELECT '
			.		'page_id, '
			.		'MAX(version_number) as version_number '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'is_system_page = 1 '
			.	'GROUP BY page_id '
			.') AS MAXPV ON ( '
			.		'pv.page_id = MAXPV.page_id '
			.		'AND pv.version_number = MAXPV.version_number '
			.') '
			.'WHERE '
			.	'pv.is_system_page = 1 '
			;
		if ( $is_all === FALSE )
		{
			$sql .= 
				'AND '
				.	'pv.display_page_level = 0 '
				.'AND '
				.	'pv.navigation_show = 1 ';
		}

		$sql .= 
			'AND '
			.	'pp.page_path NOT LIKE ? '
			.'ORDER BY '
			.	'display_order ASC';
		$query = $this->db->query($sql, array('dashboard/%'));
		
		return $query->result();
	}

	function get_page_structures()
	{
		// 処理が複雑化しそうなのでコメントを残していく
		$ret = array();

		// step1 : まずはトップページを取得。これはpage_id = 1で決め打ちでOK
		$sql = 'SELECT '
			.		'page_id, '
			.		'page_title, '
			.		'is_ssl_page, '
			.		'parent '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = 1 '
			.	'ORDER BY version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql);

		$ret[] = $query->row_array();

		// step2 : 次に、トップの子ページを取得し、そのページの子ページを再帰的に探索
		$sql = 'SELECT '
			.		'DISTINCT PV.page_id, '
			.		'PV.page_title, '
			.		'PV.alias_to, '
			.		'PV.external_link, '
			.		'PV.target_blank, '
			.		'PV.is_ssl_page, '
			.		'PV.parent, '
			.		'PV.display_order, '
			.		'PV.is_system_page '
			.	'FROM '
			.		'page_versions as PV '
			.	'LEFT OUTER JOIN page_paths as PP '
			.		'USING(page_id) '
			.	'WHERE '
			.		'PV.page_id > 0 '
			.		'AND PV.parent = 1 '
			.		'AND (PP.page_id IS NULL OR PP.is_enabled = 1) '
//			.		'AND is_system_page = 0 '
			.	'ORDER BY '
			.		'display_order ASC, '
			.		'version_number DESC '
			;
		$query = $this->db->query($sql);

		$ret[0]['childs'] = 0;

		$cache = array();

		if ($query->num_rows() > 0)
		{
			$ch = array();
			foreach ($query->result_array() as $value)
			{
				if (array_key_exists($value['page_id'], $cache))
				{
					continue;
				}
				$value['childs'] = $this->_has_child_page($value['page_id']);
				$ch[] = $value;
				$ret[0]['childs']++;
				$cache[$value['page_id']] = 1;
			}
			$ret['childs'] = $ch;
		}
		else
		{
			$ret['childs'] = FALSE;
		}

		//　初期は1階層まで
		return $ret;
	}

	function _has_child_page($pid)
	{
		$sql = 'SELECT '
			.		'DISTINCT PV.page_id '
			.	'FROM '
			.		'page_versions as PV '
			.	'JOIN page_paths as PP '
			.		'USING(page_id) '
			.	'WHERE '
			.		'PV.parent = ? '
			.		'AND PP.is_enabled = 1 '
//			.		'AND is_system_page = 0 '
			.	'ORDER BY '
			.		'PV.display_order ASC, '
			.		'PV.version_number DESC'
			;
		$query = $this->db->query($sql, array($pid));

		if ($query->num_rows() > 0)
		{
			return $query->num_rows();
		}
		else
		{
			return FALSE;
		}
	}

	function get_page_structures_system()
	{
		// 処理が複雑化しそうなのでコメントを残していく
		$ret = array();

		// トップの子ページを取得し、そのページの子ページを再帰的に探索
		$sql = 'SELECT '
			.		'DISTINCT(`page_id`) page_id, '
			.		'page_title, '
			.		'alias_to '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'display_page_level = 0 '
			.		'AND is_system_page = 1 '
			.	'ORDER BY display_order ASC'
			;
		$query = $this->db->query($sql);

		return $query->num_rows();
	}

	function _has_child_page_system($pid)
	{
		$sql = 'SELECT '
			.		'page_id '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'parent = ? '
			.		'AND is_system_page = 1 '
			.	'ORDER BY display_order ASC'
			;
		$query = $this->db->query($sql, array($pid));

		if ($query->num_rows() > 0)
		{
			return $query->num_rows();
		}
		else
		{
			return FALSE;
		}
	}

	function get_child_pages($pid)
	{
		// get current version

		if ($pid == 'dashboard')
		{
			$sql = 'SELECT '
				.		'DISTINCT PV.page_id, '
				.		'PV.page_title, '
				.		'PV.alias_to, '
				.		'PV.parent, '
				.		'PV.external_link, '
				.		'PV.target_blank, '
				.		'PV.display_order, '
				.		'PV.is_ssl_page '
				.	'FROM '
				.		'page_versions as PV '
				.	'LEFT OUTER JOIN page_paths as PP '
				.		'USING(page_id) '
				.	'WHERE '
				.		'PV.display_page_level = 0 '
				.		'AND PP.is_enabled = 1 '
//				.		'AND is_system_page = 1 '
				.	'ORDER BY PV.display_order ASC'
				;
		}
		else
		{
			$sql = 'SELECT '
				.		'pv.page_id, '
				.		'pv.page_title, '
				.		'pv.alias_to, '
				.		'pv.external_link, '
				.		'pv.target_blank, '
				.		'pv.parent, '
				.		'pv.display_order, '
				.		'pv.is_system_page, '
				.		'pv.is_ssl_page '
				.	'FROM '
				.		'page_versions as pv '
				.	'JOIN ( SELECT TMPPPV.page_id, MAX(version_number) as version_number '
				.			'FROM page_versions as TMPPPV '//WHERE is_system_page = 0 '
				.			'GROUP BY TMPPPV.page_id ) AS MAXPV ON ( '
				.				'pv.page_id = MAXPV.page_id '
				.				'AND pv.version_number = MAXPV.version_number) '
				.	'LEFT OUTER JOIN page_paths as pp ON ( '
				.		'pv.page_id = pp.page_id '
				.	') '
				.	'WHERE '
				.		'pv.parent = ? '
				.		'AND (pp.page_id IS NULL OR pp.is_enabled = 1) '
				.	'ORDER BY pv.display_order ASC'
				;
		}

		$query = $this->db->query($sql, array($pid));

		$cache = array();

		$ch = array();
		foreach ($query->result_array() as $value)
		{
			if (array_key_exists($value['page_id'], $cache))
			{
				continue;
			}
			if ($pid == 'dashboard')
			{
				$value['childs'] = $this->_has_child_page_system($value['page_id']);
			}
			else
			{
				$value['childs'] = $this->_has_child_page($value['page_id']);
			}
			$ch[] = $value;
			$cache[$value['page_id']] = 1;
		}

		return $ch;
	}

	function get_page_data($pid)
	{
		$sql = 'SELECT '
			.		'page_title, '
			.		'page_id '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = ? '
			.	'ORDER BY version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}

	function get_system_pages()
	{
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
			.	'AND '
			.		'pv.display_page_level = 0 '
			.	'AND '
			.		'pp.page_path LIKE ? '
			.	'ORDER BY '
			.		'pv.display_order ASC'
			;
		$query = $this->db->query($sql, array('dashboard%'));
//		echo $this->db->last_query();

		$result = $query->result();
		//usort($result, array($this, '_sort_path_slash_nums'));
		
		return $result;
	}
	
	function _sort_path_slash_nums($a, $b)
	{
		$path_a = substr_count($a->page_path, '/');
		$path_b = substr_count($b->page_path, '/');
		
		return ($path_a < $path_b) ? -1 : 1;
	}

	function get_current_approved_version($pid)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_versions as pv '
			.		'LEFT OUTER JOIN page_permissions as perms ON('
			.			'perms.page_id = pv.page_id '
			.		') '
			.		'RIGHT OUTER JOIN page_paths as pp ON('
			.			'pp.page_id = pv.page_id'
			.		') '
			.	'WHERE '
			.		'pv.page_id = ? '
			.	'ORDER BY pv.version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}
	
	function get_external_link_page($page_id)
	{
		$sql =
				'SELECT '
				.	'PV.page_title, '
				.	'PV.external_link, '
				.	'PV.target_blank, '
				.	'PV.navigation_show, '
				.	'PP.page_path_id '
				.'FROM '
				.	'page_versions as PV '
				.'LEFT OUTER JOIN page_paths as PP '
				.	'USING(page_id) '
				.'WHERE '
				.	'page_id = ? '
				.'LIMIT 1'
				;
		$query = $this->db->query($sql, array((int)$page_id));
		return ($query && $query->row()) ? $query->row() : FALSE;
	}

	function get_ci_controllers($path = '')
	{
		if ($path == '')
		{
			$controllers = directory_map(APPPATH . 'controllers/');
		}
		else
		{
			$controllers = directory_map($path);
		}
		$ret = array();

		// do scan level3
		foreach ($controllers as $key => $con)
		{
			if (is_array($con))
			{
				foreach ($con as $dir => $value)
				{
					if (is_array($value))
					{
						foreach ($value as $v)
						{
							$checked = $this->_check_ci_controller($v, $key . '/' . $dir . '/');
							if ( $checked !== FALSE)
							{
								$ret[$checked['page_path']] = $checked;
							}
						}
					}
					else
					{
						$checked = $this->_check_ci_controller($value, $key .'/');
						if ($checked !== FALSE)
						{
							$ret[$checked['page_path']] = $checked;
						}
					}
				}
			}
			else
			{
				$checked = $this->_check_ci_controller($con);
				if ($checked !== FALSE)
				{
					$ret[$checked['page_path']] = $checked;
				}
			}
		}
		return $ret;
	}

	function _check_ci_controller($controller, $dir = '')
	{
		$spt = explode('.', $controller);
		if (strtolower(end($spt)) !== 'php'
				|| ($dir == '' && in_array($spt[0], $this->ignore_controllers)) // ignore listed controller
				|| ($dir == 'dashboard/' && $spt[0] === 'page')                 // dashboard/page.php is not indexed
		)
		{
			return FALSE;
		}
		
		$classname = ($spt[0] === $this->router->default_controller)
								? ''
								: $spt[0];
		$path = rtrim($dir . $classname, '/');
		if (!$this->is_already_installed($path))
		{
			return array(
				'page_path' => $path,
				'page_title' => $classname
			);
		}
		return FALSE;
	}
	
	function _create_controllers_db($path, $schema_prefix)
	{
		// load database forge class
		$this->load->dbforge();
		
		$dbst = parse_db_schema($path, $schema_prefix);
		
		foreach ($dbst as $key => $val)
		{
			$key_field = FALSE;
			if ($this->db->table_exists($key))
			{
				continue;
			}
			
			// index stack
			$indexes = array();
			
			foreach ($val as $name => $column)
			{
				if (array_key_exists('key', $column))
				{
					$key_field = $name;
					unset($column['key']);
				}
				if ( isset($column['index']) )
				{
					$indexes[] = $column['index'];
					unset($column['index']);
				}
				$val[$name] = $column;
			}
				
			$this->dbforge->add_field($val);
				
			if ($key_field !== FALSE)
			{
				$this->dbforge->add_key($key_field, TRUE);
			}

			$ret = $this->dbforge->create_table($key, TRUE);

			if (!$ret)
			{
				return FALSE;
			}
			// merge index
			$this->_merge_db_index($key, $indexes);
		}
		
		return TRUE;
	}
	
	function _insert_initial_schema_data($path, $schema_prefix)
	{
		$records = parse_db_schema_records($path, $schema_prefix);
		
		foreach ($records as $key => $value)
		{
			if ( ! $this->db->table_exists($key))
			{
				// CodeIgniter has bug?
				// $this->dbforge->create_table('table') and $this->db->table_exists('table')
				// returns FALSE...
				//continue;
			}
			// I hope table is exist...
			foreach ($value as $record)
			{
				$ret = $this->db->insert($key, $record);
				if ( ! $ret)
				{
					return;
				}
			}
		}
	}
	
	function _update_controllers_db($path, $schema_prefix)
	{
		// load database forge class
		$this->load->dbforge();
		
		$dbst = parse_db_schema($path, $schema_prefix);
		
		foreach ($dbst as $key => $val)
		{
			// index stack
			$indexes = array();
			
			// If DB table exists, create table
			if ( ! $this->db->table_exists($key))
			{
				foreach ($val as $name => $column)
				{
					if (array_key_exists('key', $column))
					{
						$key_field = $name;
						unset($column['key']);
					}
					if ( isset($column['index']) )
					{
						$indexes[] = $column['index'];
						unset($column['index']);
					}
				}
					
				$this->dbforge->add_field($val);
					
				if (isset($key_field))
				{
					$this->dbforge->add_key($key_field, TRUE);
				}
				
				$ret = $this->dbforge->create_table($key, TRUE);
				if (!$ret)
				{
					return FALSE;
				}
				
				// merge index
				$this->_merge_db_index($key, $indexes);
			}
			// else, try modify table columns.
			else
			{
				foreach ($val as $name => $column)
				{
					if (array_key_exists('key', $column) || $name == 'key')
					{
						continue;
					}
					
					// column has index?
					if ( isset($column['index']) )
					{
						$indexes[] = $column['index'];
						unset($column['index']);
					}
					
					if ($this->db->field_exists($name, $key))
					{
						// modify
						$column['name'] = $name;
						$ret = $this->dbforge->modify_column($key, array($name => $column));
					}
					else
					{
						// create
						$ret = $this->dbforge->add_column($key, array($name => $column));
					}
					if ( !$ret)
					{
						return 'MISS';
					}
				}
				
				// merge index
				$this->_merge_db_index($key, $indexes);
			}
		}
		
		return TRUE;
	}
	
	function _merge_db_index($table, $index)
	{
		$ind = $this->get_table_indexes($table);
		if ( $ind === FALSE )
		{
			return;
		}
		
		// add index
		$bind = array();
		foreach ( $index as $column )
		{
			if ( in_array($column, $ind) )
			{
				continue;
			}
			$bind[] = $column;
		}
		
		if ( count($bind) > 0 )
		{
			$sql = sprintf('ALTER TABLE `%s` ADD INDEX ( `%s` )', $table, implode('`,`', $bind));
			$this->db->simple_query($sql);
		}
		
		// drop index
		foreach ( $ind as $column )
		{
			if ( in_array($column, $index) )
			{
				continue;
			}
			$this->db->simple_query(sprintf('ALTER TABLE `%s` DROP INDEX `%s`;', $table, $column));
		}
	}
	
	function get_table_indexes($table)
	{
		$query = $this->db->query('SHOW INDEX FROM ' . $table);
		
		if ( !$query )
		{
			return FALSE;
		}
		
		$ret = array();
		foreach ( $query->result() as $value )
		{
			$name = $value->Column_name;
			if ( ! in_array($name, $ret) && $value->Key_name !== 'PRIMARY')
			{
				$ret[] = $name;
			}
		}
		return $ret;
	}

	function _build_controllers_list(&$ret, $key, $arr)
	{
		if (is_array($arr))
		{
			$ret = $this->_build_controllers_list($ret, $key, $arr);
		}
		else
		{
			$path = $key . '/' . substr($arr, 0, strpos($arr, '.'));
			if (!$this->is_already_installed($path))
			{
				$ret[] = $path;
			}
		}
		return $ret;
	}

	function _check_enable_file($filename, $dir)
	{
		$spt = explode('.', $filename);
		if (strtolower($spt[1]) !== 'php' || ($dir == '' && in_array($spt[0], $this->ignore_controllers)))
		{
			return FALSE;
		}
		return TRUE;
	}

	function is_already_installed($page_path)
	{
		$sql = 'SELECT page_path_id FROM page_paths WHERE page_path = ? LIMIT 1';
		$query = $this->db->query($sql, array($page_path));

		if ($query->row())
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function is_alias_already_exists($parent_page_id, $alias_from_page_id)
	{
		$sql =
				'SELECT '
				.	'1 '
				.'FROM '
				.	'page_versions '
				.'WHERE '
				.	'parent = ? '
				.'AND '
				.	'alias_to = ? '
				;
		$query = $this->db->query($sql, array((int)$parent_page_id, (int)$alias_from_page_id));
		return ( $query && $query->row() ) ? TRUE : FALSE;
	}

	function insert_system_page($pp)
	{
		// preset allow_access_user_all_permission string
		$ulist = $this->_make_allow_all_user_permission();
		
		// sort by lower segments
		usort($pp, 'sort_by_segment'); // callback function defined at core_helper

		foreach ($pp as $key => $v)
		{
			// insert master page
			$r = $this->db->insert('pages', array('version_number' => 1));

			if (!$r)
			{
				return FALSE;
			}

			$pid = $this->db->insert_id();

			// get controller desription
			$page_data = $this->_get_controller_description($v);

			if (!$page_data)
			{
				continue;
			}

			$display_order = $this->_get_max_display_order($page_data['parent']);

			// insert page versions
			$data = array(
				'page_id'				=> $pid,
				'page_title'			=> $page_data['page_title'],
				'page_description'	=> $page_data['page_description'],
				'created_user_id'		=> (int)$this->session->userdata('user_id'),
				'template_id'			=> $this->_get_default_template_id($v),
				'is_public'			=> 1,
				'approved_user_id'	=> (int)$this->session->userdata('user_id'),
				'is_system_page'		=> 1,
				'parent'				=> $page_data['parent'],
				'display_page_level'	=> $page_data['display_page_level'],
				'display_order'		=> $display_order,
				'navigation_show'		=> 1,
				'is_ssl_page'			=> $page_data['is_ssl_page'],
				'meta_keyword'		=> '',
				'meta_description'	=> ''
			);

			// do insert
			$r = $this->db->insert('page_versions', $data);

			if (!$r)
			{
				return FALSE;
			}

			// add page_path
			$ppdata = array(
				'page_id'	=> $pid,
				'page_path'	=> $v
			);
			$r = $this->db->insert('page_paths', $ppdata);
			if (!$r)
			{
				return FALSE;
			}

			// add page_permission (empty data)
			$r = $this->db->insert('page_permissions', array('page_id' => $pid));
			if (!$r)
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
	function _get_default_template_id($path)
	{
		if (strpos($path, 'dashboard/') !== FALSE)
		{
			return 0;
		}
		$sql = 'SELECT default_template_id FROM site_info LIMIT 1';
		$query = $this->db->query($sql);
		
		$result = $query->row();
		return $result->default_template_id;
	}

	function _get_controller_description($page_path)
	{
		// to correct basename
		$page_path = kill_traversal($page_path);
		
		if ($pos = strrpos($page_path, '/'))
		{
			$exp = explode('/', $page_path);
			$classname = end($exp);

			// child page count by exp count
			$parent_path = $exp[0] . '/' . $exp[1];
			$page_level = ($exp[0] == 'dashboard') ? count($exp) - 2 : count($exp) - 1;
		}
		else
		{
			$classname = $page_path;
			$parent_path = FALSE;
			$page_level = 1;
		}

		$class = $pkg_class = $app_class = ucfirst($classname);
		$pkg_path = SZ_EXT_PATH . 'controllers/' . $page_path;
		$app_path = APPPATH . 'controllers/' . $page_path;
		
		if (is_dir($pkg_path))
		{
			$pkg_path .= '/' . $this->router->default_controller;
			$pkg_class = ucfirst($this->router->default_controller);
		}
		
		if (is_dir($app_path))
		{
			$app_path .= '/' . $this->router->default_controller;
			$app_class = ucfirst($this->router->default_controller);
		}
		

		if (file_exists($pkg_path.EXT))
		{
			require_once($pkg_path.EXT);
			$schema_dir = SZ_EXT_PATH;
			$class = $pkg_class;
		}
		else
		{
			require_once($app_path.EXT);
			$schema_dir = APPPATH;
			$class = $app_class;
		}

//		if (!class_exists($class))
//		{
//			return FALSE;
//		}
		
		$props = get_class_vars($class);

		if (file_exists($schema_dir . 'schemas/db/' . $page_path . '.xml'))
		{
			$db = $this->_create_controllers_db($page_path, $schema_dir);
			if (!$db)
			{
				return FALSE;
			}
		}
		
		// Does initial data schema exists?
		if (file_exists($schema_dir . 'schemas/data/' . $page_path . '.xml'))
		{
			$this->_insert_initial_schema_data($page_path, $schema_dir);
		}

		return array(
			'page_title'         => (isset($props['page_title']) && !is_null($props['page_title'])) ? $props['page_title'] : $classname,
			'page_description'   => (isset($props['page_description'])) ? $props['page_description'] : '',
			'parent'             => ($parent_path) ? $this->_get_parent_page_id($parent_path) : 1,
			'display_page_level' => $page_level,
			'is_ssl_page'        => (isset($props['ssl_page'])) ? (int)$props['ssl_page'] : 0
		);
	}

	function _get_parent_page_id($path)
	{
		$sql = 'SELECT page_id FROM page_paths WHERE page_path = ? LIMIT 1';
		$query = $this->db->query($sql, array($path));

		if ($query->row())
		{
			$result = $query->row();
			return (int)$result->page_id;
		}

		return 0;
	}

	function delete_page($pid)
	{
		// delete recursive
		$sql = 'SELECT '
			.		'pv.page_id, '
			.		'pp.page_path '
			.	'FROM '
			.		'page_versions as pv '
			.		'RIGHT OUTER JOIN page_paths as pp '
			.			'USING(page_id) '
			.	'WHERE '
			.		'parent = ? '
			.		'AND is_system_page = 0'
			;
		$query = $this->db->query($sql, array($pid));

		foreach ($query->result() as $page)
		{
			$this->_delete_recursive($page);
		}

		$this->db->where('page_id', $pid);
		$this->db->delete('page_versions');

		$this->db->where('page_id', $pid);
		$this->db->delete('page_paths');

		return TRUE;
	}

	function _delete_recursive($page)
	{
		$sql = 'SELECT '
			.		'pv.page_id, '
			.		'pp.page_path '
			.	'FROM '
			.		'page_versions as pv '
			.		'RIGHT OUTER JOIN page_paths as pp '
			.			'USING(page_id) '
			.	'WHERE '
			.		'parent = ? '
			.		'AND is_system_page = 0'
			;
		$query = $this->db->query($sql, array($page->page_id));

		foreach ($query->result() as $val)
		{
			$this->_delete_recursive($val);
		}

		$this->db->where('page_id', $page->page_id);
		$this->db->delete('page_versions');

		$this->db->where('page_id', $page->page_id);
		$this->db->delete('page_paths');
	}

	function rescan_system_page($pid)
	{
		// get Class name by path
		//$sql = 'SELECT page_path FROM page_paths WHERE page_id = ? LIMIT 1';
		$sql = 
			'SELECT '
			.	'pp.page_path, '
			.	'pp.plugin_id, '
			.	'plg.plugin_handle '
			.'FROM '
			.	'page_paths as pp '
			.'LEFT OUTER JOIN sz_plugins as plg ON ( '
			.	'plg.plugin_id = pp.plugin_id '
			.') '
			.'WHERE '
			.	'page_id = ? '
			.'LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		$result = $query->row();
		$path = $result->page_path;

		if (strpos($path, '/') !== FALSE)
		{
			$classname = substr($path, strrpos($path, '/') + 1);
		}
		else
		{
			$classname = $path;
		}

		$class = $pkg_class = $app_class = $plg_class = ucfirst($classname);
		$pkg_path = SZ_EXT_PATH . 'controllers/' . $path;
		$app_path = APPPATH . 'controllers/' . $path;
		$plg_path = SZ_PLG_PATH . (string)$result->plugin_handle . '/controllers/' . $path;
		
		if ( is_dir($plg_path) )
		{
			$plg_path .= '/' . $this->router->default_controller;
			$plg_class = ucfirst($this->router->default_controller);
		}
		
		if (is_dir($pkg_path))
		{
			$pkg_path .= '/' . $this->router->default_controller;
			$pkg_class = ucfirst($this->router->default_controller);
		}
		
		if (is_dir($app_path))
		{
			$app_path .= '/' . $this->router->default_controller;
			$app_class = ucfirst($this->router->default_controller);
		}
		

		if (file_exists($pkg_path.EXT))
		{
			require_once($pkg_path.EXT);
			$schema_dir = SZ_EXT_PATH;
			$class = $pkg_class;
		}
		else if ( file_exists($plg_path.EXT) )
		{
			require_once($plg_path.EXT);
			$schema_dir = SZ_PLG_PATH . $result->plugin_handle . '/';
			$class = $plg_class;
		}
		else if (file_exists($app_path.EXT))
		{
			require_once($app_path.EXT);
			$schema_dir = APPPATH;
			$class = $app_class;
		}
		else 
		{
			return FALSE;
		}

//		if (file_exists(SZ_EXT_PATH . 'controllers/' . $path.EXT))
//		{
//			require_once(SZ_EXT_PATH . 'controllers/' . $path.EXT);
//			$schema_dir = SZ_EXT_PATH;
//		}
//		else if (file_exists(APPPATH . 'controllers/' . $path . EXT))
//		{
//			require_once(APPPATH . 'controllers/' . $path . EXT);
//			$schema_dir = APPPATH;
//			
//		}
//		else
//		{
//			return FALSE;
//		}

		if (!class_exists($class))
		{
			return FALSE;
		}

		$props = get_class_vars($class);
		
		if (file_exists($schema_dir . 'schemas/db/' . $path . '.xml'))
		{
			$db = $this->_update_controllers_db($path, $schema_dir);
			if (!$db)
			{
				return FALSE;
			}
		}
		
		// notice no insert default data on update!
		$page_title	= (isset($props['page_title']))
											? $props['page_title']
											: $class;
		$page_description = (isset($props['page_description']))
														? $props['page_description']
														: '';
		$template_id = $this->_get_default_template_id($path);

		$sql = 'UPDATE '
			.		'page_versions '
			.	'SET '
			.		'page_title = ?, '
			.		'page_description = ?, '
			. 	'template_id = ? '
			.	'WHERE '
			.		'page_id = ? '
			.	'ORDER BY version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query(
										$sql,
										array(
											$page_title,
											$page_description,
											$template_id,
											$pid
										)
									);

		if ($query)
		{
			return (isset($db)) ? 'DB_WITH' : TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function move_page($from, $to)
	{
		// get max display order
		$sql = 'SELECT '
			.		'display_page_level '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = ? '
			.	'ORDER BY version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($to));

		// merge new page path
		$page_path = $this->_merge_page_path($from, $to);

		$result = $query->row();
		$od = $this->_get_max_display_order($to);
		$dpl = $result->display_page_level;

		$data = array('parent' => $to, 'display_order' => (int)$od , 'display_page_level' => (int)$dpl + 1);

		$this->db->where('page_id', $from);
		// update page_version
		$ret1 = $this->db->update('page_versions', $data);
		// update page_path
		$this->db->where('page_id', $from);
		$ret2 = $this->db->update('page_paths', array('page_path' => $page_path));
		
		$this->_fix_strict_child_page_path($from, $page_path);
		
		return ($ret1 && $ret2) ? TRUE : FALSE;
	}
	
	function _fix_strict_child_page_path($from, $parent_path)
	{
		// pre : childs exists
		$sql = 'SELECT page_id FROM page_versions WHERE parent = ?';
		$query = $this->db->query($sql, array((int)$from));
		if ( ! $query || $query->num_rows() === 0 )
		{
			return;
		}
		$sql =
				'SELECT '
				.	'PV.page_id, '
				.	'PP.page_path '
				.'FROM '
				.	'page_versions as PV '
				.'LEFT JOIN page_paths as PP ON ('
				.	'PV.page_id = PP.page_id '
				.') '
				.'JOIN ( '
				.	'SELECT '
				.		'MPV.page_id,'
				.		'MAX(MPV.version_number) as version_number '
				.	'FROM '
				.		'page_versions as MPV '
				.	'JOIN ( '
				.		'SELECT '
				.			'page_id '
				.		'FROM '
				.			'page_versions '
				.		'WHERE '
				.			'parent = ? '
				.		'AND '
				.			'is_system_page = 0 '
				.	') as PPV ON ('
				.		'MPV.page_id = PPV.page_id '
				.	') '
				.') as MAXPV ON ( '
				.	'PV.version_number = MAXPV.version_number '
				.'AND '
				.	'PV.page_id = MAXPV.page_id '
				.') ';
				
		$query = $this->db->query($sql, array((int)$from));
		if ( $query && $query->num_rows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				$exp = explode('/', $row->page_path);
				$new_path = $parent_path . '/' . end($exp);
				echo $new_path;
				$this->db->where('page_id', $row->page_id);
				$this->db->update('page_paths', array('page_path' => $new_path));
				// do recursive!
				$this->_fix_strict_child_page_path($row->page_id, $new_path);
			}
		}
				
	}
	
	function _merge_page_path($f, $t)
	{
		$sql = 'SELECT '
						.	'PP.page_path, '
						.	'PV.is_system_page '
						.'FROM '
						.	'page_paths as PP '
						.'JOIN ( '
						.		'SELECT '
						.			'page_id, '
						.			'is_system_page '
						.		'FROM '
						.			'page_versions '
						.	') as PV ON ('
						.		'PV.page_id = PP.page_id '
						.') '
						.'WHERE '
						.	'PP.page_id = ? '
						.'LIMIT 1';
		$query = $this->db->query($sql, array($f));
		$from = $query->row();
		
		if ($from->is_system_page > 0)
		{
			return $from->page_path;
		}
		
		$from_path = $from->page_path;
		// merge to new path
		$split_path = substr($from_path, (int)strrpos($from_path, '/'));

		if ( $t == 1 )
		{
			// case toppage
			return trim($split_path, '/');
		}
		$query = $this->db->query($sql, array($t));
		$to = $query->row();
		$to_path = $to->page_path;
		
		// merge to new path
		//$split_path = substr($from_path, (int)strrpos($from_path, '/'));
		return trim($to_path, '/') . '/' . trim($split_path, '/');
	}

	function move_page_order($from_pid, $to_pid, $method = 'upper')
	{
		// from page_state
		$sql = 'SELECT '
			.		'display_order, '
			.		'version_number '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = ? '
			.	'ORDER BY version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($from_pid));

		$from = $query->row();

		$query_2 = $this->db->query($sql, array($to_pid));

		$to = $query_2->row();

		$f_o = $to->display_order;
		// guard process
		// if same display level, fix order
		if ($from->display_order == $to->display_order)
		{
			if ($method == 'upper')
			{
				$f_o = ($f_o - 1 >= 0) ? $f_o-- : 0;
			}
			else // lower
			{
				$f_o++;
			}
		}
		// update from <=> to
		$this->db->where('page_id', $from_pid);
		$this->db->where('version_number', $from->version_number);
		$ret1 = $this->db->update('page_versions', array('display_order' => $f_o));

		$this->db->where('page_id', $to_pid);
		$this->db->where('version_number', $to->version_number);
		$ret2 = $this->db->update('page_versions', array('display_order' => $from->display_order));

		if ($ret1 && $ret2)
		{
			return TRUE;
		}
		return FALSE;
	}

	function do_sort_display_order($master, $order = array())
	{
		foreach ($order as $value)
		{
			$exp = explode(':', $value);
			if ($exp !== FALSE)
			{
				$this->db->where(array('parent' => $master, 'page_id' => $exp[0]));
				$res = $this->db->update('page_versions', array('display_order' => $exp[1]));
				if (!$res)
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	function copy_page($from, $to, $alias = FALSE, $uid = 0, $deep_copy = FALSE)
	{
		//get current version
		$current_v    = $this->get_current_version($to);
		$current_from = $this->get_current_version($from);
		
		// if create alias, alias page already exists?
		if ( $alias && $this->is_alias_already_exists($to, $from) )
		{
			return 'already';
		}

		// get from data
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_versions as PV '
			.	'RIGHT JOIN page_paths as PP ON ( '
			.		'PV.page_id = PP.page_id '
			.	') '
			.	'LEFT OUTER JOIN page_permissions as PERM ON ( '
			.		'PV.page_id = PERM.page_id '
			.	') '
			.	'WHERE '
			.		'PV.page_id = ? '
			.		'AND PV.version_number = ? '
			.	'LIMIT 1'
			;
		$query       = $this->db->query($sql, array($from, $current_from));
		$from_data   = $query->row();
		$page_fields = $this->db->list_fields('page_versions');

		// pre:insert alias page
		$this->db->insert('pages', array('version_number' => 1));
		$pid = (int)$this->db->insert_id();
		
		// clone fileds data
		$pv_data = array();
		foreach ( $page_fields as $field )
		{
			$pv_data[$field] = (isset($from_data->{$field})) ? $from_data->{$field} : 0;
		}
		// delete 
		unset($pv_data['page_version_id']);
		
		// override master data
		$pv_data['page_id']            = $pid;
		$pv_data['version_number']     = 1;
		$pv_data['parent']             = $to;
		$pv_data['is_public']          = 1;
		$pv_data['display_order']      = (int)$this->_get_max_display_order($to);
		$pv_data['display_page_level'] = $this->_get_display_page_level($to, $current_v);
		$pv_data['version_date']       = date('Y-m-d H:i:s', time());
		$pv_data['public_datetime']    = '0000-00-00 00:00:00';
		$pv_data['approved_user_id']   = 0;
		$pv_data['created_user_id']    = $uid;
		
		if ( $alias )
		{
			$pv_data['version_comment'] = 'page_alias';
			$pv_data['alias_to']        = $from;
			return $this->db->insert('page_versions', $pv_data);
		}
		else
		{
			$pv_data['version_comment'] = 'copied from ' . $from_data->page_title;
		}
		$ret = $this->db->insert('page_versions', $pv_data);
		
		if ( ! $ret )
		{
			// delete inserted page
			$this->db->where('page_id', $pid);
			$this->db->delete('pages');
			return FALSE;
		}
		
		// build new page_path
		$to_page       = $this->_get_page_path($to);
		$to_page_path  = rtrim($to_page->page_path, '/') . '/';
		$current_path  = explode('/', $from_data->page_path);
		$new_page_path = $to_page_path . end($current_path);
		// get no-used page page with "_copy" suffix.
		while ( $this->_is_page_path_already_exists($new_page_path) )
		{
			$new_page_path .= '_copy';
		}
//		do
//		{
//			$new_page_path .= '_copy';
//		}
//		while ( $this->_is_page_path_already_exists($new_page_path) );
		
		// create page_path
		$ret = $this->db->insert(
								'page_paths',
								array(
									'page_id'   => $pid,
									'page_path' => $new_page_path
								)
							);
		if ( ! $ret )
		{
			// delete inserted page
			$this->db->where('page_id', $pid);
			$this->db->delete('pages');
			$this->db->where('page_id', $pid);
			$this->db->delete('page_versions');
			return FALSE;
		}
		
		// create page_permission
		$ret = $this->db->insert(
								'page_permissions',
								array(
									'page_id'            => $pid,
									'allow_access_user'  => $from_data->allow_access_user,
									'allow_edit_user'    => $from_data->allow_edit_user,
									'allow_approve_user' => $from_data->allow_approve_user
								)
							);
		
		if ( ! $ret )
		{
			// delete inserted page
			$this->db->where('page_id', $pid);
			$this->db->delete('pages');
			$this->db->where('page_id', $pid);
			$this->db->delete('page_versions');
			$this->db->where('page_id', $pid);
			$this->db->delete('page_paths');
			return FALSE;
		}
		
		// duplicate area
		$query = $this->db->query('SELECT * FROM areas WHERE page_id = ?', array($from));

		$as        = array();
		$asb_stack = array();
		$date      = db_datetime();
		foreach ($query->result_array() as $a_value)
		{
			$as[] = $a_value['area_id'];
			$data = array(
						'area_name'    => $a_value['area_name'],
						'created_date' => $date,
						'page_id'      => $pid
			);
			$this->db->insert('areas', $data);
			$asb_stack[$a_value['area_id']] = (int)$this->db->insert_id();
		}

		// duplicate block
		$sql = 'SELECT DISTINCT * FROM block_versions WHERE version_number = ?';

		// if area exists, ad sql WHERE IN.
		if (count($as) > 0)
		{
			$sql .= ' AND area_id IN (' . implode(',', $as) . ')';
		}

		$query = $this->db->query($sql, array((int)$current_from));

		foreach ($query->result_array() as $value)
		{
			if ( ! in_array($value['area_id'], $as))
			{
				continue;
			}
			//duplicate block
			$block = $this->load->block(
										$value['collection_name'],
										( (int)$value['slave_block_id'] > 0 ) ? $value['slave_block_id'] : $value['block_id'],
										TRUE
									);
			$bid = $block->duplicate();
			$data = array(
				'block_id'        => $bid,
				'collection_name' => $value['collection_name'],
				'area_id'         => $asb_stack[$value['area_id']],
				//'area_version_id'		=> $asb_stack[$value['area_id']],
				'display_order'   => $value['display_order'],
				'is_active'       => $value['is_active'],
				'version_date'    => $date,
				'version_number'  => 1,
				'ct_handle'       => $value['ct_handle'],
				'slave_block_id'  => $value['slave_block_id']
			);
			$this->db->insert('block_versions', $data);
		}
		
		// Is deep copy?
		if ( $deep_copy )
		{
			$sql =
					'SELECT '
					.	'page_id '
					.'FROM '
					.	'page_versions '
					.'WHERE '
					.	'parent = ? '
					.'GROUP BY page_id'
					;
			$query = $this->db->query($sql, array($from));
			if ( $query && $query->num_rows() > 0 )
			{
				foreach ( $query->result() as $child )
				{
					$this->copy_page($child->page_id, $pid, FALSE, $uid, $deep_copy);
				}
			}
		}
		return TRUE;

	}

	function copy_page_same($from_pid, $to_pid, $uid, $recursive = FALSE)
	{
		$date = db_datetime();

//		// create new-copyed page path not exists
//		$new_page_path = $this->_get_page_path($from_pid)->page_path;
//		do
//		{
//			$new_page_path .= '_copy';
//		}
//		while ( $this->_is_page_path_already_exists($new_page_path) );
//
//		// preinsert alias page
//		$this->db->insert('pages', array('version_number' => 1));
//		
//		$new_pid      = $this->db->insert_id();
		//get current version
		$current_v    = $this->get_current_version($to_pid);
		$current_from = $this->get_current_version($from_pid);

		// get from data
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_versions as PV '
			.	'RIGHT JOIN page_paths as PP ON ( '
			.		'PV.page_id = PP.page_id '
			.	') '
			.	'LEFT OUTER JOIN page_permissions as PERM ON ( '
			.		'PV.page_id = PERM.page_id '
			.	') '
			.	'WHERE '
			.		'PV.page_id = ? '
			.		'AND PV.version_number = ? '
			.	'LIMIT 1'
			;
		$query       = $this->db->query($sql, array($from_pid, $current_from));
		$from_data   = $query->row();
		$page_fields = $this->db->list_fields('page_versions');

		// pre:insert alias page
		$this->db->insert('pages', array('version_number' => 1));
		$new_pid = (int)$this->db->insert_id();
		
		// clone fileds data
		$pv_data = array();
		foreach ( $page_fields as $field )
		{
			$pv_data[$field] = (isset($from_data->{$field})) ? $from_data->{$field} : 0;
		}
		// and delete primary key 
		unset($pv_data['page_version_id']);
		
		// override master data
		$pv_data['page_id']            = $new_pid;
		$pv_data['version_number']     = 1;
		$pv_data['parent']             = $to_pid;
		$pv_data['is_public']          = 1;
		$pv_data['display_order']      = (int)$this->_get_max_display_order($to_pid);
		$pv_data['display_page_level'] = $this->_get_display_page_level($to_pid, $current_v);
		$pv_data['version_date']       = $date;
		$pv_data['public_datetime']    = '0000-00-00 00:00:00';
		$pv_data['approved_user_id']   = 0;
		$pv_data['created_user_id']    = $uid;
		$pv_data['page_title']        .= '～コピー～';
		
		$ret = $this->db->insert('page_versions', $pv_data);
		
		if ( ! $ret )
		{
			return FALSE;
		}
		
		// get to page path
		//$to_page_path = $to_page->page_path;
		// get no-used page page with "_copy" suffix.
		$new_page_path = $from_data->page_path;
		do
		{
			$new_page_path .= '_copy';
		}
		while ( $this->_is_page_path_already_exists($new_page_path) );
		
		// create page_path
		$ret = $this->db->insert(
								'page_paths',
								array(
									'page_id'   => $new_pid,
									'page_path' => $new_page_path
								)
							);
		if ( ! $ret )
		{
			// rollback (delete) inserted page
			$this->db->where('page_id', $new_pid);
			$this->db->delete('pages');
			return FALSE;
		}
		
		// create page_permission
		$ret = $this->db->insert(
								'page_permissions',
								array(
									'page_id'            => $new_pid,
									'allow_access_user'  => $from_data->allow_access_user,
									'allow_edit_user'    => $from_data->allow_edit_user,
									'allow_approve_user' => $from_data->allow_approve_user
								)
							);
								
		if ( ! $ret )
		{
			// rollback (delete) inserted page
			$this->db->where('page_id', $new_pid);
			$this->db->delete('pages');
			$this->db->where('page_id', $new_pid);
			$this->db->delete('page_paths');
			return FALSE;
		}
		
		// duplicate area
		$query = $this->db->query('SELECT * FROM areas WHERE page_id = ?', array($from_pid));

		$as        = array();
		$asb_stack = array();
		$date      = db_datetime();
		foreach ( $query->result_array() as $a_value )
		{
			$as[] = $a_value['area_id'];
			$data = array(
						'area_name'    => $a_value['area_name'],
						'created_date' => $date,
						'page_id'      => $new_pid
			);
			$this->db->insert('areas', $data);

			$asb_stack[$a_value['area_id']] = (int)$this->db->insert_id();
		}

		// duplicate block
		$sql = 'SELECT DISTINCT * FROM block_versions WHERE version_number = ?';

		// if area exists, ad sql WHERE IN.
		if ( count($as) > 0 )
		{
			$sql .= ' AND area_id IN (' . implode(',', $as) . ')';
		}

		$query = $this->db->query($sql, array((int)$current_from));

		foreach ( $query->result_array() as $value )
		{
			if ( ! in_array($value['area_id'], $as) )
			{
				continue;
			}
			//duplicate block
			$block = $this->load->block(
										$value['collection_name'],
										( (int)$value['slave_block_id'] > 0 ) ? $value['slave_block_id'] : $value['block_id'],
										TRUE
									);
			$bid  = $block->duplicate();
			$data = array(
				'block_id'        => $bid,
				'collection_name' => $value['collection_name'],
				'area_id'         => $asb_stack[$value['area_id']],
				//'area_version_id'		=> $asb_stack[$value['area_id']],
				'display_order'   => $value['display_order'],
				'is_active'       => $value['is_active'],
				'version_date'    => $date,
				'version_number'  => 1,
				'ct_handle'       => $value['ct_handle'],
				'slave_block_id'  => $value['slave_block_id']
			);
			$this->db->insert('block_versions', $data);
		}
		
		// Does copy child page recursive?
		if ( $recursive )
		{
			// get child pages
			$sql =
					'SELECT '
					.	'page_id '
					.'FROM '
					.	'page_versions as PV '
					.'WHERE '
					.	'parent = ? '
					.'GROUP BY page_id '
					;
			$query = $this->db->query($sql, array($from_pid));
			
			if ( $query && $query->num_rows() > 0 )
			{
				foreach ( $query->result() as $child )
				{
					$this->copy_page($child->page_id, $new_pid, FALSE, $uid, TRUE);
				}
			}
		}
		return array('page_id' => $new_pid, 'page_title' => $pv_data['page_title']);
	}

	function _is_page_path_already_exists($page_path)
	{
		$sql =
			"SELECT "
			.		"1 "
			.	"FROM "
			.		"page_paths "
			.	"WHERE "
			.		"page_path = ? "
			.	"LIMIT 0, 1"
			;
		$query = $this->db->query(
			$sql,
			array($page_path)
		);

		if ( $query->num_rows() > 0 )
		{
			return true;
		}

		return false;
	}

	function _get_page_path($pid)
	{
		$sql = 'SELECT page_path FROM page_paths WHERE page_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}

	function _make_allow_all_user_permission()
	{
		$sql = 'SELECT user_id FROM users';
		$query = $this->db->query($sql);

		foreach ($query->result() as $v)
		{
			$ret[] = $v->user_id;
		}

		return ':' . implode(':', $ret) . ':';
	}

	function get_current_version($pid)
	{
		$sql = 'SELECT '
			.		'MAX(version_number) as mv '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		$result = $query->row();
		return $result->mv;
	}
	
	function _get_display_page_level($page_id, $vid)
	{
		$sql =
					'SELECT '
					.	'display_page_level '
					.'FROM '
					.	'page_versions '
					.'WHERE '
					.		'page_id = ? '
					.		'AND version_number = ? '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$page_id, (int)$vid));
		$result = $query->row();
		
		return ((int)$result->display_page_level === 0) ? 1 : (int)$result->display_page_level;
	}

	function _get_max_display_order($pid)
	{
		$sql = 'SELECT '
			.		'MAX(`display_order`) as m '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'parent = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		$result = $query->row();
		return (int)$result->m + 1;
	}

	function get_max_display_level_all()
	{
		$sql = 'SELECT '
			.		'MAX(display_page_level) as dpl '
			.	'FROM '
			.		'page_versions '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql);

		$result = $query->row();
		return $result->dpl;
	}

	// get navigation array recursive
	function get_auto_navigation_data($arr, $sub_level, $order_by = 1, $view = FALSE)
	{
		$ret = array();
		$base = FALSE;
		if ($arr['show_base_page'])
		{
			$bind = array((int)$arr['page_id'], (int)$arr['page_id'], $sub_level);
		}
		else
		{
			$bind = array((int)$arr['page_id'], $sub_level);
		}
		// get base page and child pages
		if ($view)
		{
			$sql =
				'SELECT '
				.	'TMPPV.page_id '
				.	', TMPPV.page_title '
				.	', TMPPV.display_order ' 
				.	', TMPPV.is_ssl_page '
				.	', TMPPV.external_link '
				.	', TMPPV.target_blank '
				.	', PP.page_path '
				.	', PM.allow_access_user '
				.'FROM '
				.	'( '
				.		'SELECT '
				.			'CASE '
				.				'WHEN PV.alias_to > 0 THEN PV.alias_to '
				.				'ELSE PV.page_id '
				.			'END AS page_id, '
				.			'PV.page_title, '
				.			'PV.display_order, '
				.			'PV.is_ssl_page, '
				.			'PV.external_link, '
				.			'PV.target_blank '
				.		'FROM '
				.			'page_versions as PV '
				.			'JOIN ('
				.				'SELECT '
				.					'page_id, '
				.					'MAX(version_number) AS version_number '
				.				'FROM '
				.					'page_versions '
				.				'WHERE '
				.					'('
				.						'parent = ? ';
			if ($arr['show_base_page'] > 0)
			{
				$sql .= '				OR page_id = ? ';
			}

			$sql .=					') '
				.					'AND display_page_level <= ? '
				.				'GROUP BY page_id '
				.			') AS MAXPV ON ('
				.				'PV.page_id = MAXPV.page_id '
				.				'AND PV.version_number = MAXPV.version_number '
				.			') '
				.		'WHERE '
				.			'PV.navigation_show = 1 '
				.	') AS TMPPV '
				.	' LEFT OUTER JOIN page_paths AS PP ON ('
				.		'TMPPV.page_id = PP.page_id '
				.	') '
				.	' LEFT OUTER JOIN page_permissions as PM ON ( '
				.		'TMPPV.page_id = PM.page_id '
				.	') '
				.	'WHERE PP.is_enabled = 1 '
				;
		}
		else
		{
			$sql =
				'SELECT '
				.	'CASE '
				.		'WHEN PV.alias_to > 0 THEN PV.alias_to '
				.		'ELSE PV.page_id '
				.	'END AS page_id, '
				.	'PV.page_title, '
				.	'PV.display_order, '
				.	'PV.is_ssl_page, '
				.	'PV.external_link, '
				.	'PV.target_blank, '
				.	'PM.allow_access_user '
				.'FROM '
				.		'page_versions as PV '
				.		'JOIN ('
				.			'SELECT '
				.				'page_id, '
				.				'MAX(version_number) AS version_number '
				.			'FROM '
				.				'page_versions '
				.			'WHERE '
				.				'('
				.					'parent = ? ';
			if ($arr['show_base_page'] > 0)
			{
				$sql .=				'OR page_id = ? ';
			}

			$sql .= '			) '
				.				'AND display_page_level <= ? '
				.				'GROUP BY page_id '
				.		') AS MAXPV ON ('
				.			'PV.page_id = MAXPV.page_id '
				.			'AND PV.version_number = MAXPV.version_number '
				.	'	) '
				.		'LEFT OUTER JOIN page_permissions as PM ON ( '
				.			'PV.page_id = PM.page_id '
				.		') '
				.		'LEFT OUTER JOIN page_paths as PP ON ( '
				.			'PV.page_id = PP.page_id '
				.	') '
				.'WHERE '
				.	'PV.navigation_show = 1 '
				.'AND '
				.	'PP.is_enabled = 1 '
				;
		}
		$query = $this->db->query($sql, $bind);

		if ( ! $query || $query->num_rows() === 0 )
		{
			return FALSE;
		}

		foreach ($query->result_array() as $value)
		{
			if ($value['page_id'] == $arr['page_id'])
			{
				$base = array('page' => $value, 'child' => FALSE);
			}
			else
			{
				$v['page'] = $value;
				$v['child'] = $this->get_auto_navigation_data_child($value, $sub_level, $order_by, $view);
				$ret[$value['page_title']] = $v;
			}
		}

		if (count($ret) === 0)
		{
			return array($base);
		}
		switch($order_by)
		{
		case 1:
			usort($ret, 'disp_asc');
			if ($base)
			{
				array_unshift($ret, $base);
			}
			break;
		case 2:
			usort($ret, 'disp_desc');
			if ($base)
			{
				array_push($ret, $base);
			}
			break;
		case 3:
			ksort($ret, SORT_STRING);
			if ($base)
			{
				array_unshift($ret, $base);
			}
			break;
		case 4:
			ksort($ret, SORT_STRING);
			if ($base)
			{
				array_push($ret, $base);
			}
			break;
		}
		return $ret;
	}

	function get_auto_navigation_data_child($arr, $sub_level, $order_by = 1, $view = FALSE)
	{
		$ret = array();
		// get base page and child pages
		if ($view)
		{
			$sql = 'SELECT '
				.		'DISTINCT pv.page_id, '
				.		'pv.page_title, '
				.		'pv.display_order, '
				.		'pv.is_ssl_page, '
				.		'pv.external_link, '
				.		'pv.target_blank, '
				.		'pp.page_path, '
				.		'pp.is_enabled, '
				.		'PM.allow_access_user '
				.	'FROM '
				.		'page_versions as pv '
				.	'RIGHT OUTER JOIN page_paths as pp '
				.		'USING(page_id) '
				.	'LEFT OUTER JOIN page_permissions as PM ON ( '
				.		'pv.page_id = PM.page_id '
				.	') '
				.	'WHERE '
				.			'pv.parent = ? '
				.			'AND pv.navigation_show = 1 '
				.			'AND pv.display_page_level <= ? '
				.			'AND pp.is_enabled = 1 '
				.	'ORDER BY pv.version_number DESC'
				;
		}
		else
		{
			$sql = 'SELECT '
				.		'DISTINCT pv.page_id, '
				.		'pv.page_title, '
				.		'pv.display_order, '
				.		'pv.is_ssl_page ,'
				.		'pv.external_link ,'
				.		'pv.target_blank ,'
				.		'PM.allow_access_user '
				.	'FROM '
				.		'page_versions as pv '
				.	'LEFT OUTER JOIN page_permissions as PM ON ( '
				.		'pv.page_id = PM.page_id '
				.	') '
				.	'WHERE '
				.		'parent = ? '
				.		'AND navigation_show = 1 '
				.		'AND display_page_level <= ? '
				.	'ORDER BY version_number DESC'
				;
		}
		$query = $this->db->query($sql, array((int)$arr['page_id'], $sub_level));

		foreach ($query->result_array() as $value)
		{
			$v['page'] = $value;
			$v['child'] = $this->get_auto_navigation_data_child($value, $sub_level, $order_by, $view);
			$ret[] = $v;
		}

		if (count($ret) === 0)
		{
			return FALSE;
		}

		switch($order_by)
		{
		case 1:
			usort($ret, 'disp_asc');
			break;
		case 2:
			usort($ret, 'disp_desc');
			break;
		case 3:
			ksort($ret, SORT_STRING);
			break;
		case 4:
			ksort($ret, SORT_STRING);
			break;
		}
		return $ret;
	}

	// get navigation breadcrumb
	function get_navigation_breadcrumb($cpid = 1, $view = FALSE)
	{
		// get current data
		$sql = 'SELECT '
			.		'pv.parent, '
			.		'pv.page_id, '
			.		'pv.page_title, '
			.		'pv.is_ssl_page, '
			.		'pv.navigation_show, '
			.		'PERM.allow_access_user, '
			.		'pp.page_path '
			.	'FROM '
			.		'page_versions as pv '
			.	'RIGHT OUTER JOIN page_paths as pp '
			.			'USING(page_id) '
			.	'LEFT OUTER JOIN ('
			.		'SELECT '
			.			'page_id, '
			.			'allow_access_user '
			.		'FROM '
			.			'page_permissions '
			.		'WHERE '
			.			'page_id = ? '
			.	') as PERM ON ( '
			.		'PERM.page_id = pv.page_id '
			.	') '
			.	'WHERE '
			.		'pv.page_id = ? '
			.		'AND pv.navigation_show = 1 '
			.	'ORDER BY pv.version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($cpid, $cpid));

		$result = $query->row_array();
		$ret = array($result);

		if ($cpid == 1)
		{
			return $ret;
		}

		$this->_recursive_breadcrumb($ret);

		return array_reverse($ret);
	}

	// recursive breadcrumb
	function _recursive_breadcrumb(&$ret)
	{
		$last = end($ret);
		if ( ! $last || $last['parent'] == 0 )
		{
			return;
		}
		$sql =
			'SELECT '
			.	'PV.page_id, '
			.	'PV.page_title, '
			.	'PV.parent, '
			.	'PV.navigation_show, '
			.	'PV.is_ssl_page, '
			.	'PERM.allow_access_user, '
			.	'PP.page_path '
			.	'FROM page_versions AS PV '
			.	'INNER JOIN '
			.		'page_paths AS PP ON ('
			.		'PV.page_id = PP.page_id '
			.	') '
			.	'LEFT OUTER JOIN ('
			.		'SELECT '
			.			'page_id, '
			.			'allow_access_user '
			.		'FROM '
			.			'page_permissions '
			.		'WHERE '
			.			'page_id = ? '
			.	') as PERM ON ( '
			.		'PERM.page_id = PV.page_id '
			.	') '
			.	'WHERE '
			.		'PV.page_id = ? '
			.	'ORDER BY PV.version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($last['parent'], $last['parent']));

		if ($query->num_rows() > 0)
		{
			$result = $query->row_array();
			$ret[] = $result;
			$this->_recursive_breadcrumb($ret);
		}
		else
		{
			return;
		}
	}

	// page search
	function search_page($pt = '', $pp = '', $frontend_only = FALSE)
	{
		$bind = array('%' . $pt . '%', '%' . $pp . '%');
		$sql = 'SELECT '
			.		'DISTINCT(pv.page_id), '
			.		'pv.page_title '
			.	'FROM '
			.		'page_versions as pv '
			.		'RIGHT OUTER JOIN page_paths as pp '
			.			'USING(page_id) '
			.	'WHERE 1 '
			.		'AND pv.page_title LIKE ? '
			.		'AND pp.page_path LIKE ? '
			.		'AND pv.alias_to = 0 '
			.		"AND (pv.external_link = '' OR pv.external_link IS NULL ) ";
		if ( $frontend_only )
		{
			$bind[] = 'dashboard/%';
			$sql   .= 'AND pp.page_path NOT LIKE ? ' ;
		}
		$sql .=	'ORDER BY '
			.		'pv.version_number DESC, '
			.		'pv.page_id ASC'
			;
		// set bind query
		$query = $this->db->query($sql, $bind);

		if ($query->num_rows() > 0)
		{
			$stack = array(); // guard distinct array
			foreach ($query->result() as $p)
			{
				if (!isset($stack[$p->page_id]))
				{
					$ret[] = $p;
					$stack[$p->page_id] = 1;
				}
			}
			return $ret;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * システムページ表示順の変更
	 */
	function move_system_page($from_page, $to_page)
	{
		// prepare SQL
		$sql =
					'SELECT '
					.	'pv.page_version_id, '
					.	'pv.page_id, '
					.	'pv.display_order '
					.'FROM '
					.	'page_versions as pv '
					.'JOIN ( '
					.	'SELECT '
					.		'page_id, '
					.		'MAX(version_number) as version_number '
					.	'FROM '
					.		'page_versions '
					.	'WHERE '
					.		'is_system_page = 1 '
					.	'GROUP BY '
					.		'page_id '
					.') AS MAXPV ON ( '
					.		'pv.page_id = MAXPV.page_id '
					.	'AND '
					.		'pv.version_number = MAXPV.version_number '
					.	') '
					.'WHERE '
					.	'pv.page_id = ? '
					.'LIMIT 1';
					
		$update_sql =
				'UPDATE '
				.	'page_versions '
				.'SET '
				.	'display_order = ? '
				.'WHERE '
				.	'page_version_id = ?';
		
		// get page info
		$query = $this->db->query($sql, array($from_page));
		if ( !$query || !$query->row() )
		{
			echo $this->db->last_query();
			return FALSE;
		}
		$from = $query->row();
		
		$query = $this->db->query($sql, array($to_page));
		if ( !$query || !$query->row() )
		{
			echo $this->db->last_query();
			return FALSE;
		}
		$to = $query->row();
		
		//and update!
		$ret1 = $this->db->query($update_sql, array((int)$to->display_order, (int)$from->page_version_id));
		$ret2 = $this->db->query($update_sql, array((int)$from->display_order, (int)$to->page_version_id));
		
		return ( $ret1 && $ret2 ) ? TRUE : FALSE;
	}
	
	/**
	 * SSL対象のページであるかどうかをページパスから判定
	 * @param string $page_@path
	 * @return bool
	 */
	function is_ssl_page_path($page_path)
	{
		$sql =
				'SELECT '
				.	'PV.page_id, '
				.	'PV.is_ssl_page '
				.'FROM '
				.	'page_versions as PV '
				.'JOIN ('
				.	'SELECT '
				.		'page_id '
				.	'FROM '
				.		'page_paths '
				.	'WHERE '
				.		'page_path = ? '
				.') as PP ON ( '
				.	'PP.page_id = PV.page_id '
				.') '
				.'WHERE '
				.	'PV.is_public = 1 '
				.'ORDER BY '
				.	'PV.version_number DESC '
				.'LIMIT 1';
		$query = $this->db->query($sql, array($page_path));
		
		if ( $query && $query->row() )
		{
			$result = $query->row();
			if ( $result->is_ssl_page > 0 )
			{
				return TRUE;
			}
		}
		return FALSE;
	}
}
