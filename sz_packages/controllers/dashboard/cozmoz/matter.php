<?php
class Matter extends SZ_Controller
{
	public static $page_title  = '事件簿';
	public static $description = '事件簿';
	
	
	// ----------------------------------------------------
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model( 'dashboard/cozmoz/customer_model' );
		$this->load->library('im_require_lib');
		$this->load->library('form_validation');
		$this->customer_model->table_check();
		
		$this->add_header_item(build_css('css/ajax_styles.css'));
		$this->add_header_item(build_css('css/edit_base.css'));
		$this->load->model('file_model');
	}
	
	// ----------------------------------------------------
	
	/**
	 * リスト
	 */
	public function index()
	{
		$data = array();
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 40,
				'paging' => TRUE,
				'name' => $this->customer_model->matter_table_name,
				'key' => 'id',
				'repeat-control' => ''
			),
			array(
				'name' => $this->customer_model->customer_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'id', 'join-field' => 'customer_id', 'operator' => '=' )
				)
			)
		);
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		
		//関連テーブル
		$data['matter_table'] = $this->customer_model->matter_table_name;
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		
		
		//ハッシュに保存
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$this->load->view('dashboard/cozmoz/matter_view',$data);
		
	}
	
	// ----------------------------------------------------
	public function detail()
	{
		$data = array();
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 1,
				'paging' => false,
				'name' => $this->customer_model->matter_table_name,
				'key' => 'id',
				'repeat-control' => '',
				'query' => array(
					array( 'field' => 'id', 'value' => $this->input->get('id'), 'operator' => '=' )
				)
			),
			array(
				'name' => $this->customer_model->matter_detail_tax_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'matter_id', 'join-field' => 'id', 'operator' => '=' )
				),
				'repeat-control' => 'insert delete',
			),
			array(
				'name' => $this->customer_model->public_document_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'matter_id', 'join-field' => 'id', 'operator' => '=' )
				),
				'repeat-control' => 'insert delete',
				
			),
			array(
				'name' => $this->customer_model->matter_detail_notax_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'matter_id', 'join-field' => 'id', 'operator' => '=' )
				),
				'repeat-control' => 'insert delete',
			),
			array(
				'name' => $this->customer_model->customer_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'id', 'join-field' => 'customer_id', 'operator' => '=' )
				)
				
			),
			array(
				'name' => $this->customer_model->meeting_minutes_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'id', 'join-field' => 'customer_id', 'operator' => '=' )
				),
				'repeat-control' => 'insert delete',
				
			),
			array(
				'name' => $this->customer_model->document_list_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'id', 'join-field' => 'customer_id', 'operator' => '=' )
				),
				'repeat-control' => 'insert delete',
				
			),
		);
		
		
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		
		//関連テーブル
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		$data['matter_table'] = $this->customer_model->matter_table_name;
		$data['detail_tax'] = $this->customer_model->matter_detail_tax_table_name;
		$data['detail_notax'] = $this->customer_model->matter_detail_notax_table_name;
		$data['meeting_minutes_table'] = $this->customer_model->meeting_minutes_table_name;
		$data['document_list_table'] = $this->customer_model->document_list_table_name;
		$data['public_document_table'] = $this->customer_model->public_document_table_name;
		
		
		
		//選択肢に利用
		$data['order_status_list'] = $this->customer_model->get_order_status_view();
		$data['remuneration_list'] = $this->customer_model->get_remuneration_list();
		
		
		//ハッシュに保存
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		$data['business_type_list'] = $this->customer_model->get_business_type_list();
		//var_dump($data['business_type_list']);
		
		$this->load->view('dashboard/cozmoz/matter_detail',$data);
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * PDFのテスト
	 */
	public function pdf_test()
	{
		require_once( APPPATH . 'libraries/thirdparty/mpdf/mpdf.php');
		$mpdf = new mPDF('ja','A4');
		$mpdf->useAdobeCJK = true;
		$html = '<html><body>1111あ<b>いうえ</b>お</body></html>';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
	}
	// ----------------------------------------------------
}