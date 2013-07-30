<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * 管理ユーザー管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class User_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function get_user_list()
	{
		$sql = 'SELECT '
			.		'user_id, '
			.		'user_name, '
			.		'admin_flag '
			.	'FROM '
			.		'users'
			;
		$query = $this->db->query($sql);

		$u = new stdClass();
		$u->user_name = '一般ユーザー';
		$u->admin_flag = 0;
		$ret = array($u);

		foreach($query->result() as $value)
		{
			$ret[$value->user_id] = $value;
		}

		return $ret;
	}

	function search_user($uname, $email, $limit, $offset)
	{
		$bind = array();
		$sql = 'SELECT * FROM users WHERE is_admin_user = 1';
		if ($uname != '')
		{
			$sql .= ' AND user_name LIKE ?';
			$bind[] = '%' . $uname . '%';
		}
		if ($email != '')
		{
			$sql .= ' AND email LIKE ?';
			$bind[] = '%' . $email . '%';
		}
		$sql .= ' LIMIT ? OFFSET ?';
		$bind[] = $limit;
		$bind[] = $offset;
		$query = $this->db->query($sql, $bind);

		return $query->result();
	}

	function search_result_count($uname, $email)
	{
		$bind = array();
		$sql = 'SELECT COUNT(`user_id`) as total FROM users WHERE is_admin_user = 1';
		if ($uname != '')
		{
			$sql .= ' AND user_name LIKE ?';
			$bind[] = '%' . $uname . '%';
		}
		if ($email != '')
		{
			$sql .= ' AND email LIKE ?';
			$bind[] = '%' . $email . '%';
		}
		$query = $this->db->query($sql, $bind);

		$result = $query->row();
		return $result->total;
	}

	function check_already_email($email)
	{
		$sql = 'SELECT user_id FROM users WHERE email = ?';
		$query = $this->db->query($sql, array($email));

		if ($query->row())
		{
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * ユーザ名の重複チェック
	 * @param $username
	 * @param $user_id
	 */
	function check_already_username($username, $user_id)
	{
		$sql =
				'SELECT '
				.	'user_id '
				.'FROM '
				.	'users '
				.'WHERE '
				.	'user_name = ? '
				;
		$query = $this->db->query($sql, array($username));
		
		// case no hit record
		if ( $query->num_rows() === 0 )
		{
			return TRUE;
		}
		
		$result = $query->row();
		// matched user_id equals edit target user_id?
		if ( (int)$result->user_id === $user_id )
		{
			return TRUE;
		}
		return FALSE;
	}

	function update_user($uid, $data)
	{
		$this->db->where('user_id', $uid);
		return $this->db->update('users', $data);
	}

	function delete_user_one($uid)
	{
		// also, now editting pages out
		$this->db->where('edit_user_id', $uid);
		$this->db->update('pages', array('is_editting' => 0, 'edit_user_id' => 0));

		$this->db->where('user_id', $uid);
		$this->db->delete('users');

		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function get_all_admin_users($limit, $offset)
	{
		$sql = 'SELECT '
			.		'user_id, '
			.		'user_name, '
			.		'email, '
			.		'admin_flag, '
			.		'login_times, '
			.		'login_miss_count, '
			.		'image_data '
			.	'FROM '
			.		'users '
			.	'WHERE '
			.		'is_admin_user = 1 '
			.	'ORDER BY user_id ASC '
			.	'LIMIT ? '
			.	'OFFSET ?'
			;
		$query = $this->db->query($sql, array($limit, $offset));

		return $query->result();
	}
	
	function get_all_admin_users_count()
	{
		$sql = 'SELECT user_id FROM users WHERE is_admin_user = 1';
		$query = $this->db->query($sql);

		return $query->num_rows();
	}
	
	function unlock_user_account($user_id)
	{
		$sql =
					'UPDATE '
					.	'users '
					.'SET '
					.	'login_miss_count = 0 '
					.'WHERE '
					.	'user_id = ? '
					.'LIMIT 1';
		return $this->db->query($sql, array((int)$user_id));
	}
	
	function is_dashboard_user($user_id)
	{
		$sql =
					'SELECT '
					.	'user_id '
					.'FROM '
					.	'users '
					.'WHERE '
					.	'user_id = ? '
					.'AND '
					.	'is_admin_user = 1 '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$user_id));
		
		if ($query && $query->row())
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function update_profile_image($data, $user_id)
	{
		// get old image data
		$sql = 
					'SELECT '
					.	'image_data '
					.'FROM '
					.	'users '
					.'WHERE '
					.	'user_id = ? '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$user_id));
		$result = $query->row();
		
		$this->db->where('user_id', $user_id);
		$ret = $this->db->update('users', $data);
		
		if ($ret)
		{
			if (file_exists(FCPATH . 'fieles/members/' . $result->image_data))
			{
				unlink(FCPATH . 'fieles/members/' . $result->image_data);
			}
			return TRUE;
		}
		return FALSE;
	}
}
