<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * CIフック応答用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Process_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function is_login()
	{
		if ($this->session->userdata('user_id'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function get_page_permission_of_ci_controller($cp)
	{
		// get page_id from page_path
		$sql = 'SELECT '
			.		'perms.allow_access_user, '
			.		'perms.allow_edit_user, '
			.		'perms.allow_approve_user, '
			.		'pv.parent, '
			.		'pv.page_id '
			.	'FROM '
			.		'page_permissions as perms '
			.		'RIGHT OUTER JOIN page_paths as pp '
			.			'USING(page_id) '
			.		'LEFT OUTER JOIN page_versions as pv '
			.			'USING(page_id) '
			.	'WHERE '
			.		'pp.page_path = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($cp));

		if ($query->row())
		{
			return $query->row();
		}
		return FALSE;
	}

	function get_collection_name($id)
	{
		$sql = 'SELECT collection_name FROM collections WHERE collection_id = ? LIMIT 1';
		$query = $this->db->query($sql, array(intval($id)));
		if ($query->row())
		{
			$result = $query->row();
			return $result->collection_name;
		}
	}

	function get_collection_master($id)
	{
		$sql = 'SELECT * FROM collections WHERE collection_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$id));

		return $query->row();

	}

	function get_area_id($area, $pid)
	{
		$sql = 'SELECT '
			.		'area_id '
			.	'FROM '
			.		'areas '
			.	'WHERE '
			.		'area_name = ? '
			.		'AND page_id = ? '
			.	'ORDER BY area_id DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query(
			$sql,
			array(
				$area,
				intval($pid)
			)
		);
		if ($query->row())
		{
			$result = $query->row();
			return $result->area_id;
		}
	}

	function insert_block_data($area_id, $collection, $block_id)
	{
//		$block_path = 'blocks/' . $collection->collection_name . '/' . $collection->collection_name . '.php';
//		
//		if (file_exists(SZ_EXT_PATH . $block_path))
//		{
//			require_once(SZ_EXT_PATH . $block_path);
//		}
//		else if (file_exists(FCPATH . $block_path))
//		{
//			require_once(FCPATH . $block_path);
//		}
		$CI =& get_instance();
		$block = $CI->load->block($collection->collection_name, $block_id, TRUE);
		
		$class   = ucfirst($collection->collection_name) . '_block';
//		$block   = new $class();
		$ignores = $block->get_skip_sanityzed_columns();
		$fields  = $this->db->field_data($collection->db_table);
		$data    = array();

		foreach ($fields as $value)
		{
			if ($this->input->post($value->name) !== FALSE)
			{
				//sanytize input data skip textcontent and htmlcontent.
				//if ($collection->collection_name === 'textcontent' || $collection->collection_name === 'html')
				if (in_array($value, $ignores))
				{
					$v = $this->input->post($value->name);
					//$data[$value] = $this->input->post($value);
				}
				else
				{
					$v = $this->input->post($value->name, TRUE);
					//$data[$value] = $this->input->post($value, TRUE);
				}
				// cast field type
				if ( strpos($value->type, 'int') !== FALSE)
				{
					$data[$value->name] = (is_array($v)) ? array_map('intval', $v) : (int)$v;
				}
				else 
				{
					$data[$value->name] = (is_array($v)) ? array_map('strval', $v) : (string)$v;
				}
				
			}
			else
			{
				// cast field type
				if ( strpos($value->type, 'int') !== FALSE)
				{
					$data[$value->name] = 0;
				}
				else 
				{
					$data[$value->name] = '';
				}
			}
		}
		
		if ( $block->validation($data) === FALSE )
		{
			return FALSE;
		}

		if (!array_key_exists('block_id', $data))
		{
			$data['block_id'] = $block_id;
		}

		$block->save($data);
		return TRUE;
	}

	function add_block_by_draft($pid, $did, $area)
	{
		// get target block
		$sql = 'SELECT '
			.		'block_id, '
			.		'collection_name, '
			.		'ct_handle '
			.	'FROM '
			.		'draft_blocks '
			.	'WHERE '
			.		'draft_blocks_id = ? '
			.		'AND drafted_user_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($did, (int)$this->session->userdata('user_id')));

		if ($query->num_rows() === 0)
		{
			return;
		}

		// if block exists, duplicate block
		$result  = $query->row();

		// get area version
		$area_id = $this->get_area_id($area, $pid);

		// get version state
		$sql = 'SELECT version_number FROM pages WHERE page_id = ? LIMIT 1';
		$query_2 = $this->db->query($sql, array($pid));

		$result_2 = $query_2->row();
		$vid = $result_2->version_number;

		// load block
		$block   = $this->load->block($result->collection_name, $result->block_id, TRUE);
		// do duplicate
		$new_bid = $block->duplicate();
		
//		$ct_handle = '';
//		// try get editting blocks
//		$sql = 'SELECT ct_handle FROM ? '
//				. 'WHERE block_id = ? LIMIT 1';
//		$query = $this->db->query($sql, array('block_versions', $result->block_id));
//		
//		if ($query->row())
//		{
//			$r = $query->row();
//			$ct_handle = $r->ct_handle;
//		}
//		else
//		{
//			// is block in pending?
//			$query = $this->db->query($sql, array('pending_bloks', $result->block_id));
//			if ($query->row())
//			{
//				$r = $query->row();
//				$ct_handle = $r->ct_handle;
//			}
//		}
		
		// format data
		$data = array(
			'block_id'        => $new_bid,
			'collection_name' => $result->collection_name,
			'area_id'         => $area_id,
			'display_order'   => (int)$this->_get_display_order_max($area_id, $vid) + 1,
			'is_active'       => 1,
			'version_number'  => $vid,
			'version_date'    => db_datetime(),
			'ct_handle'       => $result->ct_handle
		);
		// insert_block_data
		return $this->db->insert('pending_blocks', $data);
	}
	
	/**
	 * Add blocks from registed blockset
	 * @param $page_id
	 * @param $master_id
	 * @param $area
	 */
	function add_block_by_blockset($page_id, $master_id, $area)
	{
		// get registed blockset
		$sql =
				'SELECT '
				.	'BS.block_id, '
				.	'BS.ct_handle, '
				.	'B.collection_name '
				.'FROM '
				.	'block_set_data as BS '
				.'JOIN blocks as B ON ('
				.	'BS.block_id = B.block_id '
				.') '
				.'WHERE '
				.	'BS.block_set_master_id = ? '
				.'ORDER BY '
				.	'BS.display_order ASC '
				;
		$query = $this->db->query($sql, array((int)$master_id));
		
		if ( ! $query || $query->num_rows() === 0 )
		{
			return;
		}
		
		// get area id
		$area_id  = $this->get_area_id($area, $page_id);
		
		// get version state
		$sql      = 'SELECT version_number FROM pages WHERE page_id = ? LIMIT 1';
		$query_2  = $this->db->query($sql, array($page_id));
		$result_2 = $query_2->row();
		$vid      = $result_2->version_number;
		
		foreach ( $query->result() as $result )
		{
			// load block
			$block   = $this->load->block($result->collection_name, $result->block_id, TRUE);
			// do duplicate
			$new_bid = $block->duplicate();
			
			// format data
			$data = array(
				'block_id'        => $new_bid,
				'collection_name' => $result->collection_name,
				'area_id'         => $area_id,
				'display_order'   => (int)$this->_get_display_order_max($area_id, $vid) + 1,
				'is_active'       => 1,
				'version_number'  => $vid,
				'version_date'    => db_datetime(),
				'ct_handle'       => (string)$result->ct_handle
			);
			// insert_block_data
			$this->db->insert('pending_blocks', $data);
		}
	}
	
	function add_block_by_static($pid, $bid, $area)
	{
		// get target block
		$sql = 'SELECT '
			.		'collection_name '
			.	'FROM '
			.		'static_blocks '
			.	'WHERE '
			.		'block_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($bid));

		if ($query->num_rows() === 0)
		{
			return;
		}

		// if block exists, duplicate block
		$result = $query->row();

		// get area version
		$area_id = $this->get_area_id($area, $pid);

		// get version state
		$query_2 = $this->db->query('SELECT version_number FROM pages WHERE page_id = ? LIMIT 1', array($pid));

		$result_2 = $query_2->row();
		$vid = $result_2->version_number;

		// create empty block
		$block = $this->load->block($result->collection_name, FALSE, TRUE);

//		// do duplicate
//		$new_bid = $block->duplicate();
		
		
		// format data
		$data = array(
			'block_id'				=> $block->block_id,
			'collection_name'		=> $result->collection_name,
			'area_id'		=> $area_id,
			'display_order'		=> (int)$this->_get_display_order_max($area_id, $vid) + 1,
			'is_active'			=> 1,
			'version_number'		=> $vid,
			'slave_block_id'		=> $bid,
			'version_date'		=> db_datetime()
		);
		// insert_block_data
		$this->db->insert('pending_blocks', $data);
		
		// insert relation data
//		$data2 = array(
//			'block_id'			=> $bid,
//			'slave_block_id'	=> $block->block_id,
//			'version_number'	=> $vid
//		);
//		$this->db->insert('block_relations', $data2);
	}

	function insert_area_data($bid, $cname, $aid, $vid)
	{
		// 表示順の最大値を取得
		$max = $this->_get_display_order_max($aid, $vid);

		$data = array(
			'collection_name' 	=> $cname,
			'area_id'	=> $aid,
			'block_id'			=> $bid,
			'display_order' 	=> $max + 1,
			'version_number'	=> $vid,
			'version_date'		=> date('Y-m-d H:i:s', time())
		);
		$this->db->insert('pending_blocks', $data);
	}
	
	function update_block_data_from_slave($block)
	{
		$master_block = $this->load->block($block->collection_name, $block->slave_block_id, TRUE);
		
		if (!$master_block->get_table_name() || !$this->db->table_exists($master_block->get_table_name()))
		{
			return FALSE;
		}

		//$fields = $this->db->list_fields($block->get_table_name());
		$fields = $this->db->field_data($master_block->get_table_name());
		$data   = array();

		foreach ($fields as $value)
		{
			if ($this->input->post($value->name) !== FALSE)
			{
				//sanytize input data skip textcontent and htmlcontent.
				//if ($collection->collection_name === 'textcontent' || $collection->collection_name === 'html')
				if (in_array($value->name, $ignores))
				{
					$v = $this->input->post($value->name);
					//$data[$value] = $this->input->post($value);
				}
				else
				{
					$v = $this->input->post($value->name, TRUE);
					//$data[$value] = $this->input->post($value, TRUE);
				}
				// cast field type
				if ( strpos($value->type, 'int') !== FALSE)
				{
					$data[$value->name] = (is_array($v)) ? array_map('intval', $v) : (int)$v;
				}
				else 
				{
					$data[$value->name] = (is_array($v)) ? array_map('strval', $v) : (string)$v;
				}
				
			}
			else
			{
				// cast field type
				if ( strpos($value->type, 'int') !== FALSE)
				{
					$data[$value->name] = 0;
				}
				else 
				{
					$data[$value->name] = '';
				}
			}
		}
		
		if ( $block->validation($data) === FALSE )
		{
			return FALSE;
		}
		
		// get old block id
		$old_bid = $master_block->block_id;

		// save and created new block_id.
		$block->save($data);

		// get block_id and replace versioning block
		$new_bid = $block->bid;

		// replace block
		$this->db->where('block_id', $old_bid);
		$this->db->update('pending_blocks', array('block_id' => $new_bid));
		
		// update relation
		$this->db->where('block_id', $old_bid);
		$this->db->update('block_versions', array('block_id' => $new_bid));
		
		// update permission
		$this->db->where('block_id', $old_bid);
		$this->db->update('block_permissions', array('block_id' => $new_bid));
		
		// update block statics id
		$sql =
			'UPDATE '
			.	'static_blocks '
			.'SET '
			.	'tmp_static_from = ? '
			.'WHERE '
			.	'block_id = ? '
			.'OR '
			.	'tmp_static_from = ?'
			;
		$this->db->query($sql, array($new_bid, $old_bid, $old_bid));
		return TRUE;
	}

	function update_block_data($block, $pid)
	{
		$ignores = $block->get_skip_sanityzed_columns();

		if (!$block->get_table_name() || !$this->db->table_exists($block->get_table_name()))
		{
			return FALSE;
		}

		//$fields = $this->db->list_fields($block->get_table_name());
		$fields = $this->db->field_data($block->get_table_name());
		$data   = array();

		foreach ($fields as $value)
		{
			if ($this->input->post($value->name) !== FALSE)
			{
				//sanytize input data skip textcontent and htmlcontent.
				//if ($collection->collection_name === 'textcontent' || $collection->collection_name === 'html')
				if (in_array($value->name, $ignores))
				{
					$v = $this->input->post($value->name);
					//$data[$value] = $this->input->post($value);
				}
				else
				{
					$v = $this->input->post($value->name, TRUE);
					//$data[$value] = $this->input->post($value, TRUE);
				}
				// cast field type
				if ( strpos($value->type, 'int') !== FALSE)
				{
					$data[$value->name] = (is_array($v)) ? array_map('intval', $v) : (int)$v;
				}
				else 
				{
					$data[$value->name] = (is_array($v)) ? array_map('strval', $v) : (string)$v;
				}
				
			}
			else
			{
				// cast field type
				if ( strpos($value->type, 'int') !== FALSE)
				{
					$data[$value->name] = 0;
				}
				else 
				{
					$data[$value->name] = '';
				}
			}
		}
		
		if ( $block->validation($data) === FALSE )
		{
			return FALSE;
		}
		
		// get old block id
		$old_bid = $block->bid;

		// save and created new block_id.
		$block->save($data);

		// get block_id and replace versioning block
		$new_bid = $block->bid;
		
		// Is slaved block?
		// update block statics id
		if ( $block->slave_block_id > 0 )
		{
			$sql =
				'UPDATE '
				.	'static_blocks '
				.'SET '
				.	'tmp_static_from = ? '
				.'WHERE '
				.	'block_id = ? '
				;
			$this->db->query($sql, array($new_bid, $block->slave_block_id, $block->slave_block_id));
		}

		// replace block
		$this->db->where('block_id', $old_bid);
		$this->db->update('pending_blocks', array('block_id' => $new_bid));
		
		//$this->db->where('block_id', $old_bid);
		//$this->db->update('block_versions', array('block_id' => $new_bid, 'slave_block_id' => $new_bid));
		
		// update relation
		if ( $block->slave_block_id > 0 )
		{
			$this->db->where('slave_block_id', $block->slave_block_id);
			$this->db->update('pending_blocks', array('slave_block_id' => $new_bid));
		}
		
		//echo $this->db->last_query();
		
		//$this->db->where('slave_block_id', $old_bid);
		//$this->db->update('block_versions', array('slave_block_id' => $new_bid));
		
		// update permission
		$this->db->where('block_id', $old_bid);
		$this->db->update('block_permissions', array('block_id' => $new_bid));
		
		return TRUE;
	}

	function create_page($page)
	{
		$max = $this->_get_page_display_order_max($page['parent']);
		//$level = $this->_get_parent_page_level($page['parent']);

		$page['display_order'] = $max + 1;
		//$page['display_page_level'] = $level + 1;
		$page['version_date'] = date('Y-m-d H:i:s', time());
		$page['created_user_id'] = $this->session->userdata('user_id');
		$page['is_public'] = 1;
		$page['approved_user_id'] = $this->session->userdata('user_id');
		$page['version_comment'] = '初稿';

		// get page_id
		$query = $this->db->insert('pages', array('version_number' => 1));

		$pid = $this->db->insert_id();

		$page['page_id'] = $pid;
		$query = $this->db->insert('page_versions', $page);

		if ($query)
		{
			return $pid;
		}
		else
		{
			return FALSE;
		}
	}

	function update_page($page, $pid, $vid, $from_dashboard = FALSE)
	{
		// if toppage, parent is unneccesary.
		if ((int)$pid === 1)
		{
			$page['parent'] = 0;
		}

		$this->db->where('page_id', $pid);
		$this->db->where('version_number', $vid);

		if ($from_dashboard === TRUE)
		{
			// if from_dashboard request, update page_verson directory!
			$query = $this->db->update('page_versions', $page);
		}
		else
		{
			// else, edit pending pages update.
			$query = $this->db->update('pending_pages', $page);
		}
		if ($query)
		{
			return $pid;
		}
		else
		{
			return FALSE;
		}
	}

	function insert_page_path($data, $parent, $external = FALSE)
	{
		$data['page_path'] = uri_encode_path($data['page_path']);
		
		if ( ! $external && $parent > 1)
		{
			$sql = 'SELECT '
				.		'page_path '
				.	'FROM '
				.		'page_paths '
				.	'WHERE '
				.		'page_id = ? '
				.	'ORDER BY page_path_id DESC '
				.	'LIMIT 1'
				;
			$query = $this->db->query($sql, array($parent));
	
			if ($query->row())
			{
				$result = $query->row();
				$data['page_path'] = $result->page_path . '/' . $data['page_path'];
			}
		}
		
		$ret = $this->db->insert('page_paths', $data);
		return $ret;
	}

	function update_page_path($path, $ppid)
	{
		$tmp = FALSE;
		$path['page_path'] = uri_encode_path($path['page_path']);
		if ( $ppid > 0 )
		{
			$sql = 'SELECT '
				.		'page_path, '
				.		'page_id '
				.	'FROM '
				.		'page_paths '
				.	'WHERE '
				.		'page_path_id = ? '
				.	'ORDER BY page_path_id DESC '
				.	'LIMIT 1'
				;
			$query = $this->db->query($sql, array($ppid));

			$result     = $query->row();
			$page_path  = $result->page_path;
			$tmp        = $page_path;
			$target_pid = $result->page_id;

			$pos = strrpos($page_path, '/');
			if ($pos !== FALSE)
			{
				$page_path = substr($page_path, 0, $pos);
				$path['page_path'] = $page_path . '/' . $path['page_path'];
			}
		}

		$this->db->where('page_path_id', $ppid);

		// update master page path
		$ret = $this->db->update('page_paths', $path);

		if ( ! $tmp || ! $target_pid )
		{
			return $ret;
		}
		
		$this->_update_page_path_from_child_page_recursive($target_pid, $tmp . '/', $path['page_path'] . '/');
		
//		// upadte child page paths
//		$sql = 'SELECT '
//			.		'page_path_id, '
//			.		'page_path '
//			.	'FROM '
//			.		'page_paths '
//			.	'WHERE '
//			.		'page_path LIKE ? '
//			.	'AND page_path NOT LIKE ?'
//			;
//		$query = $this->db->query($sql, array($tmp . '/' . '%', 'dashboard/%'));
//
//		foreach ($query->result() as $value)
//		{
//			$data = array('page_path' => str_replace($tmp, $path['page_path'], $value->page_path));
//			$this->db->where('page_path_id', $value->page_path_id);
//			$this->db->update('page_paths', $data);
//		}
	}
	
	/** child page replace to nee page path recursive **/
	function _update_page_path_from_child_page_recursive($pid, $old_path, $new_path)
	{
		$sql = 'SELECT '
						.	'pv.page_id, '
						.	'pp.page_path, '
						.	'pp.page_path_id, '
						.	'pv.is_system_page '
						.'FROM '
						.	'page_versions as pv '
						.'RIGHT OUTER JOIN '
						.		'page_paths as pp ON('
						.			'pp.page_id = pv.page_id'
						.	') '
						.'WHERE '
						.	'pv.is_system_page = 0 '
						.'AND '
						.	'pv.parent = ? '
						.'ORDER BY pv.version_number DESC ';//LIMIT 1';
		$query = $this->db->query($sql, array($pid));
		
		// page_id cache
		$cache = array();
		if ($query->num_rows() > 0)
		{
			$result = $query->result();
			$query->free_result();
			
			foreach ($result as $v)
			{
				if (!isset($cache[$v->page_id]))
				{
					$cache[$v->page_id] = $v->page_path; // set cache
					$old = $v->page_path . '/';
					$p = str_replace($old_path, $new_path, $v->page_path);
					$this->db->where('page_path_id', $v->page_path_id);
					$this->db->update('page_paths', array('page_path' => $p));
					$this->_update_page_path_from_child_page_recursive($v->page_id, $old, $p . '/');
				}
			}
			$query->free_result();
		}
	}

	function insert_permission_data($perms, $pid)
	{
		$data = array('page_id'		=> $pid);

		if (is_array($perms['access']))
		{
			$data['allow_access_user'] = ':' . implode(':', $perms['access']) . ':';
		}

		if (is_array($perms['edit']))
		{
			$data['allow_edit_user'] = ':' . implode(':', $perms['edit']) . ':';
		}

		$this->db->insert('permissions', $data);
	}

	function update_permission_data($perms, $pid)
	{
		$sql = 'SELECT permission_id FROM permissions WHERE page_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		// permission data exists?
		if ($query->row())
		{
			if (is_array($perms['access']))
			{
				$data['allow_access_user'] = ':' . implode(':', $perms['access']) . ':';
			}
			else
			{
				$data['allow_access_user'] = '';
			}

			if (is_array($perms['edit']))
			{
				$data['allow_edit_user'] = ':' . implode(':', $perms['edit']) . ':';
			}
			else
			{
				$data['allow_edit_user'] = '';
			}

			$this->db->where('page_id', $pid);
			$this->db->update('permissions', $data);
		}
		// else : insert new permission
		else
		{
			$this->insert_permission_data($perms, $pid);
		}
	}

	function update_page_permissions($perms, $pid)
	{
		$sql = 'SELECT '
			.		'page_permissions_id '
			.	'FROM '
			.		'page_permissions '
			.	'WHERE '
			.		'page_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		if ($query->row())
		{
			$result = $query->row();
			// permissions already set
			$this->db->where('page_permissions_id', $result->page_permissions_id);
			return $this->db->update('page_permissions', $perms);
		}
		else
		{
			// permission record is not exists
			$perms['page_id'] = $pid;
			return $this->db->insert('page_permissions', $perms);
		}
	}

	function insert_page_permissions($perms)
	{
		return $this->db->insert('page_permissions', $perms);
	}

	function _get_page_display_order_max($pid)
	{
		$sql = 'SELECT '
			.		'MAX(display_order) as m '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'parent = ? '
			.		'AND is_public = 1 '
			.	'LIMIT 1';

		$query = $this->db->query($sql, array($pid));

		if ($query->row())
		{
			$result = $query->row();

			return (int)$result->m;
		}
		else
		{
			return 0;
		}
	}

	function _get_display_order_max($area_id, $vid)
	{
		$sql = 'SELECT '
			.		'MAX(display_order) as m '
			.	'FROM '
			.		'pending_blocks '
			.	'WHERE '
			.		'area_id = ? '
			.		'AND version_number = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array(intval($area_id), $vid));

		$result = $query->row();

		return (int)$result->m;
	}

	function _get_parent_page_level($pid)
	{
		$sql = 'SELECT '
			.		'display_page_level '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = ? '
			.	'ORDER BY '
			.		'version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array((int)$pid));

		if ($query->row())
		{
			$result = $query->row();
			return (int)$result->display_page_level;
		}
		else
		{
			return 1;
		}
	}

	function do_approve_order($pid, $comment = '', $is_recieve_mail = 0)
	{
		// get_editting version number
		$uid = (int)$this->session->userdata('user_id');
		$ver_num = $this->_get_editting_version($pid);
		$data = array(
			'page_id'			=> $pid,
			'version_number'	=> $ver_num,
			'ordered_user_id'	=> $uid,
			'ordered_date'	=> db_datetime(),
			'comment'			=> $comment,
			'is_recieve_mail'	=> $is_recieve_mail
		);

		// Does approve order exists?
		$sql = 'SELECT '
			.		'page_approve_orders_id '
			.	'FROM '
			.		'page_approve_orders '
			.	'WHERE '
			.		'page_id = ? '
			.		'AND ordered_user_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid, $uid));

		if ($query->row())
		{
			// update
			$result = $query->row();
			$query->free_result();
			$paoid = $result->page_approve_orders_id;
			$data['status'] = 0;
			$this->db->where('page_approve_orders_id', $paoid);
			$this->db->update('page_approve_orders', $data);
		}
		else
		{
			// insert
			$this->db->insert('page_approve_orders', $data);
		}
	}

	function _get_editting_version($pid)
	{
		$sql = 'SELECT '
			.		'version_number '
			.	'FROM '
			.		'pages '
			.	'WHERE '
			.		'page_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		$result = $query->row();
		return $result->version_number;
	}
}
