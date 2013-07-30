<?php
/**
 * ===============================================================================
 * 
 * Seezoo ログインコントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Login extends SZ_Controller
{
	public $ticket_name  ='sz_login_token';
	public $ticket;
	public $dir = 'login/';
	public $msg = '';
	public static $page_title = 'ログイン';
	public static $description = 'ログイン画面を表示します。';

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller(FALSE);
		$this->load->library('form_validation');
		$this->load->helper(array('url', 'form', 'cookie_helper'));
		$this->load->model('auth_model');

		ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . APPPATH . 'libraries/');
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		
		//	no cahce
		$this->output->set_header('Cache-Control: no-store, no-cahce, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		
		if ( defined('SEEZOO_SYSTEM_LOGIN_URI') )
		{
			if ( $this->uri->segment(1) !== SEEZOO_SYSTEM_LOGIN_URI )
			{
				show_404();
			}
		}
		
		//$this->output->enable_profiler(TRUE);
	}

	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$this->_check_remember();
		$this->_set_ticket();
		$this->_validation(FALSE);

		$this->load->view($this->dir . 'login');
	}

	/**
	 * ログイン実行
	 * @access public
	 */
	function do_login()
	{
		$this->_ticket_check();

		$this->_validation(FALSE);
		$this->session->keep_flashdata($this->ticket_name);

		if ($this->form_validation->run() === FALSE)
		{
			$this->ticket = $this->input->post($this->ticket_name);
			$this->load->view($this->dir . 'login');
		}
		else
		{
			$this->ticket = $this->input->post($this->ticket_name);
			$this->_login_process();
		}
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
			$this->load->view($this->dir . 'login', array('open' => TRUE));
		}
		else
		{
			if ($this->_send_forgotten_mail())
			{
				$this->msg = 'メールを送信しました。新しいパスワードを確認後、再度ログインして下さい。';
				$this->load->view($this->dir . 'login');
			}
			else
			{
				exit('メールの送信に失敗しました。');
			}
		}
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
	 * バリデーションセット
	 * @access private
	 * @param $is_forgotten
	 */
	function _validation($is_forgotten = FALSE)
	{
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		$this->form_validation->set_message('valid_email', 'メールアドレスの形式が正しくありません。');

		$conf = array(
			array(
				'filed'			=> 'user_name',
				'rules'			=> ($is_forgotten) ?  '' : 'trim|required',
				'label'			=> 'ユーザー名'
			),
			array(
				'field'			=> 'password',
				'rules'			=> ($is_forgotten) ? '' : 'trim|required',
				'label'			=> 'パスワード'
			),
			array(
				'field'			=> 'forgotten_mail',
				'rules'			=> ($is_forgotten) ? 'trim|required|valid_email|max_length[255]|callback_already' : '',
				'label'			=> 'メールアドレス'
			)
		);

		$this->form_validation->set_rules($conf);
	}

	/**
	 * ログイン実行
	 * @access private
	 */
	function _login_process()
	{
		$uid = $this->input->post('user_name');
		$pass = $this->input->post('password');

		$res = $this->auth_model->login($uid, $pass, TRUE);
		if ($res)
		{
			// 継続的なログインの場はトークンを発行し、クッキーにセット
			if ((int)$this->input->post('remember') > 0)
			{
				$cookie = array(
					'name'			=> 'seezoo_remembers',
					'value'			=> sha1(microtime()),
					'expire'			=> 60 * 60 * 24 * 7,
					'domain'		=> '',
					'path'				=> '/'
				);
				set_cookie($cookie);
				$this->auth_model->set_remember_token($cookie['value']);
			}
			redirect($res);
		}
		else
		{
			$this->msg = 'ログインに失敗しました。ユーザー名とパスワードを確認してください。';
			$this->load->view($this->dir . 'login');
		}
	}

	/**
	 * 継続的なログインユーザーかどうかをチェック
	 */
	function _check_remember()
	{
		$cookie = get_cookie('seezoo_remembers');
		if ($cookie)
		{
			$this->auth_model->remember_login($cookie);
		}
	}

	/**
	 * 独自バリデーション：メールアドレスが存在するかどうかチェック
	 * @param string $str
	 */
	function already($str)
	{
		$is = $this->auth_model->is_email($str);
		if (!$is)
		{
			$this->form_validation->set_message('already', '入力されたメールアドレスは存在しません。');
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * パスワード再発行データをメール送信
	 */
	function _send_forgotten_mail()
	{
		$to = set_value('forgotten_mail');
		$this->load->helper('string');
		$this->load->model(array('dashboard_model', 'mail_model'));

		$new_password = random_string('alnum', 10);

		$pass = $this->dashboard_model->enc_password($new_password);
		$ret = $this->auth_model->update_new_password_for_email($to, $pass);
		if (!$ret)
		{
			return FALSE;
		}

		$subject = '【' . SITE_TITLE . '】パスワード再発行のお知らせ';
		$from = $this->auth_model->get_master_email(); //sample mail
		$body = $this->load->view('mailbodys/forgotten_password', array('new_pass' => $new_password), TRUE);

		return $this->mail_model->send_text_mail($to, $subject, $body, $from);
	}
}
