<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * Ajax DB応答用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Ajax_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	/**
	 * get_block_list : 使用できるブロックリストを取得
	 * @param none
	 * @return array $list
	 */
	function get_block_list($mode = 'pc')
	{
		$sql = 'SELECT * FROM collections WHERE is_enabled = 1 ';
		switch ( $mode )
		{
			case 'pc':
				$sql .= 'AND pc_enabled = 1 ';
				break;
			case 'sp':
				$sql .= 'AND sp_enabled = 1 ';
				break;
			case 'mb':
				$sql .= 'AND mb_enabled = 1 ';
				break;
		}
		$query = $this->db->query($sql);

		$ret = array();

		foreach ($query->result() as $value)
		{
			$ret[$value->collection_id] = $value;
		}

		return $ret;
	}

	function get_draft_blocks($mode = 'pc')
	{
		$uid = (int)$this->session->userdata('user_id');

		$sql = 'SELECT '
			.		'D.* '
			.	'FROM '
			.		'draft_blocks as D '
			.	'JOIN collections as C ON ( '
			.		'D.collection_name = C.collection_name '
			.	') '
			.	'WHERE '
			.		'drafted_user_id = ? ';
		switch ( $mode )
		{
			case 'pc':
				$sql .= 'AND C.pc_enabled = 1 ';
				break;
			case 'sp':
				$sql .= 'AND C.sp_enabled = 1 ';
				break;
			case 'mb':
				$sql .= 'AND C.mb_enabled = 1 ';
				break;
		}
		$sql .= 'ORDER BY draft_blocks_id ASC';
		$query = $this->db->query($sql, array($uid));
		
		return $query->result();
	}
	
	function get_static_blocks($mode = 'pc')
	{
//		$uid = (int)$this->session->userdata('user_id');

		$sql = 'SELECT '
			.		'S.* '
			.	'FROM '
			.		'static_blocks as S '
			.	'JOIN collections as C ON ( '
			.		'S.collection_name = C.collection_name '
			.	') ';
		switch ( $mode )
		{
			case 'pc':
				$sql .= 'WHERE C.pc_enabled = 1 ';
				break;
			case 'sp':
				$sql .= 'WHERE C.sp_enabled = 1 ';
				break;
			case 'mb':
				$sql .= 'WHERE C.mb_enabled = 1 ';
				break;
		}
		$sql .= 'ORDER BY static_block_id ASC';
		$query = $this->db->query($sql);//, array($uid));

		return $query->result();
	}
	

	function get_block_path($id)
	{
		$sql = 'SELECT '
			.		'collection_name '
			.	'FROM '
			.		'collections '
			.	'WHERE '
			.		'collection_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array(intval($id)));
		$result = $query->row();
		return $result->collection_name;
	}

	function get_block_path_from_block_id($id)
	{
		$sql = 'SELECT '
			.		'collection_name '
			.	'FROM '
			.		'blocks '
			.	'WHERE '
			.		'block_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($id));
		$result = $query->row();
		return $result->collection_name;
	}
	
	/**
	 * ブロックが共有ブロックから追加されたものかどうかを判定し、
	 * マッチした場合はその共有元IDを返却する
	 * @param $block_id
	 */
	function check_slaved_block($block_id)
	{
		$sql =
				'SELECT '
				.	'slave_block_id '
				.'FROM '
				.	'pending_blocks '
				.'WHERE '
				.	'block_id = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$block_id));
		
		if ( $query && $query->row() )
		{
			$result = $query->row();
			return ( (int)$result->slave_block_id > 0 ) ? (int)$result->slave_block_id : 0;
		}
		return 0;
	}

	function get_block_data($bid, $bname)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		"$bname "
			.	'WHERE '
			.		'block_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($bid));
		return $query->row();
	}

	function delete_block($id)
	{
		// flag down block master record
		$sql = 'UPDATE '
			.		'blocks '
			.	'SET '
			.		'is_active = ? '
			.	'WHERE '
			.		'block_id = ?'
			;
		$query = $this->db->query($sql, array(0, $id));

		// flag down pending block record
		$sql = 'UPDATE '
			.		'pending_blocks '
			.	'SET '
			.		'is_active = ? '
			.	'WHERE '
			.		'block_id = ?'
			;
		$query2 = $this->db->query($sql, array(0, $id));

		if ($query && $query2)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function rollback_delete_block_data($block_id)
	{
		// flag up block_master_data
		$sql = 'UPDATE '
			.		'blocks '
			.	'SET '
			.		'is_active = 1 '
			.	'WHERE '
			.		'block_id = ?'
			;
		$query = $this->db->query($sql, array($block_id));

		// flag up pending block record
		$sql = 'UPDATE pending_blocks SET is_active = 1 WHERE block_id = ?';
		$query2 = $this->db->query($sql, array($block_id));

		if ($query && $query2)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function get_custom_template_list($cname)
	{
		$ret = array();
		$ct_path = 'blocks/' . $cname . '/templates';
		
		//block has custom templates?
		if ( is_dir(SZ_EXT_PATH . $ct_path))
		{
			$path = SZ_EXT_PATH . $ct_path;
		}
		else if ( is_dir(FCPATH . $ct_path))
		{
			$path = FCPATH . $ct_path;
		}
		else
		{
			return FALSE;
		}

		// use directory_helper
		$this->load->helper('directory_helper');

		$dirs = directory_map($path . '/', TRUE);

		foreach ($dirs as $dir)
		{
			if (!is_dir($path . '/' . $dir) || !file_exists($path . '/' . $dir . '/view.php'))
			{
				continue;
			}

			// check directory contents
			if (FALSE !== ($attribute = $this->_index_custom_template($path . '/' . $dir . '/')))
			{
				$ret[$dir] = $attribute;
			}
			else
			{
				$ret[$dir] = '';
			}
		}

		if (count($ret) > 0)
		{
			return $ret;
		}
		return FALSE;
	}

	function _index_custom_template($path)
	{
		if (file_exists($path . 'description.txt'))
		{
			return file_get_contents($path . 'description.txt');
		}
		return FALSE;
	}

	function set_block_custom_template($bid, $handle)
	{
		// get collection_name and block_id
		$sql = 'SELECT '
			.		'CASE '
			.			'WHEN slave_block_id > 0 THEN slave_block_id '
			.			'ELSE block_id '
			.		'END as block_id, '
			.		'collection_name '
			.	'FROM '
			.		'pending_blocks '
			.	'WHERE '
			.		'block_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($bid));
		
		if ( ! $query || ! $query->row())
		{
			return FALSE;
		}
		
		$result = $query->row();
		
		$sql =
			'UPDATE '
			.	'%s '
			.'SET '
			.	'ct_handle = ? '
			.'WHERE '
			.	'block_id = ? '
			.'OR '
			.	'slave_block_id = ?'
			;
		$ret = $this->db->query(sprintf($sql, 'block_versions'), array($handle, $result->block_id, $result->block_id));
		$ret = $this->db->query(sprintf($sql, 'pending_blocks'), array($handle, $result->block_id, $result->block_id));
//		$this->db->where('block_id', $result->block_id);
//		$ret = $this->db->update('block_versions', array('ct_handle' => $handle));
//		$this->db->where('block_id', $result->block_id);
//		$ret = $this->db->update('pending_blocks', array('ct_handle' => $handle));
		
		if (!$ret)
		{
			return FALSE;
		}
		
		return $result;
		
//		// get collection_name and block_id
//		$sql = 'SELECT '
//			.		'CASE '
//			.			'WHEN slave_block_id > 0 THEN slave_block_id '
//			.			'ELSE block_id '
//			.		'END as block_id, '
//			.		'collection_name '
//			.	'FROM '
//			.		'pending_blocks '
//			.	'WHERE '
//			.		'block_id = ? '
//			.	'LIMIT 1'
//			;
//		$query = $this->db->query($sql, array($bid));
//		
//		if ($query && $query->row())
//		{
//			return $query->row();
//		}
//		return FALSE;
	}

	function get_block_view_path($bid, $cname)
	{
		$sql = 'SELECT '
			.		'ct_handle '
			.	'FROM '
			.		'pending_blocks '
			.	'WHERE '
			.		'block_id = ?'
			;
		$query = $this->db->query($sql, array($bid));

		$result = $query->row();
		$path = $result->ct_handle;

		if (!empty($path))
		{
			//return $cname . '/templates/' . $path . '/view';
			return 'templates/' . $path . '/view';
		}
		else
		{
			//return $cname . '/view';
			return 'view';
		}
	}

	function update_display_order($data = array(), $pid = FALSE)
	{
		$sql = 'UPDATE '
			.		'pending_blocks '
			.	'SET '
			.		'display_order = ?, '
			.		'area_id = ? '
			.	'WHERE '
			.		'block_id = ? '
			.	'LIMIT 1'
			;
		foreach ($data as $key => $val)
		{
			$query = $this->db->query($sql, array((int)$val[1], (int)$val[0], $key));
			if (!$query)
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	function get_page_data($pid)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_versions '
			.		'RIGHT OUTER JOIN page_paths '
			.			'ON(page_versions.page_id = page_paths.page_id) '
			.	'WHERE '
			.		'page_versions.page_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}

	function get_current_edit_page_data($pid, $vid)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'pending_pages AS pp '
			.		'LEFT OUTER JOIN page_permissions AS perms ON ( '
			.			'perms.page_id = pp.page_id '
			.		') '
			.		'RIGHT OUTER JOIN page_paths AS pt ON ('
			.			'pp.page_id = pt.page_id '
			.		') '
			.	'WHERE '
			.		'pp.page_id = ? '
			.		'AND pp.version_number = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array((int)$pid, (int)$vid));

		return $query->row();
	}

	function get_is_page_editting($pid)
	{
		$sql =
			'SELECT '
			.	'is_editting '
			.	'FROM '
			.		'pages '
			.	'WHERE '
			.		'page_id = ? '
			.	'LIMIT 0, 1'
			;
		$query = $this->db->query(
			$sql,
			array((int)$pid)
		);

		return $query->row();
	}

	function get_page_one($pid)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_versions AS pv '
			.		'RIGHT OUTER JOIN page_paths '
			.			'USING(page_id) '
			.	'WHERE '
			.		'pv.page_id = ? '
			.		'AND pv.is_public = 1 '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}


	function get_template_list()
	{
		$sql = 'SELECT * FROM templates';
		$query = $this->db->query($sql);

		return $query->result();
	}

	function get_default_template_id()
	{
		$sql = 'SELECT default_template_id FROM site_info LIMIT 1';
		$query = $this->db->query($sql);

		$result = $query->row();
		return $result->default_template_id;
	}

	function get_current_page_path($pid)
	{
		if ($pid > 1)
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
			$query = $this->db->query($sql, array($pid));
	
			if ($query->row())
			{
				$result = $query->row();
				return $result->page_path;
			}
			else
			{
				return '';
			}
		}
		return '';
	}
	
	function get_top_page_path()
	{
		$sql =
					'SELECT '
					.	'page_path '
					.'FROM '
					.	'page_paths '
					.'WHERE '
					.	'page_id = 1 '
					.'LIMIT 1';
		$query = $this->db->query($sql);
		$result = $query->row();
		return $result->page_path . '/';
	}
	
	function check_path_exists($pid, $path)
	{
		// first CMS page exists?
		$sql =
					'SELECT '
					.	'page_id '
					.'FROM '
					.	'page_paths '
					.'WHERE '
					.	'page_path = ? ';
		if ($pid > 0)
		{
			$sql .=	'AND page_id <> ?';
			$query = $this->db->query($sql, array($path, $pid));
		}
		else 
		{
			$query = $this->db->query($sql, array($path));
		}
		
		if ($query->num_rows() > 0)
		{
			return 'PAGE_EXISTS';
		}
		else 
		{
			// second, static page exists?
			if (file_exists(FCPATH . 'statics/' . $path.EXT)
						|| file_exists(FCPATH . 'statics/' . $path . '.html'))
			{
				return 'STATIC_EXISTS';
			}
			return 'NOT_EXISTS';
		}
	}

	function get_user_list()
	{
		$sql = 'SELECT user_id, user_name, admin_flag FROM users';
		$query = $this->db->query($sql);

		$u = new stdClass();
		$u->user_name = '一般ユーザー';
		$u->admin_flag = 0;
		$ret = array($u);
		
		$m = new stdClass();
		$m->user_name = '登録メンバー';
		$m->admin_flag = 0;
		$ret['m'] = $m;

		foreach($query->result() as $value)
		{
			$ret[$value->user_id] = $value;
		}

		return $ret;
	}

	function get_user_name_list()
	{
		$sql = 'SELECT user_id, user_name FROM users';
		$query = $this->db->query($sql);

		$ret = array();

		foreach($query->result() as $value)
		{
			$ret[$value->user_id] = $value->user_name;
		}

		return $ret;
	}

	function get_page_permission($pid)
	{
		$sql = 'SELECT '
			.		'allow_access_user, '
			.		'allow_edit_user '
			.	'FROM '
			.		'permissions '
			.	'WHERE '
			.		'page_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		$ret = array();

		return $query->row_array();
	}

	function get_sitemap()
	{
		// 処理が複雑化しそうなのでコメントを残していく
		$ret = array();

		// step1 : まずはトップページを取得。これはpage_id = 1で決め打ちでOK
		$sql = 'SELECT '
			.		'page_id, '
			.		'page_title '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = 1 '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql);

		$ret[] = $query->row_array();

		// step2 : 次に、トップの子ページを取得し、そのページの子ページを再帰的に探索
		$sql = 'SELECT '
			.		'DISTINCT(`page_id`) page_id, '
			.		'page_title, '
			.		'alias_to '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'parent = 1 '
			.	'AND is_system_page = 0 '
			.	'ORDER BY display_order ASC'
			;
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			$ch = array();
			foreach ($query->result_array() as $value)
			{
				$value['childs'] = $this->_has_child_page($value['page_id']);
				$ch[] = $value;
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

	function get_all_sitemap()
	{
		$sql = 'SELECT '
			.		'DISTINCT(`page_id`) page_id, '
			.		'page_title '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'is_public = 1 '
			.		'AND is_system_page = 0 '
			.	'ORDER BY version_number DESC'
			;
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			return FALSE;
		}
	}

	function get_page_versions($pid)
	{
		$sql = 'SELECT '
			.		'version_number, '
			.		'version_comment, '
			.		'version_date, '
			.		'created_user_id, '
			.		'is_public, '
			.		'approved_user_id '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = ? '
			.	'ORDER BY version_number DESC'
			;
		$query = $this->db->query($sql, array($pid));

		return $query->result();
	}

	function page_is_approve($uid, $pid)
	{
		if ($uid == 1)
		{
			return TRUE; // master user
		}
		// Does user has admin permission?
		$sql = 'SELECT '
			.		'user_id '
			.	'FROM '
			.		'users '
			.	'WHERE '
			.		'user_id = ? '
			.		'AND admin_flag = 1 '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($uid));

		if ($query->row())
		{
			return TRUE; // admin user
		}

		// Does users has page_approve_permission?
		$sql = 'SELECT '
			.		'allow_approve_user '
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
			if (strpos($result->allow_approve_user, ':' . $uid . ':') !== FALSE)
			{
				return TRUE;
			}
		}
		// no approve permission...
		return FALSE;
	}

	function get_file_data_one($fid)
	{
		$sql = 'SELECT * FROM files WHERE file_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($fid));

		return $query->row();
	}

	function _has_child_page($pid)
	{
		$sql = 'SELECT page_id FROM page_versions WHERE parent = ?';
		$query = $this->db->query($sql, array($pid));

		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function get_child_pages($pid)
	{
		$sql = 'SELECT '
			.		'PV.page_id, '
			.		'PV.page_title, '
			.		'PV.alias_to '
			.	'FROM '
			.		'page_versions as PV, '
			.		'('
			.			'SELECT '
			.				'MAX(version_number) as max '
			.			'FROM '
			.				'page_versions '
			.			'WHERE '
			.				'parent = ? '
			.		') PVMAX '
			.	'WHERE '
			.		'parent = ? '
			.		'AND PV.version_number = PVMAX.max '
			.	'ORDER BY display_order ASC'
			;
		$query = $this->db->query($sql, array($pid, $pid));

		$ch = array();
		foreach ($query->result_array() as $value)
		{
			$value['childs'] = $this->_has_child_page($value['page_id']);
			$ch[] = $value;
		}

		return $ch;
	}
	
	function update_advance_css($tpid = 0, $css = '')
	{
		$this->db->where('template_id', $tpid);
		return $this->db->update('templates', array('advance_css' => $css));
	}

	function insert_new_question($arr, $option)
	{
		$type = $arr['question_type'];

		switch ($type)
		{
		case 'text' :
			break;
		case 'radio':
		case 'select':
		case 'checkbox':
			$arr['options'] = $option;
			break;
		case 'file':
			$arr['accept_ext'] = $option;
			$arr['max_file_size'] = (int)$this->input->post('size');
			break;
		default : break;
		}

		$arr['display_order'] = $this->_get_max_question_display_order($arr['question_key']);

		$ret = $this->db->insert('sz_bt_questions', $arr);
		if ($ret)
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	function update_question_data($arr, $option, $qid, $key)
	{
		$type = $arr['question_type'];

		switch ($type)
		{
		case 'text' :
			break;
		case 'textarea' :
//			list($row, $col) = explode('|', $option);
//			$arr['rows'] = $row;
//			$arr['cols'] = $col;
			break;
		case 'radio':
		case 'select':
		case 'checkbox':
			$arr['options'] = $option;
			break;
		case 'file':
			$arr['accept_ext'] = $option;
			$arr['max_file_size'] = (int)$this->input->post('size');
			break;
		default : break;
		}

		$this->db->where('question_id', $qid);
		$this->db->where('question_key', $key);

		return $this->db->update('sz_bt_questions', $arr);
	}

	function delete_question_data($qid)
	{
		$this->db->where('question_id', $qid);
		$this->db->delete('sz_bt_questions');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	function delete_pending_form_questions($dels = array())
	{
		$this->db->where_in('question_id', $dels);
		$this->db->delete('sz_bt_questions');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	function _get_max_question_display_order($key)
	{
		$sql = 'SELECT '
			.		'MAX(display_order) AS m '
			.	'FROM '
			.		'sz_bt_questions '
			.	'WHERE '
			.		'question_key = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($key));

		if (!$query)
		{
			return 1;
		}
		else
		{
			$result = $query->row();
			return (int)$result->m + 1;
		}
	}

	function do_sort_question($qs_key)
	{
		foreach($_POST as $key => $val)
		{
			if (intval($key) === 0)
			{
				continue;
			}
			$data = array('display_order' => intval($val));
			$this->db->where('question_id', (int)$key);
			$this->db->where('question_key', $qs_key);
			$ret = $this->db->update('sz_bt_questions', $data);

			if (!$ret)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	function get_form_parts_one($qid)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'sz_bt_questions '
			.	'WHERE '
			.		'question_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($qid));

		return $query->row();
	}

	/**
	 * ガジェットデータ取得メソッド
	 */

	function get_user_gadgets($uid)
	{
		$sql = 'SELECT '
			.		'sgm.gadget_master_id, '
			.		'sgm.gadget_id, '
			.		'sgm.token, '
			.		'sg.gadget_name '
			.	'FROM '
			.		'sz_gadget_master as sgm '
			.		'RIGHT OUTER JOIN sz_gadgets as sg '
			.			'USING(gadget_id) '
			.	'WHERE '
			.		'sgm.user_id = ? '
			.	'ORDER BY sgm.display_order ASC'
			;
		$query = $this->db->query($sql, array($uid));

		if ($query->num_rows() > 0)
		{
			return array('count' => $query->num_rows(), 'gadget' => $query->result());
		}
		else
		{
			return FALSE;
		}
	}

	function get_gadget_data_memo($key)
	{
		$sql = 'SELECT '
			.		'data, '
			.		'update_time '
			.	'FROM '
			.		'sz_gadget_memo '
			.	'WHERE '
			.		'token = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($key));
		if ($query->row())
		{
			$result = $query->row();
			return $result;
		}
		else
		{
			return FALSE;
		}
	}

	function save_memo_data($key, $data)
	{
		$this->db->where('token', $key);
		return $this->db->update('sz_gadget_memo', $data);
	}

	function get_gadget_data_weather($key)
	{
		$sql = 'SELECT '
			.		'city_id '
			.	'FROM '
			.		'sz_gadget_weather '
			.	'WHERE token = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($key));

		if ($query->row())
		{
			$result = $query->row();

			$day_list = array('today', 'tomorrow', 'dayaftertomorrow');
			// get weather data from Liverdoor weather API : http://weather.livedoor.com/weather_hacks
			$base = 'http://weather.livedoor.com/forecast/webservice/rest/v1?city=' . $result->city_id;
			$ret = array();

			foreach ($day_list as $v)
			{
				$url = $base . '&day=' . $v;
				$data = @file_get_contents($url);
				$XML = simplexml_load_string($data);
				if (!$data || !$XML)
				{
					$ret[$v] = FALSE;
					continue;
				}
				$XML = simplexml_load_string($data);
				$weather['img'] = array(
					'title'	=> $XML->image->title,
					'url'	=> $XML->image->url,
					'width'	=> $XML->image->width,
					'height'=> $XML->image->height
				);
				$weather['temp'] = array(
					'max'	=> $XML->temperature->max->celsius,
					'min'	=> $XML->temperature->min->celcius
				);
				$weather['day'] = date('Y/m/d(D)', strtotime($XML->forecastdate));
				$ret[$v] = $weather;
			}

			$loc = $XML->location->attributes();
			$ret['place'] = $loc['city'];

			return $ret;
		}
		else
		{
			return FALSE;
		}
	}

	function gadget_weather_update($key, $city_id)
	{
		$this->db->where('token', $key);
		return $this->db->update('sz_gadget_weather', array('city_id' => $city_id));
	}

	function get_gadget_list()
	{
		$sql = 'SELECT * FROM sz_gadgets';
		$query = $this->db->query($sql);

		$ret = array();
		foreach ($query->result_array() as $v)
		{
			$v['icon'] = file_exists(FCPATH . 'js/fl_images/gadget/icons/' . $v['gadget_name'] . '.png');
			$ret[] = $v;
		}

		return $ret;
	}

	function add_new_gadget($gid)
	{
		// get gadget_name
		$sql = 'SELECT '
			.		'gadget_name, '
			.		'db_table '
			.	'FROM '
			.		'sz_gadgets '
			.	'WHERE '
			.		'gadget_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($gid));

		$result = $query->row();
		$g_name = $result->gadget_name;
		$db = $result->db_table;
		$uid = (int)$this->session->userdata('user_id');
		$d_o = $this->_get_display_order_max_gadget($uid);

		$data = array(
			'user_id'		=> $uid,
			'token'			=> md5(uniqid(mt_rand(), TRUE)),
			'gadget_id'		=> $gid,
			'display_order'	=> (int)$d_o + 1
		);

		$ret = $this->db->insert('sz_gadget_master', $data);

		$new_id = $this->db->insert_id();

		// add gadget_data is use database
		if ($db)
		{
			// consider strict mode
			$ret2 = $this->db->insert($db, array('token' => $data['token']));
		}
		else
		{
			$ret2 = TRUE;
		}

		if ($ret && $ret2)
		{
			return array('gadget_id' => $gid, 'token' => $data['token'], 'gadget_name' => $g_name, 'gadget_master_id' => $new_id);
		}
		else
		{
			return FALSE;
		}
	}

	function delete_gadget($gmid)
	{
		// first, get using gadet
		$sql = 'SELECT '
			.		'sgm.token, '
			.		'sg.db_table '
			.	'FROM '
			.		'sz_gadget_master as sgm '
			.		'RIGHT OUTER JOIN sz_gadgets as sg '
			.			'USING(gadget_id) '
			.	'WHERE '
			.		'gadget_master_id = ? '
			.	'LIMIT 1';
		$query = $this->db->query($sql, array($gmid));

		if ($query->row())
		{
			// second, delete use gadget data is exists
			$result = $query->row();
			if ($result->db_table) {
				$sql = 'DELETE FROM ' . $result->db_table . ' WHERE token = ? LIMIT 1';
				$query = $this->db->query($sql, array($result->token));
			}
		}

		// finaly, delete master record
		$sql = 'DELETE FROM sz_gadget_master WHERE gadget_master_id = ?';
		$query = $this->db->query($sql, array($gmid));

		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function do_sort_gadget($data)
	{
		foreach ($data as $key => $v)
		{
			$this->db->where('token', $key);
			$this->db->update('sz_gadget_master', array('display_order' => $v));
		}
	}

	function _get_display_order_max_gadget($uid)
	{
		$sql = 'SELECT '
			.		'MAX(display_order) as m '
			.	'FROM '
			.		'sz_gadget_master '
			.	'WHERE '
			.		'user_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($uid));

		$result = $query->row();
		return $result->m;
	}

	function get_gmail_data($ac, $d)
	{
		// imap connect
		$m = 'pop.gmail.com';
		$account = $this->_get_gmail_account($ac);

		if (!$account)
		{
			return 'no_account';
		}
		if (!$mp = imap_open("{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX", $account['username'], $account['password']))
		{
			return 'error';
		}

		$count = imap_num_msg($mp);
		$pre = $count - 9;

		$result = imap_fetch_overview($mp, "{$count}:{$pre}", 0);
		$mail_data = array_reverse($result);

		$ret = array();
		foreach ($mail_data as $ov)
		{
			$data['subject']	= $this->_convert($ov->subject, 20);
			$data['from']		= $this->_convert($ov->from);
			$data['date']		= date('M/d H:i', strtotime($this->_convert($ov->date)));
			$data['seen']		= (int)$this->_convert($ov->seen);
			$body = imap_fetchbody($mp, $count--, 1);
			$data['body']		= nl2br(mb_convert_encoding($body, 'UTF-8', 'ISO-2022-JP'));
			$ret[] = $data;
		}
		imap_close($mp);

		return $ret;
	}

	function _convert($str, $limit = 0)
	{
		$ret = '';
		$dec = imap_mime_header_decode($str);
		foreach ($dec as $v)
		{
			$ret.= mb_convert_encoding($v->text, 'UTF-8', 'ISO-2022-JP');
		}

		if ($limit > 0)
		{
			return mb_substr($ret, 0, $limit) . '...';
		}

		return $ret;
	}

	function do_gmail_login($email, $password)
	{
		require_once('Crypt/Blowfish.php');

		$key = md5(uniqid(mt_rand(), TRUE));
		$iv = substr(sha1(microtime()), 0, 8);

		$blowfish = Crypt_blowfish::factory('cbc', $key, $iv);
		$crypt_password = $blowfish->encrypt($password);

		// set temp account
		$data = array(
			'email'			=> $email,
			'password'		=> base64_encode($crypt_password),
			'hash'			=> $key,
			'init_vector'	=> $iv
		);

		$ret = $this->db->insert('sz_gadget_gmail_accounts', $data);

		if ($ret)
		{
			return $key;
		}
		else
		{
			return FALSE;
		}
	}

	function _get_gmail_account($key)
	{
		// load PEAR::Crypt_blowfish
		require_once('Crypt/Blowfish.php');

		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'sz_gadget_gmail_accounts '
			.	'WHERE '
			.		'hash = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($key));

		if ($query->row())
		{
			$result = $query->row();
			$blowfish = Crypt_blowfish::factory('cbc', $result->hash, $result->init_vector);
			$data = array(
				'username'	=> $result->email,
				'password'	=> $blowfish->decrypt(base64_decode($result->password))
			);
			return $data;
		}
		else
		{
			return FALSE;
		}
	}

	function get_twitter_gadget_data($key)
	{
		$sql = 'SELECT '
			.		'account_name, '
			.		'update_time, '
			.		'show_count '
			.	'FROM '
			.		'sz_gadget_twitter '
			.	'WHERE '
			.		'token = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($key));

		if ($query->num_rows() === 0)
		{
			return FALSE;
		}
		else
		{
			return $query->row();
		}
	}

	function update_twitter_config($key, $data)
	{
		$this->db->where('token', $key);
		return $this->db->update('sz_gadget_twitter', $data);
	}

	function get_gadget_rss_data($key)
	{
		// get_rss_url
		$sql = 'SELECT '
			.		'rss_url '
			.	'FROM '
			.		'sz_gadget_rss '
			.	'WHERE '
			.		'token = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($key));

		$result = $query->row();

		return $result->rss_url;
	}

	function _get_rss($url)
	{
		$data = @file_get_contents($url);
		if (!$data)
		{
			return '';
		}

		$ret = array();
		$XML = simplexml_load_string($data);
		$ret['rss_title'] = $XML->channel->title;
		$ret['data'] = array();
		foreach ($XML->channel->item as $value)
		{
			$ret['data'][] = array(
				'title' 		=> $value->title,
				'link' 			=> $value->link,
				'time' 			=> strtotime($value->pubDate),
				'date' 			=> date('Y-m-d H:i:s', strtotime($value->pubDate))
			);
			if (count($ret['data']) > 9)
			{
				break;
			}
		}

		usort($ret['data'], "sort_asc");
		return $ret;
	}

	function _split_cdata($str)
	{
		$regex = array('/^<!\[CDATA\[/', '/\]\]>$/');
		$replace = array('', '');
		return preg_replace($regex, $replace, $str);
	}

	function update_gadget_rss_config($key, $data)
	{
		$this->db->where('token', $key);
		return $this->db->update('sz_gadget_rss', $data);
	}

	function get_recent_bbs()
	{
		$sql = 'SELECT '
			.		'posted_user_id, '
			.		'post_date, '
			.		'body '
			.	'FROM '
			.		'sz_gadget_bbs_data '
			.	'ORDER BY post_date DESC '
			.	'LIMIT 10'
			;
		$query = $this->db->query($sql);

		return $query->result();
	}

	function insert_bbs_data($data)
	{
		return $this->db->insert('sz_gadget_bbs_data', $data);
	}

	function get_since_bbs($last = '0000-00-00 00:00:00')
	{
		$sql = 'SELECT '
			.		'posted_user_id, '
			.		'post_date, '
			.		'body '
			.	'FROM '
			.		'sz_gadget_bbs_data '
			.	'WHERE '
			.		'post_date > ? '
			.	'ORDER BY post_date DESC '
			.	'LIMIT 20'
			;
		$query = $this->db->query($sql, array($last));

		return $query->result();
	}

	function get_translated_data($q, $type)
	{
		$types_array = array(1 => 'en%7Cja', 2 => 'ja%7Cen');

		if (!array_key_exists((int)$type, $types_array))
		{
			return FALSE;
		}

		// google AJAX LanguageAPI path
		$url = 'http://ajax.googleapis.com/ajax/services/language/translate?v=1.0';

		// add parameter
		$url .= '&q=' . rawurlencode($q) . '&langpair=' . $types_array[(int)$type];

		// do translate
		$data = @file_get_contents($url);

		if (!$data)
		{
			return FALSE;
		}
		else
		{
			return $data;
		}
	}

	function add_draft_block($bid, $cname, $uid, $alias_name)
	{
		// check drafts already?
		$sql = 'SELECT '
			.		'draft_blocks_id '
			.	'FROM '
			.		'draft_blocks '
			.	'WHERE '
			.		'block_id = ? '
			.		'AND collection_name = ? '
			.		'AND drafted_user_id = ?'
			;
		$query = $this->db->query($sql, array($bid, $cname, $uid));

		if ($query->num_rows() > 0)
		{
			return 'already';
		}

		$query->free_result();

		// get current custom template handle
		$sql = 'SELECT ct_handle FROM pending_blocks '
				. ' WHERE block_id = ? AND collection_name = ? '
				. 'LIMIT 1';
		$query = $this->db->query($sql, array($bid, $cname));

		if ($query && $query->row())
		{
			$result = $query->row();
			$ct_handle = $result->ct_handle;
		}
		else
		{
			$ct_handle = '';
		}

		// add draft
		$data = array(
			'block_id'			=> (int)$bid,
			'collection_name'	=> $cname,
			'drafted_user_id'	=> $uid,
			'ct_handle'		=> $ct_handle,
			'alias_name'		=> $alias_name
		);

		return $this->db->insert('draft_blocks', $data);
	}
	
	function add_to_static_block($bid, $cname, $uid, $alias_name)
	{
		// check drafts already?
		$sql = 'SELECT '
			.		'static_block_id '
			.	'FROM '
			.		'static_blocks '
			.	'WHERE '
			.		'block_id = ? '
			.		'AND collection_name = ? '
			.		'AND add_user_id = ?'
			;
		$query = $this->db->query($sql, array($bid, $cname, $uid));

		if ($query->num_rows() > 0)
		{
			return 'already';
		}

		$query->free_result();

//		// get current custom template handle
//		$sql = 'SELECT ct_handle FROM pending_blocks '
//				. ' WHERE block_id = ? AND collection_name = ? '
//				. 'LIMIT 1';
//		$query = $this->db->query($sql, array($bid, $cname));
//
//		if ($query && $query->row())
//		{
//			$result = $query->row();
//			$ct_handle = $result->ct_handle;
//		}
//		else
//		{
//			$ct_handle = '';
//		}

		// add static
		$data = array(
			'block_id'			=> (int)$bid,
			'collection_name'	=> $cname,
			'add_user_id'	=> $uid,
			'alias_name'	=> $alias_name
//			'ct_handle'		=> $ct_handle
		);

		$this->db->insert('static_blocks', $data);
		
		// and insert slave target
		$this->db->where('block_id', $bid);
		return $this->db->update('pending_blocks', array('slave_block_id' => $bid));
	}
	
	function get_highlight_native_codes($code_array = array())
	{
		$ret = array();
		$sql = 'SELECT * FROM sz_bt_code_highlight WHERE block_id IN ( ? )';
		if (count($code_array) > 0)
		{
			$query = $this->db->query($sql, array(implode(', ', $code_array)));
			foreach ($query->result() as $result)
			{
				$ret[$result->block_id] = $result->code;
			}
		}
		return $ret;
	}
	
	function get_block_permission($bid)
	{
		$sql = 
					'SELECT '
					.	'allow_edit_id, '
					.	'allow_view_id, '
					.	'allow_mobile_carrier '
					.'FROM '
					.	'block_permissions '
					.'WHERE '
					.	'block_id = ? '
					.'LIMIT 1';
		$query = $this->db->query($sql, array($bid));
		
		if ($query && $query->row())
		{
			return $query->row();
		}
		else
		{
			$perm = new StdClass;
			$perm->allow_edit_id = null;
			$perm->allow_view_id = null;
			$perm->allow_mobile_carrier = null;
			return $perm;
		}
	}
	
	/**
	 * ブロックセット取得
	 * @return array
	 */
	function get_block_set()
	{
		$sql =
				'SELECT '
				.	'block_set_master_id, '
				.	'master_name, '
				.	'create_date, '
				.	'( '
				.		'SELECT '
				.			'COUNT(block_set_data_id) '
				.		'FROM '
				.			'block_set_data '
				.		'WHERE '
				.			'block_set_data.block_set_master_id = block_set_master.block_set_master_id '
				.	') as total '
				.'FROM '
				.	'block_set_master '
				.'ORDER BY '
				.	'create_date DESC '
				;
		$query = $this->db->query($sql);
		
		if ( $query && $query->num_rows() === 0 )
		{
			return array();
		}
		return $query->result();
	}
	
	/**
	 * ブロックセットにレコード追加
	 * @param mixed $block_master_ids
	 * @param int $block_id
	 */
	function add_block_set($block_master_ids, $block_id, $ct_handle)
	{
		if ( ! is_array($block_master_ids) )
		{
			$block_master_ids = array($block_master_ids);
		}
		
		foreach ( $block_master_ids as $master_id )
		{
			$max_order = $this->_get_max_blockset_display_order($master_id);
			$data      = array(
				'block_set_master_id' => $master_id,
				'display_order'       => $max_order + 1,
				'block_id'            => $block_id,
				'ct_handle'           => $ct_handle
			);
			
			if ( ! $this->db->insert('block_set_data', $data) )
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * ブロックセットのマスター追加
	 * @param array $master_data
	 * @param int $block_id
	 */
	function make_block_set($master_data, $block_id, $ct_handle)
	{
		if ( ! $this->db->insert('block_set_master', $master_data) )
		{
			return FALSE;
		}
		
		// get new master id
		$master_id = (int)$this->db->insert_id();
		
		if ( $master_id > 0 )
		{
			if ( $this->add_block_set($master_id, $block_id, $ct_handle) )
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * ブロックセットの中で最大のdisplay_orderを取得
	 * @param $master_id
	 */
	function _get_max_blockset_display_order($master_id)
	{
		$sql =
				'SELECT '
				.	'MAX(display_order) as m '
				.'FROM '
				.	'block_set_data '
				.'WHERE '
				.	'block_set_master_id = ? '
				.'LIMIT 1';
		
		$query = $this->db->query($sql, array((int)$master_id));
		if ( ! $query->row() )
		{
			return 0;
		}
		$result = $query->row();
		return (int)$result->m;
	}
}
