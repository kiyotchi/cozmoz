<?php
class Config extends SZ_Controller
{
	public static $page_title  = '設定';
	public static $description = '設定';
	
	
	// ----------------------------------------------------
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model( 'dashboard/cozmoz/customer_model' );
		$this->load->library('im_require_lib');
		$this->load->library('form_validation');
		$this->customer_model->table_check();
	}
	
	// ----------------------------------------------------
	function index()
	{
		$data = array();
		$this->load->view('dashboard/cozmoz/config_top',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 受注ステータス定義
	 */
	public function order_status()
	{
		$data = array();
		
		
		//取得データの定義を作成
		$def = array(
			array(
				'paging' => true,
				'name' => $this->customer_model->order_status_table_name,
				'key' => 'id',
				'repeat-control' => 'insert confirm-delete',
				'sort' => array(
					array(
						'field' => 'rank',
						'direction' => 'ASC'
					),
				),
			)
		);
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['order_status_table'] = $this->customer_model->order_status_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$this->load->view('dashboard/cozmoz/config_order_status',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 事務所情報編集
	 */
	public function office_info()
	{
		$data = array();
		
		
		//取得データの定義を作成
		$def = array(
			array(
				'paging' => true,
				'records' => 1,
				'name' => $this->customer_model->office_info_table_name,
				'key' => 'id',
				'repeat-control' => 'insert confirm-delete',
				'query' => array(
					array(
						'field' => 'id',
						'value' => 1,
						'operator' => '='
					)
				)
			)
		);
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['office_info_table'] = $this->customer_model->office_info_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		
		$data['div'] = $this->customer_model->get_office_info_division();
		$data['bank_div'] = $this->customer_model->get_bank_division();
		$data['pref_list'] = $this->customer_model->get_pref_list();
		
		
		$this->load->view('dashboard/cozmoz/config_office_info',$data );
		
	}
	
	// ----------------------------------------------------
	
}