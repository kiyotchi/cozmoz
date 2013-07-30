<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * インストール用モデルクラス
 *
 * @package Seezoo Core
 * @author Yuta Sakurai <sakurai.yuta@gmail.com>
 *  =========================================================
 */
class Install_model extends Model
{
	protected $install_sql_filename;
	public $db;
	public $error_messages;

	function __construct()
	{
		parent::Model();
		$this->install_sql_filename = APPPATH . 'config/seezoo_install.sql';
	}

	/**
	 * データベースにテーブル、初期レコードを作成する
	 * @param $site_title サイト名
	 * @return boolean テーブル作成に成功したかどうか
	 */
	function create_tables($site_title)
	{
		if ( defined('SZ_INSTALLED') && SZ_INSTALLED === TRUE )
		{
			return FALSE;
		}

		if ( ! $this->db )
		{
			$this->error_messages[] =
				'Install_model: データベースインスタンスが存在しません。is_db_accessable()が実行されていない可能性があります。';
			return FALSE;
		}

		// SQLスキーマをファイルから読み込み、1クエリ毎に配列化
		$sqls = explode(
			";\n",
			file_get_contents($this->install_sql_filename)
		);
		if ( ! $sqls )
		{
			$this->error_messages[] = 'Install_model: インストール用SQLが存在しません。';
			return FALSE;
		}

		foreach ($sqls as $sql)
		{
			// コメント行を削除
			$sql = preg_replace(
				'/--.*/',
				'',
				$sql
			);
			// 改行コードを削除
			$sql = preg_replace(
				'/\n/',
				'',
				$sql
			);
			if ($sql === '')
			{
				continue;
			}

			$query = $this->db->query($sql);
			if ( !$query )
			{
				$this->error_messages[] = 'Install_model: テーブルの作成に失敗しました。sql=' . $sql;
				return FALSE;
			}
		}

		$sql = "UPDATE "
			.		"site_info "
			.	"SET "
			.		"site_title = ? "
			;
		$query = $this->db->query(
			$sql,
			array(
				$site_title
			)
		);
		if ( !$query )
		{
			$this->error_messages[] = 'Install_model: site_infoレコードの挿入に失敗しました。sql=' . $sql;
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 指定された設定でデータベースが接続可能かをチェックし、成功したらそのインスタンスを保持する
	 * @param $host ホスト名
	 * @param $user データベースユーザ名
	 * @param $password データベースパスワード
	 * @param $dbname データベース名
	 * @return boolean 指定された設定で接続可能かどうか
	 */
	function is_db_accessable($host, $user, $password, $dbname)
	{
		$dbconfs['hostname'] = $host;
		$dbconfs['username'] = $user;
		$dbconfs['password'] = $password;
		$dbconfs['database'] = $dbname;
		$dbconfs['dbdriver'] = "mysql";
		$dbconfs['dbprefix'] = "";
		$dbconfs['pconnect'] = TRUE;
		$dbconfs['db_debug'] = FALSE;
		$dbconfs['cache_on'] = FALSE;
		$dbconfs['cachedir'] = "";
		$dbconfs['char_set'] = "utf8";
		$dbconfs['dbcollat'] = "utf8_general_ci";

		$db_for_check = $this->load->database($dbconfs, TRUE);

		//クエリ発行に失敗したかをチェック
		$query = $db_for_check->query('SHOW TABLES');
		if ( !$query || !$query->conn_id )
		{
			unset($db_for_check);
			return FALSE;
		}

		//成功時はインスタンスを保持
		$this->db = $db_for_check;
		return TRUE;
	}

	/**
	 * インストール可能な状態かチェックし、問題があればその問題の詳細を配列で返す
	 * @param $config_path CIの設定ファイル格納ディレクトリ
	 * @param $dbconf DB接続設定を格納した配列
	 * @return array
	 */
	function is_installable($dbconf)
	{
		$CI =& get_instance();
		$error_strings = array();

		$is_db_accessable = $CI->install_model->is_db_accessable(
			$dbconf['hostname'],
			$dbconf['username'],
			$dbconf['password'],
			$dbconf['database']
		);
		if (! $is_db_accessable)
		{
			$error_strings[] = '入力された設定でデータベースに接続できませんでした。';
		}

		$file_permissions = check_file_permissions(get_install_filepaths());
		foreach ($file_permissions as $file => $is_writable)
		{
			if (! $is_writable)
			{
				$error_strings[] = "ファイル $file の書き込み権限がありません。";
			}
		}

		return $error_strings;
	}

	/**
	 * インストールされているかチェック
	 * @return boolean
	 */
	function check_is_installed($config_path)
	{
		// already processed?
		if (defined('SZ_INSTALLED'))
		{
			return SZ_INSTALLED;
		}
		// Does installed parameter exists?
		if ($this->config->item('seezoo_installed') === TRUE)
		{
			define('SZ_INSTALLED', TRUE);
		}
		else
		{
			define('SZ_INSTALLED', FALSE);
		}

		return SZ_INSTALLED;
//		$dbconf = $config_path . '/database.php';
//
//		//DB設定チェック
//		if (! file_exists($dbconf) )
//		{
//			define('SZ_INSTALLED', FALSE);
//			return SZ_INSTALLED;
//		}
//
//		//設定を読み込み、データベース接続できるか確認
//		include($dbconf);
//
//		define(
//			'SZ_INSTALLED',
//			$this->is_db_accessable(
//				$db['default']['hostname'],
//				$db['default']['username'],
//				$db['default']['password'],
//				$db['default']['database']
//			)
//		);
//
//		return SZ_INSTALLED;
	}

	/**
	 * 管理者を登録する
	 * @return boolean
	 */
	function regist_admin($admin_data)
	{
		if ( SZ_INSTALLED )
		{
			return FALSE;
		}

		return $this->db->insert('users', $admin_data);
	}
}
