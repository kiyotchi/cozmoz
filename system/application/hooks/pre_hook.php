<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Seezooフッククラス
 * 処理開始前に、POSTデータによるDB挿入やUPDATEを行う
 */

class PreHook
{
	protected $CI;
	protected $config_path;
	protected $ignore_controllers = array('page', 'flint', 'ajax', 'install','login', 'logout');

	/**
	 * コンストラクタ
	 *
	 * このクラスはCMSとして動かさない場合でもインスタンス化され、実行される。
	 * 基本的なチェックロジックはSZ_Controllerで行われているが、通常のCI_Controller継承のケースもあるので、
	 * ここでは改めてチェックロジックを走らせる。
	 */
	function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper(array('seezoo_install_helper'));
		$this->CI->load->database();

		/*
		 * ====================================================================
		 * if Controler class is not extended SZ_Controller,
		 * load depend model classes.
		 *  ====================================================================
		*/
		if (get_parent_class($this->CI) === 'Controller')
		{
			$this->CI->load->model(array('init_model', 'install_model', 'process_model'));
			$this->CI->view_mode = 'pc';
		}


		/*
		 * ====================================================================
		 * Check your site data exists.
		 * ====================================================================
		 */
		if ( ! isset($this->CI->site_data) )
		{
			SeezooOptions::init('common');
			$this->CI->site_data = SeezooOptions::get('site_info');//$this->CI->init_model->get_site_info();
		}

		/*
		 * ====================================================================
		 * set seezoo session configure
		 * if seezoo is not installed, session is not use database.
		 * ====================================================================
		 */
		$config_path = APPPATH . 'config';
		if ( ! $this->CI->install_model->check_is_installed($config_path) )
		{
			$this->CI->config->set_item('sess_use_database', FALSE);
		}
		// else, index_page config detect
		else
		{
			$this->_set_rewrited_index_page();
		}


		/*
		 * ====================================================================
		 * if request header have 'X-Requested-With' from JavaScript,
		 * set sess_time_to_update enougth long.
		 *  ====================================================================
		 */
		if ($this->CI->input->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest')
		{
			$this->CI->config->set_item('sess_time_to_update', 7200);
		}

		// load the session class
		$this->CI->load->library('session');

		$this->user_id = ($this->CI->session->userdata('user_id'))
		                  ? (int)$this->CI->session->userdata('user_id')
		                    : 0;


		/*
		 * ====================================================================
		 * define some Constant
		 *  ====================================================================
		 */
		// define site title
		if ( is_object($this->CI->site_data))
		{
			define('SITE_TITLE', $this->CI->site_data->site_title);
		}
//		// define log_level
//		if ( is_object($this->CI->site_data)
//				&& isset($this->CI->site_data->log_level))
//		{
//			define('SZ_LOGGING_LEVEL', (int)$this->CI->site_data->log_level);
//		}
//		else
//		{
//			define('SZ_LOGGING_LEVEL', 2);
//		}


		/*
		 * ====================================================================
		 * detect output mode
		 * ====================================================================
		 */
		$this->_set_output_mode();


		/*
		 * ====================================================================
		 * SSL settings
		 * ====================================================================
		 */
		// if opened port number eq 443, change SSL mode
		$ssl = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? TRUE : FALSE;

		// consider upgrade
		if ( is_object($this->CI->site_data)
				&& isset($this->CI->site_data->ssl_base_url)
				&& !empty($this->CI->site_data->ssl_base_url))
		{
			$ssl_url = $this->CI->site_data->ssl_base_url;
		}
		else
		{
			//$ssl_url = preg_replace('/^http:/', 'https:', $this->CI->config->item('base_url'));
			$ssl_url = $this->CI->config->item('base_url');
		}
		// ssl settings write to config
		$this->CI->config->set_item('ssl_mode', $ssl);
		$this->CI->config->set_item('ssl_base_url', $ssl_url);



		// ================ pre-process is end. execute seezoo Controller!! ==========
	}

	// check server uses mod_rewrite
	function _set_rewrited_index_page()
	{
		//$index_page = ($this->CI->init_model->check_enable_mod_rewrite()) ? '' : 'index.php';
		$index_page = ($this->CI->site_data->enable_mod_rewrite > 0) ? '' : 'index.php';
		$this->CI->config->set_item('index_page', $index_page);
	}

	function _set_output_mode()
	{
		$mode = 'pc';
		$mb   = ( isset($this->CI->site_data->enable_mobile) )
		          ? (int)$this->CI->site_data->enable_mobile
		           : 0;
		$sp   = ( isset($this->CI->site_data->enable_smartphone) )
		          ? (int)$this->CI->site_data->enable_smartphone
		          : 0;

		// create instance and judge carrier
		$mobile   = Mobile::get_instance();
		$viewmode = $this->CI->view_mode;

		if ( $sp > 0 && ($mobile->is_smartphone() || $viewmode === 'sp') )
		{
			$mode = 'sp';
		}
		else if ( $mb >0 && ($mobile->is_mobile() || $viewmode === 'mb') )
		{
			$mode = 'mb';
		}

		// set to config
		$this->CI->config->set_item('final_output_mode', $mode);
		// Y.Paku キャリア指定
		$this->CI->config->set_item('final_output_carrier',$mobile->carrier());

		// and define constant
		define('SZ_OUTPUT_MODE', $mode);
	}

	/**
	 * hook process method
	 * @note this process works some important DB tables!
	 *            So, we check some parameters.
	 * @return void
	 */
	function process()
	{
			// is not logged in, stop process
		if ($this->CI->process_model->is_login() === FALSE)
		{
			return;
		}

		// request mthod is not POST, stop process
		if ( ! isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return;
		}

		// if no referer OR POST referer is no match, stop process
		if ( ! isset($_SERVER['HTTP_REFERER'])
				||strpos($_SERVER['HTTP_REFERER'], $this->CI->config->slash_item('base_url')) === FALSE
				&& strpos($_SERVER['HTTP_REFERER'], $this->CI->config->slash_item('ssl_base_url')) === FALSE)
		{
			return;
		}

		// process parameter is not posted, stop process
		if (!$this->CI->input->post('process'))
		{
			return;
		}

		// if these check has cleared, start process!

		$pr = $this->CI->input->post('process');

		// do method control from posted 'process' paramter.
		if ($pr === 'block_add')
		{
			$this->_block_add();
		}
		else if ($pr === 'block_edit')
		{
			$this->_block_edit();
		}
		else if ($pr === 'page_add')
		{
			$this->_page_add();
		}
		else if ($pr === 'page_edit_config')
		{
			$this->_page_add(TRUE);
		}
		else if ($pr === 'external_page_add')
		{
			$this->_page_add_external();
		}
		else if ($pr === 'external_page_edit')
		{
			$this->_page_add_external(TRUE);
		}
		else if ($pr === 'edit_out')
		{
			$this->_edit_out();
		}
		else if ($pr === 'block_permission')
		{
			$this->_block_permission();
		}
	}

	function _block_add()
	{
		$this->_token_check();
		$pid = (int)$this->CI->input->post('page_id');
		$area = $this->CI->input->post('area');
		$col_id = (int)$this->CI->input->post('col_id');
		$bid = (int)$this->CI->input->post('block_id');
		$vid = (int)$this->CI->input->post('version_number');

		// check request user is edit mode user?
		if (!$this->CI->init_model->is_edit_mode($pid, $this->user_id))
		{
			return;
		}

		// Does post parameters enough?
		if ( ! $pid || ! $area || ! $col_id || ! $vid )
		{
			exit('System Error!');
		}
		$collection = $this->CI->process_model->get_collection_master($col_id);
		$area_id = $this->CI->process_model->get_area_id($area, $pid, $vid);

		if ( ! empty($collection->db_table) && $area_id )
		{
			$ret = $this->CI->process_model->insert_block_data($area_id, $collection, $bid);
			if ( $ret === TRUE )
			{
				$this->CI->process_model->insert_area_data($bid, $collection->collection_name, $area_id, $vid);
			}
			else
			{
				$this->CI->session->set_flashdata('_sz_block_error', 1);
			}
		}
		else
		{
			$this->CI->session->set_flashdata('_sz_block_error', 1);
			// no use database [this process no works...]
			$this->CI->process_model->insert_area_data($bid, $collection->collection_name, $area_id, $vid);
		}













		$this->_redirect_path((int)$pid);
	}

	function _block_edit()
	{
		$this->_token_check();
		$bid = (int)$this->CI->input->post('block_id');
		$slave_id = (int)$this->CI->input->post('slave_block_id');
		$pid = (int)$this->CI->input->post('page_id');
		$cname = $this->CI->input->post('collection_name');

		if (!$this->CI->init_model->is_edit_mode($pid, $this->user_id))
		{
			return;
		}

		if (!$bid || !$pid || !$cname)
		{
			exit('System Error!');
		}

		if ($cname && $bid)
		{
			$block = $this->CI->load->block($cname, $bid, TRUE);
			$block->bid = $bid;
			$block->slave_block_id = $slave_id;
//			if ( $block->slave_block_id > 0 )
//			{
//				$ret = $this->CI->process_model->update_block_data_from_slave($block);
//			}
//			else
//			{
				$ret = $this->CI->process_model->update_block_data($block, $pid);
//			}
			if ( $ret === FALSE )
			{
				$this->CI->session->set_flashdata('_sz_block_error', 1);
			}
			$this->_redirect_path((int)$pid);
		}
		else
		{
			exit('System Error!');
		}
	}

	function _page_add($is_update = FALSE)
	{
		$this->_token_check();

		$this->_validation_page_add();
		$this->CI->form_validation->keep_default_post();

		if ($this->CI->form_validation->run() === FALSE)
		{
			echo $this->CI->form_validation->error_string();
			exit('System Error!');
		}
		{
		// when from_po data is posted, requested by page_list dashboard.
		if ((int)$this->CI->input->post('from_po') === 1)
		{
			$from_dashboard = TRUE;
		}
		else
		{
			$from_dashboard = FALSE;
		}

		$post = array(
			'page_title'		=> set_value('page_title'),
			'meta_title'		=> set_value('meta_title'),
			'meta_keyword'		=> set_value('meta_keyword'),
			'meta_description'	=> set_value('meta_description'),
			'template_id'		=> ($this->CI->input->post('template_id')) ? (int)$this->CI->input->post('template_id') : 0,
			'navigation_show'	=> ($this->CI->input->post('navigation_show')) ? (int)$this->CI->input->post('navigation_show') : 0,
			'parent'			=> $this->CI->input->post('page_id'),
			'public_datetime'	=> $this->_format_public_datetime(),
			'is_ssl_page'		=> ($this->CI->input->post('is_ssl_page')) ? (int)$this->CI->input->post('is_ssl_page') : 0,
			'is_mobile_only'   => (int)$this->CI->input->post('is_mobile_only'),
			'target_blank'     => ( $this->CI->input->post('target_blank') ) ? 1 : 0 			
		);

			$pid = $this->CI->input->post('page_id');

			// format array to string permission data
			$access_permission = $this->CI->input->post('permission');
			if (is_array($access_permission))
			{
				$perms['allow_access_user'] = ':' . implode(':', $access_permission) . ':';
			}
			else
			{
				$perms['allow_access_user'] = '';
			}

			$edit_permission = $this->CI->input->post('permission_edit');
			if (is_array($edit_permission))
			{
				$perms['allow_edit_user'] = ':' . implode(':', $edit_permission) . ':';
			}
			else
			{
				$perms['allow_edit_user'] = '';
			}

			$approve_permission = $this->CI->input->post('permission_approve');
			if (is_array($approve_permission))
			{
				$perms['allow_approve_user'] = ':' . implode(':', $approve_permission) . ':';
			}
			else
			{
				$perms['allow_approve_user'] = '';
			}

			// Is page editting and requests from dashboard?
			$this->CI->load->model(array('ajax_model'));
			$page_state = $this->CI->ajax_model->get_is_page_editting($pid);

			if ($from_dashboard && $page_state->is_editting)
			{
				$this->_error('editting');
			}

			// is update?
			if ($is_update === TRUE)
			{
				// when update, create pageversion.
				unset($post['parent']);
				$res = $this->CI->process_model->update_page($post, $pid, $this->CI->input->post('version_number'), $from_dashboard);
			}
			else
			{
				// else, simply create page and set version 1.
				$res = $this->CI->process_model->create_page($post);
			}

			if ($res)
			{
				// if create or update page is succeed, try update or insert page path
				$page_path = set_value('page_path');
				if ($page_path != '')
				{
					$data = array('page_path' => $page_path);

					if ($is_update === TRUE)
					{
						$res2 = $this->CI->process_model->update_page_path($data, (int)$this->CI->input->post('page_path_id'));
					}
					else
					{
						$data['page_id'] = $res;
						$res2 = $this->CI->process_model->insert_page_path($data, $post['parent']);
					}
				}

				// try update or insert page_permissions
				if ($is_update === TRUE)
				{
					$this->CI->process_model->update_page_permissions($perms, $pid);
				}
				else
				{
					$perms['page_id'] = $res;
					$this->CI->process_model->insert_page_permissions($perms);
				}

				// if requested by dashboard, return JSON object string.
				if ($from_dashboard === TRUE)
				{
					// requested by ajax page_operator
					echo json_encode(array('page_title' => $post['page_title'],'page_id' => $this->CI->input->post('page_id')));
					exit;
				}

				$post['page_id'] = $res;
				$post['page_path'] = $page_path;
				$post['version_number'] = ($is_update === TRUE)
																				? $this->CI->input->post('version_number')
																				: 1;

				// else, redirect target page;
				$this->_redirect_path((int)$pid);
			}
			// update or create page missed...
			else
			{
				if ($is_update === TRUE)
				{
					exit('ページ編集に失敗しました。');
				}
				else
				{
					exit('ページ追加に失敗しました。');
				}
			}
		}
	}

	function _page_add_external($is_update = FALSE)
	{
		$this->_token_check();
		
		// when from_po data is posted, requested by page_list dashboard.
		if ( (int)$this->CI->input->post('from_po') === 0)
		{
			exit('System error!');
		}
		
		$post = array(
			'page_title'      => $this->CI->input->post('page_title'),
			'external_link'   => $this->CI->input->post('external_link'),
			'parent'          => $this->CI->input->post('page_id'),
			'navigation_show' => ( $this->CI->input->post('navigation_show') ) ? 1 : 0,
			'target_blank'    => ( $this->CI->input->post('target_blank') ) ? 1 : 0,
			'is_public'       => 1
		);
		
		$pid = $this->CI->input->post('page_id');

//		// format array to string permission data
//		$access_permission = $this->CI->input->post('permission');
//		if ( is_array($access_permission) )
//		{
//			$perms['allow_access_user'] = ':' . implode(':', $access_permission) . ':';
//		}
//		else
//		{
//			$perms['allow_access_user'] = '';
//		}
//
//		$edit_permission = $this->CI->input->post('permission_edit');
//		if ( is_array($edit_permission) )
//		{
//			$perms['allow_edit_user'] = ':' . implode(':', $edit_permission) . ':';
//		}
//		else
//		{
//			$perms['allow_edit_user'] = '';
//		}
//
//		$approve_permission = $this->CI->input->post('permission_approve');
//		if ( is_array($approve_permission) )
//		{
//			$perms['allow_approve_user'] = ':' . implode(':', $approve_permission) . ':';
//		}
//		else
//		{
//			$perms['allow_approve_user'] = '';
//		}

		// Is page editting and requests from dashboard?
		$this->CI->load->model(array('ajax_model'));
		$page_state = $this->CI->ajax_model->get_is_page_editting($pid);

		if ($page_state->is_editting)
		{
			$this->_error('editting');
		}

		// is update?
		if ( $is_update === TRUE )
		{
			// when update, create pageversion.
			unset($post['parent']);
			$res = $this->CI->process_model->update_page($post, $pid, 1, TRUE);
		}
		else
		{
			// else, simply create page and set version 1.
			$res = $this->CI->process_model->create_page($post);
		}

		if ( $res )
		{
			// if create or update page is succeed, try update or insert page path
			$page_path = $post['external_link'];
			if ($page_path != '')
			{
				$data = array('page_path' => $page_path);

				if ( $is_update === TRUE )
				{
					$res2 = $this->CI->process_model->update_page_path($data, (int)$this->CI->input->post('page_path_id'), TRUE);
				}
				else
				{
					$data['page_id'] = $res;
					$res2 = $this->CI->process_model->insert_page_path($data, $post['parent'], TRUE);
				}
			}
//
//			// try update or insert page_permissions
//			if ( $is_update === TRUE )
//			{
//				$this->CI->process_model->update_page_permissions($perms, $pid);
//			}
//			else
//			{
//				$perms['page_id'] = $res;
//				$this->CI->process_model->insert_page_permissions($perms);
//			}

			// if requested by dashboard, return JSON object string.
			// requested by ajax page_operator
			echo json_encode(
							array(
								'page_title' => $post['page_title'],
								'page_id'    => $pid
							)
						);
			exit;
		}
		// update or create page missed...
		else
		{
			if ( $is_update === TRUE )
			{
				exit('ページ編集に失敗しました。');
			}
			else
			{
				exit('ページ追加に失敗しました。');
			}
		}
	}

	function _validation_page_add()
	{
		$this->CI->load->library('form_validation');

		$conf = array(
			array(
				'field'	=> 'page_title',
				'rules'	=> 'trim|required|max_length[255]',
				'label'	=> 'ページタイトル'
			),
			array(
				'field'	=> 'page_path',
				'rules'	=> 'trim|required|max_length[255]',
				'label'	=> 'ページパス'
			),
			array(
				'field'	=> 'meta_title',
				'rules'	=> 'trim|max_length[255]',
				'label'	=> 'メタタグタイトル'
			),
			array(
				'field'	=> 'meta_keyword',
				'rules'	=> 'trim|max_length[255]',
				'label'	=> 'メタキーワード'
			),
			array(
				'field'	=> 'meta_description',
				'rules'	=> 'trim|max_length[1000]',
				'label'	=> 'メタ概要ワード'
			),
			array(
				'field'	=> 'public_ymd',
				'rules'	=> 'trim|max_length[10]',
				'label'	=> '公開日付'
			),
			array(
				'field'	=> 'public_time',
				'rules'	=> 'trim|max_length[2]|numeric',
				'label'	=> '公開日時（時間）'
			),
			array(
				'field'	=> 'public_minute',
				'rules'	=> 'trim|max_length[2]|numeric',
				'label'	=> '公開日時（分）'
			)
		);

		$this->CI->form_validation->set_rules($conf);
	}

	function _format_public_datetime()
	{
		$format = '%s %s:%s:00';
		return sprintf($format, set_value('public_ymd'), set_value('public_time'), set_value('public_minute'));
	}

	function _edit_out()
	{
		$ticket = $this->CI->input->post('ticket');
		if (!$ticket || $ticket !== $this->CI->session->flashdata('sz_page_onetime'))
		{
			exit('チケットが不正です。');
		}

		// edit out mode is destroy or scrap or approve only
		if ($this->CI->input->post('destroy'))
		{
			$mode = 'destroy';
		}
		else if ($this->CI->input->post('scrap'))
		{
			$mode = 'scrap';
		}
		else if ($this->CI->input->post('approve'))
		{
			$mode = 'approve';
		}
		else
		{
			exit('不正な処理です。');
		}

		$pid = $this->CI->input->post('pid');

		$this->CI->load->model('version_model');

		if ($mode === 'scrap' || $mode === 'approve')
		{
			$this->CI->version_model->create_version($pid, $mode, $this->user_id);

			// if out mode is approve, delete cache
			if ($mode === 'approve')
			{
				$this->CI->version_model->delete_approve_page_cache($pid);
			}
			// else if mode is scrap and approve regist, set approve status data
			else if ($mode === 'scrap' && (int)$this->CI->input->post('approval_regist') > 0)
			{
				$comment = $this->CI->input->post('approve_comment');
				$is_mail = (int)$this->CI->input->post('is_recieve_mail');
				$this->CI->process_model->do_approve_order($pid, $comment, $is_mail);
			}
		}
		else if ($mode === 'destroy')
		{
			// version destroy
			$this->CI->version_model->delete_pending_data($pid, $this->user_id);
		}
		$this->CI->version_model->edit_out($pid);
		redirect(get_base_link() . $pid);
	}

	function _block_permission()
	{
		// allow_view
		$views = $this->CI->input->post('block_permission');

		// allow_edit
		$edits = $this->CI->input->post('block_permission_edit');
		$mobiles = $this->CI->input->post('mobile_permission');

		$bid = (int)$this->CI->input->post('block_id');
		$pid = $this->CI->input->post('page_id');

		// format data
		$data = array(
			'allow_view_id'	=> (is_array($views)) ? ':' . implode(':', $views) . ':' : ':1:',
			'allow_edit_id'	=> (is_array($edits)) ? ':' . implode(':', $edits) . ':' : ':1:',
			'allow_mobile_carrier' => ( is_array($mobiles) ) ? ':' . implode(':', $mobiles) . ':' : ''		
		);

		$this->CI->load->model('permission_model');
		$ret = $this->CI->permission_model->update_block_permission($data, $bid);

		if ($ret)
		{
			redirect($pid);
		}
		else
		{
			exit('処理中にエラーが発生しました。');
		}

	}

	function _token_check()
	{
		$ticket = $this->CI->input->post('sz_token');
		if (!$ticket || $ticket !== $this->CI->session->userdata('sz_token'))
		{
			exit('不正なチケットが送信されました。');
		}
	}

	function _redirect_path($pid)
	{
		$page = $this->CI->init_model->get_page_path_and_system_page($pid);
		if ($page && (int)$page->is_system_page > 0)
		{
			redirect($page->page_path);
		}
		redirect(get_base_link() . $pid);
	}

	function _error($msg = '')
	{
		exit($msg);
	}
}
