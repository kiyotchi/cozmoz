<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * アクセス権限管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Permission_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	// get permission
	function get_permission($pid)
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
		$query = $this->db->query($sql, array((int)$pid));

		return $query->row();
	}

	/**
	 * check_allow_access : リクエストページへのアクセス権限をチェック
	 * 権限がない場合は「page/permission_denied」へリダイレクト
	 * permissionsテーブルの値に「ヒットした場合」権限がないものとして処理する。
	 * レコードがない、またはデータにヒットしない場合は権限ありとする
	 * @note permissionsテーブルuser_idsカラムには権限をつけるユーザーIDを「:」区切りで文字列として格納する
	 * @param $pid : ページID
	 */

	function check_allow_access($pid)
	{
		$uid = ($this->session->userdata('user_id')) ? (int)$this->session->userdata('user_id') : 0;
		$sql = 'SELECT user_ids FROM permissions WHERE page_id = ?';

		$query = $this->db->query($sql, array($pid));
		if ($query->num_rows() > 0)
		{
			$result = $query->row();
			$users = $result->user_ids;
			if (strpos($users, ':') === FALSE)
			{
				if ((string)$users == (string)$uid)
				{
					redirect('page/permission_denied');
				}
			}
			else
			{
				$user_array = explode(':', $users);

				if (in_array($uid, $user_array))
				{
					redirect('page/permission_denied');
				}
			}
		}
	}


	/**
	 * get_page_editable : 対象のページが編集できるかどうかをチェック
	 */
	function get_page_editable($pid)
	{
		$sql = 'SELECT allow_edit_user FROM permissions WHERE page_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		$uid = $this->session->userdata('user_id');
		if (!$uid)
		{
			$uid = 0;
		}

		if ($query->row())
		{
			$result = $query->row();
			if (strpos($result->allow_edit_user, ':' . $uid . ':') === FALSE)
			{
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * is_admin : 管理者権限を持っているユーザーかどうかを判定
	 */
	function is_admin($uid = 0)
	{
		if ($uid == 0)
		{
			$uid = $this->session->userdata('user_id');
			if ($uid == 0)
			{
				return FALSE;
			}
		}
		else if ($uid == 1)
		{
			return TRUE; // master user
		}

		$sql = 'SELECT admin_flag FROM users WHERE user_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$uid));
		if ($query->num_rows() > 0)
		{
			$result = $query->row();
			if ((int)$result->admin_flag === 1)
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	function is_logged_in()
	{
		if ((int)$this->session->userdata('user_id') > 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * get_permission_data : ページIDに関連づいた権限データを取得
	 */
	function get_permission_data($pid)
	{
		$sql = 'SELECT '
			.		'allow_access_user, '
			.		'allow_edit_user, '
			.		'allow_approve_user '
			.	'FROM '
			.		'page_permissions '
			.	'WHERE '
			.		'page_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array((int)$pid));

		if ($query->row())
		{
			return $query->row();
		}
		return FALSE;
	}

	/**
	 * get_approval_page_users : 対象ページの公開権限を持っているユーザーを取得
	 */
	function get_approval_page_user_ids($pid)
	{
		$sql = 'SELECT '
			.		'user_id '
			.	'FROM '
			.		'users '
			.	'WHERE '
			.		'user_id = 1 '
			.		'OR admin_flag = 1'
			;
		$query = $this->db->query($sql);

		foreach ($query->result() as $u)
		{
			$ret[] = $u->user_id;
		}
		$query->free_result();
		// 対象ページの公開する権限をもつユーザーを取得
		$sql2 = 'SELECT allow_approve_user FROM page_permissions '
			. 'WHERE page_id = ? LIMIT 1';
		$query2 = $this->db->query($sql2, array($pid));

		if ($query2->num_rows() === 0)
		{
			return $ret;
		}

		$result = $query2->row();
		if (!$result->allow_approve_user)
		{
			return $ret;
		}

		$users = explode(':', $result->allow_approve_user);
		foreach ($users as $v)
		{
			if (!in_array($v, $ret))
			{
				$ret[] = $v;
			}
		}
		return $ret;
	}
	
	/**
	 * update_block_permission : 対象ブロックの権限更新
	 */
	function update_block_permission($data, $bid)
	{
		// Does block permission data exists?
		$sql = 
					'SELECT '
					.	'block_permissions_id '
					.'FROM '
					.	'block_permissions '
					.'WHERE '
					.	'block_id = ? '
					.'LIMIT 1';
		$query = $this->db->query($sql, array($bid));
		
		if (!$query)
		{
			return FALSE;
		}
		
		if ($query->row())
		{
			// update
			$this->db->where('block_id', $bid);
			return $this->db->update('block_permissions', $data);
		}
		else
		{
			// insert
			$data['block_id'] = $bid;
			return $this->db->insert('block_permissions', $data);
		}
	}
}
