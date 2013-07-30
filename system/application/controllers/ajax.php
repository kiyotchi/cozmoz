<?php
/**
 * ===============================================================================
 *
 * Seezoo Ajaxコントローラ
 *
 * Ajax応答用コントローラ
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */
class Ajax extends SZ_Controller
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller(FALSE);

		// this class works ajax request only.
		// check Request Header by Flint.js posted X-Requsted-With
		if (!$this->_is_ajax_request())
		{
			exit('access_denied.');
		}

		$this->load->model(array('ajax_model'));
		$this->load->helper('ajax_helper');
		$this->user_id = ($this->session->userdata('user_id')) ? (int)$this->session->userdata('user_id') : 0;
		$this->member_id = ($this->session->userdata('member_id')) ? (int)$this->session->userdata('member_id') : 0;
		$this->is_ssl_page = FALSE;
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . APPPATH . 'libraries/');
	}

	/**
	 * ヘッダ情報からAjaxリクエストであるかどうかを判定
	 * @access private
	 */
	function _is_ajax_request()
	{
		// if User Agent is IE6, also, same value of XMLHttpRequest created by Flint.js.
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? TRUE : FALSE;
	}

	/**
	 * ファイルグループリスト取得
	 * @access public
	 * @param string $token
	 */
	function get_file_group_list($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$this->load->model('file_model');

		$data->groups = $this->file_model->get_file_groups_all();
		$data->vals = $this->input->post('vals');
		$data->file_group = $this->file_model->get_file_group($data->vals);

		$this->load->view('elements/file_group_list', $data);
	}

	/**
	 * ファイルグループセット
	 * @access public
	 * @param string $token
	 */
	function set_file_group($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$files = $this->input->post('files');
		$name = $this->input->post('group_name');
		$gr = $this->input->post('file_group');

		$this->load->model('file_model');

		if ($name)
		{
			$nid = $this->file_model->insert_new_file_group(array('group_name' => $name, 'created_date' => db_datetime()));
			if ($nid)
			{
				$gr[] = $nid;
			}
		}

		$ret = $this->file_model->set_file_group($gr, $files);

		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * 編集モード終了画面出力
	 * @access public
	 * @param int $pid
	 * @param string $token
	 */
	function page_out($pid, $token)
	{
		$this->_token_check('sz_token', $token);
		$data->pid = (int)$pid;

		$this->load->model(array('init_model', 'permission_model', 'dashboard_model'));
		$ps = $this->init_model->get_page_state($data->pid);
		$data->version = $ps['version_number'];

		$permission = $this->permission_model->get_permission_data($pid);
		$user = $this->dashboard_model->get_user_one($this->session->userdata('user_id'));

		if ($user->user_id == 1 || $user->admin_flag > 0)
		{
			$approve = TRUE;
		}
		else if ($user
					&& $permission
					&& strpos($permission->allow_approve_user, ':' . $user->user_id . ':') !== FALSE)
		{
			$approve = TRUE;
		}
		else if ( $this->session->userdata('member_id') > 0
					&& strpos($permission->allow_approve_user, ':m:') !== FALSE)
		{
			$approve = TRUE;
		}
		else
		{
			$approve = FALSE;
			$data->approval_users = TRUE;
		}
		$data->can_approve = $approve;

		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_flashdata('sz_page_onetime', $ticket);
		$data->ticket = $ticket;

		$this->load->view('parts/page_out', $data);
	}

	/**
	 * 追加できるブロックリスト出力
	 * @access public
	 * @param string $mode
	 * @param string $area
	 * @param string $token
	 */
	function add_block($mode, $area, $pid, $token)
	{
		$this->page_id = $pid;
		$this->_token_check('sz_token', $token);
		// edit mode is view mode.
		$this->edit_mode = 'NO_EDIT';
		$this->load->model('page_model');
		
		$page = $this->page_model->get_page_object($pid, 'editting');
		$output = $this->config->item('final_output_mode');
		$this->version_number = $page['version_number'];
		
		if ($mode == 'block_list')
		{
			$data->block_list    = $this->ajax_model->get_block_list($output);
			$data->draft         = $this->ajax_model->get_draft_blocks($output);
			$data->static_blocks = $this->ajax_model->get_static_blocks($output);
			$data->block_set     = $this->ajax_model->get_block_set();
			
			$this->load->view('parts/add_block_list', $data);
		}
	}

	/**
	 * block_custom_templates : カスタムテンプレートリスト取得
	 * @access public
	 * @param string $cname
	 * @param string $token
	 */
	function block_custom_templates($cname, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$data->list = $this->ajax_model->get_custom_template_list($cname);

		$this->load->view('parts/custom_template_list', $data);
	}

	/**
	 * set_custom_template : カスタムテンプレートセット
	 * @access public
	 * @param string $token
	 */
	function set_custom_template($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$bid = (int)$this->input->post('block_id');
		$handle = $this->input->post('handle');
		
		// tmp set page_id
		$this->page_id = (int)$this->input->post('page_id');

		$ret = $this->ajax_model->set_block_custom_template($bid, $handle);

		if ($ret)
		{
			$block = $this->load->block($ret->collection_name, $ret->block_id, TRUE);
			if ($handle == '')
			{
				//$path = $ret->collection_name . '/view';
				$path = 'view';
			}
			else
			{
				//$path = $ret->collection_name . '/templates/' . $handle . '/view';
				$path = 'templates/' . $handle . '/view';
			}
			
			echo json_encode(array(
				'result' => 'complete',
				'data'		=> $this->load->block_view($path, array('controller' => $block), TRUE)
				//'data'   => $block->load_view($path, array('controller' => $block), TRUE)
			));
		}
		else
		{
			echo json_encode(array(
				'result' => 'error',
				'data'		=> ''
			));
		}
	}


	/**
	 * ブロックの表示データ更新
	 * @access public
	 * @param $bid
	 * @param $cname
	 * @param $token
	 */
	function refresh_block($bid, $cname, $pid, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$view_path = $this->ajax_model->get_block_view_path($bid, $cname);
		$block = $this->load->block($cname, $bid, TRUE);
		$this->page_id = $pid;

		$this->load->block_view($view_path, array('controller' => $block));
		//$block->load_view($view_path, array('controller' => $block));
	}

	/**
	 * ブロックデータ入力ページ出力
	 * @access public
	 * @param $id
	 * @param $page_id
	 * @param $area_value
	 * @param $token_value
	 */
	function set_block($id, $page_id, $area_value, $token_value)
	{
		$this->_token_check('sz_token', $token_value);

		// get version
		$this->load->model('init_model');
		$ps  = $this->init_model->get_page_state($page_id);
		$vid = $ps['version_number'];
		// エリア日本語対応のため、デコード
		$area_value = rawurldecode($area_value);

		// load block
		$block_name = $this->ajax_model->get_block_path($id);
		$block      = $this->load->block($block_name, FALSE, TRUE);
		
		// output buffer
		$buff = form_open('page/add_block/', array('id' => 'sz-blockform'));

		// make view data
		$data->contents       = $this->load->block_view($block_name . '/add', array('controller' => $block), TRUE);
		//$data->contents       = $block->load_view('add', array('controller' => $block), TRUE);
		$data->token_name     = 'sz_token';
		$data->token          = $token_value;
		$data->area_name      = $area_value;
		$data->page_id        = $page_id;
		$data->collection_id  = $id;
		$data->version_number = $vid;
		$data->block_id       = $block->block_id;

		$buff .= $this->load->view('parts/submit_form', $data, TRUE);

		$js_path  = 'blocks/' . $block_name . '/append.js';
		$css_path = 'blocks/' . $block_name . '/append.css';
		
		// is add or edit javascript exsits?
		if (file_exists(SZ_EXT_PATH . $js_path))
		{
			$js = SZ_EXT_DIR . $js_path;
		}
		else if (file_exists(FCPATH . $js_path))
		{
			$js = $js_path;
		}
		else
		{
			$js = FALSE;
		}

		// is add or edit css exists?
		if (file_exists(SZ_EXT_PATH . $css_path))
		{
			$css = SZ_EXT_DIR . $css_path;
		}
		else if (file_exists(FCPATH . $css_path))
		{
			$css = $css_path;
		}
		else
		{
			$css = FALSE;
		}

		echo json_encode(array('content' => $buff, 'js' => $js, 'css' => $css));
	}

	/**
	 * ブロック編集データ出力
	 * @access public
	 * @param $id
	 * @param $page_id
	 * @param $token
	 */
	function edit_block($id, $page_id, $token)
	{
		$this->_token_check('sz_token', $token);

		$block_name = $this->ajax_model->get_block_path_from_block_id($id);
		
		// Does edit target block aliased?
		$slave_id = $this->ajax_model->check_slaved_block($id);

		$block = $this->load->block($block_name, ( $slave_id > 0 ) ? $slave_id : $id, TRUE, TRUE);

		// output buffer
		$buff = form_open('page/add_block/', array('id' => 'sz-blockform'));

		$data->contents = $this->load->block_view($block_name . '/edit', array('controller' => $block), TRUE);
		//$data->contents        = $block->load_view('edit', array('controller' => $block), TRUE);
		$data->token_name      = 'sz_token';
		$data->token           = $token;
		$data->page_id         = $page_id;
		$data->block_id        = $id;
		$data->slave_id        = $slave_id;
		$data->collection_name = $block_name;

		$buff .= $this->load->view('parts/submit_form_edit', $data, true);
		$buff .= form_close();

		$js_path = 'blocks/' . $block_name . '/append.js';
		$css_path = 'blocks/' . $block_name . '/append.css';
		
		// is add or edit javascript exsits?
		if (file_exists(SZ_EXT_PATH . $js_path))
		{
			$js = SZ_EXT_DIR . $js_path;
		}
		else if (file_exists(FCPATH . $js_path))
		{
			$js = $js_path;
		}
		else
		{
			$js = FALSE;
		}

		// is add or edit css exists?
		if (file_exists(SZ_EXT_PATH . $css_path))
		{
			$css = SZ_EXT_DIR . $css_path;
		}
		else if (file_exists(FCPATH . $css_path))
		{
			$css = $css_path;
		}
		else
		{
			$css = FALSE;
		}

		echo json_encode(array('content' => $buff, 'js' => $js, 'css' => $css));
	}

	/**
	 * ブロック削除
	 * @access public
	 * @param int $id
	 * @param string $type
	 * @param string $token
	 */
	function delete_block($id, $type, $token) {
		$this->_token_check('sz_token', $token);

		// deleteだが、バージョン管理のため、activeフラグを下げる処理のみ
		$ret = $this->ajax_model->delete_block($id);

		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * ブロック削除取り消し
	 * @access public
	 * @param string $token
	 */
	function rollback_delete_block($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$block_id = (int)$this->input->post('block_id');

		// 下げたactiveフラグを元に戻す処理
		$ret = $this->ajax_model->rollback_delete_block_data($block_id);

		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * POSTデータに基づきブロックの並び順をソート
	 * @access public
	 * @param $id
	 * @param $token
	 */
	function do_arrange($id, $token) {

		$this->_token_check('sz_token', $token);

		$this->session->keep_flashdata('sz_page_onetime');

		$data = array();
		foreach ($_POST as $key => $val)
		{
			// 数値のPOSTデータのみ選定
			if (intval($key) === 0)
			{
				continue;
			}
			// format : [area_id:block_id]
			$data[$key] = explode(':', $val);
		}

		$ret = $this->ajax_model->update_display_order($data, intval($id));

		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * 新規ページ作成フォーム出力
	 * @access public
	 * @param $pid
	 * @param $token
	 */
	function add_page($pid, $token)
	{
		if (!$pid || !ctype_digit($pid))
		{
			echo 'access denied';
		}

		$this->_token_check('sz_token', $token);

		// load page create data
		$data->page_id = intval($pid);
		$data->token = $token;
		$data->default_template_id = $this->ajax_model->get_default_template_id();
		$data->templates = $this->ajax_model->get_template_list();
		$data->parent_path = $this->ajax_model->get_current_page_path($pid);
		$data->permission_list = $this->ajax_model->get_user_list();
		
		$this->load->view('parts/add_page_form', $data);
	}
	
	/**
	 * 外部リンク作成フォーム出力
	 * @access public
	 * @param $pid
	 * @param $token
	 */
	function add_external_link($pid, $token)
	{
		if (!$pid || !ctype_digit($pid))
		{
			echo 'access denied';
		}

		$this->_token_check('sz_token', $token);

		// load page create data
		$data->page_id             = intval($pid);
		$data->token               = $token;
		$data->templates           = $this->ajax_model->get_template_list();
		$data->parent_path         = $this->ajax_model->get_current_page_path($pid);
		$data->permission_list     = $this->ajax_model->get_user_list();
		
		//$this->load->view('parts/add_page_form', $data);
		$this->load->view('parts/add_external_link', $data);
	}

	/**
	 * ページ設定変更用フォーム出力
	 * @access public
	 * @param $pid
	 * @param $token
	 */
	function page_config($pid, $token)
	{
		if (!$pid || !ctype_digit($pid))
		{
			echo 'access_denied';
		}

		$this->_token_check('sz_token', $token);

		$this->load->model('init_model');

		$data->page_id = intval($pid);
		$data->token = $token;

		$page_state = $this->init_model->get_page_state($pid);

		// 最新バージョン取得
		$data->version_number = $page_state['version_number'];
		$data->templates = $this->ajax_model->get_template_list();
		$data->page = $this->ajax_model->get_current_edit_page_data($pid, $data->version_number);
		$data->user_list = $this->ajax_model->get_user_list();
		
		$top_path = $this->ajax_model->get_top_page_path();
		$data->page->page_path = preg_replace('|^' . $top_path . '|', '', $data->page->page_path);
		
		$this->load->view('parts/edit_page_form', $data);
	}

	/**
	 * 管理画面からの変更処理リクエストキャッチ
	 * @access public
	 * @param int $pid
	 * @param string $token
	 */
	function page_config_from_operator($pid, $token)
	{
		if (!$pid || !ctype_digit($pid))
		{
			echo 'access_denied';
		}

		$this->_token_check('sz_token', $token);

		$this->load->helper('ajax_helper');
		$this->load->model('sitemap_model');

		$data->page_id = intval($pid);
		$data->token = $token;
		$data->templates = $this->ajax_model->get_template_list();
		$data->page = $this->sitemap_model->get_current_approved_version($pid);
		$data->version_number = $data->page->version_number;
		$data->user_list = $this->ajax_model->get_user_list();

		$top_path = $this->ajax_model->get_top_page_path();
		$data->page->page_path = preg_replace('|^' . preg_quote($top_path) . '|', '', $data->page->page_path);
		
		$this->load->view('parts/edit_page_form', $data);
	}
	
	/**
	 * 管理画面からの変更処理リクエストキャッチ（外部リンク用）
	 * @access public
	 * @param int $pid
	 * @param string $token
	 */
	function external_page_config_from_operator($pid, $token)
	{
		if (!$pid || !ctype_digit($pid))
		{
			echo 'access_denied';
		}

		$this->_token_check('sz_token', $token);

		$this->load->helper('ajax_helper');
		$this->load->model('sitemap_model');

		$data->page_id = intval($pid);
		$data->token = $token;
		$data->page = $this->sitemap_model->get_external_link_page($pid);
		if ( ! $data->page )
		{
			exit('編集対象のページが見つかりませんでした');
		}
		
		$this->load->view('parts/edit_external_link', $data);
	}

	/**
	 * サイトマップ移動用リスト取得
	 * @access public
	 * @param $current_pid
	 * @param $token
	 */
	function sitemap($current_pid, $token)
	{
		$this->_token_check('sz_token', $token);

		$this->load->model('sitemap_model');
		$data->sitemap = $this->sitemap_model->get_page_structures();
		$data->system_front_page = $this->sitemap_model->get_frontend_system_pages();
		$data->current = $current_pid;

		$this->load->view('parts/ajax_sitemap', $data);
	}

	/**
	 * ページ選択用APIハンドルメソッド
	 * @access public
	 * @param $current_pid
	 * @param $token
	 */
	function get_sitemap($current_pid, $token)
	{
		$this->_token_check('sz_token', $token);

		$this->load->model('sitemap_model');
		$data->pages = $this->sitemap_model->get_page_structures();
		$data->current = $current_pid;

		$this->load->view('parts/ajax_sitemap_block', $data);
	}

	/**
	 * ページ選択API内でのページ検索実行メソッド
	 * @access public
	 * @param $token
	 */
	function search_page_sitemap($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$this->load->model('sitemap_model');

		$pt = $this->input->post('page_title'); // ページタイトル
		$pp = $this->input->post('page_path'); // ページパス

		$data->pages = $this->sitemap_model->search_page($pt, $pp, FALSE);

		$this->load->view('parts/ajax_search_page_result', $data);
	}
	

	/**
	 * ページ選択API内でのページ検索実行メソッド
	 * @access public
	 * @param $token
	 */
	function search_page_sitemap_block($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$this->load->model('sitemap_model');

		$pt = $this->input->post('page_title'); // ページタイトル
		$pp = $this->input->post('page_path'); // ページパス

		$data->pages = $this->sitemap_model->search_page($pt, $pp, TRUE);

		$this->load->view('parts/ajax_search_page_result', $data);
	}
	

	/**
	 * バージョン一覧出力メソッド
	 * @access public
	 * @param string $pid
	 * @param string $token
	 */
	function get_versions($pid, $token)
	{
		$this->_token_check('sz_token', $token);

		$data->versions = $this->ajax_model->get_page_versions((int)$pid);
		$data->users = $this->ajax_model->get_user_name_list();
		$data->is_approve = $this->ajax_model->page_is_approve($this->user_id, $pid);
		$this->load->view('parts/page_versions', $data);
	}

	/**
	 * 単一ページデータ取得APIハンドル
	 * @access public
	 * @param $pid
	 * @param $token
	 */
	function get_page($pid, $token)
	{
		$this->_token_check('sz_token', $token);

		$page = $this->ajax_model->get_page_one((int)$pid);

		echo json_encode($page);
		exit;
	}

	/**
	 * 子ページ抽出
	 * @access public
	 * @param $pid
	 * @param $current_pid
	 * @param $token
	 */
	function get_child_sitemap($pid = 0, $current_pid, $token = FALSE)
	{
		if ((int)$pid === 0)
		{
			exit('parameter not enough');
		}

		$this->_token_check('sz_token', $token);

		$this->load->model('sitemap_model');
		$data->childs = $this->sitemap_model->get_child_pages($pid);
		$data->current = $current_pid;

		$this->load->view('parts/ajax_child_sitemap', $data);
	}

	/**
	 * フォームブロックの質問保存用メソッド
	 * @access public
	 * @param srting $token
	 */
	function set_form_question($token)
	{
		$this->_token_check('sz_token', $token);
		$post = array(
			'question_name'		=> $this->input->post('question_name', TRUE),
			'question_type'		=> $this->input->post('question_type', TRUE),
			'validate_rules'		=> $this->input->post('validate_rules', TRUE),
			'question_key'		=> $this->input->post('question_key'),
			'rows'					=> (int)$this->input->post('rows'),
			'cols'					=> (int)$this->input->post('cols'),
			'class_name'			=> $this->input->post('class_name'),
			'caption'				=> $this->input->post('caption')
		);

		// we not 'files' type for our security policy.
		if ($post['question_type'] == 'files')
		{
			exit('error');
		}

		$ret = $this->ajax_model->insert_new_question($post, $this->input->post('option'));
		echo ($ret) ? $ret : 'error';
		exit;
	}

	/**
	 * フォームブロックの質問編集保存用メソッド
	 * @access public
	 * @param $token
	 */
	function edit_form_question($token)
	{
		$this->_token_check('sz_token', $token);
		$post = array(
			'question_name'		=> $this->input->post('question_name', TRUE),
			'question_type'		=> $this->input->post('question_type', TRUE),
			'validate_rules'		=> $this->input->post('validate_rules', TRUE),
			'rows'					=> (int)$this->input->post('rows'),
			'cols'					=> (int)$this->input->post('cols'),
			'class_name'			=> $this->input->post('class_name'),
			'caption'				=> $this->input->post('caption')
		);

		$qid = intval($this->input->post('edit_target_id'));
		$key = $this->input->post('question_key');

		$ret = $this->ajax_model->update_question_data($post, $this->input->post('option'), $qid, $key);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * フォームブロックの質問削除用メソッド
	 * @access public
	 * @param $qid
	 * @param $token
	 */
	function delete_form_question($qid, $token)
	{
		$this->_token_check('sz_token', $token);

		// ここでのdeleteはレコード削除
		$ret = $this->ajax_model->delete_question_data((int)$qid);
		echo ($ret) ? 'complete' : 'error';
	}

//	function delete_pending_forms($token = FALSE)
//	{
//		$this->_token_check('sz_token');
//
//		$dels = $this->input->post('pendings');
//		$dels_data = explode(':', $dels);
//
//		if ($dels_data === FALSE)
//		{
//			$dels_data = array($dels);
//		}
//
//		$ret = $this->ajax_model->delete_pending_form_questions($dels_data);
//
//		if ($ret)
//		{
//			echo 'complete';
//		}
//		else
//		{
//			echo 'error';
//		}
//		exit;
//	}

	/**
	 * 質問表示順変更
	 * @access public
	 * @param $qs_key
	 * @param $token
	 */
	function sort_question($qs_key, $token)
	{
		$this->_token_check('sz_token', $token);

		$ret = $this->ajax_model->do_sort_question($qs_key);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * 保存した質問リスト取得
	 * @access public
	 * @param $qid
	 * @param $token
	 */
	function get_form_parts_data($qid, $token)
	{
		$this->_token_check('sz_token', $token);

		$data = $this->ajax_model->get_form_parts_one((int)$qid);
		if ( empty($data->caption) )
		{
			$data->caption = '';
		}
		echo json_encode($data);
		exit;
	}

	/**
	 * Ajaxトークンチェック
	 * @access private
	 * @param $name
	 * @param $val
	 */
	function _token_check($name, $val)
	{
		if (!$this->session->userdata($name) || $this->session->userdata($name) !== $val)
		{
			exit('access_denied.');
		}
	}

	/**
	 * オートナビブロック用サイトマップ出力（プレビュー用）
	 */
	function generate_navigation($pid)
	{
		$this->page_id = $pid;
		$this->load->model('sitemap_model');
		$this->load->helper('ajax_helper');

		foreach(array('sort_order', 'page_id', 'handle_class', 'current_class', 'show_base_page', 'current_page_id') as $v)
		{
			$data[$v] = $this->input->post($v);
		}
		$sub_level = (int)$this->input->post('subpage_level');

		// strict sub page level
		if ($sub_level == 'all')
		{
			$sub_level = $this->sitemap_model->get_max_display_level_all();
		}

		if ($sub_level == 0)
		{
			$sub_level = 1;
		}
		// set sort_order
		$ob = (int)$data['sort_order'];
		if ($ob === 2) {
			$order_by = 'display_order DESC';
		} else if ($ob === 3) {
			$order_by = 'page_title ASC';
		} else if ($ob === 4) {
			$order_by = 'page_title DESC';
		} else {
			$order_by = 'display_order ASC';
		}

		// set display_mode
		$dpm = (int)$this->input->post('display_mode');
		if ($dpm === 1) {
			$dpm_class = 'v_nav';
		} else if ($dpm === 2) {
			$dpm_class = 'h_nav';
		} else if ($dpm === 3) {
			$dpm_class = 'sz_breadcrumb';
		}

		if ($dpm < 3)
		{
			$ret = $this->sitemap_model->get_auto_navigation_data($data, $sub_level, $ob, FALSE);

			$this->page_id = $data['current_page_id'];
			$out = generate_navigation_format($ret, $data['handle_class'], $dpm_class, $data['current_class']);
		}
		else
		{
			$ret = $this->sitemap_model->get_navigation_breadcrumb($this->input->post('current_page_id'));
			$out = generate_breadcrumb($ret, $data['handle_class'], $dpm_class, $data['current_class']);
		}

		echo $out;
		exit;
	}

	/**
	 * ファイル選択API応答メソッド
	 * @access public
	 * @param $offset
	 * @param $token
	 */
	function get_files_image_dir($tmp = 0,$token = FALSE)
	{
		$this->_token_check('sz_token', $token);
		$this->load->model('file_model');

		$did = 1; // top directory
		$data->dirs = $this->file_model->get_directories($did); // directories
		$data->files = $this->file_model->get_files_from_directory($did); // files

		$data->ext_list = $this->file_model->get_file_exts(TRUE);
		$data->group_list = $this->file_model->get_file_groups_all(TRUE);

		$data->ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_file_token', $data->ticket);
		
		// force upload token
		$force_token = sha1(microtime());
		$this->session->set_userdata('sz_force_file_manager_token', $force_token);
		$data->force_token = $force_token;

		// make tree data
		$data->tree = array(1 => '/');

		$this->load->view('elements/file_lists_dir', $data);
	}

	/**
	 * ファイル選択時のファイルデータ取得
	 * @access public
	 * @param int $fid
	 * @param string $token
	 */
	function get_file($fid = 0, $token)
	{
		$this->_token_check('sz_token', $token);

		if (!$fid) {
			echo 'access denied';
		}
		$this->load->model('file_model');
		$file = $this->file_model->get_file_data((int)$fid);
		echo json_encode($file);
		exit;
	}

	/**
	 * 選択したブロックを下書きへ保存
	 * @access public
	 * @param string $token
	 */
	function block_to_draft($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$bid = $this->input->post('block_id');
		$collection_name = $this->input->post('collection_name');
		$uid = (int)$this->session->userdata('user_id');
		$alias_name = $this->input->post('alias_name');
		$ret = $this->ajax_model->add_draft_block($bid, $collection_name, $uid, $alias_name);

		if ($ret === TRUE)
		{
			echo 'complete';
		}
		else if ($ret === 'already')
		{
			echo 'already';
		}
		else
		{
			echo 'error';
		}
		exit;
	}
	
	/**
	 * 選択したブロックを共有ブロックに登録
	 * @access public
	 * @param string $token
	 */
	function add_to_static($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$bid = $this->input->post('block_id');
		$collection_name = $this->input->post('collection_name');
		$uid = (int)$this->session->userdata('user_id');
		$alias_name = $this->input->post('alias_name');
		$ret = $this->ajax_model->add_to_static_block($bid, $collection_name, $uid, $alias_name);

		if ($ret === TRUE)
		{
			echo 'complete';
		}
		else if ($ret === 'already')
		{
			echo 'already';
		}
		else
		{
			echo 'error';
		}
		exit;
	}

	/**
	 * 指定バージョンを公開状態に変更
	 * @access public
	 * @param $pid
	 * @param $v
	 * @param $token
	 */
	function approve_version($pid, $v = 0, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);
		if (!$v)
		{
			exit('error');
		}
		// load versioning mode
		$this->load->model('version_model');
		$ret = $this->version_model->approve_version_from_ajax($pid, $v);
		// 対象ページのキャッシュも削除する
		$this->version_model->delete_approve_page_cache($pid);
		echo ($ret) ? $ret : 'error';
		exit;
	}

	/**
	 * 指定バージョンを削除する
	 * @access public
	 * @param $token
	 */
	function delete_version($token = FALSE)
	{
		$this->_token_check('sz_token', $token);
		$versions = $this->input->post('deletes');
		if (!$versions)
		{
			exit('error');
		}
		if (strpos($versions, ':') === FALSE)
		{
			$vs = array($versions);
		}
		else
		{
			$vs = explode(':', $versions);
		}
		// load versioning model
		$this->load->model('version_model');
		$ret = $this->version_model->delete_versions_from_ajax((int)$this->input->post('pid'), $vs);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * 下書き保存のブロックを削除
	 * @access public
	 * @param $dfid
	 * @param $token
	 */
	function delete_draft_block($dfid, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$this->load->model('draft_model');

		$ret = $this->draft_model->delete_draft_block($dfid, $this->user_id);
		echo ($ret) ? 'complete' : 'error';
	}
	
	/**
	 * 共有ブロックをリストから削除
	 * @access public
	 * @param $dfid
	 * @param $token
	 */
	function delete_static_block($stid, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$this->load->model('draft_model');

		$ret = $this->draft_model->delete_static_block($stid, $this->user_id);
		echo ($ret) ? 'complete' : 'error';
		
	}
	
	/**
	 * 非同期カスタムCSS更新
	 * @access public
	 * @param $token
	 */
	function update_advance_css_edit($token = FALSE)
	{
		$this->_token_check('sz_token', $token);
		
		$css = $this->input->post('advance_css');
		$tpid = (int)$this->input->post('template_id');
		$ret = $this->ajax_model->update_advance_css($tpid, $css);
		echo ($ret) ? 'complete' : 'error';
	}
	
	/**
	 * highlightアクション
	 */
	function get_highlight_native_code($token = FALSE)
	{
		$this->_token_check('sz_token', $token);
		
		$codes = $this->input->post('codes');
		$gets = $this->ajax_model->get_highlight_native_codes($codes);
		
		echo json_encode($gets);
	}
	
	/**
	 * ブロック権限更新
	 */
	function block_permissions($pid, $bid, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);
		
		$data->block_id      = $bid;
		$data->page_id       = $pid;
		$data->token         = $token;
		$data->user_list     = $this->ajax_model->get_user_list();
		$data->enable_mobile = $this->site_data->enable_mobile;
		
		// split permissions
		$permissions             = $this->ajax_model->get_block_permission((int)$bid);
		$data->view_permission   = ( ! empty($permissions->allow_view_id) )
		                             ? explode(':', trim($permissions->allow_view_id, ':'))
		                             : array();
		
		$data->edit_permission   = ( ! empty($permissions->allow_edit_id) )
		                             ? explode(':', trim($permissions->allow_edit_id, ':'))
		                             : array();
		
		$data->mobile_permission = ( ! empty($permissions->allow_mobile_carrier) )
		                             ? explode(':', trim($permissions->allow_mobile_carrier, ':'))
		                             : array();
		
		$this->load->view('elements/block_permission', $data);
	}
	
	/**
	 * ページパスが存在するかどうかチェック
	 */
	function check_page_path_exists($token = FALSE)
	{
		$this->_token_check('sz_token', $token);
		
		$path = trim($this->input->post('path'), '/');
		$path = rawurlencode($path);
		$pid = (int)$this->input->post('page_id');
		switch ($this->ajax_model->check_path_exists($pid, $path))
		{
			case 'PAGE_EXISTS':
					echo '<p style="color:#c00">ページは既に存在します。</p>';
					break;
			case 'STATIC_EXISTS':
					echo '<p style="color:#c00">静的ページが存在します。</p>';
					break;
			default:
					echo '<p>ページパスは使用可能です。</p>';
					break;
		}
	}
	
	/**
	 * Ajaxリクエストから特定のブロックメソッドを呼び出す
	 * わざとパラメータ多めで複雑なリクエストにした。
	 */
	function block_method($method_nanme = '', $collection_name = FALSE, $block_id = FALSE, $token = FALSE)
	{
		if ( !$collection_name || !$block_id )
		{
			// nothing to do.
			return;
		}
		
		$this->_token_check('sz_token', $token);
		
		// load block
		$block = $this->load->block($collection_name, $block_id, TRUE);
		
		if ( $method_name && method_exists($block, $method_name) )
		{
			echo $block->{$method}();
		}
	}
	
	
	function add_block_set($block_id, $token = FALSE)
	{
		if ( ! $token || $token != $this->session->userdata('sz_token') )
		{
			echo 'access_denied.';
			return;
		}
		
		// If GET request, show input view
		if ( $this->input->server('REQUEST_METHOD') === 'GET' )
		{
			// get block set
			$data->block_set = $this->ajax_model->get_block_set();
			$data->block_id  = $block_id;
			$this->load->view('elements/block_set_list', $data);
		}
		else if ( $this->input->server('REQUEST_METHOD') === 'POST' )
		{
			$block_id = (int)$this->input->post('block_id');
			
			// get custom-template handle
			$sql =
					'SELECT '
					.	'ct_handle '
					.'FROM '
					.	'pending_blocks '
					.'WHERE '
					.	'block_id = ? '
					.'LIMIT 1'
					;
			$query = $this->db->query($sql, array((int)$block_id));
			if ( ! $query || ! $query->row() )
			{
				$ct_handle = '';
			}
			else
			{
				$result    = $query->row();
				$ct_handle = $result->ct_handle;
			}
			
			
			// Does make add request?
			if ( $this->input->post('master_name') )
			{
				$data = array(
					'master_name' => $this->input->post('master_name'),
					'create_date' => db_datetime()
				);
				
				$ret      = $this->ajax_model->make_block_set($data, $block_id, $ct_handle);
				$response = '';
				
				if ( $ret === TRUE )
				{
					$response = 'success';
				}
				// already_exists...
				else if ( $ret === 'already' )
				{
					$response = 'already';
				}
				else
				{
					$response = 'make error';
				}
				echo $response;
				return;
			}
			// else, add sets
			else 
			{
				$block_sets = $this->input->post('block_set_master_id');
				$ret        = $this->ajax_model->add_block_set($block_sets, $block_id, $ct_handle);
				
				echo ( $ret === TRUE ) ? 'success' : 'add_error';
			}
		}
	}
}
