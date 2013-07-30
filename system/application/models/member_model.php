<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ========================================================================================
 * 
 * Seezoo member Model
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */
class Member_model extends Model
{
	protected $table = 'sz_members';
	
	// ログイン失敗時にアカウントロックする回数
	private $login_lock_count = 6;
	// アクティベーションコード有効期間（日）
	private $activate_available_date = 1;

	function __construct()
	{
		parent::Model();
	}

	function regist_new_member(&$post)
	{
		$post['activation_code']       = sha1(uniqid(microtime(), TRUE));
		$post['activation_limit_time'] = date('Y-m-d H:i:s', strtotime('+' . $this->activate_available_date . ' day'));
		$ret = $this->db->insert($this->table, $post);

		if ( !$ret )
		{
			return FALSE;
		}
		return $this->db->insert_id();
	}
	
	function insert_member_attributes($atts)
	{
		return $this->db->insert('sz_member_attributes_value', $atts);
	}

	function is_email_already_exists($mail, $with_user = TRUE)
	{
		// notify: 管理ユーザーも登録済みかチェックする（管理ユーザーはインポート機能があるので）
		// @this get query written by Yuta Sakurai
		$sql =
					'SELECT 1 '
					.'FROM '
					.	'%s AS MEM '
					.'WHERE '
					.	'MEM.email = ? ';
		$query = $this->db->query(sprintf($sql, 'sz_members'),array($mail));
		
		if ($query && $query->row())
		{
			return TRUE;
		}
		
		// $with_userがFALSEの場合はメンバーのテーブルしか見ない
		if ( ! $with_user )
		{
			return FALSE;
		}
		
		$query = $this->db->query(sprintf($sql, 'users'),array($mail));
		if ($query && $query->row())
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function is_email_already_exists_frontend($mail, $member_id)
	{
		$sql =
				'SELECT 1 '
				.'FROM '
				.	'%s AS MEM '
				.'WHERE '
				.	'MEM.email = ? '
				;
		$query = $this->db->query(sprintf($sql, 'sz_members') . 'AND MEM.sz_member_id <> ? ',array($mail, (int)$member_id));
		
		if ($query && $query->row())
		{
			return TRUE;
		}
		
		$query = $this->db->query(sprintf($sql, 'users'),array($mail));
		if ($query && $query->row())
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function is_nick_name_already_exists($name)
	{
		$sql = 'SELECT sz_member_id FROM ' . $this->table . ' '
				.'WHERE nick_name = ?';
		$query = $this->db->query($sql, array($name));

		if ( $query && $query->num_rows() > 0)
		{
			return FALSE;
		}
		return TRUE;
	}
	
	function is_nick_name_already_exists_frontend($name, $mid)
	{
		$sql =
				'SELECT '
				.	'sz_member_id ' 
				.'FROM '
				.	'sz_members '
				.'WHERE '
				.	'nick_name = ? '
				.'AND '
				.	'sz_member_id <> ?';
		$query = $this->db->query($sql, array($name, (int)$mid));
		
		if ( $query && $query->num_rows > 0 )
		{
			return FALSE;
		}
		return TRUE;
	}

	function get_admin_email()
	{
		$sql = 'SELECT email FROM users WHERE user_id = 1 LIMIT 1';
		$query = $this->db->query($sql);

		if ( $query && $query->row() )
		{
			$result = $query->row();
			return $result->email;
		}
		return FALSE;

	}

	function activate($code = null)
	{
		$sql =
				'SELECT '
				.	'sz_member_id, '
				.	'nick_name, '
				.	'email, '
				.	'is_activate, '
				.	'activation_limit_time, '
				.	'activation_code '
				.'FROM '
				.	 $this->table . ' '
				.'WHERE '
				.	'activation_code = ? '
				//.'OR '
				//.	'is_activate = 1 '
				.'LIMIT 1';
		$query = $this->db->query($sql, array($code, ''));

		if ($query && $query->row())
		{
			$result = $query->row_array();
			if ($result['is_activate'] > 0)
			{
				// already activated.
				return 'already';
			}
			// code is empty
			else if ( empty($result['activation_code']) )
			{
				return FALSE;
			} 
			else if ( strtotime($result['activation_limit_time']) < time() )
			{
				// timeover
				return 'over';
			}
			// activation success!
			$this->db->where('sz_member_id', $result['sz_member_id']);
			$this->db->update($this->table,
								array(
									'is_activate'            => 1,
									'joined_date'            => db_datetime(),
									//'activation_code'        => '',
									'activation_limit_time' => '0000-00-00 00:00:00'
								)
							);
			return $result;
		}
		return FALSE;
	}
	
	function _insert_member_attribute($member_id)
	{
		$data = array(
			'sz_member_id'	=> (int)$member_id,
			'joined_date'		=> db_datetime()
		);
		
		$this->db->insert('sz_member_attributes', $data);
	}
	
	// メンバーの付加情報を取得
	// @return array
	function get_member_attributes($force_all = FALSE, $with_no_use = FALSE)
	{
		$sql =
				'SELECT '
				.	'sz_member_attributes_id, '
				.	'attribute_name, '
				.	'attribute_type, '
				.	'rows, '
				.	'cols, '
				.	'options, '
				.	'validate_rule, '
				.	'is_inputable, '
				.	'is_use '
				.'FROM '
				.	'sz_member_attributes '
				.'WHERE '
				.	'1 '
				;
		if ( $with_no_use === FALSE )
		{
			$sql .= 'AND is_use = 1 ';
			
		}
		if ( ! $force_all )
		{
			$sql .= 'AND is_inputable > 0 ';
		}
		$sql .= 'ORDER BY '
				.	'display_order ASC '
				;
		$query = $this->db->query($sql);
		
		if ( $query )
		{
			return $query->result();
		}
		return array();
	}
	
	/**
	 * メンバーの付加情報を取得
	 * @param $member_id ユーザーID
	 * @param $is_plain_value 生データを取得するかどうか
	 * @return array
	 * 
	 * note:
	 * $is_plain_valueがTRUEの場合、DB結果セットをそのまま返す。
	 * 渡さない、またはFALSEの場合はチェックボックス等の値は実際のデータに変換されて返る。
	 */
	function get_member_attributes_data($member_id, $is_plain_value = FALSE)
	{
		
		$sql =
				'SELECT '
				.	'ATK.sz_member_attributes_id, '
				.	'ATK.attribute_name as name , '
				.	'ATK.attribute_type, '
				.	'ATK.options, '
				.	'CASE '
				.		'WHEN ATV.sz_member_attributes_value IS NOT NULL THEN ATV.sz_member_attributes_value '
				.		'ELSE ATV.sz_member_attributes_value_text '
				.	'END as value '
				.'FROM '
				.	'sz_member_attributes as ATK '
				.'LEFT OUTER JOIN ( '
				.	'SELECT '
				.		'sz_member_attributes_id, '
				.		'sz_member_attributes_value, '
				.		'sz_member_attributes_value_text '
				.	'FROM '
				.		'sz_member_attributes_value '
				.	'WHERE '
				.		'sz_member_id = ? '
				.') as ATV ON ( '
				.	'ATK.sz_member_attributes_id = ATV.sz_member_attributes_id '
				.') '
				.'WHERE '
				.	'ATK.is_use = 1 '
				.'ORDER BY '
				.	'ATK.display_order ASC '
				;
		/*
		// more faster SQL.
		$sql =
				'SELECT '
				.	'ATK.sz_member_attributes_id, '
				.	'ATK.attribute_name as name , '
				.	'ATK.attribute_type, '
				.	'ATK.options, '
				.	'CASE '
				.		'WHEN ATV.sz_member_attributes_value IS NOT NULL THEN ATV.sz_member_attributes_value '
				.		'ELSE ATV.sz_member_attributes_value_text '
				.	'END as value '
				.'FROM '
				.	'sz_member_attributes_value as ATV '
				.'LEFT OUTER JOIN ( '
				.	'SELECT '
				.		'sz_member_attributes_id, '
				.		'attribute_name, '
				.		'attribute_type, '
				.		'display_order, '
				.		'options '
				.	'FROM '
				.		'sz_member_attributes '
				.	'WHERE '
				.		'is_use = 1 '
				.') as ATK ON ( '
				.	'ATV.sz_member_attributes_id = ATK.sz_member_attributes_id '
				.') '
				.'WHERE '
				.	'ATV.sz_member_id = ? '
				.'ORDER BY '
				.	'ATK.display_order ASC '
				;
		*/
		$query = $this->db->query($sql, array((int)$member_id));
		
		$ret = array();
		if ( $query )
		{
			if ( $is_plain_value === TRUE )
			{
				return $query->result();
			}
			foreach ( $query->result() as $v )
			{
				$obj = new stdClass;
				$obj->name = $v->name;
				// attribute is selectable value?
				if ( preg_match('/^select|radio|checkbox$/u', $v->attribute_type) )
				{
					$exp = explode(':', $v->options);
					// Is attribute multi-selectable value?
					if ( $v->attribute_type === 'checkbox' )
					{
						$tmp = array();
						foreach (explode(':', $v->value) as $v )
						{
							if ( ! isset($exp[$v]) ) {
								continue;
							}
							$tmp[] = $exp[$v];
						}
						$obj->value = implode(', ', $tmp) . '';
					}
					else 
					{
						$obj->value = (isset($exp[$v->value])) ? $exp[$v->value] : '';
					}
				}
				// else, simple string value
				else 
				{
					$obj->value = $v->value;
				}
				$ret[] = $obj;
			}
		}
		return $ret;
	}
	
	/**
	 * メンバーの追加項目を1件だけ指定して取得
	 * @param $attribute_id
	 */
	function get_member_attribute_setting($attribute_id)
	{
		$sql =
			'SELECT '
			.	'* '
			.'FROM '
			.	'sz_member_attributes '
			.'WHERE '
			.	'sz_member_attributes_id = ? '
			.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$attribute_id));
		
		if ( $query && $query->row() )
		{
			return $query->row();
		}
		return FALSE;
	}
	
	/**
	 * メンバーのログインを実行
	 * @param $uname
	 * @param $pass
	 */
	function login($uname, $pass)
	{
		// detect hash
		$sql =
				'SELECT '
				.	'sz_member_id, '
				.	'hash, '
				.	'password '
				.'FROM '
				.	$this->table .' '
				.'WHERE ('
				.	'nick_name = ? '
				.'OR '
				.	'email = ? '
				.') '
				.'AND '
				.	'login_miss_count < ? '
				.'AND '
				.	'is_activate = 1 '
				.'LIMIT 1';

		$query = $this->db->query($sql, array($uname, $uname, $this->login_lock_count));
		
		if ($query && $query->row())
		{
			$member = $query->row();
			
			// detect password stretching algorithm
			$algorithm_case = substr($member->password, 0, 3);
			if ( $algorithm_case === '$1$' )
			{
				// case md5 stretching
				$algorithm = 'md5';
				$password  = substr($member->password, 3);
			}
			else if ( $algorithm_case === '$2$' )
			{
				// case sha1 stretching
				$algorithm = 'sha1';
				$password  = substr($member->password, 3);
			}
			else if ( $algorithm_case === '$3$' )
			{
				// case sha256 stretching
				$algorithm = 'sha256';
				$password  = substr($member->password, 3);
			}
			else
			{
				// not stretching
				$algorithm = FALSE;
				$password  = $member->password;
			}
			
			$match_password = password_stretch($member->hash, $pass, $algorithm);
			
			// password match?
			if ($match_password === $password)
			{
				// all green!
				$this->session->set_userdata('member_id', $member->sz_member_id);
				$this->_update_login_times($member->sz_member_id);
				
				return TRUE;
			}
			else 
			{
				// password is no match...
				// increment login_miss_count.
				$sql =
					'UPDATE '
					.	'sz_members '
					.'SET '
					.	'login_miss_count = login_miss_count + 1 '
					.'WHERE '
					.	'sz_member_id = ? '
					;
				$this->db->query($sql, array((int)$member->sz_member_id));
			}
			
		}
		return FALSE;
	}
	
	function password_match($pass, $member_id)
	{
				// detect hash
		$sql =
				'SELECT '
				.	'sz_member_id, '
				.	'hash, '
				.	'password '
				.'FROM '
				.	$this->table .' '
				.'WHERE '
				.	'sz_member_id = ? '
				.'LIMIT 1'
				;
		$query = $this->db->query($sql, array($member_id));
		
		if ( ! $query || ! $query->row() )
		{
			return FALSE;
		}
		
		$member = $query->row();
		
		// detect password stretching algorithm
		$algorithm_case = substr($member->password, 0, 3);
		if ( $algorithm_case === '$1$' )
		{
			// case md5 stretching
			$algorithm = 'md5';
			$password  = substr($member->password, 3);
		}
		else if ( $algorithm_case === '$2$' )
		{
			// case sha1 stretching
			$algorithm = 'sha1';
			$password  = substr($member->password, 3);
		}
		else if ( $algorithm_case === '$3$' )
		{
			// case sha256 stretching
			$algorithm = 'sha256';
			$password  = substr($member->password, 3);
		}
		else
		{
			// not stretching
			$algorithm = FALSE;
			$password  = $member->password;
		}
		
		$match_password = password_stretch($member->hash, $pass, $algorithm);
		
		// password match?
		return ( $match_password === $password ) ? TRUE : FALSE;
	}
	
	/**
	 * メンバーのログイン情報を更新
	 * @param $member_id
	 */
	function _update_login_times($member_id)
	{
		$sql = 
				'UPDATE '
				.	'sz_members '
				.'SET '
				.	'login_times = login_times + 1, '
				.	'login_miss_count = 0 '
				.'WHERE '
				.	'sz_member_id = ? '
				;
				
		$this->db->query($sql, array((int)$member_id));
	}
	
	/**
	 * 管理者ユーザーがメンバーテーブルにエクスポートされているかをチェック
	 * @param $user_id
	 */
	function is_admin_exproted($user_id)
	{
		$sql =
					'SELECT '
					.	'sz_member_id '
					.'FROM '
					.	'sz_members '
					.'WHERE '
					.	'relation_site_user = ? '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$user_id));
		
		return ($query && $query->row()) ? TRUE : FALSE;
	}
	
	/**
	 * 管理者ユーザーをメンバーテーブルにエクスポート
	 * @param $nick_name
	 * @param $user_id
	 */
	function export_account($nick_name, $user_id)
	{
		// get admin data
		$sql =
				'SELECT '
				.	'email, '
				.	'password, '
				.	'hash, '
				.	'regist_time, '
				.	'image_data, '
				.	'login_times '
				.'FROM '
				.	'users '
				.'WHERE '
				.	'user_id = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$user_id));
		
		if (!$query || !$query->row())
		{
			return FALSE;
		}
		
		$result = $query->row();
		$query->free_result();
		
		// insert member_record
		$member = array(
			'nick_name'			=> $nick_name,
			'email'				=> $result->email,
			'is_activate'			=> 1,
			'relation_site_user'	=> $user_id,
			'activation_code'		=> '',
			'password'				=> $result->password,
			'hash'					=> $result->hash,
			'joined_date'			=> $result->regist_time,
			'image_data'			=> $result->image_data,
			'login_times'			=> $result->login_times
		);
		
		return $this->db->insert('sz_members', $member);
	}
	
	/**
	 * メンバーの基礎情報を一件取得
	 * @param $mid
	 */
	function get_member_one($mid)
	{
		$sql =
				'SELECT '
				.	'nick_name, '
				.	'email '
				.'FROM '
				.	'sz_members '
				.'WHERE '
				.	'sz_member_id = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$mid));
		
		if ($query && $query->row())
		{
			return $query->row();
		}
		return FALSE;
	}
	
	/**
	 * メールアドレスからメンバー特定
	 * @param $email
	 */
	function get_member_data_from_email($email)
	{
		$sql =
				'SELECT '
				.	'* '
				.'FROM '
				.	'sz_members '
				.'WHERE '
				.	'email = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array($email));
		
		return $query->row();
	}
	
	/**
	 * admin userからエクスポート済みのmember_idを取得
	 * @param $uid
	 * @return int
	 */
	function get_member_id_from_admin_user($uid)
	{
		// if userid eq 0, don't execute SQL.
		if ( (int)$uid === 0)
		{
			return 0;
		}
		$sql =
				'SELECT '
				.	'sz_member_id '
				.'FROM '
				.	'sz_members '
				.'WHERE '
				.	'relation_site_user = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array($uid));
		
		if ($query && $query->row())
		{
			$result = $query->row();
			return (int)$result->sz_member_id;
		}
		return 0;
	}
	
	/**
	 * メンバーの詳細データを取得
	 * @param $mid
	 */
	function get_member_detail($mid)
	{
		$sql = 'SELECT '
				.	'MEM.sz_member_id, '
				.	'MEM.nick_name, '
				.	'MEM.email, '
				.	'MEM.is_activate, '
				.	'MEM.relation_site_user, '
				.	'MEM.login_times, '
				.	'MEM.image_data, '
				.	'MEM.joined_date, '
				.	'MEM.twitter_id, '
				.	'CASE '
				.		'WHEN MEM.login_miss_count > 5 THEN 1 '
				.		'ELSE 0 '
				.	'END as banned '
				.'FROM '
				.	'sz_members as MEM '
				.'WHERE '
				.	'MEM.sz_member_id = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$mid, (int)$mid, (int)$mid));

		if ($query && $query->row())
		{
			return $query->row();
		}
		return FALSE;
	}
	
	/**
	 * メールアドレスが既に存在するかをチェック
	 * @param $str
	 */
	function check_already_email($str)
	{
		$sql =
				'SELECT '
				.	'sz_member_id '
				.'FROM '
				.	'sz_members '
				.'WHERE '
				.	'email = ? ';
		$query = $this->db->query($sql, array($str));
		
		if ($query && $query->num_rows() === 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * メンバー登録
	 * @param $post
	 */
	function regist_member($post)
	{
		return $this->db->insert('sz_members', $post);
	}
	
	/**
	 * メンバー基礎情報更新
	 * @param $mid
	 * @param $post
	 */
	function update_member($mid, $post)
	{
		$this->db->where('sz_member_id', $mid);
		return $this->db->update('sz_members', $post);
	}
	
	/**
	 * メンバー付加項目更新
	 * @param $atts
	 * @param $member_id
	 */
	function update_member_attribute_data($atts, $member_id)
	{
		foreach ( $atts as $key => $value)
		{
			// Does attribute_value record exits?
			if ( $this->_is_attribure_value_exists($member_id, $key) )
			{
				// update
				$this->db->where('sz_member_attributes_id', $key);
				$this->db->where('sz_member_id', $member_id);
				$ret = $this->db->update('sz_member_attributes_value', $value);
			}
			else
			{
				// insert
				$column = array_keys($value);
				$data = array(
					'sz_member_attributes_id'	=> $key,
					'sz_member_id'				=> $member_id,
					$column[0]						=> $value[$column[0]]
				);
				$ret = $this->db->insert('sz_member_attributes_value', $data);
			}
			
			if ( ! $ret )
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
	function _is_attribure_value_exists($member_id, $attribute_id)
	{
		$sql =
			'SELECT '
			.	'1 '
			.'FROM '
			.	'sz_member_attributes_value '
			.'WHERE '
			.	'sz_member_id = ? '
			.'AND '
			.	'sz_member_attributes_id = ? '
			.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$member_id, (int)$attribute_id));
		
		return ( $query && $query->row() ) ? TRUE : FALSE;
		
			
	}

	/**
	 * メンバー検索
	 * @param unknown_type $uname
	 * @param unknown_type $email
	 * @param  $limit
	 * @param  $offset
	 */
	function search_member($name, $email, $limit, $offset)
	{
		$bind = array();
		$sql = 
			'SELECT '
			.	'sz_member_id, '
			.	'nick_name, '
			.	'login_times, '
			.	'email, '
			.	'CASE '
			.		'WHEN login_miss_count > 5 THEN 1 '
			.		'ELSE 0 '
			.	'END as banned '
			.'FROM sz_members WHERE 1';
		if ($name != '')
		{
			$sql .= ' AND nick_name LIKE ?';
			$bind[] = '%' . $name . '%';
		}
		if ($email != '')
		{
			$sql .= ' AND email LIKE ?';
			$bind[] = '%' . $email . '%';
		}
		$sql .= ' LIMIT ? OFFSET ?';
		$bind[] = $limit;
		$bind[] = $offset;
		$query= $this->db->query($sql, $bind);

		return $query->result();
	}

	/**
	 * メンバー検索ヒット件数取得
	 * @param unknown_type $uname
	 * @param unknown_type $email
	 */
	function search_result_count($name, $email)
	{
		$bind = array();
		$sql = 'SELECT COUNT(`sz_member_id`) as total FROM sz_members WHERE 1';
		if ($name != '')
		{
			$sql .= ' AND nick_name LIKE ?';
			$bind[] = '%' . $name . '%';
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

	/**
	 * メンバーのリストを取得
	 * @param unknown_type $limit
	 * @param unknown_type $offset
	 */
	function get_all_members($limit, $offset)
	{
		$sql =
			'SELECT '
			.	'M.sz_member_id, '
			.	'M.nick_name, '
			.	'M.email, '
			.	'M.relation_site_user, '
			.	'M.is_activate, '
			.	'M.joined_date, '
			.	'CASE '
			.		'WHEN M.login_miss_count > 5 THEN 1 '
			.		'ELSE 0 '
			.	'END as banned '
			.'FROM '
			.	$this->table .' as M '
			.'ORDER BY sz_member_id ASC '
			.'LIMIT ? '
			.'OFFSET ?'
			;
		$query = $this->db->query($sql, array($limit, $offset));

		return $query->result();
	}
	
	/**
	 * メンバーの総件数を取得
	 */
	function get_all_members_count()
	{
		$sql = 'SELECT sz_member_id FROM sz_members';
		$query = $this->db->query($sql);

		return $query->num_rows();
	}
	
	/**
	 * メンバーのアカウントロックを解除
	 * @param $member_id
	 */
	function unlock_member_account($member_id)
	{
		$sql =
					'UPDATE '
					.	'sz_members '
					.'SET '
					.	'login_miss_count = 0 '
					.'WHERE '
					.	'sz_member_id = ? '
					.'LIMIT 1';
		return $this->db->query($sql, array((int)$member_id));
	}
	
	function update_profile_image($data, $member_id)
	{
		// get old image data
		$sql = 
				'SELECT '
				.	'image_data '
				.'FROM '
				.	'sz_members '
				.'WHERE '
				.	'sz_member_id = ? '
				.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$member_id));
		$result = $query->row();
		
		$this->db->where('sz_member_id', $member_id);
		$ret = $this->db->update('sz_members', $data);
		
		if ($ret)
		{
			if (file_exists(FCPATH . 'fieles/members/' . $result->image_data))
			{
				@unlink(FCPATH . 'fieles/members/' . $result->image_data);
			}
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * メンバー追加項目の入力タイプを配列で設定
	 */
	function get_member_attribute_types()
	{
		return array(
			'text'			=> '一行テキストフィールド',
			'textarea'		=> 'テキストエリア（複数行）',
			'selectbox'	=> 'セレクトボックス',
			'radio'		=> 'ラジオボタン',
			'checkbox'		=> 'チェックボックス',
			'pref'		=> '都道府県選択ボックス'
		);
	}
	
	/**
	 * メンバー追加項目更新
	 * @param $post
	 * @param $id
	 */
	function update_member_attribute($post, $id)
	{
		$this->db->where('sz_member_attributes_id', $id);
		return $this->db->update('sz_member_attributes', $post);
	}
	
	/**
	 * メンバー追加項目追加
	 * @param $post
	 */
	function insert_attribute($post)
	{
		return $this->db->insert('sz_member_attributes', $post);
	}
	
	/**
	 * メンバー追加項目の並び順の最大値+1を取得
	 */
	function get_member_attribute_max_display_order()
	{
		$sql =
				'SELECT '
				.	'MAX(display_order) as max '
				.'FROM '
				.	'sz_member_attributes '
				.'LIMIT 1';
		$query = $this->db->query($sql);
		
		if ( $query && $query->row() )
		{
			$result = $query->row();
			return (int)$result->max + 1;
		}
		return 1;
	}
	
	/**
	 * メンバー項目並び順入れ替え
	 * @param unknown_type $from
	 * @param unknown_type $to
	 */
	function update_attribute_display_order($from, $to)
	{
		$sql =
			'SELECT '
			.	'display_order '
			.'FROM '
			.	'sz_member_attributes '
			.'WHERE '
			.	'sz_member_attributes_id = ? '
			.'LIMIT 1';
		
		// from
		$query = $this->db->query($sql, array($from));
		if ( ! $query || ! $query->row() )
		{
			return FALSE;
		}
		$result = $query->row();
		$from_order = (int)$result->display_order;
		
		// to
		$query = $this->db->query($sql, array($to));
		if ( ! $query || ! $query->row() )
		{
			return FALSE;
		}
		$result = $query->row();
		$to_order = (int)$result->display_order;
		
		// cross order
		$sql = 
			'UPDATE '
			.	'sz_member_attributes '
			.'SET '
			.	'display_order = ? '
			.'WHERE '
			.	'sz_member_attributes_id = ? '
			.'LIMIT 1';
			
		if ( ! $this->db->query($sql, array($to_order, $from)) )
		{
			return FALSE;
		}
		if ( ! $this->db->query($sql, array($from_order, $to)) )
		{
			return FALSE;
		}
		return TRUE;
	}
	
	function delete_member_one($id)
	{
		$this->db->where('sz_member_id', $id);
		$this->db->delete('sz_members');
		
		// delete attribute_values too.
		$this->db->where('sz_member_id', $id);
		$this->db->delete('sz_member_attributes_value');
		
		return TRUE;
	}
	
	/**
	 * メンバー項目の使用/不使用切り替え
	 * @param $is_use
	 * @param $att_id
	 */
	function update_member_attribute_use($is_use, $att_id)
	{
		$this->db->where('sz_member_attributes_id', $att_id);
		return $this->db->update('sz_member_attributes', array('is_use' => $is_use));
	}
	
	/**
	 * メンバー項目の削除
	 * @param $att_id
	 */
	function delete_member_attribute($att_id)
	{
		$this->db->where('sz_member_attributes_id', $att_id);
		$this->db->delete('sz_member_attributes');
		
		return ( $this->db->affected_rows() > 0 ) ? TRUE : FALSE;
	}
	
	/**
	 * パスワード再設定
	 */
	function update_new_password($member_id, $password)
	{
		// 削除用のアクティベーションデータも作成
		$password['activation_code']       = '';
		$password['activation_limit_time'] = '0000-00-00 00:00:00';
		
		$this->db->where('sz_member_id', $member_id);
		if ( $this->db->update('sz_members', $password) )
		{
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * パスワード再発行時のアクティベーションコード照合
	 * @param $code
	 */
	function has_rebuild_password_activation($code)
	{
		$sql = 
				'SELECT '
				.	'sz_member_id, '
				.	'activation_limit_time, '
				.	'activation_code, '
				.	'nick_name, '
				.	'email '
				.'FROM '
				.	'sz_members '
				.'WHERE '
				.	'activation_code = ? '
				.'LIMIT 1'
				;
		$query = $this->db->query($sql, array($code));
		if ( ! $query || ! $query->row() )
		{
			return FALSE;
		}
		$result = $query->row();
		if ( empty($result->activation_code) )
		{
			return FALSE;
		}
		// activation code is avaialble?
		if ( (int)strtotime($result->activation_limit_time) < time() )
		{
			return 'over';
		}
		
		return $result;
	}
	
	/**
	 * パスワード再発行用のアクティベーションコード保存
	 * @param $member_id
	 * @param $code
	 */
	function set_rebuild_password_activation($member_id, $code)
	{
		$data = array(
			'activation_code'       => $code,
			'activation_limit_time' => date('Y-m-d H:i:s', strtotime('+' . $this->activate_available_date . ' day'))
		);
		
		$this->db->where('sz_member_id', $member_id);
		return $this->db->update('sz_members', $data);
	}
	
	/**
	 * メンバー退会処理（ = レコード削除）
	 * @param $member_id
	 */
	function do_secession($member_id)
	{
		// delete member record
		$this->db->where('sz_member_id', $member_id);
		$this->db->delete('sz_members');
		
		if ( $this->db->affected_rows() > 0 )
		{
			// delete attribute data
			$this->db->where('sz_member_id', $member_id);
			$this->db->delete('sz_member_attributes_value');
			
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Twitterからのデータでログインする
	 * @param $member_data
	 */
	function twitter_login($member_data)
	{
		// まず既にデータがあるかをチェック
		$sql = 'SELECT sz_member_id, login_times FROM sz_members WHERE twitter_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$member_data['twitter_id']));
		
		return $this->_social_login($query, $member_data);
	}
	
	/**
	 * facebookからのデータでログインする
	 * @param $member_data
	 */
	function facebook_login($member_data)
	{
		// まず既にデータがあるかをチェック
		$sql = 'SELECT sz_member_id, login_times FROM sz_members WHERE facebook_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$member_data['facebook_id']));
		
		return $this->_social_login($query, $member_data);
	}
	
	/**
	 * Googleからのデータでログインする
	 * @param $member_data
	 */
	function google_login($member_data)
	{
		// まず既にデータがあるかをチェック
		$sql = 'SELECT sz_member_id, login_times FROM sz_members WHERE google_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$member_data['google_id']));
		
		return $this->_social_login($query, $member_data);
	}
	
	
	private function _social_login($query, $member_data)
	{
		if ( $query && $query->row() )
		{
			// update
			$result = $query->row();
			$member_data['login_times'] = (int)$result->login_times + 1;
			$this->db->where('sz_member_id', $result->sz_member_id);
			if ( $this->db->update('sz_members', $member_data) )
			{
				return $result->sz_member_id;
			}
		}
		else 
		{
			// insert
			$member_data['joined_date'] = db_datetime();
			$member_data['login_times'] = 1;
			if ( $this->db->insert('sz_members', $member_data) )
			{
				return $this->db->insert_id();
			}
		}
		return FALSE;
	}
}
