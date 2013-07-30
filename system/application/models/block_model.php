<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * ブロック管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */
class Block_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function is_already_installed($module)
	{
		$sql = 'SELECT * FROM collections WHERE collection_name = ? LIMIT 1';
		$query = $this->db->query($sql, array($module));

		if ($query->num_rows() > 0)
		{
			// already installed!
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}

	function install_new_block($cname)
	{
		// to correct basename
		$cname = kill_traversal($cname);
		
		$class_name = ucfirst($cname) . '_block';
		$path = 'blocks/' . $cname . '/' . $cname . '.php';
		
		if (file_exists(SZ_EXT_PATH . $path))
		{
			require_once(SZ_EXT_PATH . $path);
		}
		else if (file_exists(FCPATH . $path))
		{
			require_once(FCPATH . $path);
		}
		else
		{
			return FALSE;
		}

		if (!class_exists($class_name))
		{
			return FALSE;
		}

		$b = new $class_name();

		$data = array(
			'collection_name'  => $cname,
			'description'      => ($b->get_description()) ? $b->get_description() : '',
			'interface_width'  => ($b->get_if_width()) ? $b->get_if_width() : 500,
			'interface_height' => ($b->get_if_height()) ? $b->get_if_height() : 500,
			'added_date'       => date('Y-m-d H:i:s', time()),
			'block_name'       => ($b->get_block_name()) ? $b->get_block_name() : $cname,
			'db_table'         => '',
			'pc_enabled'       => $b->get_enables('pc'),
			'sp_enabled'       => $b->get_enables('sp'),
			'mb_enabled'       => $b->get_enables('mb')
		);

		// creata databate_table if block instance has "db_structure()" method
		if ($b->get_table_name())
		{
			if (!method_exists($b, 'db'))
			{
				return 'NOT_ENOUGH';
			}
			$dbst = $b->db();
			$table_name = $b->get_table_name();

			if (!$dbst || !is_array($dbst) || !array_key_exists($table_name, $dbst))
			{
				return 'NOT_ENOUGH';
			}
			else if (!$table_name || empty($table_name))
			{
				return 'NO_TABLE';
			}
//			else if ($this->db->table_exists($table_name))
//			{
//				
//				return 'TABLE_EXISTS';
//			}

			foreach ($dbst as $key => $value)
			{
				$is_master_table = ($table_name === $key) ? TRUE : FALSE;

				if ($is_master_table)
				{
					$data['db_table'] = $table_name;
				}

				
				if ($this->db->table_exists($key))
				{
					$ret = $this->_update_collection_db_from_array($key, $value, $is_master_table);
				}
				else
				{
					$ret = $this->_make_db_from_array($key, $value, $is_master_table);
				}

				if ($ret === 'ERROR')
				{
					return 'NOT_ENOUGH_COLUMN';
				}
				else if ($ret === FALSE)
				{
					return FALSE;
				}
			}
		}

		$ret = $this->db->insert('collections', $data);
		if ($ret)
		{
			return 'COMPLETE';
		}
		else
		{
			return FALSE;
		}
	}

	function get_collection_data($cid)
	{
		$sql =
			'SELECT '
			.	'*, '
			.	'C.description as description '
			.'FROM '
			.	'collections as C '
			.'LEFT OUTER JOIN sz_plugins as PLG ON ( '
			.	'C.plugin_id = PLG.plugin_id '
			.') '
			.'WHERE '
			.	'C.collection_id = ? '
			.'LIMIT 1'
			;
		$query = $this->db->query($sql, array($cid));

		return ( $query && $query->row() ) ? $query->row() : FALSE;
	}

	function delete_collection($cid)
	{
		$sql = 'DELETE FROM collections WHERE collection_id = ? LIMIT 1';
		$ret = $this->db->query($sql, array($cid));

		return $ret;
	}

	function update_collection_data($cname, $cid)
	{
		// get stored collection data
		$collection = $this->get_collection_data($cid);
		// to correct basename
		$cname = kill_traversal($cname);
		
		// collection name matched?
		if ( $collection->collection_name != $cname )
		{
			return FALSE;
		}
		
		$path = 'blocks/' . $collection->collection_name . '/' . $collection->collection_name . '.php';
		
		// extension block exists?
		if (file_exists(SZ_EXT_PATH . $path))
		{
			require_once(SZ_EXT_PATH . $path);
		}
		// plugin block exists?
		else if ( ! empty($collection->plugin_handle)
		           && file_exists(SZ_PLG_PATH . $collection->plugin_handle . '/' . $path) )
		{
			require_once(SZ_PLG_PATH . $collection->plugin_handle . '/' . $path);
		}
		// core block exists?
		else if (file_exists(FCPATH . $path))
		{
			require_once(FCPATH . $path);
		}
		else
		{
			return FALSE;
		}

		$class_name = ucfirst($cname) . '_block';
		$b = new $class_name();

		if ($b->get_table_name())
		{
			if (!method_exists($b, 'db'))
			{
				return 'NOT_ENOUGH';
			}
			$table_name = $b->get_table_name();
			$dbst = $b->db();

			if (!$dbst || !is_array($dbst))
			{
				return 'NOT_ENOUGH';
			}
			else if (!$table_name || empty($table_name))
			{
				return 'NO_TABLE';
			}
			else
			{
				foreach ($dbst as $key => $value)
				{
					$is_master_table = ($key === $collection->db_table) ? TRUE : FALSE;

					if ($this->db->table_exists($key))
					{
						$ret = $this->_update_collection_db_from_array($key, $value, $is_master_table);
					}
					else
					{
						$ret = $this->_make_db_from_array($key, $value, $is_master_table);
					}
				}
			}
			if ($ret === 'ERROR')
			{
				return 'NOT_ENOUGH_COLUMN';
			}
			else if ($ret === 'MISS')
			{
				return 'MISSED_MODIFY';
			}
			else if ($ret === FALSE)
			{
				return FALSE;
			}
		}

		$data = array(
			'description'      => ($b->get_description()) ? $b->get_description() : '',
			'interface_width'  => ($b->get_if_width()) ? $b->get_if_width() : 500,
			'interface_height' => ($b->get_if_height()) ? $b->get_if_height() : 500,
			'block_name'       => ($b->get_block_name()) ? $b->get_block_name() : $cname,
			'pc_enabled'       => $b->get_enables('pc'),
			'sp_enabled'       => $b->get_enables('sp'),
			'mb_enabled'       => $b->get_enables('mb')
		);

		$this->db->where('collection_id', $cid);
		$ret = $this->db->update('collections', $data);
		if ($ret)
		{
			return 'COMPLETE';
		}
		else
		{
			return FALSE;
		}
	}

	function _make_db_from_array($table, $db, $is_master = FALSE)
	{
		// load a database forge class
		$this->load->dbforge();

		if ($is_master)
		{

			$key_field = 'block_id';

			if (!array_key_exists('block_id', $db))
			{
				return 'ERROR';
			}
			$bid_arr = $db['block_id'];
		}
		
		// stack indexes
		$indexes = array();

		// search key from array
		foreach ($db as $key => $val)
		{
			if (array_key_exists('key', $val))
			{
				$key_field = $key;
				unset($val['key']);
			}
			if ( isset($val['index']) )
			{
				$indexes[] = $val['index'];
				unset($val['index']);
			}
			$db[$key] = $val;
		}

		// add filed
		$this->dbforge->add_field($db);

		// add key if exists
		if (isset($key_field))
		{
			$this->dbforge->add_key($key_field, TRUE);
		}

		// create table
		if ( ! $this->dbforge->create_table($table, TRUE) )
		{
			return FALSE;
		}
		
		$this->_merge_db_index($table, $indexes);
		
		return TRUE;
	}

	function _update_collection_db_from_array($table, $db, $is_master = FALSE)
	{
		// load a database forge class
		$this->load->dbforge();
		
		$indexes = array();

		// search key from array
		foreach ($db as $key => $val)
		{
			if (array_key_exists('key', $val) || $key == 'block_id')
			{
				//unset($val['key']);
				continue; // key column skips
			}
			
			if ( isset($val['index']) )
			{
				$indexes[] = $key;
				unset($val['index']);
			}

			// Does key fileds exists?
			if ($this->db->field_exists($key, $table))
			{
				// modify column_data
				$val['name'] = $key;
				$ret = $this->dbforge->modify_column($table, array($key => $val));
			}
			else
			{
				// create field
				$ret = $this->dbforge->add_column($table, array($key => $val));
			}
			if (!$ret)
			{
				return 'MISS';
			}
		}

		$this->_merge_db_index($table, $indexes);

		return TRUE;
	}


	function get_use_collection_count($cid)
	{
//		// get_collection_name
//		$sql = 'SELECT collection_name FROM collections WHERE collection_id = ? LIMIT 1';
//		$query = $this->db->query($sql, array($cid));
//
//		$result = $query->row();
//		$cname = $result->collection_name;
//
//		$query->free_result();

		// get master table name
		$sql = 'SELECT db_table FROM collections WHERE collection_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($cid));
		$result = $query->row();
		
		if ( strpos($result->db_table, 'mysql.') !== FALSE )
		{
			// critical tablename uses!
			show_error('不正なテーブルを参照しようとしました。');
		}

		// get use count
		$sql = 'SELECT '
			.		'COUNT(tbl.block_id) as total '
			.	'FROM '
			.		$result->db_table . ' as tbl '
			.	'JOIN blocks as B ON ( '
			.		'B.block_id = tbl.block_id '
			.	') '
			.	'WHERE '
			.		'B.is_active = 1'
//			.	'WHERE '
//			.		'collection_name = ? '
//			.	'AND '
//			.		'is_active = 1'
			;
		$query = $this->db->query($sql);

		$result = $query->row();
		return $result->total;
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
	
	/**
	 * 管理画面用ブロックセットデータ取得
	 */
	function get_registed_block_set_master()
	{
		$sql =
				'SELECT '
				.	'M.block_set_master_id, '
				.	'M.master_name, '
				.	'M.create_date, '
				.	'( '
				.		'SELECT '
				.			'COUNT(block_set_data_id) '
				.		'FROM '
				.			'block_set_data '
				.		'WHERE '
				.			'block_set_data.block_set_master_id = M.block_set_master_id '
				.	') as block_count '
				.'FROM '
				.	'block_set_master as M '
				.'ORDER BY '
				.	'create_date ASC '
				;
		$query = $this->db->query($sql);
		return ( $query && $query->num_rows() > 0 ) ? $query->result() : array();
	}
	
	/**
	 * ブロックセットマスタ登録件数取得
	 */
	function get_registed_block_set_master_count()
	{
		$sql =
				'SELECT '
				.	'block_set_master_id '
				.'FROM '
				.	'block_set_master ';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}
	
	
	/**
	 * ブロックセット詳細データ取得
	 * @param $id
	 */
	function get_block_set_details($id)
	{
		// 先にマスタデータ取得
		$query = $this->db->query('SELECT * FROM block_set_master WHERE block_set_master_id = ? LIMIT 1', array((int)$id));
		if ( ! $query || ! $query->row() )
		{
			return FALSE;
		}
		$ret = $query->row();
		
		// 次に登録ブロックリストを取得
		$sql =
				'SELECT '
				.	'D.block_id as block_id, '
				.	'D.block_set_data_id, '
				.	'D.display_order, '
				.	'BC.collection_name, '
				.	'BC.block_name '
				.'FROM '
				.	'block_set_data as D '
				.'JOIN ( '
				.	'SELECT '
				.		'TMPB.block_id, '
				.		'TMPB.collection_name, '
				.		'TMPC.block_name '
				.	'FROM '
				.		'blocks as TMPB '
				.	'JOIN collections as TMPC ON ( '
				.		'TMPB.collection_name = TMPC.collection_name '
				.	') '
				.') as BC ON ( '
				.	'BC.block_id = D.block_id '
				.') '
				.'WHERE '
				.	'D.block_set_master_id = ? '
				.'ORDER BY '
				.	'D.display_order ASC'
				;
		$query = $this->db->query($sql, array((int)$id));
		$ret->blocks = ( $query ) ? $query->result() : array();
		
		return $ret;
	}
	
	/**
	 * ブロックセットマスタ名変更
	 * @param $id
	 * @param $name
	 */
	function rename_block_set_master($id, $name)
	{
		$this->db->where('block_set_master_id', $id);
		return $this->db->update('block_set_master', array('master_name' => $name));
	}
	
	
	/**
	 * ブロックセットデータ削除
	 * @param $dat_id
	 */
	function delete_blcok_set_data_piece($dat_id)
	{
		$this->db->where('block_set_data_id', $dat_id);
		$this->db->delete('block_set_data');
		return ( $this->db->affected_rows() > 0 ) ? TRUE : FALSE;
	}
	
	
	/**
	 * ブロックセットマスタ削除
	 * @param unknown_type $master_id
	 */
	function delete_blcok_set_master($master_id)
	{
		// マスタ削除
		$this->db->where('block_set_master_id', $master_id);
		$this->db->delete('block_set_master');
		
		$ret = ( $this->db->affected_rows() > 0) ? TRUE : FALSE;
		
		if ( $ret )
		{
			// マスタに関連づいたブロックデータも削除する
			$this->db->where('block_set_master_id', $master_id);
			$this->db->delete('block_set_data');
		}
		
		return $ret;
	}
	
	
	/**
	 * ブロックセットデータの並び替え
	 * @param $dat_id
	 * @param $order
	 */
	function update_block_set_data_order($dat_id, $order)
	{
		$this->db->where('block_set_data_id', $dat_id);
		return $this->db->update('block_set_data', array('display_order' => $order));
	}
}
