<?php

/**
 * ===============================================================================
 *
 * Seezoo dashboard ログ一覧表示コントローラ
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */

class Log_history extends SZ_Controller
{
	public static $page_title = 'システムログ';
	public static $description = 'システムログを参照します。';
	
	private $limit = 20;
	
	function __construct()
	{
		parent::SZ_Controller();
	}
	
	/**
	 * デフォルトメソッド
	 */
	function index($filter = 'all', $offset = 0)
	{
		$data->logs = $this->dashboard_model->get_system_logs($filter, $this->limit, $offset);
		$data->filters = $this->dashboard_model->get_system_logs_filter_strings();
		$data->filter = $filter;
		
		// create pagination
		$total = $this->dashboard_model->get_system_logs_count($filter);
		$endoftotal = (($offset+ $this->limit) > $total) ? $total : ($offset + $this->limit);
		if($total)
		{
			 $data->total = $total . '件中' . ($offset + 1) . '-' . $endoftotal . '件表示';
		}
		else
		{
			$data->total = '';
		}
		
		$path = page_link() . 'dashboard/site_settings/log_history/index/' . $filter . '/';
		$data->pagination = $this->_pagination($path, $total, 6, $this->limit);
		
		// assign message
		if ( $this->session->flashdata('log_flag') )
		{
			switch ( $this->session->flashdata('log_flag') )
			{
				case 1:
					$data->msg = 'ログを削除しました。';
					break;
				case 2:
					$data->msg = 'ログを空にしました。';
					break;
			}
		}
		// debug code
		//$this->output->enable_profiler(TRUE);
		
		$this->load->view('dashboard/site_settings/log_history', $data);
	}
	
	/**
	 * ログ削除
	 */
	function delete_log()
	{
		$ids = $this->input->post('log_ids');
		if ( $ids )
		{
			$ids = array_map('intval', $ids);
		}
		else
		{
			$ids = array();
		}
		$filter = $this->input->post('filter');
		
		$this->dashboard_model->delete_log($ids);
		$this->session->set_flashdata('log_flag', 1);
		redirect('dashboard/site_settings/log_history/index/' . $filter);
	}
	
	function clear_log()
	{
		$this->dashboard_model->delete_all_log();
		$this->session->set_flashdata('log_flag', 2);
		redirect('dashboard/site_settings/log_history/index/all');
	}
	
	/**
	 * ページネーション生成
	 * @param $path
	 * @param $total
	 * @param $segment
	 * @param $limit
	 */
	function _pagination($path, $total, $segment, $limit)
	{
		$this->load->library('pagination');
		
		$config = array(
		  'base_url'      => $path,
		  'total_rows'   => $total,
		  'per_page'    => $limit,
		  'uri_segment'=> $segment,
		  'num_links'    => 5,
		  'prev_link'     => '&laquo;前へ',
		  'next_link'     => '次へ&raquo;'
		);
		$this->pagination->initialize($config);
		
		return $this->pagination->create_links();
	}
}

