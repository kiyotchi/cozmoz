<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard 管理ユーザーコントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Page extends SZ_Controller
{
	public static $page_title = '管理ユーザー設定';
	public static $description = '管理者を追加したり、編集したりできます。';
	
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