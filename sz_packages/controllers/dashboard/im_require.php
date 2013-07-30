<?php
/* =============================================================================
 * InterMediator用JSコールコントローラー
 * ========================================================================== */
class im_require extends Controller
{
	// ----------------------------------------------------
	/** 
	 * コンストラクタ
	 */
	public function __construct() {
		parent::Controller();
		$this->load->model( 'dashboard/cozmoz/customer_model' );
		$this->load->library('im_require_lib');
	}
	
	// ----------------------------------------------------
	
	/**
	 * デフォルトコントローラー
	 */
	public function index()
	{
		//IMをrequire
		require FCPATH.'INTER-Mediator/INTER-Mediator.php';
		
		//データベース設定を呼び出す
		require APPPATH . 'config/database.php';
		$dsn = $db[$active_group];
		
		
		
		//第三引数
		$three = array();
		$three['db-class'] = 'PDO';
		$three['dsn'] = 'mysql:dbname=' . $dsn['database'] . ';host=' . $dsn['hostname'];
		$three['user'] = $dsn['username'];
		$three['password'] = $dsn['password'];
		$three['option'] = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET `utf8`"
		);
		//var_dump($this->im_require_lib->get_def( $this->input->get('hash') ));
		
		
		IM_Entry(
			$this->im_require_lib->get_def( $this->input->get('hash') ),
			null,
			$three,
			0
		);
		return;
	}
	
	// ----------------------------------------------------
	
	/**
	 * テスト2
	 */
	public function test2()
	{
		//データベース設定を呼び出す
		require APPPATH . 'config/database.php';
		$dsn = $db[$active_group];
		
		$three = array();
		$three['db-class'] = 'PDO';
		$three['dsn'] = 'mysql:dbname=' . $dsn['database'] . ';host=' . $dsn['hostname'];
		$three['user'] = $dsn['username'];
		$three['password'] = $dsn['password'];
		
		$dsn = $three['dsn'];
		$user = $three['user'];
		$password = $three['password'];
		
		try{
			$dbh = new PDO($dsn, $user, $password);
		}catch (PDOException $e){
			print('Error:'.$e->getMessage());
			die();
		}
		var_dump($dbh );
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * テスト
	 */
	public function test()
	{
		$data = array();
		$data['im_path'] = '<script type="text/javascript" src="' . file_link() . '/index.php/dashboard/im_require"></script>'; 
		$this->load->view('imtest/test',$data);
	}
	
	// ----------------------------------------------------
	
}