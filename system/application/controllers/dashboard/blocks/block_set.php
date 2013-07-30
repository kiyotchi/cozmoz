<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ブロックセット管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Block_set extends SZ_Controller
{
	public static $page_title = 'ブロックセット管理';
	public static $description = 'ブロックセットを管理します。';
	
	public $msg;
	public $view_dir = 'dashboard/blocks/';
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('block_model');
	}
	
	function index($status = '')
	{
		$data->block_set       = $this->block_model->get_registed_block_set_master();
		$data->block_set_count = $this->block_model->get_registed_block_set_master_count();
		$this->msg             = $this->_create_message($status);
		
		$this->load->view($this->view_dir . 'block_set_list', $data);
	}
	
	function detail_edit($block_set_master_id = 0 )
	{
		if ( ! $block_set_master_id )
		{
			$this->index('notfound');
			return;
		}
		
		$data->set = $this->block_model->get_block_set_details($block_set_master_id);
		$this->load->view($this->view_dir . 'block_set_detail_edit', $data);
	}
	
	protected function _create_message($status)
	{
		switch ( $status )
		{
			case 'notfound':
				return 'ブロックセットマスタが指定されていません';
			case 'rename_success':
				return '名前を変更しました。';
			case 'rename_error':
				return '名前の変更に失敗しました。';
			default :
				return '';
		}
	}
	
	
	/**
	 * Ajax応答、ブロックセットデータの並び替え
	 * @param $token
	 */
	function update_sort_order($token = FALSE)
	{
		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}
		
		$i    = 0;
		$flag = TRUE;
		while ( $this->input->post('order' . ++$i) )
		{
			$id = (int)$this->input->post('order' . $i);
			if ( ! $this->block_model->update_block_set_data_order($id, $i) )
			{
				$flag = FALSE;
				break;
			}
		}
		
		echo ( $flag === TRUE ) ? 'success' : 'error';
	}
	
	
	/**
	 * Ajax1応答、ブロックセットマスタの削除
	 * @param $master_id
	 * @param $token
	 */
	function delete_block_set_master($master_id, $token = FALSE)
	{
		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}
		
		$ret = $this->block_model->delete_blcok_set_master($master_id);
		echo ( $ret ) ? 'complete' : 'error';
	}
	
	
	/**
	 * Ajax応答、ブロックセットデータの削除
	 * @param $dat_id
	 * @param $token
	 */
	function delete_block_set_data($dat_id, $token = FALSE)
	{
		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}
		
		$ret = $this->block_model->delete_blcok_set_data_piece($dat_id);
		echo ( $ret ) ? 'complete' : 'error';
	}
	
	
	/**
	 * ブロックセットマスタ名変更リクエストハンドル
	 * @param $token
	 */
	function rename($token = FALSE)
	{
		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}
		
		$id   = (int)$this->input->post('id');
		$name = $this->input->post('rec_name');
		$ret  = ( $this->block_model->rename_block_set_master($id, $name) ) ? 'rename_success' : 'rename_error';
		redirect('dashboard/blocks/block_set/index/' . $ret);
	}
}