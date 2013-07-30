<?php

/**
 * ===============================================================================
 *
 * Seezoo dashboard SSL設定コントローラ
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */

class Ssl extends SZ_Controller
{
	public $msg = '';
	
	public static $page_title = 'SSL設定';
	public static $description = 'SSLの設定が行えます。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
	}
	
	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$data->ssl_base_url = preg_replace('/\Ahttps:\/\//u', '', $this->site_data->ssl_base_url);
		$data->ticket = ticket_generate();
		
		switch ( $this->session->flashdata('flag') )
		{
			case 1:
				$data->msg = 'SSL設定を更新しました。';
				break;
			case 2:
				$data->msg = 'SSL設定の更新に失敗しました。';
				break;
			case 3:
				$data->msg = 'SSL設定を解除しました。';
				break;
			default:
				$data->msg = FALSE;
		}
		
		$this->load->view('dashboard/site_settings/ssl', $data);
	}
	
	/**
	 * アップデート実行
	 */
	function update()
	{
		ticket_check();
		$uri = $this->input->post('ssl_base_url', TRUE);
		$is_del = ( $this->input->post('delete') ) ? TRUE : FALSE;
		
		// valid URL?
		if ( ! $is_del && ! preg_match('/^https:\/\/[a-z0-9A-Z\s~%\.:_\-\/]+$/u', 'https://' . $uri) )
		{
			// validation error
			$data->ssl_base_url = $uri;
			$data->msg = 'URIの形式が正しくありません。';
			$data->ticket = $this->input->post('ticket');
			$this->load->view('dashboard/site_settings/ssl', $data);
			return;
		}
		
		if ( $is_del )
		{
			$this->dashboard_model->update_site_ssl_base_url(FALSE);
			$flag = 3;
		}
		else if ( $this->dashboard_model->update_site_ssl_base_url('https://' . rtrim($uri, '/') . '/') )
		{
			$flag = 1;
		}
		else 
		{
			$flag = 2;
		}
		$this->session->set_flashdata('flag', $flag);
		redirect('dashboard/site_settings/ssl');
		
	}
}