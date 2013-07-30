<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard トラックバック管理用コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Trackbacks extends SZ_Controller
{
	public $page_title = 'トラックバック管理';
	public $page_description = 'トラックバックされたリクエストを管理します。';
	
	public $msg;
	public $ticket_name = 'sz_tb_token';
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
		
		$data->trackbacks = $this->blog_model->get_requested_trackbacks(TRUE, $this->limit, (int)$offset);
		$total   = $this->blog_model->get_requested_trackbacks_count(TRUE);
		
		$path = page_link('dashboard/blog/trackbacks/index');
		$data->pagination = $this->_pagination($path, $total, 5, $this->limit);
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
		$data->ticket = $this->_set_ticket();
		
		if ( $this->session->flashdata('status') === 'deleted' )
		{
			$this->msg = 'トラックバックを削除しました。';
		}
		
		$this->load->view('dashboard/blog/trackbacks', $data);
	}
	
	/**
	 * Ajax トラックバック承認
	 */
	function allow_trackback($tb_id = 0)
	{
		if ( ! $tb_id )
		{
			echo 'error';
		}
		
		echo ( $this->blog_model->update_allow_trackback($tb_id, TRUE) ) ? 'success' : 'error';
	}
	
	/**
	 * 選択されたトラックバックを一括削除
	 */
	function delete_tb_selectables()
	{
		$this->_check_ticket();
		
		if (!$this->input->post('sz_delete_trackback'))
		{
			redirect('dashboard/blog/trackbacks');
		}
		
		$tbs = $this->input->post('sz_delete_trackback');
		$ret = $this->blog_model->delete_trackback($tbs);
		
		$this->session->set_flashdata('status', 'deleted');
		redirect('dashboard/blog/trackbacks');
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
	
	/**
	 * トークン生成
	 * @access private
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata($this->ticket_name, $ticket);
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

		if (!$ticket || $ticket !== $this->session->userdata($this->ticket_name))
		{
			exit('不正な操作です。また、リロードは禁止されています。');
		}
	}
}