<?php
/**
 * ========================================================================================
 * 
 * Seezoo member registration Controller
 * 
 * @package Seezoo Plugin
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */

class Registration extends SZ_Controller
{
	public static $page_title = 'ユーザー登録';
	public static $description = '新規ユーザーを登録します。';

	public $view_path = 'registration/';

	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('member_model');
		$this->load->helper('registration_helper');
		
		$this->generate_cms_mode();
		$this->add_header_item(build_css('css/registration.css'));
		// for debug
		//$this->output->enable_profiler(TRUE);
		
		
		// flag check
		if ( ! isset($this->site_data->is_accept_member_registration)
				|| $this->site_data->is_accept_member_registration < 1 )
		{
			show_404();
		}
	}

	function index()
	{
		// generate attribute data
		$data->attributes = $this->member_model->get_member_attributes();
		
		$this->_validation($data->attributes);
		$data->ticket = ticket_generate();

		if ($this->input->post('modify'))
		{
			$this->form_validation->run();
		}

		$this->content_data = $this->load->view($this->view_path . 'index', $data, TRUE);

		$this->render_view('system');
	}

	function activate($code = FALSE)
	{
		if ( ! $code )
		{
			show_404();
		}

		$ret = $this->member_model->activate($code);

		if ($ret === FALSE)
		{
			show_404();
		}
		else if ($ret === 'already')
		{
			$this->content_data = $this->load->view($this->view_path . 'already_activated', '', TRUE);
		}
		else if ( $ret === 'over' )
		{
			$this->content_data = $this->load->view($this->view_path . 'activate_timeover', '', TRUE);
		}
		else
		{
			// send registration complete mail
			$this->_sendmail($ret, 'activate');
			$this->content_data = $this->load->view($this->view_path . 'activation_end', $ret, TRUE);
		}
		$this->render_view('system');
	}

	function confirm()
	{
		ticket_check();
		
		// generate attribute data
		$data->attributes = $this->member_model->get_member_attributes();
		$this->_validation($data->attributes);
		
		$data->ticket = ticket_generate();

		if ($this->form_validation->run() === FALSE)
		{
			$this->content_data = $this->load->view($this->view_path . 'index', $data, TRUE);
		}
		else
		{
			// set basic hidden data
			foreach (array('nick_name', 'email', 'email_match', 'password') as $v)
			{
				$data->hidden[$v] = $this->input->post($v);
			}
			// and custom attributes
			foreach ( $data->attributes as $atts )
			{
				$key = 'attribute_' . $atts->sz_member_attributes_id;
				$data->hidden[$key] = $this->input->post($key);
			}
			

			$this->content_data = $this->load->view($this->view_path . 'confirm', $data, TRUE);
		}
		$this->render_view('system');
	}

	function do_regist()
	{
		ticket_check();
		// check referer too.
		ref_check();
		// generate attribute data
		$data->attributes = $this->member_model->get_member_attributes();
		$this->_validation($data->attributes, TRUE);

		if ( !$this->_referer_is_acceptable())
		{
			exit('不正なポストの可能性があります。処理を中断しました。');
		}

		if ($this->form_validation->run() === FALSE)
		{
			exit('不正なポストの可能性があります。処理を中断しました。');
		}
		else
		{
			foreach (array('nick_name', 'email', 'password') as $v)
			{
				$post[$v] = $this->input->post($v);
			}
			// encrypt_password
			$this->load->model('dashboard_model');

			$pass = $this->dashboard_model->enc_password($post['password']);

			$post['password'] = $pass['password'];
			$post['hash'] = $pass['hash'];

			// return inserted member_id.
			// !!!notice added 'activation_code' data on $post data this method.
			$id = $this->member_model->regist_new_member($post);

			if ($id > 0)
			{
				// if insert success, insert custom attribute data too.
				$is_att_registed = $this->_regist_attribute($id, $data->attributes);
				if ( $is_att_registed )
				{
					// send activation mail
					if ($this->_sendmail($post))
					{
						redirect('registration/regist_end/end');
					}
				}
			}
			redirect('registration/regist_end/error');
		}
	}
	
	function _regist_attribute($id, $attributes)
	{
		foreach ( $attributes as $att )
		{
			$attid = $att->sz_member_attributes_id;
			// parameter set on table
			$data = array(
				'sz_member_id'				=> $id,
				'sz_member_attributes_id'	=> $attid
			);
			$key = 'sz_member_attributes_value';
			if ( $att->attribute_type === 'textarea' )
			{
				$key .= '_text';
			}
			$data[$key] = $this->input->post('attribute_' . $attid);
			if ( ! $this->member_model->insert_member_attributes($data) )
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	function _sendmail($post, $template = 'registration')
	{
		$from = $this->member_model->get_admin_email();
		if ( !$from )
		{
			return FALSE;
		}
		$post['from'] = $from;
		$to           = $post['email'];
		if ($template === 'activate')
		{
			$subject = '【' . SITE_TITLE . '】ユーザー本登録完了のお知らせ';
		}
		else
		{
			$subject = '【' . SITE_TITLE . '】ユーザー仮登録のお知らせ';
		}
		
		$this->load->model('mail_model');
		
		$body        = $this->load->view('mailbodys/' . $template, $post, TRUE);
		$from_header = array($from, SITE_TITLE);

		return $this->mail_model->send_text_mail($to, $subject, $body, $from_header);
	}


	function regist_end($result = 'error')
	{
		$this->content_data = $this->load->view($this->view_path . $result, '', TRUE);
		$this->render_view('system');
	}

	function _referer_is_acceptable()
	{
		$ref = $this->input->server('HTTP_REFERER');
		return (strpos($ref, file_link()) === FALSE) ? FALSE : TRUE;
	}


	function _validation($attributes, $is_regist = FALSE)
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="r_error">', '</p>');

		$conf = array(
			array(
				'field'		=> 'nick_name',
				'label'		=> 'ニックネーム',
				'rules'		=> 'trim|required|max_length[30]|callback_already_exists_name'
			),
			array(
				'field'		=> 'email',
				'label'		=> 'メールアドレス',
				'rules'		=> 'trim|required|valid_email|max_length[255]|callback_already_exists'
			),
			array(
				'field'		=> 'password',
				'label'		=> 'パスワード',
				'rules'		=> 'trim|required|min_length[8]|max_length[128]'
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

		if ( ! $is_regist)
		{
			$conf[] = array(
							'field'		=> 'email_match',
							'label'		=> 'メールアドレス（確認）',
							'rules'		=> 'trim|required|valid_email|max_length[255]|matches[email]'
						);
			$conf[] = array(
							'field'		=> 'password_match',
							'label'		=> 'パスワード（確認）',
							'rules'		=> 'trim|required|min_length[8]|max_length[128]|matches[password]'
						);
		}

		$this->form_validation->set_rules($conf);
	}

	function already_exists($str)
	{
		if ( $this->member_model->is_email_already_exists($str))
		{
			$this->form_validation->set_message('already_exists', '入力されたメールアドレスは既に登録されています。');
			return FALSE;
		}
		return TRUE;
	}
	
	function already_exists_name($str)
	{
		if ( ! $this->member_model->is_nick_name_already_exists($str))
		{
			$this->form_validation->set_message('already_exists_name', '入力されたニックネームは既に使用されています。');
			return FALSE;
		}
		return TRUE;
	}
}
