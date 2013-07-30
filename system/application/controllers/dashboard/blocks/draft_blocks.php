<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard 下書きブロック管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Draft_blocks extends SZ_Controller
{
	public static $page_title = '下書き / 共有管理';
	public static $description = '下書き/共有したブロックを管理します。';
	
	public $msg;
	public $edit_mode = 'NO_EDIT'; // temporary
	public $version_number = 0; // temporary
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('draft_model');
	}
	
	function index($status = '')
	{
		$data->draft_block_list  = $this->draft_model->get_draft_block_list($this->user_id);
		$data->draft_block_count = $this->draft_model->get_draft_block_count($this->user_id);
		
		$this->load->model('ajax_model');
		$data->static_blocks       = $this->ajax_model->get_static_blocks($this->user_id);
		$data->static_blocks_count = count($data->static_blocks);
		
		if ( ! empty($status) )
		{
			$this->msg = ( $status === 'success' )
			                 ? '名前を変更しました。'
			                 : '名前変更に失敗しました。';
		}
		
		$this->load->view('dashboard/blocks/draft_blocks', $data);
	}
	
	function rename_draft($token = FALSE)
	{
		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}
		
		$id   = (int)$this->input->post('id');
		$name = $this->input->post('rec_name');
		$ret  = ( $this->draft_model->rename_draft_block($id, $name) ) ? 'success' : 'error';
		redirect('dashboard/blocks/draft_blocks/index/' . $ret);
	}
	
	function rename_static($token = FALSE)
	{
//		if ( ! $token || $token !== $this->session->userdata('sz_token') )
//		{
//			exit('access_denied');
//		}
		
		$id   = (int)$this->input->post('id');
		$name = $this->input->post('rec_name');
		$ret  = ( $this->draft_model->rename_static_block($id, $name) ) ? 'success' : 'error';
		redirect('dashboard/blocks/draft_blocks/index/' . $ret);
	}
}