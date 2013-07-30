<?php
/**
 * ===============================================================================
 * 
 * Seezoo ログアウトコントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Logout extends SZ_Controller
{
	public static $page_title = 'ログアウト';
	public static $description = 'ログアウトを行います。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller(FALSE);
		$this->load->model('auth_model');
		$this->load->helper('cookie');
	}
	
	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$this->auth_model->logout();
		// 継続ログインクッキーも削除する
		delete_cookie('seezoo_remembers');
		
		redirect('/');
	}
	
	/**
	 * メンバーのログアウト
	 */
	function logout_member()
	{
		$this->auth_model->member_logout();
		
		// 継続ログインクッキーも削除する
		delete_cookie('seezoo_remembers_member');
		
		redirect('/');
	}
}