<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * ログイン用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */
class Auth_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function login($uname, $pass, $is_admin_login = FALSE)
	{
		$sql = 
			'SELECT '
			.	'user_id, '
			.	'hash, '
			.	'password, '
			.	'login_times, '
			.	'admin_flag, '
			.	'login_miss_count '
			.'FROM '
			.	'users '
			.'WHERE '
			.	'user_name = ? '
			.'AND '
			.	'login_miss_count < 3 ';
		if ($is_admin_login)
		{
			$sql .= 'AND is_admin_user = 1 ';
		}
		$sql .= 'LIMIT 1';
		$query = $this->db->query($sql, array($uname));

		if ($query->row())
		{
			$result = $query->row();

			// crypted password is match?
			//$password = md5($result->hash . $pass);
			
			// detect password stretching algorithm
			$algorithm_case = substr($result->password, 0, 3);
			if ( $algorithm_case === '$1$' )
			{
				// case md5 stretching
				$algorithm = 'md5';
				$password  = substr($result->password, 3);
			}
			else if ( $algorithm_case === '$2$' )
			{
				// case sha1 stretching
				$algorithm = 'sha1';
				$password  = substr($result->password, 3);
			}
			else if ( $algorithm_case === '$3$' )
			{
				// case sha256 stretching
				$algorithm = 'sha256';
				$password  = substr($result->password, 3);
			}
			else
			{
				// not stretching
				$algorithm = FALSE;
				$password  = $result->password;
			}
			
			$match_password = password_stretch($result->hash, $pass, $algorithm);

			if ($match_password === $password)
			{
				// update login user data
				$data = array(
					'last_login'			=> date('Y-m-d H:i:s', time()),
					'login_times'			=> (int)$result->login_times + 1,
					'login_miss_count'	=> 0
				);

				$this->db->where('user_id', $result->user_id);
				$this->db->update('users', $data);

				// set login session
				$this->session->set_userdata('user_id', $result->user_id);
				
				// protect code
				if ($this->session->userdata('edit_version'))
				{
					$this->session->unset_userdata('edit_version');
				}

				// 前回までのログインデータが残っていると不整合が起きるので全削除
				$this->_delete_all_edit_state($result->user_id);

				if ($result->admin_flag == 1)
				{
					$return_path = 'dashboard/panel';
				}
				else
				{
					$return_path = '/';
				}

				return $return_path;
			}
			else 
			{
				if ($result->login_miss_count < 4 && $result->user_id > 1)
				{
					$update = array(
						'login_miss_count' => (int)$result->login_miss_count + 1
					);
					$this->db->where('user_id', $result->user_id);
					$this->db->update('users', $update);
				}
			}
		}
		return FALSE;
	}

	function logout()
	{
		if ( $this->session->userdata('user_id') )
		{
			$sql = 'UPDATE '
				.		'pages '
				.	'SET '
				.		'is_editting = ?, '
				.		'edit_user_id = ? '
				.	'WHERE '
				.		'edit_user_id = ?';
			$query = $this->db->query($sql, array(0, 0, $this->session->userdata('user_id')));
	
			$this->session->unset_userdata('user_id');
			$this->session->unset_userdata('rollback_user');
			$this->session->unset_userdata('viewmode');
		}
	}
	
	function member_logout()
	{
		if ( $this->session->userdata('member_id') )
		{
			$this->session->unset_userdata('member_id');
		}
	}

	function relogin_with_other_user($uid)
	{
		if ((int)$this->session->userdata('user_id') !== 1)
		{
			return FALSE;
		}
		$this->session->set_userdata('user_id', $uid);
		return TRUE;
	}

	function _delete_all_edit_state($uid)
	{
		$data = array(
			'is_editting'		=> 0,
			'edit_user_id'		=> 0,
			'is_arranging'		=> 0,
			'edit_start_time'	=> '0000-00-00 00:00:00'
		);
		$this->db->where('edit_user_id', $uid);
		$this->db->update('pages', $data);

		if ($this->session->userdata('edit_version'))
		{
			$this->session->unset_userdata('edit_version');
		}
		if ($this->session->userdata('is_arrange'))
		{
			$this->session->unet_userdata('is_arrange');
		}
	}

	function set_remember_token($val)
	{
		$this->db->where('user_id', $this->session->userdata('user_id'));
		$this->db->update('users', array('remember_token' => $val));
	}

	function remember_login($val)
	{
		$sql = 'SELECT '
			.		'user_id, '
			.		'admin_flag '
			.	'FROM '
			.		'users '
			.	'WHERE '
			.		'remember_token = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($val));

		if ($query->row())
		{
			$result = $query->row();
			$this->session->set_userdata('user_id', $result->user_id);
			if ($this->session->userdata('edit_version'))
			{
				$this->session->unset_userdata('edit_version');
			}
			// 前回までのログインデータが残っていると不整合が起きるので全削除
			$this->_delete_all_edit_state($result->user_id);

			if ($result->admin_flag > 0)
			{
				redirect('dashboard/panel');
			}
			else
			{
				redirect('/');
			}
		}
		return FALSE;
	}

	function is_email($mail)
	{
		$sql = 'SELECT user_id FROM users WHERE email = ? LIMIT 1';
		$query = $this->db->query($sql, array($mail));

		if ($query->row())
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function update_new_password_for_email($mail, $data)
	{
		$this->db->where('email', $mail);
		return $this->db->update('users', $data);
	}
	
	function get_master_email()
	{
		$CI =& get_instance();
		if ( isset($CI->site_data) && !empty($CI->site_data->system_mail_from) )
		{
			return $CI->site_data->system_mail_from;
		}
		$sql =
				'SELECT '
				.	'email '
				.'FROM '
				.	'users '
				.'WHERE '
				.	'user_id = 1 '
				.'LIMIT 1';
		$query = $this->db->query($sql);
		
		if ($query && $query->row())
		{
			$result = $query->row();
			return $result->email;
		}
		return 'info@example.com';
	}
	
	function generate_member_activation_code($member_id, $email)
	{
		$salt = sha1(uniqid(mt_rand(), TRUE));
		$code = sha1($member_id . $email . $salt);
		
		$insert = array(
			'activation_code'       => $code,
			'sz_member_id'          => $member_id,
			'email'                 => $email,
			'activation_limit_time' => date('Y-m-d H:i:s', strtotime('+1 day'))
		);
		
		if ( $this->db->insert('sz_activation_data', $insert) )
		{
			return $code;
		}
		return FALSE;
	}
	
	function do_member_activation($code)
	{
		$sql =
				'SELECT '
				.	'sz_member_id, '
				.	'email, '
				.	'activation_limit_time '
				.'FROM '
				.	'sz_activation_data '
				.'WHERE '
				.	'activation_code = ? '
				.'LIMIT 1'
				;
		$query = $this->db->query($sql, array($code));
		
		if ( $query->num_rows() == 0 )
		{
			return FALSE;
		}
		
		$result = $query->row();
		if ( strtotime($result->activation_limit_time) < time() )
		{
			$ret = 'timeout';
		}
		else
		{
			$ret = TRUE;
		}
		
		// update email
		$this->db->where('sz_member_id', $result->sz_member_id);
		if ( ! $this->db->update('sz_members', array('email' => $result->email)) )
		{
			return FALSE;
		}
		
		// delete record
		$this->db->where('activation_code', $code);
		$this->db->delete('sz_activation_data');
		
		return $ret;
	}
}
