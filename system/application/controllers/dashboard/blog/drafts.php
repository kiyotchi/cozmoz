<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard 下書き保存済みブログエントリ表示用コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Drafts extends SZ_Controller
{
	public $page_title = '下書き一覧';
	public $page_description = '下書き保存されたエントリーの一覧を表示します。';
	
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
		
		$data->entry = $this->blog_model->get_all_entries($this->limit, $offset, TRUE);
		$total = $this->blog_model->get_entry_count(TRUE);
		
		$path = page_link('dashboard/blog/drafts/index');
		$data->pagination = $this->_pagination($path, $total, 5, $this->limit);
		$data->category = $this->blog_model->get_category_array();
		
		if ( $this->session->flashdata('blog_flag') !== FALSE )
		{
			switch ( $this->session->flashdata('blog_flag') )
			{
				case 0:
					$data->msg = '下書きの削除に失敗しました。';
					break;
				case 1:
					$data->msg = '下書きを削除しました。';
					break;
			}
		}
		
		$this->load->view('dashboard/blog/drafts', $data);
	}
	
	/**
	 * 下書きエントリー削除
	 * @param $draft_id
	 */
	function delete($draft_id)
	{
		$ret = $this->blog_model->delete_drafted_entry($draft_id);
		$this->session->set_flashdata('blog_flag', (int)$ret);
		redirect('dashboard/blog/drafts/index');
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