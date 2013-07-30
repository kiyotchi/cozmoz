<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * ページバージョン管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Version_model extends Model
{
	protected $p;

	function __construct()
	{
		parent::Model();
	}

	/**
	 * create_panding : 現在のバージョンを複製してpendingテーブルに移行する
	 */
	function create_pending($pid, $vid, $uid)
	{
		// pre : delete history pending data
		$this->delete_pending_data($pid, $uid);

		$new_v = (int)$vid + 1;
		// duplicate to pending action
		// page
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_versions '
			.	'WHERE '
			.		'page_id = ? '
			.		'AND version_number = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array((int)$pid, (int)$vid));

		// insert tmp table
		$result = $query->row_array();
		$query->free_result();
		
		$result['version_number'] = $new_v;
		unset($result['version_comment']);
		$result['created_user_id'] = $uid;
		// do
		$this->db->insert('pending_pages', $result);

		// area
		$sql = 'SELECT area_id FROM areas WHERE page_id = ?';
		$query = $this->db->query($sql, array((int)$pid));

		$as = array();
		foreach ($query->result() as $value)
		{
			$as[] = $value->area_id;
		}

		// memory liberating
		$query->free_result();

		// block
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'block_versions '
			.	'WHERE '
			.		'version_number = ? '
			.		'AND is_active = 1 '
			;
		if ( count($as) > 0 )
		{
			$sql .= 'AND area_id IN (' . implode(', ', $as) . ') ';
		}

		$sql .= 'GROUP BY block_id';
		
		$query = $this->db->query($sql, array((int)$vid));

		$cache = array();

		foreach ($query->result_array() as $value)
		{
			if (array_key_exists($value['block_id'], $cache))
			{
				continue;
			}
			$value['version_number'] = $new_v;
			$this->db->insert('pending_blocks', $value);
			$cache[$value['block_id']] = 1;
		}

		return $new_v;

	}

	/**
	 * create_version ページバージョン更新
	 * @note バージョン管理方法
	 * 	常にpagesのデータが最新になるような設計。
	 * 	つまり、ページ編集時に、現在のpagesのデータをpage_versionsに移し、その後でマスターをupdateする。
	 */
	function create_version($pid, $mode = FALSE, $uid)
	{
		// get current version
		$sql = 'SELECT version_number FROM pages WHERE page_id = ? LIMIT 1';
		$q = $this->db->query($sql, array($pid));
		$result = $q->row();

		$v = $result->version_number;

		if (!$v || !$mode)
		{
			return;
		}
		$ver_date = date('Y-m-d H:i:s', time());
		// marge pending to version
		// page
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'pending_pages '
			.	'WHERE '
			.		'page_id = ? '
			.		'AND version_number = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array((int)$pid, (int)$v));

		$result = $query->row_array();
		
		$query->free_result();
		
		unset($result['pending_page_version_id']);
		unset($result['page_version_id']);
		$result['version_date'] = $ver_date;

		if ($mode === 'approve')
		{
			$result['is_public'] = 1;
			$result['approved_user_id'] = $this->session->userdata('user_id');
			$this->_page_to_all_unpublic($pid);
		}
		else
		{
			$result['is_public'] = 0;
			$result['approved_user_id'] = 0;
		}
		$result['version_comment'] = $this->input->post('version_comment', TRUE);

		// create version
		$this->db->insert('page_versions', $result);

		// area
		$sql = 'SELECT area_id FROM areas WHERE page_id = ?';
		$query = $this->db->query($sql, array((int)$pid, (int)$v));
		
		$area_count = $query->num_rows();

		// create block version if area exists
		if ($area_count > 0)
		{
			foreach ($query->result() as $value)
			{
				$bs[] = $value->area_id;
				$ba[$value->area_id] = $value->area_id;
			}
	
			// memory liberating
			$query->free_result();
	
			// block(use area stacks)
			$sql = 'SELECT '
				.		'* '
				.	'FROM '
				.		'pending_blocks '
				.	'WHERE '
				.		'version_number = ? '
				.		'AND is_active = 1 '
				.		'AND area_id IN ('
				.			implode(', ', $bs)
				.		' ) '
				.	'GROUP BY block_id'
				;
			$query = $this->db->query($sql, array((int)$v));
			$blocks = $query->result_array();
			
			// memory liberating
			$query->free_result();
			$slaves = array();
			
			foreach ($blocks as $val)
			{
				unset($val['block_version_id']);
				unset($val['pending_block_id']);
				$val['area_id'] = $ba[$val['area_id']];
				$val['version_date'] = $ver_date;
				$this->db->insert('block_versions', $val);
				
				if ( $val['slave_block_id'] > 0 )
				{
					$slaves[] = $val['slave_block_id'];
				}
			}
			
			$this->db->where_in('area_id', $bs);
			$this->db->where('version_number', $v);
			$this->db->delete('pending_blocks');
		}

		// delete all pendings from page_id
		$this->db->where('page_id', $pid);
		$this->db->delete('pending_pages');
		
		if ( count($slaves) > 0 )
		{
			// update static block data from tmp
			$sql = 
				'SELECT '
				.	'block_id, '
				.	'tmp_static_from '
				.'FROM '
				.	'static_blocks '
				.'WHERE '
				.	'tmp_static_from = '
				. implode(' OR tmp_static_from = ', $slaves);
			$query = $this->db->query($sql);
			
			// update already versioned static block
			// and current relational block id in statics
			if ( $query && $query->num_rows() > 0 )
			{
				foreach ( $query->result() as $static )
				{
					$this->db->where('slave_block_id', $static->block_id);
//					$this->db->or_where('block_id', $static->block_id);
					$this->db->update('block_versions', array('slave_block_id' => $static->tmp_static_from));
					
					$this->db->where('tmp_static_from', $static->tmp_static_from);
					$this->db->where('block_id', $static->block_id);
					$this->db->update('static_blocks', array('block_id' => $static->tmp_static_from, 'tmp_static_from' => 0));
				}
			}
		}
	}

	function delete_pending_data($pid, $uid)
	{
		// get version
		$v_query = $this->db->query('SELECT version_number FROM pages WHERE page_id = ? LIMIT 1', array($pid));
		$result = $v_query->row();
		$vid = $result->version_number;
		
		$sql = 'SELECT area_id FROM areas WHERE page_id = ?';
		$query = $this->db->query($sql, array((int)$pid));

		//initialize array
		$bs = array();
		foreach ($query->result() as $area)
		{
			$bs[] = $area->area_id;
		}

		// memory liberating
		$query->free_result();
		
		// Does area data exists?
		if (count($bs) > 0)
		{
//			// delete relations
//			$sql =
//				'SELECT '
//			.		'block_id '
//			.	'FROM '
//			.		'pending_blocks '
//			.	'WHERE '
//			.		'area_id IN (' . implode(', ', array_map('intval', $bs)) . ')'
//			;
//			$query = $this->db->query($sql);
//			if ( $query->num_rows() > 0 )
//			{
//				$this->db->where('version_number', $vid);
//				foreach ( $query->result() as $blocks )
//				{
//					$this->db->or_where('block_id', $blocks->block_id);
//				}
//				$this->db->delete('block_relations');
//			}
			
			// pending data delete
			$this->db->where_in('area_id', $bs);
			$this->db->delete('pending_blocks');
		}

		// delete pending page
		$this->db->where('page_id', $pid);
		$this->db->delete('pending_pages');
		
		// delete static from
		$this->db->where('add_user_id', $uid);
		$this->db->update('static_blocks', array('tmp_static_from' => 0));
	}

	function edit_out($pid)
	{
		$sql = 'UPDATE '
			.		'pages '
			.	'SET '
			.		'is_editting = ?, '
			.		'edit_user_id = ?, '
			.		'version_number = ? '
			.	'WHERE '
			.		'page_id = ?'
			;
		$res = $this->db->query($sql, array(0, 0, 0, $pid));
	}

	function _page_to_all_unpublic($pid)
	{
		$this->db->where('page_id', $pid);
		$this->db->update('page_versions', array('is_public' => 0));
	}

	function approve_version_from_ajax($pid, $v)
	{
		$user_id = (int)$this->session->userdata('user_id');
		if ( $user_id === 0 )
		{
			return FALSE;
		}
		// down flag of current approve version
		$this->db->where('page_id', $pid);
		$this->db->where('is_public', 1);
		$ret = $this->db->update('page_versions', array('is_public' => 0));

		if (!$ret)
		{
			return FALSE;
		}

		// set new approve version
		$this->db->where('page_id', $pid);
		$this->db->where('version_number', $v);
		if ( $this->db->update('page_versions', array('is_public' => 1, 'approved_user_id' => $user_id)) )
		{
			// get approved username
			$sql =
				'SELECT '
				.	'user_name '
				.'FROM '
				.	'users '
				.'WHERE '
				.	'user_id = ? '
				.'LIMIT 1';
			$query = $this->db->query($sql, array($user_id));
			$result = $query->row();
			return $result->user_name;
		}
		return FALSE;
	}

	function delete_versions_from_ajax($pid, $vs)
	{
		// pre: get versioned block data
		$sql = 
					'SELECT '
					.	'block_version_id '
					.'FROM '
					.	'block_versions as BV '
					.'JOIN ( '
					.	'SELECT '
					.		'area_id '
					.	'FROM '
					.		'areas '
					.	'WHERE '
					.		'page_id = ? '
					.') as PA ON ( '
					.	'PA.area_id = BV.area_id '
					.') '
					.'WHERE '
					.	'version_number IN (?)';
		$query = $this->db->query($sql, array((int)$pid, implode(', ', $vs)));
		$stack = $query->result();
		
		$this->db->where('page_id', $pid);
		$this->db->where_in('version_number', $vs);
		$this->db->delete('page_versions');

		// delete block_version
		foreach ($stack as $bv)
		{
			$this->db->where('block_version_id', $bv->block_version_id);
			$this->db->update('block_versions', array('is_active' => 0));
		}
		
		return ($this->db->affected_rows() > 0);
	}

	function delete_approve_page_cache($pid)
	{
		$sql  =
				'SELECT '
				.	'page_path '
				.'FROM '
				.	'page_paths '
				.'WHERE '
				.	'page_id = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$pid));
		if ( ! $query || ! $query->row() )
		{
			return;
		}
		
		$result = $query->row();
		$dir    = ( ! config_item('cache_path') ) ? BASEPATH . 'cache/' : config_item('cache_path');
		$uri    = 'Seezoo_output_cache' . $result->page_path;

		// delete cache page
		$page_path = $dir . md5($uri);
		if (file_exists($page_path))
		{
			unlink($page_path);
		}
		
		// top page page path equals '' case.
		// so, if page_id = 1, check empty hashed cache file too.
		if ( (int)$pid === 1 )
		{
			$page_path = $dir .  md5('Seezoo_output_cache');
			if ( file_exists($page_path) )
			{
				unlink($page_path);
			}
		}
	}
}
