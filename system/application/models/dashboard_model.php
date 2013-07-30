<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * 管理画面用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Dashboard_model extends Model
{
	function __cosntruct()
	{
		parent::Model();
	}

	function is_master()
	{
		if ((int)$this->session->userdata('user_id') === 1)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function is_loaded_mod_rewrite()
	{
		$sql = 'SELECT enable_mod_rewrite FROM site_info LIMIT 1';
		$query = $this->db->query($sql);

		$result = $query->row();
		return $result->enable_mod_rewrite;
		//$apache = apache_get_modules();
		//return in_array('mod_rewrite', $apache);
	}

	function change_mod_rewrite_flag($flag)
	{
		return $this->db->update('site_info', array('enable_mod_rewrite' => $flag));
	}

	function change_site_cache($flag)
	{
		return $this->db->update('site_info', array('enable_cache' => $flag));
	}

	function get_first_child_page($pid)
	{
		if ($pid == 0)
		{
			return 'dahsboard/panel';
		}
		$sql = 'SELECT '
			.		'PP.page_path '
			.	'FROM '
			.		'page_versions as PV '
			.		'RIGHT OUTER JOIN page_paths as PP '
			.			'USING(`page_id`) '
			.	'WHERE '
			.		'PV.parent = ? '
			.	'ORDER BY '
			.		'PV.display_order ASC, '
			.		'PV.version_number DESC '
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
			return 'dashboard/panel';
		}
	}

	function get_user_data()
	{
		$uid = $this->session->userdata('user_id');

		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'users '
			.	'WHERE '
			.		'user_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($uid));

		return $query->row();
	}

	function get_user_name_by_id($uid)
	{
		$sql = 'SELECT user_name FROM users WHERE user_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($uid));

		$result = $query->row();
		return $result->user_name;
	}

	function is_maintenance_mode()
	{
		$sql = 'SELECT is_maintenance FROM site_info LIMIT 1';
		$query = $this->db->query($sql);

		$result = $query->row();
		if ((int)$result->is_maintenance > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function get_edit_page_count($uid)
	{
		$sql = 'SELECT '
			.		'COUNT(page_id) as p '
			.	'FROM '
			.		'pages '
			.	'WHERE '
			.		'edit_user_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($uid));
		$result = $query->row();

		return $result->p;
	}

	function get_site_info()
	{
		$sql = 'SELECT * FROM site_info LIMIT 1';
		$query = $this->db->query($sql);

		return $query->row();
	}

	function update_site_info($post)
	{
		return $this->db->update('site_info', $post);
	}

	function get_default_template($tid)
	{
		$sql = 'SELECT template_name FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$tid));

		$result = $query->row();
		return $result->template_name;
	}

	function get_all_users($limit, $offset)
	{
		$sql = 'SELECT '
			.		'user_id, '
			.		'user_name, '
			.		'email, '
			.		'admin_flag, '
			.		'login_times '
			.	'FROM '
			.		'users '
			.	'ORDER BY user_id ASC '
			.	'LIMIT ? '
			.	'OFFSET ?'
			;
		$query = $this->db->query($sql, array($limit, $offset));

		return $query->result();
	}

	function get_all_users_count()
	{
		$sql = 'SELECT user_id FROM users';
		$query = $this->db->query($sql);

		return $query->num_rows();
	}

	function get_user_one($uid)
	{
		$sql = 'SELECT * FROM users WHERE user_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($uid));

		return $query->row();
	}

	function enc_password($password)
	{
		$hash      = sha1(microtime());
		$algorithm = ( defined('SEEZOO_PASSWORD_ENCRYPT_ALGORITHM') )
		               ? SEEZOO_PASSWORD_ENCRYPT_ALGORITHM
		               : 'md5';
		$prefix    = $this->_make_password_stretch_algorithm_prefix(strtolower($algorithm)); 
		$enc_pass  = password_stretch($hash, $password, $algorithm);

		return array('hash' => $hash, 'password' => $prefix . $enc_pass);
	}
	
	function _make_password_stretch_algorithm_prefix($algorithm)
	{
		$prefix = '';
		if ( ! function_exists($algorithm) )
		{
			return $prefix;
		}
		else if ( $algorithm === 'md5' )
		{
			$prefix = '$1$';
		}
		else if ( $algorithm === 'sha1' )
		{
			$prefix = '$2$';
		}
		else if ( $algorithm === 'sha256' )
		{
			$prefix = '$3$';
		}
		
		return $prefix;
	}

	function regist_user($post)
	{
		if ( ! $this->db->insert('users', $post) )
		{
			return FALSE;
		}
		$uid = $this->db->insert_id();
		// set allow access permission on current page-versions (front page only).
		$sql =
			'SELECT '
			.	'PERM.allow_access_user, '
			.	'PERM.page_permissions_id, '
			.	'PP.page_id, '
			.	'PP.page_path '
			.'FROM '
			.	'page_paths as PP '
			.'LEFT OUTER JOIN page_permissions as PERM '
			.	'USING (page_id) '
			.'WHERE '
			.	'PP.page_path NOT LIKE \'dashboard/%\' '
			.'AND '
			.	'PERM.allow_access_user IS NOT NULL';

		$query = $this->db->query($sql);
		
		if ( $query && $query->num_rows() > 0 )
		{
			// prepare SQL
			$update_sql = 
					'UPDATE '
					.	'page_permissions '
					.'SET '
					.	'allow_access_user = ? '
					.'WHERE '
					.	'page_permissions_id = ? '
					.'LIMIT 1';
			
			foreach ( $query->result() as $pm )
			{
				// if permission is empty, set new record
				if ( $pm->allow_access_user == '' )
				{
					$v = ':' . $uid . ':';
				}
				else 
				{
					$v = $pm->allow_access_user . $uid . ':';
				}
				if ( ! $this->db->query($update_sql, array($v, (int)$pm->page_permissions_id)) )
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	function get_template_name_by_id($tid)
	{
		$sql = 'SELECT template_name FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$tid));

		$result = $query->row();
		return $result->template_name;
	}

	function delete_approve_order($paoid)
	{
		$this->db->where('page_approve_orders_id', $paoid);
		$this->db->delete('page_approve_orders');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	function get_approve_statuses($uid)
	{
		$sql = 'SELECT '
			.		'pao.*, '
			.		'pv.page_title, '
			.		'u.user_name '
			.	'FROM '
			.		'page_approve_orders as pao '
			.		'RIGHT OUTER JOIN page_versions as pv ON('
			.			'pao.version_number = pv.version_number'
			.		') '
			.		'LEFT OUTER JOIN users as u ON('
			.			'pao.approved_user_id = u.user_id'
			.		') '
			.		'WHERE '
			.			'pao.ordered_user_id = ? '
			.		'LIMIT 1';
		$query = $this->db->query($sql, array($uid));

		if ($query && $query->num_rows() > 0)
		{
			return $query->result();
		}
		return array();
	}

	function get_approve_requests($user_id)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_approve_orders as pao '
			.		'RIGHT OUTER JOIN page_permissions as pp '
			.			'USING(page_id) '
			.		'LEFT OUTER JOIN page_versions as pv ON('
			.			'pao.version_number = pv.version_number'
			.		') '
			.		'RIGHT OUTER JOIN users as u ON('
			.			'pao.ordered_user_id = u.user_id'
			.		') '
			.		'WHERE '
			.			'pao.status = 0';
		$query = $this->db->query($sql);
		$ret = array();
		if ($query && $query->num_rows() > 0)
		{
			foreach ($query->result() as $v)
			{
				if (strpos($v->allow_approve_user, ':' . $user_id . ':') !== FALSE)
				{
					$ret[] = $v;
				}
			}
		}
		return $ret;
	}

	function get_approve_requests_of_master()
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'page_approve_orders as pao '
			.		'RIGHT OUTER JOIN page_versions as pv ON('
			.			'pao.version_number = pv.version_number'
			.		') '
			.		'LEFT OUTER JOIN users as u ON('
			.			'pao.ordered_user_id = u.user_id'
			.		') '
			.	'WHERE '
			.		'status = 0';
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		return array();
	}

	function update_approve_order($paoid, $data)
	{
		$this->db->where('page_approve_orders_id', $paoid);
		return $this->db->update('page_approve_orders', $data);
	}

	function get_approved_target_page($pid, $vid)
	{
		$sql =
					'SELECT '
					.	'PV.page_title, '
					.	'PV.is_ssl_page, '
					.	'PP.page_path '
					.'FROM '
					.	'page_versions as PV '
					.'JOIN ( '
					.		'SELECT '
					.			'page_id, '
					.			'page_path '
					.		'FROM '
					.			'page_paths '
					.		'WHERE '
					.			'page_id = ? '
					.	') as PP ON ( '
					.		'PP.page_id = PV.page_id '
					.') '
					.'WHERE '
					.	'PV.page_id = ? '
					.'AND '
					.	'PV.version_number = ? '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$pid, (int)$pid, (int)$vid));
		return $query->row();

	}

	function get_user_data_from_approve_order_id($paoid)
	{
		$sql =
					'SELECT '
					.	'U.user_name, '
					.	'U.email '
					.'FROM '
					.	'users as U '
					.'JOIN ( '
					.		'SELECT '
					.			'page_id, '
					.			'ordered_user_id '
					.		'FROM '
					.			'page_approve_orders '
					.		'WHERE '
					.			'page_approve_orders_id = ? '
					.	') as PAO ON ( '
					.		'PAO.ordered_user_id = U.user_id '
					.') '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$paoid));

		return $query->row();
	}

	function is_sendmail_approve($paoid)
	{
		$sql =
					'SELECT '
					.	'page_approve_orders_id '
					.'FROM '
					.	'page_approve_orders '
					.'WHERE '
					.	'page_approve_orders_id = ? '
					.'AND '
					.	'is_recieve_mail = 1 '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$paoid));

		if ($query && $query->row())
		{
			return TRUE;
		}
		return FALSE;
	}

	function execute_seezoo_upgrade()
	{
		if ( ! file_exists(FCPATH . 'files/upgrades/sz_upgrade_script' . SEEZOO_VERSION . '.php'))
		{
			return TRUE; // no upgrade script
		}

		require_once(FCPATH . 'files/upgrades/sz_upgrade_script' . SEEZOO_VERSION . '.php');

		if ( ! class_exists('SeezooUpgrade'))
		{
			return FALSE;
		}
		$up = new SeezooUpgrade();
		return $up->run();
	}
	
	/**
	 * SSL用base_url更新
	 */
	function update_site_ssl_base_url($uri)
	{
		if ( $uri !== FALSE )
		{
			return $this->db->update('site_info', array('ssl_base_url' => $uri));
		}
		// If $uri is FALSE, update empty and all SSL page is release
		$this->db->update('site_info', array('ssl_base_url' => ''));
		
		$this->db->where('is_ssl_page', 1);
		$this->db->update('page_versions', array('is_ssl_page' => 0));
	}
	
	/**
	 * システムログ一覧取得
	 * @param $filter
	 * @param $limit
	 * @param $offset
	 * @return array
	 */
	function get_system_logs($filter, $limit, $offset)
	{
		$sql =
			'SELECT '
			.	'* '
			.'FROM '
			.	'sz_system_logs ';

		// Does filter string is not all?
		if ( $filter !== 'all' )
		{
			$sql .= 'WHERE log_type = ? ';
			$bind[] = $filter;
		}
		
		$sql .= 'ORDER BY '
				.	'logged_date DESC '
				.'LIMIT ? OFFSET ?';
		$bind[] = (int)$limit;
		$bind[] = (int)$offset;
		
		$query = $this->db->query($sql, $bind);
		
		if ( $query && $query->num_rows() > 0 )
		{
			return $query->result();
		}
		return array();
	}
	
	/**
	 * ログタイプの文字列一覧を取得
	 */
	function get_system_logs_filter_strings()
	{
		$sql =
			'SELECT '
			.	'log_type '
			.'FROM '
			.	'sz_system_logs '
			.'GROUP BY '
			.	'log_type '
			;
		$query = $this->db->query($sql);
		
		$ret = array('all' => 'すべて');
		if ( $query )
		{
			foreach ( $query->result() as $types )
			{
				$ret[$types->log_type] = $types->log_type;
			}
		}
		return $ret;
	}
	
	function get_system_logs_count($filter)
	{
		$sql =
			'SELECT '
			.	'sz_system_logs_id '
			.'FROM '
			.	'sz_system_logs ';
			
		if ( $filter !== 'all' )
		{
			$sql .= 'WHERE log_type = ? ';
			$query = $this->db->query($sql, array($filter));
		}
		else 
		{
			$query = $this->db->query($sql);
		}
		
		return ( $query ) ? $query->num_rows() : 0;
	}
	
	function update_logging_level($level = 0)
	{
		return $this->db->update('site_info', array('log_level' => $level));
	}
	
	function update_accept_registration($mode)
	{
		return $this->db->update('site_info', array('is_accept_member_registration' => $mode));
	}
	
	function update_debug_level($level = 0)
	{
		return $this->db->update('site_info', array('debug_level' => $level));
	}
	
	/**
	 * ログ指定削除
	 * @param $ids
	 */
	function delete_log($ids = array())
	{
		if ( count($ids) === 0 )
		{
			return;
		}
		$this->db->where_in('sz_system_logs_id', $ids);
		$this->db->delete('sz_system_logs');
	}
	
	/**
	 * ログ全削除
	 */
	function delete_all_log()
	{
		// Where-deleteよりTRUNCATEの方が早いと思う。
		$this->db->simple_query('TRUNCATE TABLE sz_system_logs');
	}
	
	/**
	 * OGPデータをDBから取得
	 */
	function get_ogp_settings()
	{
		$sql =
				'SELECT '
				.	'* '
				.'FROM '
				.	SZ_DB_PREFIX . 'sz_ogp_data '
				;
		$query = $this->db->query($sql);

		return $query->row();
	}
	
	function update_ogp_setting($ogp)
	{
		return $this->db->update('sz_ogp_data', $ogp);
	}
	
	function update_mobile_enables($settings)
	{
		return $this->db->update('site_info', $settings);
	}
}
