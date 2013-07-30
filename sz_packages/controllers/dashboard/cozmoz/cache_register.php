<?php
class Cache_register extends SZ_Controller
{
	public static $page_title  = 'レジスター';
	public static $description = 'キャッシュレジスター';
	
	
	// ----------------------------------------------------
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model( 'dashboard/cozmoz/customer_model' );
		$this->load->model( 'dashboard/cozmoz/register_model' );
		
		$this->load->library('im_require_lib');
		$this->load->library('form_validation');
		$this->customer_model->table_check();
		
	}
	
	// ----------------------------------------------------
	function index()
	{
		$data = array();
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 40,
				'paging' => true,
				'name' => $this->customer_model->cache_register_table_name,
				'key' => 'id',
				'repeat-control' => '',
			),
		);
		
		
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		
		//関連テーブル
		$data['cache_register_table'] = $this->customer_model->cache_register_table_name;
		
		
		//ハッシュに保存
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$this->load->view('dashboard/cozmoz/cache_register',$data);
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * 新オーダー
	 */
	public function new_order()
	{
		$id = $this->register_model->new_order();
		redirect( 'dashboard/cozmoz/cache_register/new_order_step?id='.$id);
	}
	
	// ----------------------------------------------------
	
	public function new_order_step()
	{
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 1,
				'paging' => false,
				'name' => $this->register_model->cache_register_table_name,
				'key' => 'id',
				'repeat-control' => '',
				'query' => array(
					array( 'field' => 'id', 'value' => $this->input->get('id'), 'operator' => '=' )
				)
			),
			array(
				'name' => $this->register_model->cache_register_detail_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'cache_register_id', 'join-field' => 'id', 'operator' => '=' )
				),
				'repeat-control' => 'insert delete',
				
			)
		);
		
		
		$data['cache_register_table']  = $this->register_model->cache_register_table_name;
		$data['cache_register_detail_table']  = $this->register_model->cache_register_detail_table_name;
		
		
		//リスト用
		$data['order_status_list'] = $this->register_model->get_order_status();
		$data['payment_method_list'] = $this->register_model->get_order_paymen_list();
		
		//ハッシュに保存
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$this->load->view('dashboard/cozmoz/cache_register_order',$data);
	}
	
	
	// ----------------------------------------------------
	
	/**
	 * オーダーステータス定義
	 */
	public function def_order_status()
	{
		$def = array(
			array(
				'paging' => false,
				'name' => $this->customer_model->cache_register_order_status_table_name,
				'key' => 'id',
				'repeat-control' => 'insert delete',
				'sort' => array(
					array(
						'field' => 'rank',
						'direction' => 'ASC'
					),
				),
			)
		);
		
		//テーブル名定義
		$data['cache_register_order_status_table'] = $this->customer_model->cache_register_order_status_table_name;
		
		
		//ハッシュに保存
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$this->load->view('dashboard/cozmoz/cache_register_def_order_status',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 支払い方法定義
	 */
	public function def_payment_method()
	{
		$def = array(
			array(
				'paging' => false,
				'name' => $this->customer_model->cache_register_payment_method_table_name,
				'key' => 'id',
				'repeat-control' => 'insert delete',
				'sort' => array(
					array(
						'field' => 'rank',
						'direction' => 'ASC'
					),
				),
			)
		);
		
		$data['cache_register_payment_method'] = $this->customer_model->cache_register_payment_method_table_name;
		
		
		//ハッシュに保存
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$this->load->view('dashboard/cozmoz/cache_register_def_payment_method',$data);
	}
	
	// ----------------------------------------------------
}