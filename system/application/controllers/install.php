<?php
/**
 * ===============================================================================
 *
 * Seezoo インストールコントローラ
 *
 * @package Seezoo Core
 * @author Yuta Sakurai <sakurai.yuta@gmail.com>
 *
 * ===============================================================================
 */

class Install extends SZ_Controller
{
	protected $ticket_name;
	protected $config_path;
	protected $view_dir;
	protected $validation_rules;
	protected $css_uri;
	protected $site_uri;
	protected $formdata;

	protected $required_php_version = '5.0';

	function __construct()
	{
		parent::SZ_Controller(FALSE);

		$this->load->model('install_model');
		$this->load->helper(
			array(
				'seezoo_install_helper'
			)
		);
		$this->load->library('form_validation');

		$this->site_uri = get_seezoo_uri();
		$this->ticket_name = 'sz_install_token';
		$this->config_path = APPPATH . 'config';
		$this->view_dir = 'install';
		$this->css_uri = get_seezoo_uri() . 'css';

		if ( $this->install_model->check_is_installed($this->config_path) )
		{
			show_error('既にインストールは完了しています。');
		}
	}

	/**
	 * インストール設定入力
	 * @access public
	 */
	function index()
	{
		$fields = $this->_validation_form();

		// 入力フォーム設定を読み込み、デフォルト値をセット
		$viewdata['formdata'] = create_viewdata_from_validation($fields);
		if ( ! $viewdata['formdata']['site_uri']['real'])
		{
			$viewdata['formdata']['site_uri']['real'] = $this->site_uri;
			$viewdata['formdata']['site_uri']['for_form'] = $this->site_uri;
		}
		$viewdata['hidden'] = array(
			$this->ticket_name => publish_ticket($this->ticket_name)
		);
		$viewdata['file_permissions'] = check_file_permissions(get_install_filepaths());
		$viewdata['site_uri']         = $this->site_uri;
		$viewdata['css_uri']          = $this->css_uri;
		// サーバー要件チェック
		$viewdata['is_mod_rewrite']   = check_mod_rewrite_loaded();
		$viewdata['php_version']      = version_compare(PHP_VERSION, $this->required_php_version, '>');
		// 必須モジュールチェック
		$viewdata['is_json_encode']   = function_exists('json_encode');
		$viewdata['is_xml']           = function_exists('simplexml_load_string');
		$viewdata['is_gd']            = extension_loaded('gd');
		$viewdata['is_mbstring']      = extension_loaded('mbstring');
		$viewdata['is_ziparchive']    = class_exists('ZipArchive');
		
		// DBサーバーのアドレスはデフォルトlocalhostにする
		if ( $this->input->server('REQUEST_METHOD') === 'GET' )
		{
			$viewdata['formdata']['db_address']['for_form'] = 'localhost';
		}

		$this->load->view(
			$this->view_dir . '/index',
			$viewdata
		);

	}

	/**
	 * インストール実行
	 * @access public
	 */
	function do_install()
	{
		check_ticket($this->ticket_name);

		$this->load->model('dashboard_model');
		$fields = $this->_validation_form();
		$this->formdata = create_viewdata_from_validation($fields);

		$dbconf = array(
			'hostname' => $this->formdata['db_address']['real'],
			'username' => $this->formdata['db_username']['real'],
			'password' => $this->formdata['db_password']['real'],
			'database' => $this->formdata['db_name']['real'],
		);

		$this->form_validation->_error_array +=
			$this->install_model->is_installable($dbconf);

		// エラーがあれば設定画面に戻る
		if (
			! $this->form_validation->run()
			|| $this->form_validation->error_string() !== ''
		)
		{
			$this->css_uri = $this->css_uri;
			$this->index();

			return;
		}

		// データベースにテーブル作成
		if (
			! $this->install_model->create_tables(
				$this->formdata['site_name']['real']
			)
		)
		{
			var_dump($this->install_model->error_messages);
			exit;
		}

		// 初期ユーザ登録
		$admin_password_data = $this->dashboard_model->enc_password(
			$this->formdata['admin_password']['real']
		);
		$admin_data = array(
			'user_name'		=> $this->formdata['admin_username']['real'],
			'password'		=> $admin_password_data['password'],
			'hash'		=> $admin_password_data['hash'],
			'email'			=> $this->formdata['admin_email']['real'],
			'admin_flag'	=> 1,
			'regist_time'	=> date('Y-m-d H:i:s', time()),
			'is_admin_user'	=> 1
		);
		$this->install_model->regist_admin($admin_data);

		// 書き換え対象のファイル名と内容を取得し、書き換える
		$files = get_install_filepaths();
		foreach ($files as $filename)
		{
			if ( is_dir($filename) )
			{
				continue;
			}

			$contents = file_get_contents($filename);
			$patched_contents = $this->_patch_file($filename, $contents);

			$fp = fopen($filename, 'w');
			fwrite($fp, $patched_contents);
			fclose($fp);
		}

		$viewdata['css_uri'] = $this->css_uri;

		// installation complete!
		$this->load->database($dbconf);
		$this->session->sess_use_database = TRUE;
		$this->session->sess_create();
		$this->session->set_userdata('user_id', '1');
		
		// seezoomore用設定
		// デフォルトテンプレートハンドルがあればデータインストール
		if ( defined('SEEZOO_DEFAULT_INSTALL_PACKAGE_HANDLE')
		      && file_exists(SZ_EXT_PATH . 'installation/' . SEEZOO_DEFAULT_INSTALL_PACKAGE_HANDLE . '.php') )
		{
			require_once(SZ_EXT_PATH . 'installation/' . SEEZOO_DEFAULT_INSTALL_PACKAGE_HANDLE . '.php');
			$installation_class = 'Install_' . SEEZOO_DEFAULT_INSTALL_PACKAGE_HANDLE;
			$package = new $installation_class();
			$package->run();
		}

		$this->load->view($this->view_dir . '/complete', $viewdata);
	}

	function _validation_form()
	{
		if ($this->validation_rules)
		{
			return $this->validation_rules;
		}

		$conf = array(
			array(
				'field'		=> 'site_name',
				'rules'		=> 'required',
				'label'		=> 'サイト名'
			),
			array(
				'field'		=> 'site_uri',
				'rules'		=> 'required',
				'label'		=> 'サイトアドレス'
			),
			array(
				'field'		=> 'admin_email',
				'rules'		=> 'trim|required',
				'label'		=> '管理者のメールアドレス'
			),
			array(
				'field'		=> 'admin_username',
				'rules'		=> 'trim|alpha_dash|required',
				'label'		=> '管理者のユーザ名'
			),
			array(
				'field'		=> 'admin_password',
				'rules'		=> 'trim|required',
				'label'		=> '管理者のパスワード'
			),
			array(
				'field'		=> 'db_address',
				'rules'		=> 'trim|required',
				'label'		=> 'データベースサーバのアドレス'
			),
			array(
				'field'		=> 'db_username',
				'rules'		=> 'trim|required',
				'label'		=> 'データベースサーバのユーザ名'
			),
			array(
				'field'		=> 'db_password',
				'rules'		=> 'trim',
				'label'		=> 'データベースサーバのパスワード'
			),
			array(
				'field'		=> 'db_name',
				'rules'		=> 'trim|required',
				'label'		=> 'データベース名'
			)
		);
		$this->form_validation->set_rules($conf);

		$this->validation_rules = $conf;
		return $this->validation_rules;
	}

	function _patch_file($rewrite_filename, $contents)
	{
		$patched_contents = $contents;

		switch ($rewrite_filename)
		{
		case preg_match('/config\.php$/', $rewrite_filename) == 1:
			$patched_contents = preg_replace(
				'/(\$config\[\'base_url\'\]\s*=\s*[\'"])[a-z\/\.-_:]*([\'"];)/',
				"\${1}{$this->formdata['site_uri']['real']}\${2}",
				$patched_contents
			);
			$patched_contents .= "\n" . '$config[\'seezoo_installed\'] = TRUE;';
			$patched_contents .= "\n" . '$config[\'seezoo_current_version\'] = \'' . SEEZOO_VERSION . '\';';
			$patched_contents .= "\n" . '$config[\'seezoo_generic_key\'] = \'' . sha1(uniqid(mt_rand(), TRUE)) . '\';';
			break;
		case preg_match('/database\.php$/', $rewrite_filename) == 1:
			$patched_contents = preg_replace(
				'/(\$db\[\'default\'\]\[\'hostname\'\]\s*=\s*[\'"])[a-z\/\.-_:]*([\'"];)/',
				"\${1}{$this->formdata['db_address']['real']}\${2}",
				$patched_contents
			);
			$patched_contents = preg_replace(
				'/(\$db\[\'default\'\]\[\'username\'\]\s*=\s*[\'"])[a-z\/\.-_:]*([\'"];)/',
				"\${1}{$this->formdata['db_username']['real']}\${2}",
				$patched_contents
			);
			$patched_contents = preg_replace(
				'/(\$db\[\'default\'\]\[\'password\'\]\s*=\s*[\'"])[a-z\/\.-_:]*([\'"];)/',
				"\${1}{$this->formdata['db_password']['real']}\${2}",
				$patched_contents
			);
			$patched_contents = preg_replace(
				'/(\$db\[\'default\'\]\[\'database\'\]\s*=\s*[\'"])[a-z\/\.-_:]*([\'"];)/',
				"\${1}{$this->formdata['db_name']['real']}\${2}",
				$patched_contents
			);
			$patched_contents = preg_replace(
				'/(\$db\[\'default\'\]\[\'db_debug\'\]\s*=\s*)[a-zA-Z\/\.-_:]*(;)/',
				"\${1}TRUE\${2}",
				$patched_contents
			);
			break;
		default:
			die("patching config file failed: $rewrite_filename");
		}

		return $patched_contents;
	}
}
