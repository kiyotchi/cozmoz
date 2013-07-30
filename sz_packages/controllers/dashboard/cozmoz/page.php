<?php

class Page extends SZ_Controller
{
	public static $page_title = '行政書士管理システム';
	public static $description = '行政書士システム';
	
	function __construct()
	{
		parent::SZ_Controller();
		
		// ディレクトリ扱いのコントローラのため、子ページを検索してリダイレクトさせる
		$child_page = $this->dashboard_model->get_first_child_page($this->page_id);
		// redirect to second level
		redirect($child_page);
	}
}