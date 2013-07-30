<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ブログping送信先管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Ping extends SZ_Controller
{
	public $page_title = 'ping送信先管理';
	public $page_description = '新規記事投稿時のping送信先を管理します。';
	
	public $msg;
	public $ticket_name = 'sz_ticket';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('blog_model');
		
		$this->info = $this->blog_model->get_blog_info();
	}

	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$this->_enable_check();
		
		$data->ping_list = $this->blog_model->get_ping_list();
		$data->times = 0;
		$data->js_token = $this->_set_ticket();
		
		$this->load->view('dashboard/blog/ping_settings', $data);
	}
	
	/**
	 * Ajax応答：ping送信先追加
	 * @param string $token
	 */
	function ajax_add_ping($token = FALSE)
	{
		if (!$this->session->userdata('sz_token') || $this->session->userdata('sz_token') !== $token)
		{
			exit('error');
		}
				
		$data = array(
			'ping_server'	=> $this->input->post('ping_server', TRUE),
			'ping_name'	=> $this->input->post('ping_name', TRUE)
		);
		
		$ret = $this->blog_model->add_new_ping($data);
		
		if ($ret && is_numeric($ret))
		{
			$data['sz_blog_ping_list'] = $ret;
			echo json_encode($data);
		}
		else
		{
			echo 'error';
		}
		exit;
	}
	
	/**
	 * ping送信先編集
	 * @param $pid
	 * @param $token
	 */
	function edit_ping($pid, $token = FALSE)
	{
		if (!$token|| $this->session->flashdata($this->ticket_name) !== $token || (int)$pid === 0)
		{
			exit('error');
		}
		
		$this->session->keep_flashdata($this->ticket_name);
		$data->ping = $this->blog_model->get_ping_one($pid);
		$data->ticket = $token;
		
		$this->load->view('dashboard/blog/edit_ping', $data);
	}
	
	/**
	 * Ajax応答：ping送信先削除
	 * @param $pid
	 * @param $token
	 */
	function delete_ping($pid, $token = FALSE)
	{
		if (!$token|| $this->session->flashdata($this->ticket_name) !== $token || (int)$pid === 0)
		{
			exit('error');
		}
		
		$ret = $this->blog_model->delete_ping_data($pid);
		
		$this->session->keep_flashdata($this->ticket_name);
		echo ($ret) ? 'complete' : 'error';
		
	}
	
	/**
	 * Ajax応答：ping送信先編集実行
	 */
	function ajax_do_edit_ping()
	{
		$token = $this->input->post('ticket');
		if (!$token|| $this->session->flashdata($this->ticket_name) !== $token || (int)$this->input->post('pid') === 0)
		{
			exit('error');
		}
		
		$data = array(
			'ping_server'	=> $this->input->post('ping_server', TRUE),
			'ping_name'	=> $this->input->post('ping_name', TRUE)
		);
		
		$ret = $this->blog_model->update_ping_data((int)$this->input->post('pid'), $data);
		
		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			$this->session->keep_flashdata($this->ticket_name);
			echo 'error';
		}
		exit;
	}
	
	/**
	 * ブログが利用可能かどうか判定
	 */
	function _enable_check()
	{
		// if blog id unabled, redirect index
		if ((int)$this->info->is_enable === 0)
		{
			redirect('dashboard/blog/settings/');
		}
	}
	
	/**
	 * トークン生成
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_flashdata($this->ticket_name, $ticket);
		return $ticket;
	}
	
	/**
	 * トークンチェック
	 * @param $ticket
	 */
	function _check_ticket($ticket = FALSE)
	{
		if (!$ticket)
		{
			$ticket = $this->input->post($this->ticket_name);
		}
		if (!$ticket || $ticket !== $this->session->flashdata($this->ticket_name))
		{
			exit('不正な操作です。また、リロードは禁止されています。');
		}
	}
}