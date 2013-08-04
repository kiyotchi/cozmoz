<?php

class Customer extends SZ_Controller
{
	public static $page_title  = '顧客管理';
	public static $description = '顧客管理';
	
	//敬称リスト
	private $hono = array(
		'御中' => '御中',
		'様' => '様'
	);
	
	//都道府県リスト
	private $pref = array(
		'北海道' => '北海道',
		'青森県' => '青森県',
		'岩手県' => '岩手県',
		'宮城県' => '宮城県',
		'秋田県' => '秋田県',
		'山形県' => '山形県',
		'福島県' => '福島県',
		'茨城県' => '茨城県',
		'栃木県' => '栃木県',
		'群馬県' => '群馬県',
		'埼玉県' => '埼玉県',
		'千葉県' => '千葉県',
		'東京都' => '東京都',
		'神奈川県' => '神奈川県',
		'新潟県' => '新潟県',
		'富山県' => '富山県',
		'石川県' => '石川県',
		'福井県' => '福井県',
		'山梨県' => '山梨県',
		'長野県' => '長野県',
		'岐阜県' => '岐阜県',
		'静岡県' => '静岡県',
		'愛知県' => '愛知県',
		'三重県' => '三重県',
		'滋賀県' => '滋賀県',
		'京都府' => '京都府',
		'大阪府' => '大阪府',
		'兵庫県' => '兵庫県',
		'奈良県' => '奈良県',
		'和歌山県' => '和歌山県',
		'鳥取県' => '鳥取県',
		'島根県' => '島根県',
		'岡山県' => '岡山県',
		'広島県' => '広島県',
		'山口県' => '山口県',
		'徳島県' => '徳島県',
		'香川県' => '香川県',
		'愛媛県' => '愛媛県',
		'高知県' => '高知県',
		'福岡県' => '福岡県',
		'佐賀県' => '佐賀県',
		'長崎県' => '長崎県',
		'熊本県' => '熊本県',
		'大分県' => '大分県',
		'宮崎県' => '宮崎県',
		'鹿児島県' => '鹿児島県',
		'沖縄県' => '沖縄県'
	);
	
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
		
		//取得データの定義を作成
		$query = array();
		$query_key_list = array(
			'company',
			'name1',
			'name2',
			'tel',
			'pref',
			'address1',
			'address2',
			'address3',
		);
		foreach( $query_key_list as $key )
		{
			$query[] = array(
				'field' => $key,
				'operator' => 'LIKE',
				'value' => '%' . $this->input->get( $key ) . '%',
			);
		}
		
		$def = array(
			array(
				'records' => 2,
				'paging' => true,
				'name' => $this->customer_model->customer_table_name,
				'key' => 'id',
				'repeat-control' => 'confirm-delete',
				'protect-reading' => array('pref'),
				'query' => $query,
			)
		);
		
		$data['pref'] = array_merge(
				array( '' => ''),
				$this->pref
			);
		
		
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		
		
		$this->load->view('dashboard/cozmoz/customer_view',$data);
	}
	// ----------------------------------------------------
	
	/**
	 * 顧客追加フォーム
	 */
	public function add()
	{
		$data = array();
		$data['hono'] = $this->hono;
		$data['pref'] = $this->pref;
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		$this->load->view('dashboard/cozmoz/customer_add',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 顧客確認画面
	 */
	public function confirm()
	{
		$data = array();
		$data['hono'] = $this->hono;
		$data['pref'] = $this->pref; 
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		
		$this->form_validation->set_rules( 'id', 'ID', 'xss_clean');
		$this->form_validation->set_rules( 'company', '会社名/屋号', 'xss_clean');
		$this->form_validation->set_rules( 'honorific', '敬称', 'xss_clean');
		$this->form_validation->set_rules( 'name1', '名前（姓）', 'xss_clean|required');
		$this->form_validation->set_rules( 'name2', '名前（名）', 'xss_clean|required');
		$this->form_validation->set_rules( 'kana1', 'かな（姓）', 'xss_clean|required');
		$this->form_validation->set_rules( 'kana2', 'かな（名）', 'xss_clean|required');
		$this->form_validation->set_rules( 'tel', '電話番号', 'xss_clean|required|tel_number');
		$this->form_validation->set_rules( 'fax', 'FAX', 'xss_clean|tel_number');
		$this->form_validation->set_rules( 'mailaddress', 'メールアドレス', 'xss_clean|valid_email');
		$this->form_validation->set_rules( 'zip', '郵便番号', 'xss_clean|required');
		$this->form_validation->set_rules( 'pref', '都道府県', 'xss_clean');
		$this->form_validation->set_rules( 'address1', '住所1', 'xss_clean|required');
		$this->form_validation->set_rules( 'address2', '住所2', 'xss_clean|required');
		$this->form_validation->set_rules( 'address3', '住所3', 'xss_clean');
		
		$res = $this->form_validation->run();
		
		
		if( $res === FALSE )
		{
			$this->load->view('dashboard/cozmoz/customer_add',$data);
			return;
		}
		
		
		//作成日
		$data['create_date'] = date('Y-m-d H:i:s');
		$data['update_date'] = date('Y-m-d H:i:s');
		
		//データベース定義
		$def = array(
			array(
				'records' => 1,
				'paging' => true,
				'name' => $this->customer_model->customer_table_name,
				'key' => 'id',
				'post-reconstruct' => true,
				'post-dismiss-message' => '登録処理中',
				'post-move-url' => site_url('dashboard/cozmoz/customer')
				//'repeat-control' => '',
			)
		);
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		
		
		$this->load->view('dashboard/cozmoz/customer_confirm',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 再入力
	 */
	public function repeat()
	{
		$data = array();
		$data['hono'] = $this->hono;
		$data['pref'] = $this->pref; 
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		
		$this->form_validation->set_rules( 'id', 'ID', 'xss_clean');
		$this->form_validation->set_rules( 'company', '会社名/屋号', 'xss_clean');
		$this->form_validation->set_rules( 'honorific', '敬称', 'xss_clean');
		$this->form_validation->set_rules( 'name1', '名前（姓）', 'xss_clean|required');
		$this->form_validation->set_rules( 'name2', '名前（名）', 'xss_clean|required');
		$this->form_validation->set_rules( 'kana1', 'かな（姓）', 'xss_clean|required');
		$this->form_validation->set_rules( 'kana2', 'かな（名）', 'xss_clean|required');
		$this->form_validation->set_rules( 'tel', '電話番号', 'xss_clean|required|tel_number');
		$this->form_validation->set_rules( 'fax', 'FAX', 'xss_clean|tel_number');
		$this->form_validation->set_rules( 'mailaddress', 'メールアドレス', 'xss_clean|valid_email');
		$this->form_validation->set_rules( 'zip', '郵便番号', 'xss_clean|required');
		$this->form_validation->set_rules( 'pref', '都道府県', 'xss_clean');
		$this->form_validation->set_rules( 'address1', '住所1', 'xss_clean|required');
		$this->form_validation->set_rules( 'address2', '住所2', 'xss_clean|required');
		$this->form_validation->set_rules( 'address3', '住所3', 'xss_clean');
		
		$res = $this->form_validation->run();
		
		$this->load->view('dashboard/cozmoz/customer_add',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 編集モード
	 */
	public function edit()
	{
		$data = array();
		$data['hono'] = $this->hono;
		$data['pref'] = $this->pref; 
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 1,
				'paging' => true,
				'name' => $this->customer_model->customer_table_name,
				'key' => 'id',
				'query' => array(
					array( 'field' => 'id', 'value' => $this->input->get('id'), 'operator' => '=' )
				),
				'protect-writing' => array(
					 'company', 'honorific', 'name1', 'name2', 'kana1', 'kana2', 'tel', 'fax', 'mailaddress', 'zip', 'pref', 'address1', 'address2', 'address2'
				)
			)
		);
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		
		$this->load->view('dashboard/cozmoz/customer_add',$data);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 詳細ページ
	 */
	public function detail()
	{
		$data = array();
		$data['hono'] = $this->hono;
		$data['pref'] = $this->pref; 
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 1,
				'paging' => false,
				'name' => $this->customer_model->customer_table_name,
				'key' => 'id',
				'repeat-control' => '',
				'query' => array(
					array( 'field' => 'id', 'value' => $this->input->get('id'), 'operator' => '=' )
				)
			),
			array(
				'name' => $this->customer_model->matter_table_name,
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'customer_id', 'join-field' => 'id', 'operator' => '=' )
				),
				'repeat-control' => 'insert delete',
				
			)
		);
		
		$data['hash'] = $this->im_require_lib->save_def( $def );
		$data['cutomer_table'] = $this->customer_model->customer_table_name;
		$data['matter_table'] = $this->customer_model->matter_table_name;
		$data['im_url'] = site_url( 'dashboard/im_require' ) . '?hash='.$data['hash'] ;
		$this->add_header_item( build_javascript( $data['im_url'] ) );
		
		
		$this->load->view('dashboard/cozmoz/customer_detail',$data);
	}
	
	// ----------------------------------------------------
}