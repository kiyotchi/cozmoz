<?php
class Business_type extends SZ_Controller
{
	public static $page_title  = '業務種別';
	public static $description = '業務種別';
	
	
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
	
	/**
	 * 業務種別など定義
	 */
	public function index()
	{
		$data = array();
		$this->load->view('dashboard/cozmoz/business_type_top',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 業務種別定義
	 */
	public function type()
	{
		$data = array();
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 200,
				'paging' => true,
				'name' => $this->customer_model->business_type_table_name,
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
		$data['business_type_table'] = $this->customer_model->business_type_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$this->load->view('dashboard/cozmoz/business_type',$data);
	}
	// ----------------------------------------------------
	
	/**
	 * 報酬額定義
	 */
	public function remuneration()
	{
		$data = array();
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 200,
				'paging' => true,
				'name' => $this->customer_model->remuneration_table_name,
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
		$data['remuneration_table'] = $this->customer_model->remuneration_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$data['business_type_list'] = $this->customer_model->get_business_type_list();
		
		
		$this->load->view('dashboard/cozmoz/business_type_remuneration',$data);
	}
	
	// ----------------------------------------------------
	
}