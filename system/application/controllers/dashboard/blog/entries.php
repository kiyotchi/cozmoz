<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard 投稿済みブログエントリ表示用コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Entries extends SZ_Controller
{
	public $page_title = 'エントリー一覧';
	public $page_description = '投稿されたエントリーの一覧を表示します。';
	
	public $msg;
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
		
		$data->entry = $this->blog_model->get_all_entries($this->limit, $offset);
		$total = $this->blog_model->get_entry_count();
		
		$path = page_link('dashboard/blog/entries/index');
		$data->pagination = $this->_pagination($path, $total, 5, $this->limit);
		$data->category = $this->blog_model->get_category_array();
		
		$this->load->view('dashboard/blog/entries', $data);
	}
	
	/**
	 * エントリ詳細表示
	 * @param int $eid
	 */
	function detail($eid = 0)
	{
		$this->_enable_check();
		
		if (!$eid)
		{
			$this->msg = '対象の記事IDが見つかりませんでした。';
			$this->index();
			return;
		}
		
		$data->entry = $this->blog_model->get_entry_one($eid, TRUE);
		
		if (!$data->entry)
		{
			$this->msg = '対象の記事が見つかりませんでした。';
			$this->index();
			return;
		}
		
		$data->category = $this->blog_model->get_category_array();
		
		$this->load->view('dashboard/blog/detail', $data);
	}
	
	/**
	 * 削除確認
	 */
	function delete_confirm($eid = 0)
	{
		if (!$eid)
		{
			$this->msg = '対象の記事IDが見つかりませんでした。';
			$this->index();
			return;
		}
		$data->entry = $this->blog_model->get_entry_one($eid);
		
		if (!$data->entry)
		{
			$this->msg = '対象の記事が見つかりませんでした。';
			$this->index();
			return;
		}
		
		$data->category = $this->blog_model->get_category_array();
		$data->ticket = $this->_set_delete_ticket();
		$data->ref = $this->input->server('HTTP_REFERER');
		$data->id = (int)$eid;
		
		$this->load->view('dashboard/blog/delete_confirm', $data);
	}
	
	/**
	 * エントリ削除
	 */
	function do_delete()
	{
		$this->_enable_check();
		$this->_check_ticket();
		
		$eid = $this->input->post('sz_blog_id');
		if (!$eid)
		{
			$this->msg = '対象の記事IDが見つかりませんでした。';
			$this->index();
			return;
		}
		
		$ret = $this->blog_model->delete_entry($eid);
		redirect($this->input->post('referer'));
	}
	
	/**
	 * 削除用トークンセット
	 */
	function _set_delete_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_blog_delete_ticket', $ticket);
		return $ticket;
	}
	
	/**
	 * 削除用トークンチェック
	 */
	function _check_ticket()
	{
		$ticket = $this->input->post('sz_blog_delete_ticket');
		if (!$ticket || $ticket != $this->session->userdata('sz_blog_delete_ticket'))
		{
			exit('クッキーを有効にしてください。有効な場合は不正な操作です。');
		}
	}
	
	/**
	 * ブログが利用可能か判定
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
	 * ページネーションセット
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
}