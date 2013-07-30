<?php
/**
 * Seezoo Jr dashboard Pseudo-variable assign Class
 */

class Pseudo_variables extends SZ_Controller
{
	public static $page_title = '静的変数一覧';
	public static $description = 'テンプレートで使用する変数を設定できます。';
	
	function __construct()
	{
		parent::SZ_Controller();
		
		$this->load->helper('directory');
		$this->load->model(array('dashboard_model', 'page_model'));
	}
	
	function index()
	{
		$statics = simplexml_load_file(APPPATH . '/libraries/statics/statics.xml');
		
		foreach ($statics as $v) 
		{
			$variables[(string)$v->name] = object_to_array($v);
		}
		
		$data->variables = $variables;
		
		$this->load->view('dashboard/pages/static_vars', $data);
	}
	
}