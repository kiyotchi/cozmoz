<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard 管理トップコントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Panel extends SZ_Controller
{
	public static $page_title = '管理トップ';
	
	function __construct()
	{
		parent::SZ_Controller(FALSE);
		$this->load->model('init_model');
		if ($this->init_model->is_login() === FALSE)
		{
			redirect(SEEZOO_SYSTEM_LOGIN_URI);
		}
		// 管理画面トップはログインユーザーは基本的にアクセス許可とする。
		// そこで、SZ_Controllerのinitializeメソッドはスキップして独自にパラメータをセットする
		$this->_init_dashboard_top();
	}
	
	/**
	 * 手動で初期化
	 * @access private
	 */
	protected function _init_dashboard_top()
	{
		// set user_id
		// if notlogged in, this. property set 0.
		$this->user_id = (int)$this->session->userdata('user_id');
		$this->user_data = $this->dashboard_model->get_user_data($this->user_id);
		// access user is master user?
		$this->is_master = $this->dashboard_model->is_master();
		
		$this->is_admin = $this->permission_model->is_admin($this->user_id);
		
				// access class is dashboard?
		$is_dashboard = TRUE;
		
		$this->load->helper('dashboard_helper');

		// set some parameters
		$this->is_maintenance_mode = $this->dashboard_model->is_maintenance_mode();
		$this->page_id = $this->init_model->get_page_id_from_page_path($this->router->fetch_directory() . $this->router->fetch_class());
		$this->parent_id = 0;

		// set output header
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');
	}
	
	/**
	 * Default method
	 * @param $msg
	 * @access public
	 */
	function index($msg = '')
	{
		$data->user_data = $this->dashboard_model->get_user_data();
		$data->edit_pages = $this->dashboard_model->get_edit_page_count($this->user_data->user_id);
		$data->site = $this->dashboard_model->get_site_info();
		$data->default_template = $this->dashboard_model->get_default_template($data->site->default_template_id);
		$data->approve_orders = $this->dashboard_model->get_approve_statuses($this->user_id);
		if ($this->is_admin)
		{
			$data->approve_requests = $this->dashboard_model->get_approve_requests_of_master();
		}
		else
		{
			$data->approve_requests = $this->dashboard_model->get_approve_requests($this->user_id);
		}
		
		if (!empty($msg))
		{
			$data->msg = $this->_set_message($msg);
		}
		$this->load->view('dashboard/panel', $data);
	}
	
	/**
	 * ページ公開を証人
	 * @param $token
	 * @access public ajax
	 */
	function approve($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		$status = $this->input->post('approve_type');
		$data = array(
			'comment'				=> $this->input->post('approve_comment'),
			'status'				=> 0,
			'approved_user_id'	=> $this->user_id
		);
		
		$pid = (int)$this->input->post('approve_page');
		$vid = (int)$this->input->post('approve_version');
		// update page_version to approve
		if ($status === 'approve')
		{
			$this->load->model('version_model');
			$ret = $this->version_model->approve_version_from_ajax($pid, $vid);
			$data['status'] = 1;
		}
		else
		{
			$ret = TRUE;
			$data['status'] = 2;
		}
		
		if ($ret)
		{
			// update approve_order_request
			$paoid = (int)$this->input->post('approve_order_id');
			$ret2 = $this->dashboard_model->update_approve_order($paoid, $data);
			
			// Is need to send mail?
			if ($this->dashboard_model->is_sendmail_approve($paoid) === TRUE)
			{
				$data['pid'] = $pid;
				$data['vid'] = $vid;
				$this->_send_mail($paoid, $data);
			}
			if ($ret2)
			{
				redirect('dashboard/panel/index/approve_success');
			}
		}
		redirect('dashboard/panel/index/approve_error');
	}
	
	/**
	 * ページ公開を差し戻し
	 * @param $paoid
	 * @param $token
	 * @access public ajax
	 */
	function cancel_approve($paoid, $token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		
		$ret = $this->dashboard_model->delete_approve_order((int)$paoid);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}
	
	/**
	 * 前のログインユーザに戻る
	 * @access public
	 */
	function rollback_user()
	{
		$uid = (int)$this->session->userdata('rollback_user');
		if ( $uid > 0 )
		{
			$this->session->set_userdata('user_id', $uid);
			$this->session->unset_userdata('rollback_user');
			redirect('dashboard/panel');
		}
		show_404();
	}
	
	/**
	 * メッセージ生成
	 * @param $msg
	 * @access private
	 */
	function _set_message($msg)
	{
		switch($msg)
		{
			case 'approve_success':
				return 'ページの承認ステータスを変更しました。';
			case 'approve_error':
				return '承認ステータスの変更に失敗しました。';
			default:
				return '';
		}
	}
	
	/**
	 * 申請結果をメール送信
	 * @param $id
	 * @param $data
	 * @access private
	 */
	function _send_mail($id, $data)
	{
		// set mail from
		if ( empty($this->site_data->system_mail_from) )
		{
			$email = $this->user_data->email;
		}
		else 
		{
			$email = $this->site_data->system_mail_from;
		}
		
		// get ordered page data
		$page    = $this->dashboard_model->get_approved_target_page($data['pid'], $data['vid']);
		$user    = $this->dashboard_model->get_user_data_from_approve_order_id($id);
		$subject = '【' . SITE_TITLE . '】システムメール';
		$body    = $this->load->view(
									'mailbodys/approve_mailbody',
									array(
										'data'      => $data,
										'from_user' => $this->user_data,
										'to_user'   => $user,
										'page'      => $page
									),
									TRUE
								);
		$this->load->model('mail_model');
		$this->mail_model->send_text_mail($user->email, $subject, $body, $email);
	}
	
	function test()
	{
		show_token_error();
	}
}