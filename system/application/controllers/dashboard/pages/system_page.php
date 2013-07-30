<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard システムページ管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class System_page extends SZ_Controller
{
	public $page_title = 'システムページ管理';
	public $page_description = 'システムページを追加したり削除したりします。';
	
	public $msg = '';
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model('sitemap_model');

	}
	
	function move_system_page($token = FALSE) {
		
		check_ajax_token($token);
		
		$from = (int)$this->input->post('from');
		$to   = (int)$this->input->post('to');
		
		if ( $this->sitemap_model->move_system_page($from, $to) )
		{
			echo 'success';
			return;
		}
		echo 'error';
	}
	
	function index($process = '')
	{
		$data->frontend_pages = $this->sitemap_model->get_frontend_system_pages(TRUE);
		$data->system_pages = $this->sitemap_model->get_system_pages();
		
		if ($process == 'success')
		{
			$this->msg = 'システムページをインストールしました。';
		}
		else if ($process == 'error')
		{
			$this->msg = 'システムページのインストールに失敗しました。';
		}
		$this->load->view('dashboard/pages/system_pages', $data);
	}
	
	function add_system_page()
	{
		$ticket = $this->input->post('sz_ticket');
		if (!$ticket || $ticket !== $this->session->flashdata('sz_ticket'))
		{
			exit('access denied');
		}
		if ($this->input->post('add_page_path'))
		{
			$pp = $this->input->post('add_page_path');
			$ret = $this->sitemap_model->insert_system_page($pp);
		}
		redirect('dashboard/pages/system_page/index/' . (($ret) ? 'success' : 'error'));
	}
	
	function rescan($pid)
	{
		if ((int)$pid === 0)
		{
			$this->msg = '更新対象のページが見つかりませんでした。';
			$this->index();
		}
		else
		{
			$ret = $this->sitemap_model->rescan_system_page((int)$pid);

			if ($ret === TRUE)
			{
				$this->msg = 'ページの再読み込みを行いました。';
			}
			else if ($ret == 'DB_WITH')
			{
				$this->msg = 'ページの再読み込みを行いました。<br /><br />DB構造も最新のものに更新されました。';
			}
			else if ($ret == 'MISS')
			{
				$this->msg = 'データベース構造の更新に失敗しました。';
			}
			else
			{
				$this->msg = 'ページの再読み込みに失敗しました。';
			}
			$this->index();
		}
	}
	
	// ============================= ajax methods ===================================//
	
	
	function delete($pid, $token = FALSE)
	{
		if (!$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}
		
		$ret = $this->sitemap_model->delete_page((int)$pid);
		
		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
		}
	}
	
	function page_config($pid = 0, $token = FALSE)
	{

		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		
			if (!$pid || !ctype_digit($pid))
		{
			exit('access_denied');
		}
		
		$this->load->helper('ajax_helper');
		$this->load->model('ajax_model');

		$data->page_id = intval($pid);
		$data->token = $token;

		$data->page = $this->sitemap_model->get_current_approved_version($pid);
		
		$data->version_number = $data->page->version_number;
		$data->templates = $this->ajax_model->get_template_list();
		
		$data->system_page = 1;

		$data->user_list = $this->ajax_model->get_user_list();
		$data->is_system = 1;

		//$data->page_permission = $this->ajax_model->get_page_permission($pid);

		$this->load->view('parts/edit_page_form', $data);
	}
	
	function scan_system_page()
	{
		//scan CI controllers
		// get package controllers
		$exts = $this->sitemap_model->get_ci_controllers(SZ_EXT_PATH . 'controllers/');
		// get core controllers
		$cores = $this->sitemap_model->get_ci_controllers();
		
		// get plugin controllers and merge extensions
		$plugin = SeezooPluginManager::get_instance();
		$active_plugin_names   = $plugin->get_installed_plugin_names();
		foreach ( $active_plugin_names as $plugin_handle )
		{
			$dirs = $this->sitemap_model->get_ci_controllers(SZ_PLG_PATH . $plugin_handle . '/controllers/');
			foreach ( $dirs as $p_path => $controller )
			{
				if ( ! isset($exts[$p_path]) )
				{
					$exts[$p_path] = $controller;
				}
			}
		}
		
		// merge controlelrs
		foreach ($cores as $key => $value)
		{
			if (isset($exts[$key]))
			{
				continue;
			}
			$exts[$key] = $value;
		}
		$data->scaned_page = $exts;
		
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_flashdata('sz_ticket', $ticket);
		
		$data->ticket = $ticket;
		
		$this->load->view('dashboard/pages/scaned_list', $data);
	}
}