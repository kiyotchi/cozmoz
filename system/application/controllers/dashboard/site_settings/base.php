<?php
/**
 * ===============================================================================
 *
 * Seezoo dashboard ベース設定コントローラ
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */

class Base extends SZ_Controller
{
	public $msg = '';

	public static $page_title = 'サイト運用設定';
	public static $description = 'サイトのタイトルや全体の設定を行います。';
	
	protected $_msg = array(
		'SITE_SETTING_SUCCESS'   => 'サイト設定を更新しました。',
		'SITE_SETTING_ERROR'     => 'サイト設定の更新に失敗しました。',
		'OGP_SETTING_SUCCESS'    => 'OGP設定を更新しました。',
		'OGP_SETTING_ERROR'      => 'OGP設定の更新に失敗しました。',
		'MOBILE_SETTING_SUCCESS' => 'モバイルキャリア設定を更新しました。',
		'MOBILE_SETTING_ERROR'   => 'モバイルキャリア設定の更新に失敗しました。'
	);

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
	}

	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$data->site = $this->dashboard_model->get_site_info();
		$data->ticket = $this->_set_ticket();
		//$data->rewrite_enable = $this->dashboard_model->is_loaded_mod_rewrite();
		if ( $this->config->item('cgi_mode') === TRUE 
				&& $this->config->item('has_path_info') !== FALSE )
		{
			$path         = './files/data/mod_rewrite_cgi.txt';
			$rewrite_base = trim(str_replace($this->input->server('DOCUMENT_ROOT'), '', FCPATH), '/');
			$fragment     = 'QSA,L';
		}
		else
		{
			$path         = './files/data/mod_rewrite.txt';
			$rewrite_base = '';
			$fragment     = 'L';
		}
		
		$rewrite_txt     = file_get_contents($path);
		$grep            = array('{REWRITE_BASE}', '{FRAGMENT}');
		$sed             = array('/' . $rewrite_base, $fragment);
		
		$data->rewrite_txt  = str_replace($grep, $sed, $rewrite_txt);
		$data->log_lists    = $this->_generate_logging_level_list();
		$data->debug_level  = $this->_generate_debug_level_list();
		$data->ogp_setting  = $this->dashboard_model->get_ogp_settings();
		$data->ogp_types    = $this->_generate_ogp_type_list();
		
		// mobile analytics judge
		if ( ! empty($data->site->google_analytics)
		     && $data->site->enable_mobile > 0
		     && ! file_exists(FCPATH . 'ga.php') )
		{
			$data->mobile_ga_notify = TRUE;
		}
		else
		{
			$data->mobile_ga_notify = FALSE;
		}
		
		// maintenance_mode
		$this->is_maintenance_mode = ( $data->site->is_maintenance > 0 )
		                             ? TRUE
		                             : FALSE;
		// set message
		$msg_flag  = $this->session->flashdata('site_setting_status');
		$data->msg = ( $msg_flag && isset($this->_msg[$msg_flag]) )
		              ? $this->_msg[$msg_flag]
		              : $this->msg;

		$this->_validation();

		$this->load->view('dashboard/site_settings/index', $data);
	}

	/**
	 * mod_rewrite設定
	 */
	function set_mod_rewrite_flag($flag = 0, $token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$ret = $this->dashboard_model->change_mod_rewrite_flag((int)$flag);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * サイトキャッシュ削除
	 */
	function delete_site_cache()
	{
		$this->load->model('page_model');
		$ret = $this->page_model->delete_site_cache_all();

		if ($ret)
		{
			exit('complete');
		}
		else
		{
			exit('error');
		}
	}

	/**
	 * サイトキャッシュ設定変更
	 */
	function change_site_cache($type, $token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$ret = $this->dashboard_model->change_site_cache((int)$type);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}
	
	/**
	 * ロギングレベル更新
	 * @param $token
	 */
	function update_log_level($token)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			show_error('access_denied');
		}
		
		$level = (int)$this->input->post('level');
		$ret = $this->dashboard_model->update_logging_level($level);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}
	
	/**
	 * デバッグレベル更新
	 * @param $token
	 */
	function update_debug_level($token)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			show_error('access_denied');
		}
		
		$level = (int)$this->input->post('level');
		$ret = $this->dashboard_model->update_debug_level($level);
		echo ($ret) ? 'complete' : 'error';
	}
		

	/**
	 * サイト情報更新
	 */
	function update()
	{
		$this->_check_ticket();

		$this->_validation();

		if ($this->form_validation->run() === FALSE)
		{
			$this->msg = $this->form_validation->error_string;
			$this->index();
			return;
		}
		$post = array(
			'site_title'       => $this->input->post('site_title'),
			'google_analytics' => $this->input->post('google_analytics'),
			'is_maintenance'   => (int)$this->input->post('is_maintenance'),
			'system_mail_from' => $this->input->post('system_mail_from')
		);

		$res = $this->dashboard_model->update_site_info($post);

		$this->session->set_flashdata('site_setting_status', ( $res ) ? 'SITE_SETTING_SUCCESS' : 'SITE_SETTING_ERROR');
		redirect('dashboard/site_settings/base');
//		if ($res)
//		{
//			$this->msg = 'サイト設定を更新しました。';
//			if ($post['is_maintenance'] === 1)
//			{
//				$this->is_maintenance_mode = TRUE;
//			}
//			else
//			{
//				$this->is_maintenance_mode = FALSE;
//			}
//			$this->index();
//		}
//		else
//		{
//			exit('データベースエラー');
//		}
	}
	
	/**
	 * OGP設定の更新
	 */
	function update_ogp()
	{
		$this->_check_ticket();

		$this->_validation();

		if ($this->form_validation->run() === FALSE)
		{
			$this->msg = $this->form_validation->error_string();
			$this->index();
			return;
		}
		$post = array(
			'is_enable' => (int)$this->input->post('enable_ogp'),
			'site_type' => set_value('site_type'),
			'file_id'   => (int)$this->input->post('file_id'),
			'extra'     => set_value('extra')
		);

		$res = $this->dashboard_model->update_ogp_setting($post);

		$this->session->set_flashdata('site_setting_status', ( $res ) ? 'OGP_SETTING_SUCCESS' : 'OGP_SETTING_ERROR');
		redirect('dashboard/site_settings/base');
	}
	
	/**
	 * モバイル有効設定更新
	 */
	function update_enable_carrier()
	{
		$this->_check_ticket();
		
		$post = array(
			'enable_mobile'     => (int)$this->input->post('enable_mobile'),
			'enable_smartphone' => (int)$this->input->post('enable_smartphone'),
		);
		
		$res = $this->dashboard_model->update_mobile_enables($post);
		
		$this->session->set_flashdata('site_setting_status', ( $res ) ? 'MOBILE_SETTING_SUCCESS' : 'MOBILE_SETTING_ERROR');
		redirect('dashboard/site_settings/base#tab_content4');
	}

	/**
	 * faviconアップロードフォームセットアップ
	 */
	function favicon_upload()
	{
		$this->load->view('dashboard/site_settings/favicon_upload_form');
	}

	/**
	 * faviconアップロード実行
	 */
	function do_favicon_upload()
	{
		// load upload library
		$this->load->library('upload');

		$config = array(
			'upload_path'		=> 'files/favicon/',
			'allowed_types'	=> 'ico',
			'overwrite'		=> TRUE,
			'encrypt_name'	=> FALSE,
			'file_name'		=> 'favicon.ico'
		);

		$this->upload->initialize($config);

		// try uplaod
		$result = $this->upload->do_upload('upload_data');

		if (!$result)
		{
			$data['error'] = $this->upload->display_errors('', '');
		}
		else
		{
			$info = $this->upload->data();

			// if image width/height over 16px, resize correct size
			if ($info['image_width'] > 16 || $info['image_height'] > 16)
			{
				$cvt = $this->_convert_favicon($info);
				if (!$cvt)
				{
					$data['error'] = 'ファイルのコンバートに失敗しました。';
				}
			}
			// if file extension is not .ico, rename this file
			if ($info['file_name'] != 'favicon')
			{
				if (rename($info['full_path'], 'files/favicon/favicon.ico'))
				{
					$data['success'] = 1;
				}
				else
				{
					$data['error'] = 'ファイルのリネームに失敗しました。';
				}
			}

		}
		$this->load->view('dashboard/site_settings/favicon_upload_form', $data);
	}

	// convert favicon
	function _convert_favicon($data)
	{
		$conf = array(
			'source_image'		=> $data['full_path'],
			'create_thumb'		=> FALSE,
			'new_image'			=> 'files/favicon/',
			'thumb_marker'		=> '',
			'width'				=> 16,
			'height'				=> 16,
			'maintain_ratio'		=> TRUE
		);

		// load image_lib library and initialize
		$this->load->library('image_lib', $conf);

		if (! $this->image_lib->resize())
		{
			//echo $this->image_lib->display_errors();
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * faviconの削除
	 */
	function delete_favicon()
	{
		$ret = @unlink(FCPATH . 'files/favicon/favicon.ico');
		echo ($ret) ? 'complete' : 'error';
		exit;
	}
	
	/**
	 * ユーザー登録受付フラグ更新
	 * @param $mode
	 */
	function update_accept_registration($mode = 0)
	{
		$this->dashboard_model->update_accept_registration($mode);
		redirect('dashboard/site_settings/base#tab_content4');
	}

	/**
	 * トークンセット
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('setting_token', $ticket);

		return $ticket;
	}

	/**
	 * トークンチェック
	 */
	function _check_ticket()
	{
		$ticket = $this->input->post('ticket');
		if (!$ticket || $ticket !== $this->session->userdata('setting_token'))
		{
			exit('不正なデータ送信がありました。処理を中断します。');
		}
	}

	/**
	 * バリデーションセット
	 * @access private
	 */
	function _validation()
	{
		$this->load->library('form_validation');
		
		if ( $this->input->post('update_ogp') )
		{
			$conf = array(
				array(
					'field' => 'enable_ogp',
					'label' => 'OGP有チェック',
					'rules' => 'trim|numeric'
				),
				array(
					'field' => 'site_type',
					'label' => 'サイトのタイプ',
					'rules' => 'trim|alpha_numeric'
				),
				array(
					'field' => 'file_id',
					'label' => 'OGP用の画像',
					'rules' => 'trim|numeric'
				),
				array(
					'field' => 'extra',
					'label' => '追加OGPタグ',
					'rules' => 'trim|max_length[1000]'
				)
			);
		}
		else
		{
			$conf = array(
				array(
					'field' => 'site_title',
					'rules' => 'trim|required|max_length[255]',
					'label' => 'サイトタイトル'
				),
				array(
					'field' => 'system_mail_from',
					'rules' => 'trim|valid_email|max_length[255]',
					'label' => 'システムメールアドレス'
				)
			);
		}

		$this->form_validation->set_rules($conf);
	}
	
	/**
	 * ログレベルリスト生成
	 */
	function _generate_logging_level_list()
	{
		$levels = array(
			0 => '低負荷設定',
			1 => '運用レベル',
			2 => '開発レベル'
		);
		
		$messages = array(
			0 => '負荷軽減のため、ログを保存しません。',
			1 => '404ページとメールの送信ログを保存します。',
			2 => 'エラーやメール送信、404ページのログを保存します。'
		);
		
		return array('level' => $levels, 'message' => $messages);
	}
	
	/**
	 * デバッグレベルリスト生成
	 */
	function _generate_debug_level_list()
	{
		$levels = array(
			0 => '運用レベル',
			1 => '開発レベル1',
			2 => '開発レベル2'
		);
		
		$messages = array(
			0 => 'PHP/DBエラー表示なし、システムプロファイラもオフになります。',
			1 => 'PHP/DBエラーは表示しますが、システムプロファイラはオフになります。',
			2 => 'PHP/DBエラーを表示し、システムプロファイラもオンになります。',
		);
		
		return array('level' => $levels, 'message' => $messages);
	}
	
	function _generate_ogp_type_list()
	{
		$types = array(
						'Websites'                   => array('website', 'blog', 'article'),
						'Activities'                 => array('activity', 'sport'),
						'Businesses'                 => array('bar', 'company', 'cafe', 'hotel', 'restaurant'),
						'Groups'                     => array('cause', 'sports_league', 'sports_team'),
						'Organizations'              => array('band', 'government', 'non_profit', 'school', 'university'),
						'People'                     => array('actor', 'athlete', 'author', 'director', 'musician', 'politician', 'profile', 'public_figure'),
						'Places'                     => array('city', 'country', 'landmark', 'state_province'),
						'Products and Entertainment' => array('album', 'book', 'drink', 'food', 'game', 'movie', 'product', 'song', 'tv_show')
					);
		$ret = array();
		foreach ( $types as $key => $value )
		{
			$ret[$key] = array();
			foreach ( $value as $v )
			{
				$ret[$key][$v] = $v;
			}
		}
		return $ret;
	}
}
