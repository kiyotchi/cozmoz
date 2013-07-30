<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ====================================================
 * Extended CodeIgniter builtin Controller Class
 *
 * @note
 *  parent::SZ_Controller()の際にFALSEを渡すと、CMSページでのアクセスチェックは行われないので注意(default TRUE)
 *  generate_cms_mode()メソッド定義
 *    継承クラスからこのメソッドを呼ぶと、そのコントローラはCMSモードで動作するようになる
 *@package Seezoo Core
 *@author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ====================================================
 */
class SZ_Controller extends Controller
{
	// no listed builtin controllers
	protected $ignore_controllers   = array('page', 'flint', 'ajax', 'install','login', 'logout', 'gadget_ajax', 'action');

	public $additional_header_javascript = array();
	public $additional_header_css        = array();
	public $additional_footer_javascript = array();
	public $additional_footer_element    = array();
	public $page_data                    = array();
	public $view_mode;
	public $mobile;


	function SZ_Controller($use_access_check = TRUE)
	{
		parent::Controller();
		$this->hook =& load_class('Hooks');
		$this->mobile =& Mobile::get_instance();

		$this->_check_is_installed();
		// load need core classes
		$this->load->library(array('session'));
		$this->load->database();
		// load depend model
		$this->load->model(
						array(
							'dashboard_model',
							'permission_model',
							'process_model',
							'version_model',
							'init_model',
							'install_model'
						)
					);

		// pre get access page id and check permission if $use_access_check is TRUE
		if ( $use_access_check === TRUE )
		{
			$this->_set_ci_controller_page_status();
		}
		else
		{
			$this->user_id   = ( $this->session->userdata('user_id') ) ? (int)$this->session->userdata('user_id') : 0; // user_id "0" is no logins
			$this->member_id = ( $this->session->userdata('member_id') ) ? (int)$this->session->userdata('member_id') : 0;
			$this->is_login  = ( (int)$this->user_id > 0 ) ? TRUE : FALSE;
			$this->is_admin  = $this->permission_model->is_admin($this->user_id);
			$this->is_master = ( $this->user_id === 1 ) ? TRUE : FALSE;
		}
		$this->site_data       = $this->init_model->get_site_info();
		$this->cms_mode        = FALSE;
		$this->enable_cache    = FALSE;
		$this->page_is_mobile  = FALSE;
		$this->is_edit_timeout = FALSE;

		// additional header
		// Internet Explorer must be output no sniff header
		if ( $this->input->server('HTTP_USER_AGENT') &&
				strpos($this->input->server('HTTP_USER_AGENT'), 'MSIE') !== FALSE )
		{
			$this->output->set_header('X-Content-Type-Options: nosniff');
		}

		// add Utility object
		if ( class_exists('SeezooUtility') )
		{
			$sz = new SeezooUtility();
			$this->load->vars(array('seezoo' => $sz));
		}

		// set profiler mode ( ignore XHR request
		if ( defined('SZ_DEBUG_LEVEL')
		     && SZ_DEBUG_LEVEL > 1
		     && $this->input->server('HTTP_X_REQUESTED_WITH') !== 'XMLHttpRequest' )
		{
			$this->output->enable_profiler(TRUE);
		}

		// set view mode
		$this->view_mode = $this->session->userdata('viewmode');
		if ( ! $this->is_login || $this->view_mode === FALSE )
		{
			$this->view_mode = 'pc';
		}
	}

	function render_view($type = '', $vars = array())
	{
		if ( $type != '' )
		{
			$type = $type . '_view';
		}
		else
		{
			$type = 'view';
		}
		// Is page mobile_only and view_mobile.php file exists?
		// #notice this process works not logged in case only.
		if ( $this->config->item('final_output_mode') === 'mb'
				&& file_exists($this->relative_template_path . 'mobile/' . $type.EXT) )
		{
			$path = 'mobile/' . $type;
		}
		else if ( $this->config->item('final_output_mode') === 'sp'
					&& file_exists($this->relative_template_path . 'smartphone/' . $type.EXT) )
		{
			$path = 'smartphone/' . $type;
		}
		else
		{
			$path = $type;
		}

		if ( file_exists(FCPATH . 'templates/' . $this->_rel_template_path . $path .EXT) )
		{
			$this->load->template_view($this->_rel_template_path . $path, $vars);
		}
		else if ( file_exists(FCPATH . 'templates/' . $this->_rel_template_path . 'system_view'.EXT) )
		{
			$this->load->template_view($this->_rel_template_path . 'system_view', $vars);
		}
		else
		{
			$this->load->template_view($this->_rel_template_path . 'view', $vars);
		}
	}

	function _set_ci_controller_page_status()
	{
		// set user_id
		// if notlogged in, this. property set 0.
		$this->user_id   = (int)$this->session->userdata('user_id');
		$this->member_id = (int)$this->session->userdata('member_id');
		$this->user_data = $this->dashboard_model->get_user_data($this->user_id);
		// access user is master user?
		$this->is_master = $this->dashboard_model->is_master();

		/**
		 * ## 重要な変更と注意
		 * サブパッケージからのルーティングを可能にしたため、Router::fetch_directory()の値の正当性は保証されない。
		 * そこで、別途fetch_directory_reg()というメソッドを定義している。
		 * サブパッケージからのルーティングの場合、is_packaged_directoryというプロパティがTRUEになるので、
		 * 擬似的にapplication/controllers/以下にルーティングしたかのように見せるには条件判定が必要になる。
		 * これは、seezooのページパス管理がapplication/controllers/以下のパスで管理する設計になっているため。
		 */
		$class = ( $this->router->fetch_class() == $this->router->default_controller )
						? ''
						: $this->router->fetch_class();

		if ( $this->router->is_packaged_directory )
		{
			$class_path = rtrim($this->router->fetch_directory_reg() . $class, '/');
		}
		else
		{
			$class_path = rtrim($this->router->fetch_directory() . $class, '/');
		}

		// if class path in ignores, return
		if ( in_array($class_path, $this->ignore_controllers) || !class_exists($this->router->fetch_class()) )
		{
			return;
		}

		$admin = $this->permission_model->is_admin($this->user_id);

		// access class is dashboard?
		$is_dashboard = (strpos($class_path, 'dashboard') !== FALSE) ? TRUE : FALSE;

		// get permission_data
		$permission   = $this->process_model->get_page_permission_of_ci_controller($class_path);

		// if permission is not defiend, show 404.
		if ( ! $permission )
		{
			show_404();
		}
		// if user is master user or has admin_permission, permission is always success!
		if ( $admin === FALSE )
		{
			// check_permission is allowed?
			if ( ! has_permission($permission->allow_access_user, $this->user_id)
					|| ($this->member_id > 0 && ! has_permission($permission->allow_access_user, 'm')) )
			{
				// permission denied...
				// if access class is dashboard/*, redirect login page
				if ( $is_dashboard )
				{
					redirect(SEEZOO_SYSTEM_LOGIN_URI);
				}
				// else, redirect to permission denied page
				else
				{
					redirect('page/permission_denied');
				}
			}
		}
		
		

		// if access class is dashboard/*, load necessary extensions
		if ( $is_dashboard )
		{
			$this->load->helper('dashboard_helper');
		}

		// set some parameters
		$this->is_maintenance_mode = $this->dashboard_model->is_maintenance_mode();
		$this->page_id             = $permission->page_id;
		$this->parent_id           = $permission->parent;
		$this->is_admin            = $admin;
		$this->is_login            = ( (int)$this->user_id > 0 ) ? TRUE : FALSE;

		// and set output header
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');
	}

	/**
	 * _check_is_installed
	 * インストールされているかどうかチェック
	 */
	function _check_is_installed()
	{
		$this->load->helper(array('seezoo_install_helper'));
		$this->load->model('install_model');
		// 未インストール時の処理
		// ・セッションにDBを使わない
		// ・installコントローラが呼ばれていなければ、installコントローラへリダイレクト
		if ( ! $this->install_model->check_is_installed(APPPATH . 'config') )
		{
			$this->config->set_item('sess_use_database', FALSE);

			if ( $this->router->fetch_class() !== 'install' )
			{
				redirect(get_seezoo_uri() . 'index.php/install');
			}
		}
	}

	/**
	 * generare_cms_mode : ロードされたコントローラにCMSで必要なプロパティをセットする
	 */
	function generate_cms_mode()
	{
		// attach public property
		$this->cms_mode = TRUE;

		$dir   = $this->router->fetch_directory_reg();
		$class = $this->router->fetch_class();

		$this->page_id  = $this->init_model->get_page_id_from_page_path($dir . $class);

		$this->load->model('page_model');
		// is_maintenance?
		if ( (int)$this->site_data->is_maintenance === 1 )
		{
			redirect('page/maintenance');
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
					$this->session->set_flashdata('sz_unlock_token', $token);
					$this->is_edit_timeout = TRUE;
				}
				else if ( $this->is_master === TRUE )
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
				$this->enable_cache = $this->page_model->is_enable_cache();
			}
		}

		// get current_page_version
		$page_data = $this->page_model->get_page_object($this->page_id, $ver_mode);

		$this->version_number = $page_data['version_number'];
		$this->is_ssl_page   = ($page_data['is_ssl_page'] > 0) ? TRUE : FALSE;

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
				redirect('page/mobile_only');
			}
			$this->page_is_mobile = TRUE;
		}

		// Does template advance CSS exists?
		$this->is_advance_css = $this->page_model->has_advance_css(intval($page_data['template_id']));
		$this->template_id    = intval($page_data['template_id']);

		// set template path absolute, relative
		$p_path  = $this->template_path       = $this->_rel_template_path = $page_data['template_path'];
		$page_data['template_path']           = (( $this->input->server('SERVER_PORT') == 443 ) ? $this->site_data->ssl_base_url : file_link()) . 'templates/' . $p_path; // absolute
		$page_data['_relative_template_path'] = $this->relative_template_path = FCPATH . 'templates/' . $p_path; // relative

		// assign to stack property
		$this->page_data = $page_data;

		// Is page mobile_only and view_mobile.php file exists?
		if ( $this->page_is_mobile === TRUE && file_exists($p_path . 'view_moble.php') )
		{
			$path = $p_path . 'system_view_mobile';
		}
		else
		{
			$path = $p_path . 'system_view';
			$this->page_is_mobile = FALSE; // view.php encoding is UTF-8 for PC output
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
			if ($permissions->allow_access_user !== ''
					&& strpos($permissions->allow_access_user, $uid) === FALSE)
			{
				if ( $this->member_id < 0 || strpos($permissions->allow_acess_user, ':m:') === FALSE )
				{
					// 権限が無ければpermission_denied
					redirect('page/permission_denied');
				}
			}
			$this->can_edit = ( strpos($permissions->allow_edit_user, $uid) === FALSE ) ? FALSE : TRUE;
		}
	}

	/**
	 * add_header_item
	 * アウトプットヘッダーに追加
	 */
	function add_header_item($str)
	{
		if ( preg_match('/^<[script]/', $str) )
		{
			$this->additional_header_javascript[] = $str;
		}
		else if ( preg_match('/^<[style|link|meta]/', $str) )
		{
			$this->additional_header_css[] = $str;
		}
	}

	/**
	 * add_footer_item
	 * アウトプットフッターに追加
	 */
	function add_footer_item($str)
	{
		if ( preg_match('/^<[script]/u', $str) )
		{
			$this->additional_footer_javascript[] = $str;
		}
		else
		{
			$this->additional_footer_element[] = $str;
		}
	}

	// 出力フック(デフォルト)
	function _output($output)
	{
		// CMSモードがオンでなければ即return
		if ( ! $this->cms_mode )
		{
			return $output;
		}
		// 各ブロックで必要なJS、CSSをヘッダに追加
		$replace_header = '';
		$replace_footer = '';

		// header items
		if ( count($this->additional_header_javascript) > 0 )
		{
			$replace_header .= implode("\n", $this->additional_header_javascript) . "\n";
		}
		if ( count($this->additional_header_css) > 0 )
		{
			$replace_header .= implode("\n", $this->additional_header_css);
		}

		// footer items
		if ( count($this->additional_footer_element) > 0 )
		{
			$replace_footer .= implode("\n", $this->additional_footer_element) . "\n";
		}
		if ( count($this->additional_footer_javascript) > 0 )
		{
			$replace_footer .= implode("\n", $this->additional_footer_javascript) . "\n";
		}

		$this->load->library('parser');
		$statics = simplexml_load_file(APPPATH . '/libraries/statics/statics.xml');

		foreach ($statics as $v)
		{
			$template_vars[(string)$v->name] = object_to_array($v);
		}

		$template_vars['OUTPUT_HEADERS'] = $replace_header;
		$template_vars['OUTPUT_FOOTERS'] = $replace_footer;

		$mobile = Mobile::get_instance();

		// edit menu replaces <body>.
		// load edit menu
		if ( $this->is_login === TRUE )
		{
			// get edit menu position from cookie tht created from JavaScript
			$this->load->helper('cookie_helper');
			$x = get_cookie('flCookie_x');
			$y = get_cookie('flCookie_y');

			$ae['menu_x']      = ($x) ? substr($x, 0, strpos($x, '&d')) : 50;
			$ae['menu_y']      = ($y) ? substr($y, 0, strpos($y, '&d')) : 50;
			$ae['advance_css'] = $this->page_data['advance_css'];
			$ae['template_id'] = $this->page_data['template_id'];
			$edit_menu   = $this->load->view('parts/edit_menu', $ae, TRUE);

			$output = str_replace('</body>', $edit_menu . "\n</body>" , $output);
		}

		// 2010/08/18 modified google Analytics tracking code put on before </head>.
		if ( ! $this->is_login )
		{
			//require_once APPPATH . 'libraries/mobile_ip.php';
			//MobileConfig::set_cache_dir(BASEPATH . 'cache/');
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
			//$output = mb_convert_kana($output, 'k', 'UTF-8');
			$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		}

		// Does page cache is enabled?
		if ( $this->enable_cache == TRUE )
		{
			$this->output->cache(60);
		}

		return $output;
	}

	/**
	 * OGP文字列挿入
	 * @param string $output
	 */
	protected function _generate_ogp_string($output)
	{
		SeezooOptions::init('common');
		$ogp = SeezooOptions::get('ogp_data');
		if ( ! $ogp || $ogp->is_enable < 1 )
		{
			return $output;
		}
		// generate string
		$meta_format = '<meta property="og:%s" content="%s" />';
		$url = ( $this->page_data['is_ssl_page'] > 0 )
		         ? preg_replace('#\Ahttp:#u', 'https:', current_url())
		         : current_url();
		$ogp_string = array(
			sprintf($meta_format, 'title', $this->page_data['page_title']),
			sprintf($meta_format, 'type', $ogp->site_type),
			sprintf($meta_format, 'url', $url)
		);
		if ( $ogp->file_id > 0 )
		{
			$ogp_string[] = sprintf($meta_format, 'image', file_link() . get_file($ogp->file_id, TRUE));
		}
		if ( ! empty($ogp->extra) )
		{
			$ogp_string[] = $ogp->extra;
		}

		// replace and add ogp namespace
		$output = preg_replace('/<html([^>]*)>/u', '<html$1' . ' xmlns:og="http://ogp.me/ns#">', $output);
		$output = preg_replace('/<title>/u', implode("\n", $ogp_string) . "\n\n<title>", $output);
		return $output;
	}

	/**
	 * ガラケー用に<body>の横幅調整
	 * @param string $output
	 * @param int $width
	 */
	protected function set_body_width_for_mobile_preview($output, $width = 0)
	{
		if ( $width > 0 )
		{
			$output = preg_replace(
							'/<body([^>]*)>/u',
							'<body$1 style="overflow:auto;border:solid 2px #333;width:' . $width . 'px">',
							$output
						);
		}
		return $output;
	}

	/**
	 * システムページのsitemap.xml生成インデックス生成
	 * 対象のコントローラでセグメント別に複数ページがある場合にはoverrideしてください。
	 * @param none
	 * @return FALSE
	 */
	public static function sitemap_index()
	{
		return FALSE;
	}

}
