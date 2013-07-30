<?php

class Plugin_list extends SZ_Controller
{
	public static $page_title  = 'プラグイン一覧';
	public static $description = 'プラグインの一覧を表示します。';
	public $msg = '';
	
	private $plugin;
	protected $table = 'sz_plugins';
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->plugin =& SeezooPluginManager::get_instance();
		
		$this->add_header_item(build_javascript('js/plugin_list.js'));
	}
	
	function index()
	{
		$data->installed_list     = $this->plugin->get_plugin_list();
		$not_installed_list       = array();
		$installed_plugins        = $this->plugin->get_installed_plugin_names();
		$indexed_list             = $this->plugin->plugin_index();
		
		// generate message
		$status = $this->session->flashdata('plg_status');
		if ( $status )
		{
			$this->msg = $this->plugin->get_message($status);
		}
		
		foreach ( $indexed_list as $name )
		{
			if ( in_array($name, $installed_plugins) )
			{
				continue;
			}
			$not_installed_list[] = $name;
		}

		$data->no_list = $this->load->view('dashboard/plugins/scaned_list', array('list' => $not_installed_list), TRUE);
		
		$this->load->view('dashboard/plugins/list', $data);
	}
	
	function get_plugin_detail($name, $token = FALSE)
	{
		$name      = kill_traversal($name);
		$detail    = $this->plugin->get_plugin_detail($name);
		
		if ( $detail === FALSE )
		{
			exit('<h3>プラグインの定義ファイルが見つかりませんでした。</h3>');
		}
		$this->load->view('dashboard/plugins/detail', $detail);
	}
	
	function install_plugin($handle = '')
	{
		$flag = TRUE;
		
		// handle validate
		
		// Is package handle empty?
		if ( ! $handle || ! in_array($handle, $this->plugin->plugin_index()) )
		{
			$status = 'PLUGIN_NOT_FOUND';
			$flag = FALSE;
		} 
		// Does handle plugin already installed?
		else if ( in_array($handle, $this->plugin->get_installed_plugin_names()) )
		{
			$status = 'PLUGIN_ALREADY_INSTALLED';
			$flag = FALSE;
		}
		
		// If flag equals TRUE, install execute
		if ( $flag === TRUE )
		{
			$ret = $this->plugin->install($handle);
			if ( $ret === TRUE )
			{
				$status = 'PLUGIN_INSTALL_SUCCESS';
			}
			else 
			{
				$status = $ret;
			}
		}
		
		// finally, redirect list page
		$this->session->set_flashdata('plg_status', $status);
		redirect('dashboard/plugins/plugin_list');
	}
	
	function delete($plugin_handle = '')
	{
		$result = $this->plugin->uninstall($plugin_handle);
		
		$this->session->set_flashdata('plg_status', $result);
		redirect('dashboard/plugins/plugin_list');
	}
	
	
}