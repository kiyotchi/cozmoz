<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * 初期プロセス用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */
class Init_model extends Model
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

	function check_enable_mod_rewrite()
	{
		$sql = 'SELECT enable_mod_rewrite FROM site_info LIMIT 1';
		$query = $this->db->query($sql);

		$result = $query->row();
		return ($result->enable_mod_rewrite > 0) ? TRUE : FALSE;
	}

	/**
	 * get_page_state : 対象ページの編集状態を取得
	 */
	function get_page_state($pid)
	{
		$sql = 'SELECT * FROM pages WHERE page_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		$result = $query->row_array();
		/*
		 * guard process
		 * 途中でセッションが切れた場合、is_editting = 1 かつedit_user_id = 0の現象が起こりえる。
		 * その場合は現在のユーザーIDで上書きして編集モードに強制移行させる。
		 */
		if ($result['is_editting'] == 1 && (int)$result['edit_user_id'] == 0)
		{
			$this->db->where('page_id', $pid);
			$this->db->update('pages', array('edit_user_id' => $this->user_id));
			$result['edit_user_id'] = $this->user_id;
		}
		return $result;
	}

	/**
	 * get_page_id_from_page_path :ページパスからページID取得
	 */
	function get_page_id_from_page_path($path)
	{
		$sql = 'SELECT page_id FROM page_paths '
			. 'WHERE page_path = ? LIMIT 1';
		$query = $this->db->query($sql, array($path));

		if ($query->row())
		{
			$result = $query->row();
			return $result->page_id;
		}
		return 0;
	}

	/**
	 * get_system_page_path_by_page_id : ページIDからページパス取得
	 */
	function get_system_page_path_by_page_id($pid)
	{
		$sql = 'SELECT '
			.		'page_path '
			.	'FROM '
			.		'page_paths '
			.	'WHERE '
			.		'page_id = ? '
			.	'AND '
			.		'is_enabled = 1 '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		if ($query && $query->row())
		{
			$result = $query->row();
			return $result->page_path;
		}
		return FALSE;
	}

	/**
	 * get_page_path_and_system_page : ページIDからパスとシステムページであるかどうかを取得
	 */
	function get_page_path_and_system_page($pid)
	{
		$sql = 'SELECT '
			.		'pv.is_system_page, '
			.		'pv.is_ssl_page, '
			.		'pp.page_path '
			.	'FROM '
			.		'page_versions as pv '
			.		'RIGHT OUTER JOIN page_paths as pp ON('
			.			'pv.page_id = pp.page_id'
			.		') '
			.	'WHERE '
			.		'pv.page_id = ? '
			.	'ORDER BY pv.version_number DESC '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($pid));

		if ($query && $query->row())
		{
			return $query->row();
		}
		return FALSE;
	}

	/**
	 *  is_edit_mode : 対象ページが編集モードであるかを判定
	 */
	function is_edit_mode($pid, $uid)
	{
		$sql = 'SELECT edit_user_id, is_editting FROM pages WHERE page_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		$result = $query->row();
		if ($result && $result->is_editting == 1 && $result->edit_user_id == $uid)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * alias_redirect : エイリアス元ページにリダイレクト
	 */
	function alias_redirect($alias_to)
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
		$query = $this->db->query($sql, array((int)$alias_to));

		if ($query->num_rows() > 0)
		{
			$result = $query->row();
			redirect($result->page_path);
		}
		else
		{
			redirect($alias_to);
		}
	}

	/*
	 * get_edit_mode : 対象のページの編集状態を取得
	 */
	function get_edit_mode($pid)
	{
		$sql = 'SELECT '
			.		'is_editting, '
			.		'edit_start_time, '
			.		'edit_user_id '
			.	'FROM '
			.		'pages '
			.	'WHERE '
			.		'page_id = ?'
			;
		$query = $this->db->query($sql, array($pid));
		$row = $query->row();

		if ((int)$row->is_editting === 0)
		{
			return 'NO_EDIT';
		}
		else
		{
			$uid = $this->session->userdata('user_id');
			$now = date('Y-m-d H:i:s', time());
			if ($row->edit_user_id == $this->session->userdata('user_id'))
			{
				return 'EDIT_SELF';
			}
			else
			{
				return 'EDIT_OTHER';
			}
		}
	}

	/**
	 * get_arrange_mode : 場所移動状態を取得
	 */
	function get_arrange_mode($pid)
	{
		$sql = 'SELECT is_arranging FROM pages WHERE page_id = ?';
		$query = $this->db->query($sql, array(intval($pid)));
		$result = $query->row();

		return (intval($result->is_arranging) === 1 && $this->session->userdata('is_arrange') == $pid) ? TRUE : FALSE;
	}

	/**
	 * set_edit_mode : ページ編集状態に移行
	 */
	function set_edit_mode($pid, $nv)
	{
		$sql = 'UPDATE '
			.		'pages '
			.	'SET '
			.		'is_editting = ?, '
			.		'edit_user_id = ?, '
			.		'edit_start_time = ?, '
			.		'version_number = ? '
			.	'WHERE '
			.		'page_id = ?'
			;
		$res = $this->db->query($sql, array(1, (int)$this->session->userdata('user_id'), date('Y-m-d H:i:s', time()), $nv, $pid));
	}


	function edit_out()
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

	/**
	 * set_arrange : 移動モードへ
	 */
	function set_arrange($pid)
	{
		$sql = 'UPDATE pages SET is_arranging = ? WHERE page_id = ?';
		$res = $this->db->query($sql,array(1, $pid));

		if ($res)
		{
			$this->session->set_userdata('is_arrange', $pid);
		}
	}

	/**
	 * arrange_out : 移動モードを抜ける
	 */
	function arrange_out($pid)
	{
		$sql = 'UPDATE pages SET is_arranging = ? WHERE page_id = ?';
		$res = $this->db->query($sql, array(0, $pid));

		if ($res)
		{
			$this->session->unset_userdata('is_arrange');
		}
	}

	/**
	 * db_routing : DBパスからルーティング変更
	 */
	function db_routing($path)
	{
		$sql = 'SELECT '
			.		'page_id '
			.	'FROM '
			.		'page_paths '
			.	'WHERE '
			.		'page_id = ? '
			.		'OR page_path = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($path, $path));
		if ($query->row())
		{
			$result = $query->row();
			return $result->page_id;
		}
		else
		{
			if (ctype_digit($path))
			{
				$sql = 'SELECT page_id FROM pages WHERE page_id = ? LIMIT 1';
				$query = $this->db->query($sql, array(intval($path)));
				if ($query->num_rows() > 0)
				{
					return intval($path);
				}
			}
			return FALSE;
		}
	}

	/**
	 * db_routing_all : DBパスからルーティング変更(全パス対象)
	 */
	function db_routing_all($path)
	{
		$sql = 'SELECT '
			.		'page_id '
			.	'FROM '
			.		'page_paths '
			.	'WHERE '
			.		'( page_path = ? OR page_id = ? ) '
			.	'AND '
			.		'is_enabled = 1 '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($path, $path));
		if ($query->row())
		{
			$result = $query->row();
			return $result->page_id;
		}
		else
		{
			if (ctype_digit($path))
			{
				$sql = 'SELECT page_id FROM pages WHERE page_id = ? LIMIT 1';
				$query = $this->db->query($sql, array(intval($path)));
				if ($query->num_rows() > 0)
				{
					return intval($path);
				}
			}
			return FALSE;
		}
	}

	/**
	 * get_site_info : サイト情報取得
	 */
	function get_site_info()
	{
		$sql = 'SELECT * FROM site_info LIMIT 1';
		$query = $this->db->query($sql);

		if ($query && $query->row())
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * get_master_email : 管理者のメールアドレスを取得
	 */
	function get_master_email()
	{
		$sql    = 'SELECT email FROM users WHERE user_id = 1';
		$query  = $this->db->query($sql);
		$result = $query->row();
		return $result->email;
	}
}
