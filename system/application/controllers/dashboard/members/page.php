<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard 登録ユーザ管理コントローラ
 * 
 * @package Seezoo Plugins
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Page extends SZ_Controller
{
	public static $page_title = 'メンバー管理';
	public static $description = 'サイトに登録されたメンバーを管理します。';
	
	function __construct()
	{
		parent::SZ_Controller();
		
		$child_page = $this->dashboard_model->get_first_child_page($this->page_id);
		redirect($child_page);
	}
	
	function index()
	{
		show_404();
	}
}
