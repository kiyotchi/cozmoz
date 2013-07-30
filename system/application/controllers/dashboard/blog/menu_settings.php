<?php
/**
 * ==========================================================================
 * 
 * Seezoo ブログメニュー設定用コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ==========================================================================
 */

class Menu_settings extends SZ_Controller
{
	public static $page_title = 'ブログメニュー設定';
	public static $description = 'ブログメニューの表示を設定します。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('blog_model');
		
		$this->info = $this->blog_model->get_blog_info();
		$this->add_header_item(build_css(file_link() . 'css/sz_blog.css'));
	}
	
	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$this->_enable_check();
		
		$data->menu_data = $this->blog_model->get_blog_menu_data();
		
		$this->load->view('dashboard/blog/menu_settings', $data);
	}
	
	/**
	 * Ajax応答用リクエストデータ保存
	 */
	function update_menu_setting()
	{
		$this->_check_ticket();
		
		$post = $this->input->post('settings');
		
		$ret = $this->blog_model->update_menu_settings($post);
		
		echo ($ret) ? 'complete' : 'error';
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
			$ticket = $this->input->post('token');
		}
		if (!$ticket || $ticket !== $this->session->userdata('sz_token'))
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