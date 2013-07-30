<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ページコントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Page extends SZ_Controller
{
	public static $page_title = 'ブロック管理';
	public static $description = 'フロントで使用するブロックを管理します。';

	function __construct()
	{
		parent::SZ_Controller();
		
		// ディレクトリ扱いのコントローラのため、子ページを検索してリダイレクトさせる
		$child_page = $this->dashboard_model->get_first_child_page($this->page_id);
		redirect($child_page);
		
	}
	
	function index()
	{
		show_404();
	}
}
	