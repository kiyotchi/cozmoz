<?php
/**
 * ===============================================================================
 *
 * Seezoo dashboard システムアップデートコントローラ
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */

class Upgrade extends SZ_Controller
{
	public static $page_title = 'アップグレード';
	public static $description = 'Seezooのシステムをアップグレードします。';

	public $version;
	public $msg = '';

	protected $CLI;

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		$this->CLI = config_item('cli_mode');
		
		// skip access check when CodeIgniter started by cli
		parent::SZ_Controller(($this->CLI) ? FALSE : TRUE);

		$this->version = $this->config->item('seezoo_current_version');
		
		// if version config is not found, set 1.0.0
		if (!$this->version)
		{
			$this->version = '1.0.0';
		}

		$this->load->helper('directory_helper');
	}

	/**
	 * インデックスメソッド
	 */
	function index($msg = '')
	{
		$data->ticket = $this->_set_ticket();
		$data->msg = $this->_select_msg($msg);
		if (version_compare($this->config->item('seezoo_current_version'), SEEZOO_VERSION, '>='))
		{
			$data->upgrade = 0;
		}
		else
		{
			if ( ! file_exists(FCPATH . 'files/upgrades/sz_upgrade_script' . SEEZOO_VERSION . '.php'))
			{
				$data->upgrade = 1;
			}
			else
			{
				$data->upgrade = 2;
			}
		}

		$this->load->view('dashboard/site_settings/upgrades', $data);
	}

	/**
	 * アップデート実行
	 */
	function execute($up_version = FALSE, $token = FALSE)
	{
		// Does execute from CLI?
		if ($this->CLI === TRUE)
		{
			if ($this->version === SEEZOO_VERSION)
			{
				exit('version:' . $this->version . ' システムは最新の状態です。');
			}

			// update config.php is writeable?
			if ( ! is_really_writable(APPPATH . 'config/config.php'))
			{
				exit(APPPATH . 'config/config/phpに書き込み権限を与えてください。');
			}
			
		}
		else
		{
			$this->_check_ticket($token);
			if ($this->version === SEEZOO_VERSION)
			{
				redirect('dashboard/site_settings/upgrade/index/current');
			}
			
			// update config.php is writeable?
			if ( ! is_really_writable(APPPATH . 'config/config.php'))
			{
				redirect('dashboard/site_settings/upgrade/index/notwritable');
			}
			
		}
		
		$ret = $this->dashboard_model->execute_seezoo_upgrade();
		
		if ($ret)
		{
			$this->_update_current_version_setting_value();
		}
		
		if ($this->CLI === TRUE)
		{
			$msg = ($ret === TRUE) ?  'システムをバージョンを' . SEEZOO_VERSION . 'にアップグレードしました。'
												: 'システムのアップグレードに失敗しました。';
			exit($msg);
		}

		$segment = ($ret) ? 'success' : 'error';
		redirect('dashboard/site_settings/upgrade/index/' . $segment);
	}

	/**
	 * カレントシステムバージョン更新
	 */
	function _update_current_version_setting_value()
	{
		$filename = APPPATH . 'config/config.php';
		
		$contents = file_get_contents($filename);
		if ( ! $this->config->item('seezoo_current_version') )
		{
			$contents .= "\n" . '$config[\'seezoo_current_version\'] = \'' . SEEZOO_VERSION . '\';';
		}
		else 
		{
			$contents = preg_replace(
				'/(\$config\[\'seezoo_current_version\'\]\s*=\s*[\'"])[a-z\/\.-_:]*([\'"];)/',
				"\${1}" . SEEZOO_VERSION . "\${2}",
				$contents
			);
		}
		
		$fp = fopen($filename, 'w');
		fwrite($fp, $contents);
		fclose($fp);
	}
	/**
	 * トークンセット
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('setting_token', $ticket);

		return $ticket;
	}

	/**
	 * トークンチェック
	 */
	function _check_ticket($ticket)
	{
		if (!$ticket || $ticket !== $this->session->userdata('setting_token'))
		{
			exit('連続アップグレード防止のため、リロードは禁止しています。');
		}
	}
	
	/**
	 * アップグレード実行結果メッセージ
	 */
	function _select_msg($msg)
	{
		switch ($msg)
		{
			case 'success':
				return 'システムを' . SEEZOO_VERSION . 'にアップグレードしました。';
			case 'error':
				return 'アップグレードに失敗しました。';
			case 'current':
				return 'システムは最新の状態です。';
			case 'notfound':
				return 'アップグレードに必要なファイルが見つかりませんでした。';
			case 'norwritable':
				return '設定ファイルへの書き込み権限を与えてください';
			default:
				return '';
		}
	}
}
