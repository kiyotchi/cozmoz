<?php
/**
 * ===============================================================================
 *
 * Seezoo dashboard サイト設定ベースコントローラ
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */

class Page extends SZ_Controller
{
	public static $page_title = 'サイト運用設定';
	public static $description = 'サイトのタイトルや全体の設定を行います。';
	
	function __construct()
	{
		parent::SZ_Controller();

		// ディレクトリ扱いのコントローラのため、子ページを検索してリダイレクトさせる
		$child_page = $this->dashboard_model->get_first_child_page($this->page_id);
		// redirect to second level
		redirect($child_page);
	}
}