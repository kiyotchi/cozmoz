<?php
/**
 * ========================================================================================
 * 
 * Seezoo member login Controller
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */

/**===============================================================
 * ログイン後のリダイレクトページ設定
 * 空値の場合はトップページにリダイレクトします。
 * =============================================================== */

define('SZ_MEMBER_LOGIN_REDIRECT_PAGE', 'profile');

/* ============================================================== */


class Member_login extends SZ_Controller
{
	public static $page_title = 'メンバーログイン';
	public static $description = '登録ユーザーのログイン処理を行ないます。';
	
	public $view_path = 'member_login/';
	public $ticket_name = 'sz_member_login_token';
	public $ticket;
	
	// ========== Twitterログイン用設定 ======================= //
	// 使用する場合はパラメータを設定してください。空の場合は処理が行われません。
	private $twitter_consumer_key    = '';
	private $twitter_consumer_secret = '';
	
	
	// ========== Facebookログイン用設定 ======================= //
	// 使用する場合はパラメータを設定してください。空の場合は処理が行われません。
	private $facebook_application_id     = '';
	private $facebook_application_secret = '';
	
	
	// ========== Googleログイン用設定 ======================= //
	// 使用する場合はパラメータを設定してください。空の場合は処理が行われません。
	private $google_client_id     = '';
	private $google_client_secret = '';
	
	
	
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('member_model');
		
		$this->generate_cms_mode();
		$this->add_header_item(build_javascript('js/member_login.js'));
		$this->add_header_item(build_css('css/member_login.css'));
		
		// no cache
		$this->output->set_header('Cache-Control: no-store, no-cahce, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		
		if ( ! is_dir(FCPATH . 'files/members') )
		{
			@mkdir(FCPATH . 'files/members');
		}
		
		// flag check
		if ( ! isset($this->site_data->is_accept_member_registration)
				|| $this->site_data->is_accept_member_registration < 1 )
		{
			show_404();
		}
		
	}
	
	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$this->_set_ticket();
		$this->_validation(FALSE);
				
		if ( $this->session->flashdata('sz_member_login_forgotten_result') )
		{
			$data->complete_msg = ((int)$this->session->flashdata('sz_member_login_forgotten_result') === 1)
			                          ? 'メールを送信しました。新しいパスワードを確認後、再度ログインしてください。'
			                          : 'メールの送信に失敗しました。お手数ですが時間をおいて再度お試しください。';
		}
		else 
		{
			$data->complete_msg = FALSE; 
		}
		
		$data->enable_twitter_login  = $this->_is_enable_twitter_login();
		$data->enable_facebook_login = $this->_is_enable_facebook_login();
		$data->enable_google_login = $this->_is_enable_google_login();
		
		$this->content_data = $this->load->view($this->view_path . 'member_login', $data, TRUE);
		
		$this->render_view('system');
	}
	
	/**
	 * メンバーログイン実行
	 */
	function do_member_login()
	{
		$this->_ticket_check();
		
		$this->_validation(FALSE);
		$this->session->keep_flashdata($this->ticket_name);
		
		$this->ticket = $this->input->post($this->ticket_name);
		if ($this->form_validation->run() === FALSE)
		{
			$data->enable_twitter_login  = $this->_is_enable_twitter_login();
			$data->enable_facebook_login = $this->_is_enable_facebook_login();
			$data->enable_google_login = $this->_is_enable_google_login();
			$this->content_data = $this->load->view($this->view_path . 'member_login', $data, TRUE);
			$this->render_view('system');
		}
		else
		{
			$this->_login_process();
		}
	}
	
	/**
	 * Googleでのログイン
	 */
	function google_login()
	{
		if ( ! $this->_is_enable_google_login() )
		{
			show_404();
		}
		$this->load->library('oauth/google');
		$this->google->initialize(array(
			'client_id'     => $this->google_client_id,
			'client_secret' => $this->google_client_secret
		));
		$this->google->set_callback(page_link('member_login/google_login_callback'));
		if ( $this->google->is_authorized() )
		{
			$this->_login_google_process();
		}
		else
		{
			$this->google->auth();
		}
	}
	
	/**
	 * Googleのログイン実行
	 */
	function google_login_callback()
	{
		if ( ! $this->_is_enable_google_login() )
		{
			show_404();
		}
		$this->load->library('oauth/google');
		$this->google->initialize(array(
			'client_id'     => $this->google_client_id,
			'client_secret' => $this->google_client_secret
		));
		
		$this->google->set_callback(page_link('member_login/google_login_callback'));
		
		if ( ! $this->google->auth() )
		{
			exit($this->google->display_errors());
		}
		$this->_login_google_process();
	}
	
	
	
	/**
	 * Facebookでのログイン
	 */
	function facebook_login()
	{
		if ( ! $this->_is_enable_facebook_login() )
		{
			show_404();
		}
		$this->load->library('oauth/facebook');
		$this->facebook->initialize(array(
			'application_id'     => $this->facebook_application_id,
			'application_secret' => $this->facebook_application_secret
		));
		$this->facebook->set_callback(page_link('member_login/facebook_login_callback'));
		
		if ( $this->facebook->is_authorized() )
		{
			$this->_login_facebook_process();
		}
		else
		{
			$this->facebook->auth();
		}
	}
	
	/**
	 * Facebookのログイン実行
	 */
	function facebook_login_callback()
	{
		if ( ! $this->_is_enable_facebook_login() )
		{
			show_404();
		}
		$this->load->library('oauth/facebook');
		$this->facebook->initialize(array(
			'application_id'     => $this->facebook_application_id,
			'application_secret' => $this->facebook_application_secret
		));
		
		$this->facebook->set_callback(page_link('member_login/facebook_login_callback'));
		if ( ! $this->facebook->auth() )
		{
			exit($this->facebook->display_errors());
		}
		$this->_login_facebook_process();
	}
	
	/**
	 * Twitterでログイン
	 */
	function twitter_login()
	{
		if ( ! $this->_is_enable_twitter_login() )
		{
			show_404();
		}
		$this->load->library('oauth/twitter');
		$this->twitter->initialize(array(
			'consumer_key'    => $this->consumer_key,
			'consumer_secret' => $this->consumer_secret
		));
		
		$this->twitter->set_callback(page_link('member_login/twitter_login_callback'));
		
		if ( $this->twitter->is_authorized() )
		{
			$this->_login_twitter_process();
		}
		else
		{
			$this->twitter->auth();
		}
	}
	
	/**
	 * Twitterのログイン実行
	 */
	function twitter_login_callback()
	{
		if ( ! $this->_is_enable_twitter_login() )
		{
			show_404();
		}
		$this->load->library('twitter');
		$this->twitter->initialize(array(
			'consumer_key'    => $this->consumer_key,
			'consumer_secret' => $this->consumer_secret
		));
		
		if ( ! $this->twitter->auth() )
		{
			show_404();
		}
		$this->_login_twitter_process();
	}
	
	/**
	 * Twitter取得データ更新
	 */
	function _login_twitter_process()
	{
		if ( ! $this->twitter->is_authorized() )
		{
			return FALSE;
		}
		$items        = $this->twitter->get_all_items();
		$account_data = $this->twitter->get_member_data($items['user_id']);
		
		// DBに必要なデータを抽出
		$member_data = array(
			'nick_name'       => $account_data->screen_name,
			'image_data'      => str_replace('_normal', '_reasonably_small', $account_data->profile_image_url),
			'twitter_id'      => $items['user_id'],
			'email'           => '',
			'password'        => '',
			'hash'            => '',
			'activation_code' => ''
		);
		
		$this->twitter->unset_tokens();
		
		$member_id = (int)$this->member_model->twitter_login($member_data);
		if ( (int)$member_id > 0 )
		{
			$this->session->set_userdata('member_id', $member_id);
			$data->result = TRUE;
		}
		else 
		{
			$data->result = FALSE;
		}
		$this->load->view('member_login/oauth_login_end', $data);
	}
	
	/**
	 * Facebook取得データ更新
	 */
	function _login_facebook_process()
	{
		if ( ! $this->facebook->is_authorized() )
		{
			return FALSE;
		}
		$items        = $this->facebook->get_all_items();
		$account_data = $this->facebook->get_member_data();
		
		// DBに必要なデータを抽出
		$member_data = array(
			'nick_name'       => $account_data->name,
			'image_data'      => $account_data->image,
			'facebook_id'     => $account_data->id,
			'email'           => '',
			'password'        => '',
			'hash'            => '',
			'activation_code' => ''
		);
		
		$this->facebook->unset_tokens();
		
		$member_id = (int)$this->member_model->facebook_login($member_data);
		if ( (int)$member_id > 0 )
		{
			$this->session->set_userdata('member_id', $member_id);
			$data->result = TRUE;
		}
		else 
		{
			$data->result = FALSE;
		}
		$this->load->view('member_login/oauth_login_end', $data);
	}
	
	/**
	 * Google取得データ更新
	 */
	function _login_google_process()
	{
		if ( ! $this->google->is_authorized() )
		{
			return FALSE;
		}
		//$items        = $this->facebook->get_all_items();
		$account_data = $this->google->get_member_data();

		// DBに必要なデータを抽出
		$member_data = array(
			'nick_name'       => $account_data->name,
			'image_data'      => $account_data->picture,
			'google_id'       => $account_data->id,
			'email'           => '',
			'password'        => '',
			'hash'            => '',
			'activation_code' => ''
		);
		
		$this->google->unset_tokens();
		
		$member_id = (int)$this->member_model->google_login($member_data);
		if ( (int)$member_id > 0 )
		{
			$this->session->set_userdata('member_id', $member_id);
			$data->result = TRUE;
		}
		else 
		{
			$data->result = FALSE;
		}
		$this->load->view('member_login/oauth_login_end', $data);
	}
	
	/**
	 * パスワード再発行メソッド
	 * @access public
	 */
	function forgotten_password()
	{
		$this->_ticket_check();
		$this->_validation(TRUE);
		$this->session->keep_flashdata($this->ticket_name);

		$this->ticket = $this->input->post($this->ticket_name);
		if ($this->form_validation->run() === FALSE)
		{
			$this->_set_ticket();
			$this->content_data = $this->load->view($this->view_path . 'member_login', array('forgotten_error' => TRUE), TRUE);
			$this->render_view('system');
		}
		else
		{
			if ($this->_send_forgotten_mail())
			{
				$v = 1;
			}
			else
			{
				$v = 2;
			}
			$this->session->set_flashdata('sz_member_login_forgotten_result', $v);
			redirect('member_login');
		}
	}
	
	/**
	 * パスワード再発行入力画面
	 * @param $code
	 */
	function re_password($code)
	{
		$exec = $this->member_model->has_rebuild_password_activation($code);
		if ( $exec === 'over' )
		{
			$data = array(
				'heading' => '無効なリクエストです',
				'msg'     => 'パスワード再発行期間を過ぎています。お手数ですが再度申請してください。' 
			); 
			$this->content_data = $this->load->view($this->view_path . 'end', $data, TRUE);
		}
		else if ( is_object($exec) )
		{
			$exec->ticket   = $this->_set_ticket();
			$this->_reset_pass_validation();
			$this->content_data = $this->load->view($this->view_path . 'new_password_input', $exec, TRUE);
		}
		else
		{
			show_404();
		}
		
		$this->render_view('system');
	}
	
	function do_reset_password($code = '')
	{
		if ( empty($code) )
		{
			show_404();
		}
		$this->_ticket_check();
		$this->_reset_pass_validation();
		
		if ( ! $this->form_validation->run() )
		{
			$data = $this->member_model->has_rebuild_password_activation($code);
			$data->ticket = $this->_set_ticket();
			$this->content_data = $this->load->view($this->view_path . 'new_password_input', $data, TRUE);
		}
		else
		{
			$this->load->helper('string');
			$this->load->model('dashboard_model');
	
			$new_password = $this->input->post('new_password');
	
			$pass   = $this->dashboard_model->enc_password($new_password);
			$member = $this->member_model->has_rebuild_password_activation($code); 
			$ret  = $this->member_model->update_new_password($member->sz_member_id, $pass);
			
			if ( ! $ret )
			{
				$data = array(
					'heading' => 'パスワードの保存に失敗しました',
					'msg'     => 'パスワードの保存に失敗しました。お手数ですが再度お試しください。' 
				); 
				$this->content_data = $this->load->view($this->view_path . 'end', $data, TRUE);
			}
			else 
			{
				if ( $this->_send_reset_password_mail($member) )
				{
					$this->content_data = $this->load->view($this->view_path . 'reset_password_complete', $member, TRUE);
				}
				else
				{
					$data = array(
						'heading' => '確認メールの送信に失敗しました',
						'msg'     => '確認メールの送信ができませんでした。パスワードの変更は完了しましたので、新しいパスワードでログインしてください。' 
					); 
					$this->content_data = $this->load->view($this->view_path . 'end', $data, TRUE);
				}
			}
		}
		
		$this->render_view('system');
	}
	
	/**
	 * ログイン用ワンタイムトークン生成
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata($this->ticket_name, $ticket);
		$this->ticket = $ticket;
	}

	/**
	 * ワンタイムトークンチェック
	 */
	function _ticket_check()
	{
		$ticket = $this->input->post($this->ticket_name);
		if (!$ticket || $ticket !== $this->session->userdata($this->ticket_name))
		{
			exit('クッキーを許可してください。');
		}
	}
	
	/**
	 * Twitterでのログインが可能かどうか
	 * @return bool
	 */
	function _is_enable_twitter_login()
	{
		return ( ! empty($this->twitter_consumer_key) && ! empty($this->twitter_consumer_secret) )
		          ? TRUE
		          : FALSE;
	}
	
	/**
	 * Facebookでのログインが可能かどうか
	 * @return bool
	 */
	function _is_enable_facebook_login()
	{
		return ( !empty($this->facebook_application_id) && !empty($this->facebook_application_secret) )
		         ? TRUE
		         : FALSE;
	}
	
	/**
	 * Googleでのログインが可能かどうか
	 * @return bool
	 */
	function _is_enable_google_login()
	{
		return ( !empty($this->google_client_id) && !empty($this->google_client_secret) )
		         ? TRUE
		         : FALSE;
	}
	
	/**
	 * バリデーションセット
	 * @access private
	 * @param $is_forgotten
	 */
	function _validation($is_forgotten = FALSE)
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->form_validation->set_message('valid_email', 'メールアドレスの形式が正しくありません。');

		$conf = array(
			array(
				'field'			=> 'member_name',
				'rules'			=> ($is_forgotten) ? '' : 'trim|required',
				'label'			=> 'ユーザー名'
			),
			array(
				'field'			=> 'password',
				'rules'			=> ($is_forgotten) ? '' : 'trim|required',
				'label'			=> 'パスワード'
			),
			array(
				'field'			=> 'forgotten_email',
				'rules'			=> ($is_forgotten) ? 'trim|required|valid_email|max_length[255]|callback_already' : '',
				'label'			=> 'メールアドレス'
			)
		);
		$this->form_validation->set_rules($conf);
	}
	
	/**
	 * パスワード再発行用バリデーション
	 */
	function _reset_pass_validation()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');

		$conf = array(
			array(
				'field'			=> 'new_password',
				'rules'			=> 'trim|required',
				'label'			=> '新しいパスワード'
			),
			array(
				'field'			=> 'new_password_confirm',
				'rules'			=> 'trim|required|matches[new_password]',
				'label'			=> '新しいパスワード（確認）'
			)
		);
		$this->form_validation->set_rules($conf);
	}
	
	/**
	 * 独自バリデーション：メールアドレスが存在するかどうかチェック
	 * @param string $str
	 */
	function already($str)
	{
		$is = $this->member_model->is_email_already_exists($str, FALSE);
		if (!$is)
		{
			$this->form_validation->set_message('already', '入力されたメールアドレスは存在しません。');
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * ログイン実行
	 * @access private
	 */
	function _login_process()
	{
		$uid = $this->input->post('member_name');
		$pass = $this->input->post('password');

		$res = $this->member_model->login($uid, $pass);
		if ($res)
		{
			if ( ! defined('SZ_MEMBER_LOGIN_REDIRECT_PAGE') || SZ_MEMBER_LOGIN_REDIRECT_PAGE == '')
			{
				$path = '/';
			}
			else
			{
				$path = SZ_MEMBER_LOGIN_REDIRECT_PAGE;
			}
			redirect($path);
		}
		else
		{
			$data->enable_twitter_login  = $this->_is_enable_twitter_login();
			$data->enable_facebook_login = $this->_is_enable_facebook_login();
			$data->enable_google_login = $this->_is_enable_google_login();
			$this->msg = 'ログインに失敗しました。ユーザー名とパスワードを確認してください。';
			$this->content_data = $this->load->view($this->view_path . 'member_login', $data, TRUE);
			$this->render_view('system');
		}
	}
	
	/**
	 * パスワード再発行データをメール送信
	 */
	function _send_forgotten_mail()
	{
		$this->load->model(array('auth_model', 'mail_model'));
		// generate activation code
		$code   = sha1(uniqid(mt_rand(), TRUE));
		$to     = set_value('forgotten_email');
		$member = $this->member_model->get_member_data_from_email($to);
		$ret    = $this->member_model->set_rebuild_password_activation($member->sz_member_id, $code);
		if (!$ret)
		{
			return FALSE;
		}
		
		$subject   = '【' . SITE_TITLE . '】パスワード再発行のお知らせ';
		$from_mail = ( !empty($this->site_data->system_mail_from) )
		               ? $this->site_data->system_mail_from
		               : $this->auth_model->get_master_email();
		$from      = array($from_mail, SITE_TITLE);
		$member    = $this->member_model->get_member_data_from_email($to);
		$body      = $this->load->view('mailbodys/forgotten_password_member', array('code' => $code, 'member' => $member), TRUE);

		return $this->mail_model->send_text_mail($to, $subject, $body, $from);
	}
	
	/**
	 * パスワード再発行データをメール送信
	 */
	function _send_reset_password_mail($member)
	{
		$this->load->model(array('auth_model', 'mail_model'));

		$subject   = '【' . SITE_TITLE . '】パスワード再発行完了のお知らせ';
		$to        = $member->email;
		$from_mail = ( !empty($this->site_data->system_mail_from) )
		               ? $this->site_data->system_mail_from
		               : $this->auth_model->get_master_email();
		$from      = array($from_mail, SITE_TITLE);
		$body      = $this->load->view('mailbodys/reset_password_complete', array('member' => $member), TRUE);

		return $this->mail_model->send_text_mail($to, $subject, $body, $from);
	}
}
