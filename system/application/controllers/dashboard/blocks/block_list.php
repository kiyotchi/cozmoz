<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ブロック管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Block_list extends SZ_Controller
{
	public static $page_title = '一般ブロック管理';
	public static $description = '一般ブロックの追加や削除を行います。';
	public $msg = '';
	
	protected $block_dir = 'blocks/';
	protected $ext_block_dir;

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory_helper'));
		$this->load->model(array('permission_model', 'block_model'));
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		
		$this->ext_block_dir = SZ_EXT_PATH . $this->block_dir;
	}
	
	function index($method = '', $collection_name = '', $result = '')
	{
		$list = array();
		$installed_list = array();
		
		// pre index extension block directory
		$ext_dirs = directory_map($this->ext_block_dir, TRUE);
		// index core block directory
		$core_dirs = directory_map($this->block_dir, TRUE);
		
		// First, extension blocks detection
		if ($ext_dirs && is_array($ext_dirs))
		{
			foreach ($ext_dirs as $dir)
			{
				if (!is_dir($this->ext_block_dir . $dir))
				{
					continue;
				}
				$has = $this->block_model->is_already_installed($dir);
				if ($has)
				{
					$installed_list[$dir] = $has;
					continue;
				}
				
				// block module has necessary files?
				$path = $this->ext_block_dir . $dir . '/';
				if ($this->_is_allow_use_module($path, $dir) === TRUE)
				{
					$cname = ucfirst($dir) . '_block';
					$b = new $cname();
					$list[$dir] = array(
						'block_name' 		=> ($b->get_block_name()) ? $b->get_block_name() : $dir,
						'collection_name'	=> $dir
					);
				}
			}
		}
		
		// Second, plugin blocks detection
		$plugin = SeezooPluginManager::get_instance();
		$active_plugins = $plugin->get_installed_plugin_names();
		
		foreach ( $active_plugins as $plugin_name )
		{
			$plugin_prefix_dir = SZ_PLG_PATH . $plugin_name . '/' . $this->block_dir;
			$plg_dirs = directory_map($plugin_prefix_dir, TRUE);
			if ( $plg_dirs && is_array($plg_dirs) )
			{
				foreach ( $plg_dirs as $dir )
				{
					if (!is_dir($plugin_prefix_dir . $dir))
					{
						continue;
					}
					$has = $this->block_model->is_already_installed($dir);
					if ($has)
					{
						$installed_list[$dir] = $has;
						continue;
					}
					
					// block module has necessary files?
					$path = $plugin_prefix_dir . $dir . '/';
					if ($this->_is_allow_use_module($path, $dir) === TRUE)
					{
						$cname      = ucfirst($dir) . '_block';
						$b          = new $cname();
						$list[$dir] = array(
							'block_name'      => ($b->get_block_name()) ? $b->get_block_name() : $dir,
							'collection_name' => $dir
						);
					}
				}
			}
		}

		// Third, index core blocks
		foreach ($core_dirs as $dir)
		{
			if (!is_dir($this->block_dir . $dir) || isset($list[$dir]))
			{
				continue;
			}
			$has = $this->block_model->is_already_installed($dir);
			if ($has)
			{
				if ( ! isset($installed_list[$dir]))
				{
					$installed_list[$dir] = $has;
				}
				continue;
			}
			
			// block module has necessary files?
			$path = $this->block_dir . $dir . '/';
			if ($this->_is_allow_use_module($path, $dir) === TRUE)
			{
				$cname = ucfirst($dir) . '_block';
				$b = new $cname();
				$list[$dir] = array(
					'block_name' 		=> ($b->get_block_name()) ? $b->get_block_name() : $dir,
					'collection_name'	=> $dir
				);
			}
		}
		
		$data->list = $list;
		$data->installed_list = $installed_list;
		
		$data->ticket = $this->_set_ticket();
		
		// create display message if exists
		$this->_create_display_message($method, $collection_name, $result);
		
		$this->load->view('dashboard/blocks/block_list', $data);
	}
	
	/**
	 * ブロックのインストール
	 * @param $cname
	 * @param $token
	 */
	function install($cname, $token = FALSE)
	{
		$this->_check_ticket($token);
		
		$ret = $this->block_model->install_new_block($cname);
		
		if (!$ret)
		{
			$ret = 'error';
		}
		
		redirect('dashboard/blocks/block_list/index/install/' . $cname . '/' .strtolower($ret));
	}
	
	/**
	 * ブロック詳細表示
	 * @param $cid
	 */
	function detail($cid = FALSE)
	{
		if (!$cid)
		{
			$this->index();
			return;
		}
		
		$data->block = $this->block_model->get_collection_data((int)$cid);
		
		if ( ! $data->block )
		{
			redirect('dashboard/blocks/block_list');
		}
		$data->use_count = $this->block_model->get_use_collection_count((int)$cid);
		$data->ticket = $this->_set_ticket();
		
		$this->load->view('dashboard/blocks/detail', $data);
	}
	
	/**
	 *  ブロックデータ更新
	 */
	function update()
	{
		$cname = $this->input->post('col_name');
		$cid   = $this->input->post('col_id');
		$token = $this->input->post('ticket');
		
		$this->_check_ticket($token);
		
		if (!$cname)
		{
			$cname = '-';
			$ret = 'undefined';
		}
		else
		{
			$ret = $this->block_model->update_collection_data($cname, (int)$cid);
			if (!$ret)
			{
				$ret = 'error';
			}
		}
		redirect('dashboard/blocks/block_list/index/update/' . $cname . '/' . strtolower($ret));
	}
	
	/**
	 * ブロック削除
	 */
	function delete()
	{
		$cid = $this->input->post('col_id');
		$token = $this->input->post('ticket');
		
		$this->_check_ticket($token);
		
		if (!$cid)
		{
			$msg = 'undefined';
		}
		else
		{
			$ret = $this->block_model->delete_collection((int)$cid);
			
			if ($ret)
			{
				$msg = 'success';
			}
			else
			{
				$msg = 'error';
			}
		}
		
		redirect('dashboard/blocks/block_list/index/delete/-/' . $msg);
		
	}
	
	/**
	 * ブロックが利用可能な状態であるか判定
	 * @access private
	 * @param $path
	 * @param $dir
	 * @return bool
	 */
	function _is_allow_use_module($path, $dir)
	{
		// Does edit.php, add.php, edit.php [dirname].php, view.php exists?
		if (file_exists($path . 'edit.php')
				&& file_exists($path . 'add.php')
				&& file_exists($path . $dir . '.php')
				&& file_exists($path . 'view.php'))
		{
			// class exists?
			require_once($path . $dir . '.php');
			$cname = ucfirst($dir) . '_block';
			if (class_exists($cname))
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * トークンセット
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('ticket', $ticket);
		
		return $ticket;
	}
	
	/**
	 * トークンチェック
	 * @param $token
	 */
	function _check_ticket($token)
	{
		if (!$token || $token !== $this->session->userdata('ticket'))
		{
			exit('リロードはキャンセルされました。');
		}
	}
	
	/**
	 * セグメントデータからメッセージを生成
	 * @param string $method
	 * @param string $cname
	 * @param string $result
	 */
	function _create_display_message($method, $cname, $result)
	{
		if ($method == '' || $cname == '')
		{
			$this->msg = '';
			return;
		}
		
		if ($method == 'install')
		{
			switch($result)
			{
				case 'complete':
					$this->msg = 'ブロック「' . $cname . '」をインストールしました。';
					break;
				case 'not_enough':
					$this->msg = 'データベース構造が定義されていない、または不明なため、インストールできませんでした。';
					break;
				case 'no_table':
					$this->msg = 'データベースのテーブル名が定義されていないため、インストールできませんでした。';
					break;
				case 'table_exists':
					$this->msg = '定義されているデータベースのテーブルは既に使用されているため、インストールできませんでした。';
					break;
				case 'not_enough_column':
					$this->msg = '定義されたデータベース構造は条件を満たしていないため、インストールできませんでした。';
					break;
				case 'error':
					$this->msg = 'ブロックのインストールに失敗しました。';
					break;
				default:
					break;
			}
		}
		else if ($method == 'update')
		{
			switch($result)
			{
				case 'complete':
					$this->msg = 'ブロック「' . $cname . '」を更新しました。';
					break;
				case 'not_enough':
					$this->msg = 'データベース構造が定義されていない、または不明なため、更新できませんでした。';
					break;
				case 'not_enough':
					$this->msg = 'データベースのテーブル名が定義されていないため、更新できませんでした。';
					break;
				case 'not_enough_column':
					$this->msg = '定義されたデータベース構造は条件を満たしていないため、更新できませんでした。';
					break;
				case 'missed_modify':
					$this->msg = 'データベースの構造変更に失敗しました。';
					break;
				case 'undefined':
					$this->msg = '更新対象のブロックが指定されていません。';
					break;
				case 'error':
					$this->msg = '更新に失敗しました';
					break;
				default:
					break;
			}
		}
		else if ($method == 'delete')
		{
			switch($result)
			{
				case 'success':
					$this->msg = 'ブロックをアンインストールしました。';
					break;
				case 'error':
					$this->msg = 'ブロックのアンインストールに失敗しました。';
					break;
				case 'undefined':
					$this->msg = '削除対象のブロックが指定されていません。';
					break;
				default:
					break;
			}
		}
		else
		{
			$this->msg = '';
		}
	}	
}