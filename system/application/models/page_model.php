<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * CMSページ関連モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Page_model extends Model
{
	protected $table = 'pages';

	function __construct()
	{
		parent::Model();
	}

	/**
	 * get_page_object : ページIDからページのインスタンスを生成
	 * @param int $pid
	 * @param string $ver
	 * @return Object Page
	 */

	function get_page_object($pid, $ver)
	{
		// switch get page method by version mode
		if ($ver === 'editting')
		{
			// current editting version
			$sql = 'SELECT '
				.		'* '
				.	'FROM '
				.		'pending_pages '
				.	'WHERE '
				.		'page_id = ? '
				.	'ORDER BY version_number DESC '
				.	'LIMIT 1'
				;
			$query = $this->db->query($sql, array($pid));
		}
		else if ($ver === 'approve')
		{
			// normal access view. get approved version
			$sql = 'SELECT '
				.		'* '
				.	'FROM '
				.		'page_versions '
				.	'WHERE '
				.		'page_id = ? '
				.		'AND is_public = 1 '
				.		'AND public_datetime < ? '
				.	'ORDER BY version_number DESC '
				.	'LIMIT 1'
				;
			$query = $this->db->query($sql, array($pid, db_datetime()));
		}
		else if (is_numeric($ver))
		{
			// version string is numeric, get target version (this process works preview only.)
			$sql = 'SELECT '
				.		'* '
				.	'FROM '
				.		'page_versions '
				.	'WHERE '
				.		'page_id = ? '
				.		'AND version_number = ? '
				.	'LIMIT 1'
				;
			$query= $this->db->query($sql, array($pid, (int)$ver));
		}
		else // recent
		{
			$sql = 'SELECT '
				.		'* '
				.	'FROM '
				.		'page_versions as pv '
				.		'LEFT OUTER JOIN page_permissions as perms '
				.			'USING(page_id) '
				.	'WHERE pv.page_id = ? '
				.	'ORDER BY pv.version_number DESC '
				.	'LIMIT 1'
				;
			$query = $this->db->query($sql, array($pid));
		}

		if ($query->num_rows() === 0)
		{
			return FALSE;
		}

		$page = $query->row_array();

		if (intval($page['template_id']) > 0)
		{
			$page['template_path'] = $this->get_template_path(intval($page['template_id']));
			$page['advance_css'] = $this->get_template_advance_css((int)$page['template_id']);
		}
		else
		{
			$page['template_path'] = 'default/';
			$page['advance_css'] = '';
		}

		return $page;
	}

	/**
	 * get_page_state : flint.jsパラメータ用のデータの生成
	 * @param int $pid
	 * @param string $ver
	 * @return Object page
	 */
	function get_page_state($pid, $ver)
	{
		// get most editting versions
		$sql = 'SELECT '
			.		'pv.version_number, '
			.		'perms.allow_edit_user, '
			.		'perms.allow_access_user, '
			.		'p.is_editting, '
			.		'p.edit_user_id, '
			.		'p.is_arranging '
			.	'FROM '
			.		'page_versions as pv '
			.		'RIGHT OUTER JOIN pages as p '
			.			'USING(page_id) '
			.		'LEFT OUTER JOIN page_permissions as perms '
			.			'USING(page_id) '
			.	'WHERE '
			.		'pv.page_id = ? '
			;
		if ($ver === 'approve')
		{
			$sql .= 'AND is_public = 1 AND public_datetime < ' . time() . ' ';
		}

		$sql .= 'ORDER BY version_number DESC LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}

	/**
	 * get_page_state : flint.jsパラメータ用のデータの生成
	 * @param int $pid
	 * @param string $ver
	 * @return object $state
	 */
	function get_page_state_for_init($pid, $ver = 'approve')
	{
		$sql = 'SELECT '
			.		'is_editting, '
			.		'edit_user_id, '
			.		'is_arranging '
			.	'FROM '
			.		'pages '
			.	'WHERE '
			.		'page_id = ? ';
		if ($ver === 'approve')
		{
			$sql .= 'AND is_public = 1 AND public_datetime < ' . time() . ' ';
		}
		$sql .= 'ORDER BY version_number DESC LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}

	/**
	 * get_template_path : テンプレートパスを取得
	 * @access public
	 * @param int $pid
	 * @return String $template_path
	 */
	function get_template_path($pid)
	{
		$sql = 'SELECT template_handle FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array(intval($pid)));
		$result = $query->row();
		return $result->template_handle . '/';
	}
	
	/**
	 * get_template_advance_css : 拡張CSSを文字列で取得
	 * @access public
	 * @param int $tpid
	 * @return string $advance_css
	 */
	function get_template_advance_css($tpid)
	{
		$sql = 'SELECT advance_css FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($tpid));
		$result = $query->row();
		return $result->advance_css;
	}

	/**
	 * get_areas : 引数のページに存在するエリアを取得
	 * @param int $pid
	 * @return Object DB_RESULT
	 */
	function get_areas($pid)
	{
		$sql = 'SELECT * FROM areas WHERE page_id = ?';
		$query = $this->db->query($sql, array(intval($pid)));
		return $query->result();
	}

	/**
	 * delete_page_data : ページ情報をIDを元に全削除
	 * @access public
	 * @param int $pid
	 * @return bool $ret
	 */
	function delete_page_data($pid)
	{
		// delete page_paths
		$sql= 'DELETE FROM page_paths WHERE page_id = ?';
		$query = $this->db->query($sql, array($pid));

		// delete pages data
		$sql2 = 'DELETE FROM pages WHERE page_id = ?';
		$query2 = $this->db->query($sql2, array($pid));

		// delete pendign data
		$sql3 = 'DELETE FROM pending_pages WHERE page_id = ?';
		$query3 = $this->db->query($sql3, array($pid));

		// delete page_version
		$sql4 = 'DELETE FROM page_versions WHERE page_id = ?';
		$query4 = $this->db->query($sql4, array($pid));

		return TRUE;
	}

	/**
	 * has_advance_css : 使用テンプレートが追加CSSを定義しているかチェック
	 * @access public
	 * @param int $template_id
	 * @return bool $has
	 */
	function has_advance_css($template_id)
	{
		$sql = 'SELECT advance_css FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($template_id));

		if ($query->row())
		{
			$result = $query->row();
			if (!empty($result->advance_css))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * get_advance_css_by_template_id : template_idからパス取得
	 * @param $tid
	 */
	function get_advance_css_by_template_id($tid)
	{
		$sql = 'SELECT advance_css FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$tid));

		$result = $query->row();
		return $result->advance_css;
	}

	/**
	 * get_static_page_variables : static ページ用にセットする擬似変数を取得
	 */
	function get_static_page_variables($is_list = FALSE)
	{
		// test code
		if ($is_list)
		{
			$sql = 'SELECT '
				.		'static_vars_id, '
				.		'var_name, '
				.		'is_user_var, '
				.		'description '
				.	'FROM '
				.		'static_vars '
				.	'WHERE '
				.		'is_system_var = 0 '
				.	'ORDER BY static_vars_id ASC'
				;
			$query = $this->db->query($sql);
			return $query->result();
		}

		$sql = 'SELECT '
			.		'static_vars_id, '
			.		'var_name, '
			.		'var_value '
			.	'FROM '
			.		'static_vars '
			.	'ORDER BY static_vars_id ASC'
			;
		$query = $this->db->query($sql);

		foreach ($query->result() as $value)
		{
			$ret[$value->var_name] = $value->var_value;
		}

		return $ret;
	}

	/**
	 * delete_site_cache_all : ページキャッシュを全て削除する
	 */
	function delete_site_cache_all()
	{
		$this->load->helper('file_helper');

		delete_files('./files/page_caches/');
		return TRUE;
	}

	/**
	 *
	 */
	function is_enable_cache()
	{
		$sql = "SELECT "
			.		"enable_cache "
			.	"FROM "
			.		"site_info "
			.	"LIMIT 1"
			;
		$query = $this->db->query($sql);

		if ( ! $query )
		{
			return FALSE;
		}

		$result = ($query->row());
		$returndata = ((int)$result->enable_cache === 1) ? TRUE : FALSE;
		return $returndata;
	}
}
