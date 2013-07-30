<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ブログカテゴリ管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Categories extends SZ_Controller
{
	public $page_title = 'カテゴリ管理';
	public $page_description = 'ブログカテゴリを管理します。';
	
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
		
		$data->category = $this->blog_model->get_category_array();
		
		$this->load->view('dashboard/blog/categories', $data);
	}
	
	/**
	 * トークンチェック
	 * @param string $ticket
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
	
	/**
	 * ブログが利用可能状態であるかどうかをチェック
	 * 利用可能で無ければ設定画面にリダイレクト
	 */
	function _enable_check()
	{
		// if blog id unabled, redirect index
		if ((int)$this->info->is_enable === 0)
		{
			redirect('dashboard/blog/settings');
		}
	}
	
	/**
	 * Ajax応答用カテゴリ追加
	 * @param $token
	 */
	function ajax_add_category($token)
	{
		if (!$this->session->userdata('sz_token') || $this->session->userdata('sz_token') !== $token)
		{
			exit('error');
		}
		
		// keep main flash token
		$this->session->keep_flashdata($this->ticket_name);
		
		$post = array(
			'category_name'	=> $this->input->post('category_name', TRUE)
		);
		$ret = $this->blog_model->insert_new_category($post);
		
		if ($ret && is_numeric($ret))
		{
			echo json_encode(array('sz_blog_category_id' => $ret));
		}
		else
		{
			echo 'error';
		}
	}
	
	/**
	 * Ajax応答用カテゴリ名編集
	 * @param string $token
	 */
	function ajax_update_category($token)
	{
		if (!$this->session->userdata('sz_token') || $this->session->userdata('sz_token') !== $token)
		{
			exit('error');
		}
		
		$post = array(
			'category_name'	=> $this->input->post('category_name', TRUE)
		);

		$ret = $this->blog_model->update_category($post, $this->input->post('sz_blog_category_id'));
		echo ($ret) ? $post['category_name'] : 'error';
		exit;
	}
	
	/**
	 * Ajax応答用カテゴリ削除
	 * @param $cid
	 * @param $token
	 */
	function ajax_delete_category($cid, $token)
	{
		if (!$this->session->userdata('sz_token') || $this->session->userdata('sz_token') !== $token || !$cid)
		{
			exit('error');
		}
		
		$ret = $this->blog_model->delete_category((int)$cid);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}
	
	/**
	 * Ajax応答用カテゴリソート
	 * @param $token
	 */
	function ajax_sort_order($token = FALSE)
	{
		if ( ! $token || $this->session->userdata('sz_token') !== $token )
		{
			exit('access denied');
		}
		
		$i   = 0;
		$ret = TRUE;
		while ( $this->input->post('order' . ++$i) )
		{
			$id = $this->input->post('order' . $i);
			if ( ! $this->blog_model->update_category_sort_order($id, $i) )
			{
				$ret = FALSE;
				break;
			}
		}
		echo ( $ret ) ? 'complete' : 'error';
		exit;
	}
}