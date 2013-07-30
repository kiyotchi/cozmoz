<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * 下書きブロック管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */
class Draft_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function get_draft_block_list($uid)
	{
		$sql = 'SELECT '
			.		'draft_blocks_id, '
			.		'block_id, '
			.		'collection_name, '
			.		'alias_name '
			.	'FROM '
			.		'draft_blocks '
			.	'WHERE '
			.		'drafted_user_id = ? '
			.	'ORDER BY draft_blocks_id DESC'
			;
		$query = $this->db->query($sql, array($uid));

		return $query->result();
	}

	function get_draft_block_count($uid)
	{
		$sql = 'SELECT '
			.		'COUNT(draft_blocks_id) as total '
			.	'FROM '
			.		'draft_blocks '
			.	'WHERE '
			.		'drafted_user_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($uid));

		$result = $query->row();
		return $result->total;
	}

	function delete_draft_block($dfid, $uid)
	{
		$this->db->where('draft_blocks_id', $dfid);
		$this->db->where('drafted_user_id', $uid);
		$this->db->delete('draft_blocks');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
	function delete_static_block($stid, $uid)
	{
		$this->db->where('static_block_id', $stid);
		$this->db->where('add_user_id', $uid);
		$this->db->delete('static_blocks');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
	/**
	 * 下書きブロック名前変更
	 * 
	 * @param $id
	 * @param $name
	 */
	function rename_draft_block($id, $name)
	{
		$this->db->where('draft_blocks_id', $id);
		return $this->db->update('draft_blocks', array('alias_name' => $name));
	}
	
	/**
	 * 共有ブロック名前変更
	 * 
	 * @param $id
	 * @param $name
	 */
	function rename_static_block($id, $name)
	{
		$this->db->where('static_block_id', $id);
		return $this->db->update('static_blocks', array('alias_name' => $name));
	}
}
