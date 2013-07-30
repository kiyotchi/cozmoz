<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard 静的ページ管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Static_pages extends SZ_Controller
{
	public static $page_title = '静的ページ管理';
	public static $description = '静的なページを管理します。';
	
	function __construct()
	{
		parent::SZ_Controller();
		
		$this->load->helper('directory');
		$this->load->model(array('dashboard_model', 'sitemap_model'));
	}
	
	function index()
	{
		$data->static_pages = directory_map('./statics/');//$this->sitemap_model->get_static_pages();

		// FALSEが戻る場合があるので、空の配列に変換
		if ( ! $data->static_pages)
		{
			$data->static_pages = array();
		}
		
		$this->load->view('dashboard/pages/static_pages', $data);
	}
	
}
