<?php

/**
 * ====================================================================
 * 
 * Seezoo Member Profile Controller
 * 
 * @package Seezoo Plugins
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 *  ===================================================================
 */

class Profile extends SZ_Controller
{
	public static $page_title = 'メンバープロフィール';
	public static $description = 'メンバーのプロフィールを表示、更新します。';
	
	public $view_path = 'profile/';
	public $ticket_name = 'sz_profile_token';
	public $ticket;
	
	protected $output_flag = TRUE;
	protected $upload_dir =  'files/members/';
	protected $allowed_types = 'gif|jpg|jpeg|png';
	
	function __construct()
	{
		parent::SZ_Controller();
		//$this->load->model('profile_model');
		$this->load->model('member_model');
		
		$this->generate_cms_mode();
		
		$this->add_header_item(build_css('css/profile.css'));
		$this->add_header_item(build_javascript('js/profile.js'));
		$this->member_id = (int)$this->session->userdata('member_id');
		
		// member logged in or site admin user?
		if ($this->member_id === 0 )
		{
			$this->member_id = (int)$this->member_model->get_member_id_from_admin_user((int)$this->session->userdata('user_id'));
		}
		
		if ( ! is_dir(FCPATH . 'files/members') )
		{
			@mkdir(FCPATH . 'files/members');
		}
	}
	
	/**
	 * プロフィール表示メソッド
	 * @param $member_id
	 */
	function _show($member_id = 0)
	{
		if ($member_id == 0)
		{
			$data->profile = $this->member_model->get_member_detail((int)$this->member_id);
		}
		else 
		{
			$data->profile = $this->member_model->get_member_detail((int)$member_id);
		}
		// get attributes
		if ( $data->profile )
		{
			$data->attributes = $this->member_model->get_member_attributes_data($data->profile->sz_member_id);
			$this->page_data['page_title'] = $data->profile->nick_name . ' さんのプロフィール';
		}
		
		
		if (($member_id == 0 && $this->member_id > 0)
				|| ((int)$member_id === $this->member_id && $member_id > 0))
		{
			// self
			$data->is_self = TRUE;
		}
		else
		{
			// other
			$data->is_self = FALSE;
		}
		$data->member_id = $member_id;
		
		$this->content_data = $this->load->view($this->view_path . 'profile', $data, TRUE);
		$this->render_view('system');
	}
	
	/**
	 * iframe handle プロフィール画像アップロード
	 */
	function image_upload()
	{
		// set normal output mode
		$this->cms_mode = FALSE;
		
		$member_id = $this->member_id;
		if ( $member_id == 0 )
		{
			exit;
		}
		$this->output_flag = FALSE;
		
		if ( $this->input->server('REQUEST_METHOD') !== 'POST' )
		{
			$this->load->view('profile/image_upload', '');
		}
		else 
		{
			// post request. do upload
			// load the Upload class
			$this->load->library('upload');
			
			// configure
			$config = array(
				'upload_path'   => FCPATH . $this->upload_dir,
				'allowed_types' => $this->allowed_types,
				'max_size'      => 100,  // KB
				'overwrite'     => FALSE,
				'encrypt_name'  => TRUE,
				'remove_spaces' => TRUE
			);
			
			$this->upload->initialize($config);
			
			// try upload
			$result = $this->upload->do_upload('upload_data');
			
			if (!$result)
			{
				$data->error = $this->upload->display_errors('', '\n');
				$this->load->view('profile/image_upload', $data);
				return;
			}
			
			// get uploaded data
			$upload_data = $this->upload->data();
			
			// insert member_db
			$db_data = array(
				'image_data' => $upload_data['raw_name'] . $upload_data['file_ext']
			);
			
			// resize image if uploaded file width/height over 100px
			if ($upload_data['is_image'] > 0
					&& ($upload_data['image_width'] > 100 || $upload_data['image_height'] > 100))
			{
				$image_conf = array(
					'source_image'   => $upload_data['full_path'],
					'create_thumb'   => FALSE,
					'width'          => 100,
					'height'         => 100,
					'maintain_ratio' => TRUE
				);
				
				$this->load->library('image_lib', $image_conf);
				
				if ( ! $this->image_lib->resize() )
				{
					$data->error = '画像の縮小に失敗しました';
					$this->load->view('profile/image_upload', $data);
					return;
				}
			}
			
			$res = $this->member_model->update_profile_image($db_data, $member_id);
			$data->success = 1;
			
			$this->load->view('profile/image_upload', $data);
			
		}
	}

	/**
	 * プロフィール編集
	 */
	function edit()
	{
		$member_id = (int)$this->member_id;
		
		if ($member_id === 0)
		{
			show_404();
		}
		
		// depends on registration_helper
		$this->load->helper('registration_helper');
		// and use registarion table
		$this->add_header_item(build_css('css/registration.css'));
		
		// make assign values
		$data->is_validated      = FALSE;
		$data->ticket            = ticket_generate();
		$data->profile           = $this->member_model->get_member_detail((int)$this->member_id);
		$data->attributes        = $this->member_model->get_member_attributes();
		$data->attributes_values = $this->member_model->get_member_attributes_data((int)$this->member_id, TRUE);
		// validation set
		$this->_validation($data->attributes);
		
		// If post modify, run validation on backend
		if ( $this->input->post('modify') )
		{
			$this->form_validation->run();
			$data->is_validated = TRUE;
		} 
		
		$this->content_data = $this->load->view('profile/edit', $data, TRUE);
		$this->render_view('system');
	}
	
	/**
	 * プロフィール編集確認
	 */
	function edit_confirm()
	{
		$member_id = (int)$this->member_id;
		if ($member_id === 0)
		{
			show_404();
		}
		
		ticket_check();
		
		// stack default POST
		$_def_post = $_POST;
		
		// depends on registration_helper
		$this->load->helper('registration_helper');
		// and use registarion table
		$this->add_header_item(build_css('css/registration.css'));
		
		$data->attributes = $this->member_model->get_member_attributes();
		$data->ticket     = $this->input->post('ticket');
		$this->_validation($data->attributes);
		
		if ($this->form_validation->run() === FALSE)
		{
			// reovery
			$_POST = $_def_post;
			$data->is_validated = TRUE;
			$this->content_data = $this->load->view('profile/edit', $data, TRUE);
		}
		else
		{
			$data->hidden['nick_name'] = $this->input->post('nick_name');

			// and custom attributes
			foreach ( $data->attributes as $atts )
			{
				$key                = 'attribute_' . $atts->sz_member_attributes_id;
				$data->hidden[$key] = $this->input->post($key);
			}
			
			$this->content_data = $this->load->view('profile/edit_confirm', $data, TRUE);
		}
		
		$this->render_view('system');
	}
	
	/**
	 * プロフィール更新実行
	 */
	function edit_process()
	{
		$member_id = (int)$this->member_id;
		if ($member_id === 0)
		{
			show_404();
		}
		
		ticket_check();
		ref_check();
		
		$attributes = $this->member_model->get_member_attributes();
		$this->_validation($attributes);
		
		if ($this->form_validation->run() === FALSE)
		{
			$this->_show($this->input->post('member_id'));
			return;
		}
		
		$post = array(
			'nick_name' => $this->input->post('nick_name')
		);
		
		// update membefr_basic data
		if ( ! $this->member_model->update_member($this->member_id, $post) )
		{
			$data->msg = 'プロフィールの更新に失敗しました。';
			$this->content_data = $this->load->view('profile/end', $data, TRUE);
			$this->render_view('system');
			return;
		}
		
		$attribute_values = array();
		foreach ( $attributes as $atts )
		{
			$key         = 'attribute_' . $atts->sz_member_attributes_id;
			$base_column = 'sz_member_attributes_value';
			$v = $this->input->post($key);
			// convert string if post value is array
			if ( is_array($v) )
			{
				$v = implode(':', array_map('intval', $v));
			}
			
			if ( $atts->attribute_type === 'textarea' )
			{
				$column = $base_column . '_text';
			}
			else 
			{
				$column = $base_column;
			}
			$attribute_values[$atts->sz_member_attributes_id] = array($column => $v);
		}
		
		$ret        = $this->member_model->update_member_attribute_data($attribute_values, (int)$this->member_id);
		$data->head = ( $ret ) ? 'プロフィール更新完了' : 'プロフィール更新失敗';
		$data->msg  = ( $ret ) ? 'プロフィールを更新しました。' : 'プロフィールの更新に失敗しました。';
		
		$this->content_data = $this->load->view('profile/end', $data, TRUE);
		$this->render_view('system');
	}
	
	/**
	 * メンバー退会
	 */
	function secession()
	{
		// show confirm
		$data->ticket       = ticket_generate();
		$this->content_data = $this->load->view('profile/secession_confirm', $data, TRUE);
		$this->render_view('system');
	}
	
	/**
	 * 退会実行
	 * Enter description here ...
	 */
	function do_secession()
	{
		// check token
		$ticket = $this->uri->segment(3, '');
		if ( ! $ticket || $ticket !== $this->session->userdata('ticket') )
		{
			exit('不正なリクエストの可能性があります。処理を中断しました。');
		}
		
		// load depends model
		$this->load->model('auth_model');
		
		// get and stack member data tmporary
		$member = $this->member_model->get_member_detail($this->member_id);
		// do secession [ delete record ]
		$ret    = $this->member_model->do_secession($this->member_id);
		
		if ( $ret )
		{
			$this->_send_secession_mail($member);
			// remove session
			$this->auth_model->member_logout();
			$this->content_data = $this->load->view('profile/secession_complete', '', TRUE);
		}
		else
		{
			$this->content_data = $this->load->view('profile/secession_error', '', TRUE);
		}
		
		$this->render_view('system');
	}
	
	/**
	 * メールアドレス変更用バリデーション
	 * @param $confirm
	 */
	function _email_validation($confirm = FALSE)
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="r_error">', '</p>');
		
		$conf = array(
			array(
				'field' => 'email',
				'label' => 'メールアドレス',
				'rules' => 'trim|required|valid_email|max_length[255]|callback_already_exists'
			),
			array(
				'field' => 'email_match',
				'label' => 'メールアドレス（確認）',
				'rules' => 'trim|required|valid_email|max_length[255]|matches[email]'
			)
		);
		
		if ( ! $confirm )
		{
			$conf[] = array(
				'field' => 'cur_password',
				'label' => '現在のパスワード',
				'rules' => 'trim|required|min_length[8]|max_length[128]|callback__match_current_password'
			);
		}
		
		$this->form_validation->set_rules($conf);
	}
	
	/**
	 * パスワード変更用バリデーション
	 * @param $confirm
	 */
	function _password_validation()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="r_error">', '</p>');
		
		$conf = array(
			array(
				'field' => 'password',
				'label' => 'パスワード',
				'rules' => 'trim|required|min_length[8]|max_length[128]'
			),
			array(
				'field' => 'password_match',
				'label' => 'パスワード（確認）',
				'rules' => 'trim|required|min_length[8]|max_length[128]|matches[password]'
			),
			array(
				'field' => 'cur_password',
				'label' => '現在のパスワード',
				'rules' => 'trim|required|min_length[8]|max_length[128]|callback__match_current_password'
			)
		);
		
		$this->form_validation->set_rules($conf);
		
	}
	
	/**
	 * バリデーションセット
	 * @access private
	 * @param $attributes
	 * @param $is_logins
	 */
	function _validation($attributes = array(), $is_logins = FALSE, $is_login_confirm = FALSE)
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="r_error">', '</p>');
		
		$conf = array(
			array(
				'field' => 'nick_name',
				'label' => 'ニックネーム',
				'rules' => 'trim|required|max_length[30]|callback_already_exists_name_frontend'
			)
		);
		
		// setup validation to attributes
		foreach ( $attributes as $attribute )
		{
			$conf[] = array(
							'field' => 'attribute_' . $attribute->sz_member_attributes_id,
							'label' => $attribute->attribute_name,
							'rules' => $attribute->validate_rule
						);
		}

		$this->form_validation->set_rules($conf);
	}
	
	/**
	 * バリデーションコールバック : メールアドレスが既に存在するかをチェック
	 * @param $str
	 */
	function already_exists($str)
	{
		if ( $this->member_model->is_email_already_exists_frontend($str, $this->member_id))
		{
			$this->form_validation->set_message('already_exists', '入力されたメールアドレスは既に登録されています。');
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * バリデーションコールバック : ニックネームが既に存在するかをチェック
	 * @param $str
	 */
	function already_exists_name_frontend($str)
	{
		if ( ! $this->member_model->is_nick_name_already_exists_frontend($str, $this->member_id))
		{
			$this->form_validation->set_message('already_exists_name_frontend', '入力されたニックネームは既に使用されています。');
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * バリデーションコールバック : 現在のパスワードの一致を見る
	 * @param $str
	 */
	function _match_current_password($str)
	{
		if ( ! $this->member_model->password_match($str, $this->member_id) )
		{
			$this->form_validation->set_message('_match_current_password', '現在のパスワードが一致しません。');
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * ログインアカウント変更選択画面
	 */
	function select_change_login_account()
	{
		$this->add_header_item(build_css('css/registration.css'));
		
		$this->content_data = $this->load->view('profile/select_change_account', '', TRUE);
		$this->render_view('system');
	}
	
	/**
	 * ログインメールアドレス変更入力画面
	 */
	function edit_email()
	{
		$member_id = (int)$this->member_id;
		
		if ($member_id === 0)
		{
			redirect('member_login');
		}
		
		// depends on registration_helper
		$this->load->helper('registration_helper');
		// and use registarion table
		$this->add_header_item(build_css('css/registration.css'));
		
		// make assign values
		$data->is_validated = FALSE;
		$data->ticket       = ticket_generate();
		$data->profile      = $this->member_model->get_member_detail((int)$this->member_id);
		// validation set
		$this->_email_validation();
		
		// If post modify, run validation on backend
		if ( $this->input->post('modify') )
		{
			//$this->form_valiation->run();
			// set temoprary validated flag
			$data->is_validated = TRUE;
		} 
		
		$this->content_data = $this->load->view('profile/edit_email', $data, TRUE);
		$this->render_view('system');
	}
	
	function edit_email_confirm()
	{
		$member_id = (int)$this->member_id;
		if ($member_id === 0)
		{
			redirect('member_login');
		}
		
		ticket_check();
		$def_post = $_POST;
		
		// depends on registration_helper
		$this->load->helper('registration_helper');
		// and use registarion table
		$this->add_header_item(build_css('css/registration.css'));
		
		$this->_email_validation();
		$data->ticket       = $this->input->post('ticket');
		$data->is_validated = TRUE;
		
		if ($this->form_validation->run() === FALSE)
		{
			$_POST = $def_post;
			$this->content_data = $this->load->view('profile/edit_email', $data, TRUE);
		}
		else
		{
			foreach ( array('email', 'email_match' ) as $v )
			{
				$data->hidden[$v] = $this->input->post($v);
			}
			$this->content_data = $this->load->view('profile/edit_email_confirm', $data, TRUE);
		}
		
		$this->render_view('system');
	}
	
	function do_edit_email()
	{
		$member_id = (int)$this->member_id;
		if ($member_id === 0)
		{
			redirect('member_login');
		}
		
		ticket_check();
		ref_check();
		
		$this->_email_validation(TRUE);
		
		if ($this->form_validation->run() === FALSE)
		{
			$this->_show($this->input->post('member_id'));
			return;
		}
		
		destroy_ticket();
		$new_email = $this->input->post('email');
		$before    = $this->member_model->get_member_detail((int)$this->member_id);
		
		// Email changes after activation only.
		if ( $this->_sendmail_for_emailchange($before, $new_email) )
		{
			$data->head = '処理が完了しました';
			$data->msg = '入力されたメールアドレスにメールを送信しました。' . "\n"
							. 'メールをご確認の上、アクティベーション処理を行なってください。' . "\n"
							. 'また、確認のため、以前のメールアドレスにもメールを送信しております。';
		}
		else
		{
			$data->head = '処理に失敗しました';
			$data->msg = 'メールアドレスの変更に失敗しました。。' . "\n"
							. 'お手数ですが始めからやり直してください。' . "\n";
		}
		
		$this->content_data = $this->load->view('profile/end', $data, TRUE);
		$this->render_view('system');
	}
	
	function edit_password()
	{
		$member_id = (int)$this->member_id;
		
		if ($member_id === 0)
		{
			redirect('member_login');
		}
		
		// depends on registration_helper
		$this->load->helper('registration_helper');
		// and use registarion table
		$this->add_header_item(build_css('css/registration.css'));
		
		// make assign values
		$data->ticket       = ticket_generate();
		
		// validation set
		$this->_password_validation();
		
		$this->content_data = $this->load->view('profile/edit_password', $data, TRUE);
		$this->render_view('system');
	}
	
	function edit_password_confirm()
	{
		$member_id = (int)$this->member_id;
		if ($member_id === 0)
		{
			redirect('member_login');
		}
		
		ticket_check();
		
		// depends on registration_helper
		$this->load->helper('registration_helper');
		// and use registarion table
		$this->add_header_item(build_css('css/registration.css'));
		
		//$this->_validation(array(), TRUE, TRUE);
		$this->_password_validation();
		$data->ticket       = $this->input->post('ticket');
		$data->is_validated = TRUE;
		
		// keep password session flashdata is exists
		if ( $this->session->flashdata('sz_login_pass') )
		{
			$this->session->keep_flashdata('sz_login_pass');
		}
		
		if ($this->form_validation->run() === FALSE)
		{
			$data->pass = ($this->session->flashdata('sz_login_pass'))
			                ? $this->session->flashdata('sz_login_pass')
			                  : '';
			$this->content_data = $this->load->view('profile/edit_password', $data, TRUE);
		}
		else
		{
			$this->session->set_flashdata('sz_login_pass', $this->input->post('password'));
			$this->content_data = $this->load->view('profile/edit_password_confirm', $data, TRUE);
		}
		
		$this->render_view('system');
	}
	
	function do_edit_password()
	{
		$member_id = (int)$this->member_id;
		if ($member_id === 0)
		{
			redirect('member_login');
		}
		
		ticket_check();
		ref_check();
		
//		// validation manually
//		$new_password = $this->session->flashdata('sz_login_pass');
//		if ( ! $new_password                 /* required */
//		      || strlen($new_password) < 8   /* min_length[8] */
//		      || strlen($new_password) > 128 /* max_length[128] */
//		)
//		{
//			$this->_show($this->input->post('member_id'));
//			return;
//		}
// depends on registration_helper
		$this->load->helper('registration_helper');
		// and use registarion table
		$this->add_header_item(build_css('css/registration.css'));
		$def_post = $_POST;
		$this->_password_validation();
		if ( $this->form_validation->run() === FALSE )
		{
			$_POST = $def_post; 
			$data->ticket       = $this->input->post('ticket');
			$this->content_data = $this->load->view('profile/edit_password', $data, TRUE);
			$this->render_view('system');
			return;
		}
		
		$_POST = $def_post; 
		destroy_ticket();
		
		$post = array(
			'password'	=> $this->input->post('password')
		);
		
		$member_data = $this->member_model->get_member_detail((int)$this->member_id);
		
		// encrypt password
		$this->load->model('dashboard_model');
		$pass = $this->dashboard_model->enc_password($post['password']);
		
		$post['password'] = $pass['password'];
		$post['hash']     = $pass['hash'];
		
		
		// update member password
		if ( ! $this->member_model->update_member($this->member_id, $post) )
		{
			$data->head = '処理に失敗しました';
			$data->msg  = 'パスワードの変更に失敗しました。';
			$this->content_data = $this->load->view('profile/end', $data, TRUE);
			$this->render_view('system');
			return;
		}
		
		$data->head = 'パスワードを変更しました';
		if ( ! $this->_sendmail_for_passwordchange($member_data) )
		{
			$data->msg = '次回のログインからは新しいパスワードをご利用ください。' . "\n";
		}
		else 
		{
			$data->msg = '次回のログインからは新しいパスワードをご利用ください。' . "\n"
							. '確認のメールを送信しておりますので、ご確認ください。' . "\n";
		}
		
		$this->content_data = $this->load->view('profile/end', $data, TRUE);
		$this->render_view('system');
	}
	
	/**
	 * メールアドレス変更後のアクティベーション
	 */
	function account_activate()
	{
		$code = $this->uri->segment(3, FALSE);
		if ( ! $code )
		{
			show_404();
		}
		
		// activation start
		$this->load->model('auth_model');
		$result = $this->auth_model->do_member_activation($code);
		
		if ( $result === TRUE )
		{
			$data->head = '処理が完了しました';
			$data->msg  = 'アクティベーション認証が完了しました。' . "\n" . '次回からは新しいメールアドレスでログインしてください。';
		}
		else if ( $result === 'timeout' )
		{
			$data->head = '有効期限切れです';
			$data->msg  = 'アクティベーション有効期間を過ぎました。' . "\n" . 'お手数ですが、再度アカウント変更画面から変更手続きを行なってください。';
		}
		else
		{
			show_404();
			//$data->head = '処理に失敗しました';
			//$data->msg = 'アクティベーション認証に失敗しました。' . "\n" . 'お手数ですが、再度アカウント変更画面から変更手続きを行なってください。';
		}
		
		$this->content_data = $this->load->view('profile/end', $data, TRUE);
		$this->render_view('system');
	}
	
	
	/**
	 * メール送信
	 * @param $accounts
	 */
	function _sendmail($accounts)
	{
		require_once (APPPATH . 'libraries/qdmail.php');
		
		mb_language('ja');
		$protocol = 'text';
		if ( ! empty($this->site_data->system_mail_from) )
		{
			$from = $this->site_data->system_mail_from;
		}
		else 
		{
			$from = $this->member_model->get_admin_email();
		}
		if ( !$from )
		{
			return FALSE;
		}
		$accounts->from = $from;
		$to             = $accounts->email;
		$subject        = '【' . SITE_TITLE . '】ログイン情報変更のお知らせ';
		$body           = $this->load->view('mailbodys/account_change', $accounts, TRUE);
		$from_header    = array($from, SITE_TITLE);

		$ret            = qd_send_mail($protocol, $to, $subject, $body, $from_header);
		
		// insert mail log
		$LOG =& load_class('Log');
		$LOG->write_mail_log($subject, $to, $body, $ret);
		
		return $ret;
	}
	
	function _sendmail_for_passwordchange($member_data)
	{
		require_once (APPPATH . 'libraries/qdmail.php');
		
		mb_language('ja');
		$protocol = 'text';
		if ( ! empty($this->site_data->system_mail_from) )
		{
			$from = $this->site_data->system_mail_from;
		}
		else 
		{
			$from = $this->member_model->get_admin_email();
		}
		if ( !$from )
		{
			return FALSE;
		}

		$member_data->from = $from;
		$to                = $member_data->email;
		$subject           = '【' . SITE_TITLE . '】ログインパスワード変更のお知らせ';
		$body              = $this->load->view('mailbodys/member_password_change', $member_data, TRUE);
		$from_header       = array($from, SITE_TITLE);

		$ret               = qd_send_mail($protocol, $to, $subject, $body, $from_header);
		
		// insert mail log
		$LOG =& load_class('Log');
		$LOG->write_mail_log($subject, $to, $body, $ret);
		
		return $ret;
	}
	
	function _sendmail_for_emailchange($member_data, $new_email)
	{
		require_once (APPPATH . 'libraries/qdmail.php');
		
		mb_language('ja');
		$protocol = 'text';
		if ( ! empty($this->site_data->system_mail_from) )
		{
			$from = $this->site_data->system_mail_from;
		}
		else 
		{
			$from = $this->member_model->get_admin_email();
		}
		if ( !$from )
		{
			return FALSE;
		}
		$member_data->from = $from;
		$from_header       = array($from, SITE_TITLE);
		$subject           = '【' . SITE_TITLE . '】ログインメールアドレス変更のお知らせ';
		
		// first, send old mailaddress to notification
		$to   = $member_data->email;
		$body = $this->load->view('mailbodys/account_change_old_address', $member_data, TRUE);
		
		$ret = qd_send_mail($protocol, $to, $subject, $body, $from_header);
		
		// insert mail log
		$LOG =& load_class('Log');
		$LOG->write_mail_log($subject, $to, $body, $ret);
		
		// second, send new mailaddress with activation
		// generate activation code
		$this->load->model('auth_model');
		$activation_code = $this->auth_model->generate_member_activation_code($this->member_id, $new_email);
		if ( ! $activation_code )
		{
			return FALSE;
		}
		$to                           = $new_email;
		$member_data->activation_link = page_link() . 'profile/account_activate/' . $activation_code;
		$body                         = $this->load->view('mailbodys/account_change_new_address', $member_data, TRUE);
		
		$ret2                         = qd_send_mail($protocol, $to, $subject, $body, $from_header);
		
		// insert mail log
		$LOG =& load_class('Log');
		$LOG->write_mail_log($subject, $to, $body, $ret2);
		
		return ( $ret && $ret2 ) ? TRUE : FALSE;
	}
	
	
	/**
	 * 退会メール送信
	 * @param $accounts
	 */
	function _send_secession_mail($member)
	{
		// note : Twitterアカウントでのログインの場合、メールアドレスは登録されないので、送信しない
		if ( empty($member->email) )
		{
			return;
		}
		require_once (APPPATH . 'libraries/qdmail.php');
		
		mb_language('ja');
		$protocol = 'text';
		if ( ! empty($this->site_data->system_mail_from) )
		{
			$from = $this->site_data->system_mail_from;
		}
		else 
		{
			$from = $this->member_model->get_admin_email();
		}
		if ( !$from )
		{
			return FALSE;
		}
		$member->from = $from;
		$to           = $member->email;
		$subject      = '【' . SITE_TITLE . '】退会処理完了のお知らせ';
		$body         = $this->load->view('mailbodys/secession_complete', $member, TRUE);
		$from_header  = array($from, SITE_TITLE);

		$ret          = qd_send_mail($protocol, $to, $subject, $body, $from_header);
		
		// insert mail log
		$LOG =& load_class('Log');
		$LOG->write_mail_log($subject, $to, $body, $ret);
		
		return $ret;
	}
	
	/**
	 * メソッドリマップ
	 * @param string $method
	 */
	function _remap($method)
	{
		if (method_exists($this, $method))
		{
			$this->{$method}();
		}
		else if (ctype_digit($method))
		{
			$this->_show($method);
		}
		else
		{
			$this->_show(0);
		}
	}

}
