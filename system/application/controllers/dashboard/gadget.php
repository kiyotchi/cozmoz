<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ガジェット管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Gadget extends SZ_Controller
{
	public static $page_title = 'ユーザーツール設定';
	public static $description = '編集時に使えるガジェットを管理します。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model('auth_model');
	}
	
	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$this->load->view('dashboard/gadget/index');
	}
}