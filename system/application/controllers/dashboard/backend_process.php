<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard バックエンドプロセス管理コントローラ
 * 
 * @note バックエンドプロセスをインストール・実行、またはcronからの実行管理
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Backend_process extends SZ_Controller
{
	public static $page_title = 'バックエンド処理';
	public static $description = 'バックエンドで行う処理を管理します。';
	
	protected $table = 'sz_backend';
	protected $cron_token;
	protected $is_cron_run = FALSE;
	
	public $msg = '';
	
	function __construct()
	{
		parent::SZ_Controller(FALSE);
		
		// notice: バックエンド処理はcronから実行される場合もあるため、アクセス権限チェックはマニュアルで行う。
		// cronからの実行の場合はcron_tokenがセグメントで渡される。これにより判定
		$this->_init_dashboard_backend();
		$this->load->model('backend_model');
		$this->cron_token = $this->config->item('seezoo_generic_key');
		
		// load backend base class
		require_once(APPPATH . 'libraries/seezoo/Backend.php');
	}
	
	function _init_dashboard_backend()
	{
		if ($this->config->item('cli_mode') === FALSE)
		{
			$this->_set_ci_controller_page_status();
		}
	}
	
	function index($msg = '')
	{
//		// set processed message
//		if ( ! empty($msg))
//		{
//			$ths->generate_processed_message($msg);
//		}
		// load directory helper
		$this->load->helper('directory_helper');
		
		$data->installed_backend = $this->backend_model->get_enable_backend_list();
		$data->enable_install_list = $this->backend_model->get_enable_install_list();
		$data->install_token = $this->_set_token();
		
		$this->load->view('dashboard/backend/index', $data);
	}
	
	function run()
	{
		// if PHP don't works safe mode, limit time to infinity
		if ( ! ini_get('safe_mode'))
		{
			set_time_limit(0);
		}
		$token = $this->input->post('token');
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		$sbid = (int)$this->input->post('id');
		$result = $this->backend_model->single_run($sbid);
		echo json_encode($result);
	}
	
	function cron_run($token = FALSE)
	{
		// this process CLI access only.
		if ( $this->config->item('cli_mode') === FALSE)
		{
			exit('access_denied.');
		}
		
		// if PHP don't works safe mode, limit time to infinity
		if ( ! ini_get('safe_mode'))
		{
			set_time_limit(0);
		}
		$this->backend_model->cron_run();
	}
	
	function _set_token()
	{
		$token = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_backend_token', $token);
		return $token;
	}
	
	function _check_token()
	{
		$token = $this->input->post('install_token');
		if (!$token || $token != $this->session->userdata('sz_backend_token'))
		{
			exit('Access denied');
		}
	}
	
	function install_process()
	{
		$this->_check_token();
		$handle = $this->input->post('handle');
		
		$ret = $this->backend_model->install_process($handle);
		
		redirect('dashboard/backend_process/');
	}
	
	function uninstall()
	{
		$id = (int)$this->input->post('sz_backend_id');
		
		$this->backend_model->uninstall_process($id);
		
		redirect('dashboard/backend_process');
	}
	
	function generate_processed_message($msg)
	{
		$m = '';
		switch ($msg)
		{
			case 'success': $m = 'プロセスをインストールしました。'; break;
			case 'error'  : $m = 'プロセスのインストールに失敗しました　。'; break;
			case 'invalid_handle' : $m = 'プロセス名に文字が含まれています。'; break;
			case 'file_not_found' : $m = 'プロセス実行対象ファイルが見つかりません。'; break;
			case 'class_not_found': $m = 'プロセス実行クラスが見つかりません。'; break;
		}
		$this->msg = $m;
	}
}
