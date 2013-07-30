<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ブログコメント管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Comment extends SZ_Controller
{
	public $page_title = 'コメント管理';
	public $page_description = '投稿につけられたコメントを管理します。';
	
	public $msg;
	public $ticket_name = 'sz_ticket';
	
	private $limit = 20;
	
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
	 * @param $offset
	 */
	function index($offset = 0)
	{
		$this->_enable_check();
		
		$data->comments = $this->blog_model->get_posted_comments($this->limit, $offset);
		$total = $this->blog_model->get_posted_comments_count();
		
		// make display total string
		$endoftotal = (($offset+ $this->limit) > $total) ? $total : ($offset + $this->limit);
		if($total > 0)
		{
			$data->total = $total . '件中' . ($offset + 1) . '-' . $endoftotal . '件表示';
		}
		else
		{
			$data->total = '';
		}
		
		$path = page_link('dashboard/blog/comment/index/');
		// set pagination
		$data->pagination = $this->_pagination($path, $total, 5, $this->limit);
		$data->titles = $this->blog_model->get_entry_titles();
		$data->ticket = $this->_set_ticket();
		
		$this->load->view('dashboard/blog/comments', $data);
	}
	
	/**
	 * Ajax応答用コメント削除
	 * @param $cid
	 * @param $token
	 */
	function delete_comment($cid, $token = FALSE)
	{
		if (!$token || $token !== $this->session->userdata('sz_token'))
		{
			echo 'access denied';
		}
		$ret = $this->blog_model->delete_comment_one((int)$cid);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}
	
	/**
	 * 選択されたコメントを一括削除
	 */
	function delete_comment_selectables()
	{
		$this->_check_ticket();
		
		if (!$this->input->post('sz_delete_comment'))
		{
			redirect('dashboard/blog/comment');
		}
		
		$com = $this->input->post('sz_delete_comment');
		$ret = $this->blog_model->delete_comment($com);
		
		redirect('dashboard/blog/comment');
	}
	
	/**
	 * ページネーションセット
	 * @access private
	 * @param string $path
	 * @param int $total
	 * @param int $segment
	 * @param int $limit
	 */
	function _pagination($path, $total, $segment, $limit)
	{
		$this->load->library('pagination');
		$config = array(
			'base_url'		=> $path,
			'total_rows'	=> $total,
			'per_page'		=> $limit,
			'uri_segment'	=> $segment,
			'num_links'		=> 5,
			'prev_link'		=> '&laquo;前へ',
			'next_link'		=> '&raquo;次へ'
		);
		
		$this->pagination->initialize($config);
		return $this->pagination->create_links();
	}
	
	/**
	 * トークン生成
	 * @access private
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_flashdata($this->ticket_name, $ticket);
		return $ticket;
	}
	
	/**
	 * トークンチェック
	 * @access private
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
	 * ブログが利用可能かどうか判定
	 */
	function _enable_check()
	{
		// if blog id unabled, redirect index
		if ((int)$this->info->is_enable === 0)
		{
			redirect('dashboard/blog/settings');
		}
	}
	
	
}