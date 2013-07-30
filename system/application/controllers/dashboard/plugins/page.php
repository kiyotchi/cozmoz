<?php

class Page extends SZ_Controller
{
	public static $page_title = 'プラグイン管理';
	public static $description = 'プラグインを管理します。';
	
	function __construct()
	{
		parent::SZ_Controller();
		
		redirect('dashboard/plugins/plugin_list');
	}
}