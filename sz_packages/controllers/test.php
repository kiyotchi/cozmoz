<?php
class test extends Controller
{
	// ----------------------------------------------------
	
	public function index()
	{
		$this->load->library( 'im_emulator' );
		
		
		//取得データの定義を作成
		$def = array(
			array(
				'records' => 40,
				'paging' => TRUE,
				'name' => 'cozmoz_matter',
				'key' => 'id',
				'repeat-control' => ''
			),
			array(
				'name' => 'cozmoz_customer',
				'key' => 'id',
				'relation' => array(
					array( 'foreign-key' => 'id', 'join-field' => 'customer_id', 'operator' => '=' )
				)
			),
			array(
				'name' => 'cozmoz_customer2',
				'view' => 'cozmoz_customer',
				'key' => 'id',
			)
		);
		
		$this->im_emulator->def(
			$def
		);
		
		
		$this->im_emulator->parse( 'test/test', array() );
		
		
	}
}