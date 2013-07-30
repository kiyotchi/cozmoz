<?php
/**
 * ===============================================================================
 *
 * Seezoo ページコントローラ
 *
 * CMSルーティングと出力を行うコントローラ
 *
 * @default_controller
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */

class Page extends SZ_Controller
{
	public $page_id;
	public $version_number;
	public $is_ssl_page;
	public $page_path;
	public $edit_mode;
	public $user_id;
	public $site_data;
	public $template_id;
	public $relative_template_path;
	public $is_advance_css;

	// defualt set properties
	public $cms_mode                     = TRUE;
	public $edit_menu                    = '';
	public $is_enable_edit_unlock        = FALSE;
	public $is_edit_timeout              = FALSE;
	public $enable_cache                 = FALSE;
	public $page_is_mobile               = FALSE;
	public $additional_header_javascript = array();
	public $additional_header_css        = array();
	public $additional_footer_javascript = array();
	public $additional_footer_element    = array();

	protected $page_object;

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller(FALSE);
		$this->load->model(array('page_model', 'init_model', 'permission_model', 'version_model'));

		ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . APPPATH . 'libraries/');

		$this->user_id   = ( $this->session->userdata('user_id') ) ? (int)$this->session->userdata('user_id') : 0; // user_id "0" is no logins
		$this->member_id = ( $this->session->userdata('member_id') ) ? (int)$this->session->userdata('member_id') : 0;
		$this->is_login  = ( (int)$this->user_id > 0 ) ? TRUE : FALSE;
		$this->is_admin  = $this->permission_model->is_admin($this->user_id);
		$this->is_master = ( (int)$this->user_id === 1 ) ? TRUE : FALSE;
		// debug code
		//$this->output->enable_profiler(TRUE);
		$this->cms_mode  = TRUE;
	}

	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		// トップページ表示
		$this->page_id = 1;
		$this->_view();
	}

	/**
	 * permission_denied : アクセス不可のメッセージのみを出力するメソッド
	 */
	function permission_denied()
	{
		$this->load->library('exceptions');
		echo $this->exceptions->show_error('', '', 'error_401', 403);
		exit;
	}

	/**
	 * モバイル非対応端末の場合
	 * Enter description here ...
	 */
	function permission_mobile_denied()
	{
		$this->load->library('exceptions');

		$output = $this->exceptions->show_error('', '', 'error_mobile_401', 200);
		$mobile = Mobile::get_instance();
		if (! $this->is_login && $mobile->is_docomo() )
		{
			$this->output->set_header('Content-Type: text/html; charset=SJIS-WIN');
			$output = str_replace('CHARSET', 'SHIFT_JIS', $output);
			$output = mb_convert_kana($output, 'k', 'UTF-8');
			$output = mb_convert_encoding($output, 'SJIS-WIN', 'UTF-8');
			header('Content-Type: text/html; charset=SJIS-WIN');
		}
		else
		{
			$output = str_replace('CHARSET', 'UTF-8', $output);
			header('Content-Type: text/html; charset=UTF-8');
			$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		}

		echo $output;
		exit;
	}

	/**
	 * mobile_only : モバイルキャリア専用ページであることを通知
	 */
	function mobile_only()
	{
		$this->load->library('exceptions');
		echo $this->exceptions->show_error('', '', 'error_403', 403);
		exit;
	}

	/**
	 * maintenance : メンテナンス中の表示
	 */
	function maintenance()
	{
		// Is site really maintenance ?
		$site_info = $this->init_model->get_site_info();

		if ( (int)$site_info->is_maintenance > 0 )
		{
			$this->load->library('exceptions');
			echo $this->exceptions->show_error('', '', 'error_503', 503);
			exit;
		}
		else
		{
			// If site is not maintenance mode, this method returns 404.
			show_404();
		}
	}

	/**
	 * preview : バージョンプレビュー
	 */
	function preview()
	{
		// _remapによりセグメントデータが破棄されるため、URIクラスから取得
		$vid = $this->uri->segment(3, 0);
		$pid = $this->uri->segment(4, 0);
		if ( ! $vid || ! $pid )
		{
			exit('access_denied');
		}

		$this->page_id   = (int)$pid;
		$this->site_data = $this->init_model->get_site_info();

		$ver_mode  = $vid;
		$page_data = $this->_get_page($ver_mode);

		// tmp set some properties
		$this->can_edit       = FALSE;
		$this->edit_mode      = 'NO_EDIT';
		$this->template_id    = intval($page_data['template_id']);
		$this->is_preview     = TRUE;
		$this->version_number = (int)$vid;
		$this->view_mode      = $this->session->userdata('viewmode');

		$p_path = $this->_rel_template_path = $page_data['template_path'];
		$page_data['template_path']           = file_link() . 'templates/' . $p_path; // absolute
		$page_data['_relative_template_path'] = $this->relative_template_path = 'templates/' . $p_path; // relative

		$this->page_data = $page_data;

		if ( $this->config->item('final_output_mode') === 'mb'
				&& file_exists($page_data['_relative_template_path'] . 'mobile/view.php') )
		{
			$path = $p_path . 'mobile/view';
		}
		else if ( $this->config->item('final_output_mode') === 'sp'
					&& file_exists($page_data['_relative_template_path'] . 'smartphone/view.php') )
		{
			$path = $p_path . 'smartphone/view';
		}
		else
		{
			$path = $p_path . 'view';
			$this->page_is_mobile = FALSE; // view.php encoding is UTF-8 for PC output
		}

//		// if page is mobile_only and view_mobile.php file exists?
//		if ((int)$page_data['is_mobile_only'] > 0 && file_exists($p_path . 'view_mobile.php'))
//		{
//			$path = $p_path . 'view_mobile';
//		}
//		else
//		{
//			$path = $p_path . 'view';
//		}
		$this->edit_menu = '';

		$this->load->template_view($path, $page_data);
	}

	/**
	 * get_diff_versions : バージョン比較結果出力
	 */
	function get_diff_versions($pid = 0, $v1 = 0, $v2 = 0, $token = FALSE)
	{
		// Sorry, temporary not works...
		show_404();
		
		$pid   = $this->uri->segment(3, 0);
		$v1    = $this->uri->segment(4, 0);
		$v2    = $this->uri->segment(5, 0);
		$token = $this->uri->segment(6, FALSE);

		// check access token
		if ( ! $token || $token != $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}
		// check parameters
		if ( ! $pid || ! $v1 || ! $v2 )
		{
			exit('バージョン比較に必要なデータが渡されませんでした。');
		}
		// Does diff library exists?
		if ( ! file_exists(APPPATH . 'libraries/thirdparty/Htmldiff.php') )
		{
			exit('バージョン比較ライブラリが組み込まれていません。');
		}

		// strict to version id
		if ($v1 >= $v2)
		{
			$past   = $v2;
			$latest = $v1;
		}
		else
		{
			$past   = $v1;
			$latest = $v2;
		}


		// get version output data
		$version1 = page_link() . 'page/preview/' . $past . '/' . $pid;
		$version2 = page_link() . 'page/preview/' . $latest . '/' . $pid;

		// load diff library
		$this->load->library('thirdparty/htmldiff');

		// set diff versions
		$this->htmldiff->set_versions($past, $latest);

		echo( $this->htmldiff->getDiffFromURIs($version1, $version2));
		exit;
	}

	/**
	 * view : ページ表示系メソッド
	 * セグメントデータからページID取得、指定ページを抽出する
	 */
	function _view()
	{
		// guard direct access!
		if ( ! $this->page_id )
		{
			show_404();
		}

		// is_maintenance?
		if ( (int)$this->site_data->is_maintenance === 1 )
		{
			$this->maintenance();
		}

		// check permission
		$this->_check_permission();

		// get current page status
		$ae = $this->init_model->get_page_state($this->page_id);
		// set_edit_mode
		if ( (int)$ae['is_editting'] === 0 )
		{
			$this->edit_mode = 'NO_EDIT';
		}
		else
		{
			if ( $ae['edit_user_id'] == $this->user_id )
			{
				$this->edit_mode = 'EDIT_SELF';
			}
			else
			{
				// added version1.0-beta5
				// 編集途中で1時間以上経過した場合、他の編集権限を持つユーザーは
				// 編集ロックを開放することができるようにする
				if ( strtotime('-1 hour') >= strtotime($ae['edit_start_time']) )
				{
					$token = sha1(microtime());
					$this->session->set_userdata('sz_unlock_token', $token);
					$this->is_edit_timeout = $token;
				}
				else if ($this->is_master === TRUE)
				{
					$this->is_enable_edit_unlock = TRUE;
				}
				$this->edit_mode = 'EDIT_OTHER';
			}
		}

		// get page version type
		// if edit mode, get current edit version
		// else, user is logged in, get curerent stable version
		// else get most approved version
		if ( $this->is_login && $this->edit_mode == 'EDIT_SELF' )
		{
			$ver_mode = 'editting';
		}
		else if ( $this->user_id > 0 )
		{
			$ver_mode = 'current';
		}
		else
		{
			$ver_mode = 'approve';

			// if output version mode is approve, set cache
			if ( ! $this->is_login )
			{
				$this->enable_cache = ($this->site_data->enable_cache > 0) ? TRUE : FALSE;
			}
		}

		// get current_page_version
		$page_data = $this->page_data = $this->_get_page($ver_mode);

		if ((int)$page_data['alias_to'] > 0)
		{
			// redirect alias from
			$this->init_model->alias_redirect($page_data['alias_to']);
		}
		else if ((int)$page_data['is_system_page'] > 0)
		{
			// if page_is system output, redirect by page path to execute CodeIgniter Controller.
			$system_path = $this->init_model->get_system_page_path_by_page_id($this->page_id);
			if ( $system_path )
			{
				redirect($system_path);
			}
			show_404();
		}
		else if ( ! empty($page_data['external_link']) )
		{
			redirect($page_data['external_link']);
		}

		$this->version_number = $page_data['version_number'];
		$this->is_ssl_page = ($page_data['is_ssl_page'] > 0) ? TRUE : FALSE;

		// if output cahce enable, try display from cache file
		if ( $this->enable_cache == TRUE && $this->input->server('REQUEST_METHOD') === 'GET' )
		{
			// Does display cache is enabled?
			if ( $this->output->display_cache($this->config, $this->uri, $this->page_id) == TRUE )
			{
				exit;
			}
		}

		// access page is mobile only?
		if ( (int)$page_data['is_mobile_only'] > 0 && $this->user_id === 0 )
		{
			if ( ! $this->config->item('is_mobile') )
			{
				$this->mobile_only();
			}
			$this->page_is_mobile = TRUE;
		}

		// Does template advance CSS exists?
		$this->is_advance_css = $this->page_model->has_advance_css(intval($page_data['template_id']));
		$this->template_id    = intval($page_data['template_id']);

		// set template path absolute, relative
		$p_path = $page_data['template_path'];
		$page_data['template_path']           = file_link() . 'templates/' . $p_path; // absolute
		$page_data['_relative_template_path'] =
		$page_data['relative_template_path']  = 'templates/' . $p_path; // relative
		$this->_rel_template_path             = $p_path;
		$this->relative_template_path         = FCPATH . $this->_rel_template_path;

		// Is page mobile_only and view_mobile.php file exists?
		// notice:: this process works not logged in case only.
		if ( $this->config->item('final_output_mode') === 'mb'
				&& file_exists($page_data['_relative_template_path'] . 'mobile/view.php') )
		{
			$path = $p_path . 'mobile/view';
		}
		else if ( $this->config->item('final_output_mode') === 'sp'
					&& file_exists($page_data['_relative_template_path'] . 'smartphone/view.php') )
		{
			$path = $p_path . 'smartphone/view';
		}
		else
		{
			$path = $p_path . 'view';
			$this->page_is_mobile = FALSE; // view.php encoding is UTF-8 for PC output
		}

		// load edit menu
		if ( $this->is_login === TRUE )
		{
			// get edit menu position from cookie tht created from JavaScript
			$this->load->helper('cookie_helper');
			$x = get_cookie('flCookie_x');
			$y = get_cookie('flCookie_y');

			$ae['menu_x']      = ($x) ? substr($x, 0, strpos($x, '&d')) : 50;
			$ae['menu_y']      = ($y) ? substr($y, 0, strpos($y, '&d')) : 50;
			$ae['advance_css'] = $page_data['advance_css'];
			$ae['template_id'] = $page_data['template_id'];
			$this->edit_menu   = $this->load->view('parts/edit_menu', $ae, TRUE);
		}
		else
		{
			$this->edit_menu = '';
		}

		// load view data and send _output method
		$this->load->template_view($path, $page_data);
	}

	/**
	 * _view_statics : 静的ページ出力
	 */
	function _view_statics($page)
	{
		// load customed template parse class
		$this->load->library('parser');

		// build some template variables
		//$data = $this->page_model->get_static_page_variables();
		$statics = simplexml_load_file(APPPATH . '/libraries/statics/statics.xml');

		foreach ( $statics as $v )
		{
			$template_vars[(string)$v->name] = object_to_array($v);
		}

		$this->parser->parse_static($page, $template_vars);
	}

	/**
	 * advance_css : 追加CSSをインクルード
	 */
	function advance_css()
	{
		$tid = $this->uri->segment(3, 0);
		$css = $this->page_model->get_advance_css_by_template_id((int)$tid);

		if ( $css )
		{
			header("Content-Type: text/css");
			echo htmlspecialchars($css, ENT_QUOTES, 'UTF-8');
			exit();
		}
	}

	/**
	 * unlock_edit_page : ページの編集ロックを開放する
	 */
	function unlock_edit_page()
	{
		$page_id = (int)$this->uri->segment(3, 0);

		if ( $page_id === 0 )
		{
			exit('不正なページアクセスです。');
		}
		if ( $this->is_master
				|| $this->session->userdata('sz_unlock_token') === $this->uri->segment(4, '') )
		{
			// version destroy
			$this->version_model->delete_pending_data($page_id, $this->user_id);
			$this->version_model->edit_out($page_id);
			redirect($page_id);
		}
		exit('不正な操作です。');
	}

	/**
	 * _get_page : ページ検索
	 * @param string $ver_mode
	 * @return array $page
	 */
	function _get_page($ver_mode = 'approve')
	{
		$page = $this->page_model->get_page_object($this->page_id, $ver_mode);

		if ( ! $page )
		{
			show_404();
		}
		return $page;
	}

	/**
	 * set_edit : エディットモードにセット
	 */
	function set_edit($pid = 0)
	{
		if ( $pid === 0 )
		{
			$pid = $this->uri->segment(3, 0);
		}
		$redirect_to = $this->input->post('redirect_path');
		// Are you not logged in or redirect to external path?
		if ( ! $this->user_id || strpos($redirect_to, base_url()) === FALSE)
		{
			redirect($pid);
		}
		$page = $this->_get_page('recent');

		if ( $this->is_admin
				|| strpos($page['allow_edit_user'], ':' . $this->user_id . ':') !== FALSE )
		{
			// create pending data
			$nv = $this->version_model->create_pending($pid, $page['version_number'], $this->user_id);
			$this->init_model->set_edit_mode($pid, $nv);
		}

		// if target page is system, redirect_by_path
		//redirect_path($pid);
		redirect($redirect_to);
	}

	/**
	 * addblock_from_draft : 下書きブロックリストからブロック追加
	 */
	function addblock_from_draft()
	{
		$pid   = $this->uri->segment(3, 0);
		$did   = $this->uri->segment(4, 0);
		$area  = $this->uri->segment(5, 0);
		$token = $this->uri->segment(6, FALSE);

		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}

		if ( ! $pid || ! $did || ! $area )
		{
			show_error('parameter is not enough.');
		}

		$area = rawurldecode($area);

		// use process model
		$this->load->model('process_model');
		$ret = $this->process_model->add_block_by_draft($pid, $did, $area);

		redirect_path($pid);
	}

	/**
	 * addblock_from_static : 共有ブロックリストからブロック追加
	 */
	function addblock_from_static()
	{
		$pid   = $this->uri->segment(3, 0);
		$bid   = $this->uri->segment(4, 0);
		$area  = $this->uri->segment(5, 0);
		$token = $this->uri->segment(6, FALSE);

		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}

		if ( ! $pid || ! $bid || ! $area )
		{
			show_error('parameter is not enough.');
		}

		$area = rawurldecode($area);

		// use process model
		$this->load->model('process_model');
		$ret = $this->process_model->add_block_by_static($pid, $bid, $area);

		redirect_path($pid);
	}
	
	/**
	 * addblock_from_blockset : ブロックセットから一括追加
	 */
	function addblock_from_blockset()
	{
		$page_id   = $this->uri->segment(3, 0);
		$master_id = $this->uri->segment(4, 0);
		$area_name = $this->uri->segment(5, 0);
		$token     = $this->uri->segment(6, FALSE);

		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}

		if ( ! $page_id || ! $master_id || ! $area_name )
		{
			show_error('parameter is not enough.');
		}

		$area = rawurldecode($area_name);

		// use process model
		$this->load->model('process_model');
		$ret = $this->process_model->add_block_by_blockset($page_id, $master_id, $area);

		redirect_path($page_id);
	}

	/**
	 * delete_page : ページ削除
	 */
	function delete_page()
	{
		$pid   = $this->uri->segment(3, 0);
		$token = $this->uri->segment(4, 0);

		if ( ! $pid || ! ctype_digit($pid) )
		{
			echo 'access_denied';
		}
		if ( (int)$pid === 1 )
		{
			echo 'トップページは削除できません。';
			exit;
		}

		$this->_token_check('sz_token', $token);
		$ret = $this->page_model->delete_page_data($pid);

		if ( $ret )
		{
			redirect('/');
		}
		else
		{
			exit;
		}
	}


	/**
	 * _remap : メソッド再マッピング
	 */
	function _remap($method = 'index')
	{
		// when method is index, show toppage.
		if ( $method == 'index' )
		{
			$this->page_id = 1;
			$this->_view();
			return;
		}
		// page class has method, do that method.
		else if ( method_exists($this, $method) )
		{
			$this->page_id = $this->uri->segment(3, 1);
			$this->$method();
			return;
		}

		// else, sub-routing from page_path
		$uri_string = trim($this->uri->uri_string(), '/');

		// routing order by priority
		$route = ( defined('ROUITNG_PRIORITY') ) ? ROUTING_PRIORITY : 'cms';

		if ( $route === 'cms' )
		{
			// if CMS routing priority larger than static,
			// try cms->static routing.
			$is_page = $this->_cms_routing($uri_string);

			if ( $is_page !== FALSE )
			{
				$this->_view();
				return;
			}
			else
			{
				$is_page = $this->_static_routing($uri_string);

				if ( $is_page !== FALSE )
				{
					$this->_view_statics($is_page);
					return;
				}
				else
				{
					show_404();
				}
			}
		}
		else if ( $route === 'static' )
		{
			// else if static routing priority larger than cms,
			// try static->cms routing.
			$is_page = $this->_static_routing($uri_string);

			if ( $is_page !== FALSE )
			{
				$this->_view_statics($is_page);
				return;
			}
			else
			{
				$is_page = $this->_cms_routing($uri_string);

				if ( $is_page !== FALSE )
				{
					$this->_view();
					return;
				}
				else
				{
					show_404();
				}
			}
		}
		else
		{
			// default , guard process. page not found.
			show_404();
		}
	}

	function _cms_routing($uri)
	{
		$page = $this->init_model->db_routing_all(uri_encode_path($uri));
		if ( $page )
		{
			$this->page_id = $page;
			return TRUE;
		}
		else
		{
			// フルパスでヒットしなかった場合、最後のセグメントだけを削って再検索
			$last_segment = strrpos($uri, '/');
			if ($last_segment === FALSE)
			{
				return FALSE;
			}

			$path = substr($uri, 0, $last_segment);
			$page = $this->init_model->db_routing_all($path);

			if ($page)
			{
				$this->page_id = $page;
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
	}

	function _static_routing($uri)
	{
		$uri = preg_replace('/\.html?$|\.php$/', '', $uri);

		// if static page exists, render that page
		if (file_exists(FCPATH . 'statics/' . $uri . '.html'))
		{
			return FCPATH . 'statics/' . $uri . '.html';
		}
		else if (file_exists(FCPATH . 'statics/' . $uri . EXT))
		{
			return FCPATH . 'statics/' . $uri . EXT;
		}
		else
		{
			return FALSE;
		}
	}

	// 出力フック
	function _output($output)
	{
		// 各ブロックで必要なJS、CSSをヘッダに追加
		$replace_header = '';
		$replace_footer = '';
		// output mode
		$output_mode    = $this->config->item('final_output_mode');
		// header_required
		if ( count($this->additional_header_css) > 0 )
		{
			$replace_header .= implode("\n", $this->additional_header_css);
		}
		if ( count($this->additional_header_javascript) > 0 )
		{
			$replace_header .= implode("\n", $this->additional_header_javascript) . "\n";
		}
		

		// footer_items
		if ( count($this->additional_footer_element) > 0 )
		{
			$replace_footer .= implode("\n", $this->additional_footer_element) . "\n";
		}
		if ( count($this->additional_footer_javascript) > 0 )
		{
			$replace_footer .= implode("\n", $this->additional_footer_javascript) . "\n";
		}
		
		// block notification exists?
		if ( $this->session->flashdata('_sz_block_error') )
		{
			$replace_header .= '<script type="text/javascript" src="' . file_link(). 'js/notification.js"></script>';
			$replace_footer .= $this->load->view('elements/notification', array('msg' => '<span>ブロックの追加に失敗しました。</span>'), TRUE);
		}

		$this->load->library('parser');
		$statics = simplexml_load_file(APPPATH . '/libraries/statics/statics.xml');

		foreach ( $statics as $v )
		{
			$template_vars[(string)$v->name] = object_to_array($v);
		}

		$template_vars['OUTPUT_HEADERS'] = $replace_header;
		$template_vars['OUTPUT_FOOTERS'] = $replace_footer;

		// output settings
		// mobile instance
		$mobile = Mobile::get_instance();

		// edit menu replaces <body>.
		if ( ! empty($this->edit_menu) )
		{
			$output = str_replace('</body>', $this->edit_menu . "\n</body>" , $output);
		}
		// 2010/08/18 modified google Analytics tracking code put on before </head>.
		// output analytics code on not-loggedin case only.
		if ( ! $this->is_login && ! preg_match('/^[0-9]+$/u', trim($this->uri->uri_string(), '/')) )
		{
			//require_once APPPATH . 'libraries/mobile_ip.php';
			//MobileConfig::set_cache_dir(FCPATH . 'files/ip_caches/');
			//$mobileIP = Mobile_ip::get_instance();

			if ( $mobile->is_mobile() )
			{
				$mb_tracking_code = generate_google_analytics_mobile_tag($this->site_data->google_analytics);
				$output = str_replace('</body>', $mb_tracking_code . "\n</body>" , $output);
			}
			else
			{
				$output = str_replace('</head>', $this->site_data->google_analytics . "\n</head>", $output);
			}
		}

		$output = $this->parser->parse_vars($output, $template_vars, TRUE);

		// output mode
		$output_mode = $this->config->item('final_output_mode');
		$output_carrier = $this->config->item('final_output_carrier');

		// OGP insert
		$output = $this->_generate_ogp_string($output);
		// mobile width setting if access user is pc
		if ( ! $mobile->is_mobile() && ! $mobile->is_smartphone() )
		{
			$width = 0;
			if ( $output_mode === 'mb' )
			{
				$width = 300;
			}
			else if ( $output_mode === 'sp' )
			{
				$width = 480;
			}
			$output = $this->set_body_width_for_mobile_preview($output, $width);
		}

		// Does page cache is enabled?
		if ( $this->enable_cache == TRUE )
		{
			$this->output->cache(60);
		}
		// output covert
		// @notice
		//  Docomo   carrier accept Shift_JIS
		//  Au       carrier accept UTF-8
		//  Softbank carrier accept UTF-8
		// So, we covert to Shift_JIS when Docomo case only.
		$mobile = Mobile::get_instance();
		if ( $output_mode === 'mb' && ! $this->is_login )
		{
			$output = mb_convert_kana($output, 'k', 'UTF-8');
			if ( strpos(SEEZOO_CONVERT_MOBILE_CARRIERS, $mobile->carrier()) !== FALSE )
			{
				$this->output->set_header('Content-Type: text/html; charset=SJIS-WIN');
				$output = mb_convert_encoding($output, 'SJIS-WIN', 'UTF-8');
				header('Content-Type: text/html; charset=SJIS-WIN');
			}
			else
			{
				header('Content-Type: text/html; charset=UTF-8');
				$this->output->set_header('Content-Type: text/html; charset=UTF-8');
			}
		}
		else
		{
			header('Content-Type: text/html; charset=UTF-8');
			$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		}

		return $output;
	}

	// ワンタイムトークンチェック
	function _token_check($name, $val)
	{
		if ( ! $this->session->userdata($name) || $this->session->userdata($name) !== $val )
		{
			exit('access denied.');
		}
	}

	/**
	 * _check_permission
	 * 現在のページIDに対しての権限チェック
	 */
	function _check_permission()
	{
		// masterユーザーは常にTRUE
		if ( $this->is_admin )
		{
			$this->can_edit = TRUE;
			return;
		}
		$permissions = $this->permission_model->get_permission_data($this->page_id);

		// permissionデータが見つからない場合は、一般ユーザー以外は許可とする
		if ( ! $permissions )
		{
			$this->can_edit = ( $this->is_login ) ? TRUE : FALSE;
		}
		else
		{
			$uid = ':' . $this->user_id . ':';
			// permissionデータが見つかった場合は権限チェック
			if ( strpos($permissions->allow_access_user, $uid) === FALSE )
			{
				// memberとしてログインしていれば権限チェック
				if ( $this->member_id < 1 || strpos($permissions->allow_access_user, ':m:') === FALSE )
				{
					// 権限が無ければpermission_denied
					$this->permission_denied();
				}
			}

			$this->can_edit = (strpos($permissions->allow_edit_user, $uid) === FALSE
									&& ( $this->member_id < 0 || strpos($permissions->allow_edit_user, ':m:') === FALSE)) ? FALSE : TRUE;
		}
	}
}
