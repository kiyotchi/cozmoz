<?php
class Edit_user extends SZ_Controller
{
	public $page_title = 'ユーザー追加/編集';
	public $page_description = 'ユーザーを追加/編集します。';
	
	public $msg;
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model(array('auth_model', 'user_model'));
		
	}
	
	function index($uid = FALSE)
	{
		if ($uid)
		{
			if ((int)$uid === 1 && !$this->is_master)
			{
				redirect('dashboard/users');
			}
		}
		else if ($this->input->post('uid'))
		{
			$uid = $this->input->post('uid');
		}
		else
		{
			$uid = 0;
		}
		if (!$this->is_master && $this->user_id != $uid || ($uid > 0 && !$this->user_model->is_dashboard_user($uid)))
		{
			//redirect('dashboard/users/user_list');
			$this->load->view('dashboard/users/master_only');
			return;
		}

		$data->ticket = $this->_set_ticket();
		
		$this->_validation($uid);
		
		$this->form_validation->run();
		
		$data->is_validated = (bool)$this->input->post('modify');
		
		if ((int)$uid > 0 || $this->input->post('uid'))
		{
			$data->user = $this->dashboard_model->get_user_one($uid);
		}
		
		$data->uid = $uid;
		
		$this->load->view('dashboard/users/add', $data);
	}
	
	
	function confirm()
	{
		$this->_check_ticket($this->input->post('ticket'));
		
		$data->ticket = $this->input->post('ticket');
		
		$uid = (int)$this->input->post('uid');
		
		if (!$this->is_master && $uid != $this->user_id)
		{
			redirect('dashboard/users/user_list');
		}
		
		$this->_validation($uid);
		
		if ($this->form_validation->run() === FALSE)
		{
			$data->is_validated = TRUE;
			$data->uid = (int)$this->input->post('uid');
			
			$this->load->view('dashboard/users/add', $data);
		}
		else
		{
			$data->hidden = array(
				'user_name'		=> $this->input->post('user_name'),
				'email'			=> $this->input->post('email'),
				'password'		=> $this->input->post('password'),
				'admin_flag'	=> (int)$this->input->post('admin_flag')
			);
			
			$data->uid = $uid;
			$this->load->view('dashboard/users/confirm', $data);
		}
	}
	
	function regist()
	{
		$this->_check_ticket($this->input->post('ticket'));
		
		$uid = (int)$this->input->post('uid');
		
		if (!$this->is_master && $uid != $this->user_id)
		{
			redirect('dashboard/users/user_list');
		}
		
		$this->_validation($uid);
		
		if ($this->form_validation->run() === FALSE)
		{
			show_error('データの相違があったので処理を中断しました。');
		}
		else
		{
			$post = array(
				'user_name'		=> $this->input->post('user_name'),
				'email'			=> $this->input->post('email'),
				'regist_time'	=> date('Y-m-d H:i:s', time())
			);
			if ($this->is_master || ($uid == 0 && $uid != $this->user_id))
			{
				$post['admin_flag'] =  (int)$this->input->post('admin_flag');
			}
			
			$new_pass = $this->input->post('password');
			
			if ($uid === 0)
			{
				$passwords = $this->dashboard_model->enc_password($new_pass);
				$post['password'] = $passwords['password'];
				$post['hash'] = $passwords['hash'];
				$post['login_times'] = 0;
				$post['is_admin_user'] = 1;
				
				$ret = $this->dashboard_model->regist_user($post);
				if ($ret)
				{
					$data->msg = '新しくユーザーを追加しました。';
				}
				else
				{
					$data->msg = '<span class="error">ユーザーの追加に失敗しました。</span>';
				}
			}
			else
			{
				// if edit user and password is empty, not update password.
				if ($new_pass != '')
				{
					$passwords = $this->dashboard_model->enc_password($new_pass);
					$post['password'] = $passwords['password'];
					$post['hash'] = $passwords['hash'];
				}
				
				$ret = $this->user_model->update_user($this->input->post('uid'), $post);
				if ($ret)
				{
					$data->msg = 'ユーザーを編集しました。';
				}
				else
				{
					$data->msg = '<span class="error">ユーザーの編集に失敗しました。</span>';
				}
			}
			$this->load->view('dashboard/users/complete', $data);
		}
	}

	function _validation($uid)
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$conf = array(
			array(
				'label'		=> 'ユーザー名',
				'field'		=> 'user_name',
				'rules'		=> 'trim|required|min_length[3]|max_length[100]|callback__already_user'
			),
			array(
				'label'		=> 'メールアドレス',
				'field'		=> 'email',
				'rules'		=> 'trim|required|min_length[3]|max_length[100]|valid_email' . (($uid  === 0) ? '|callback_is_already' : '')
			),
			array(
				'label'		=> '管理者権限',
				'field'		=> 'admin_flag',
				'rules'		=> 'trim|integer'
			)
		);
		if ($uid > 0)
		{
			$conf[] = array(
					'label'		=> 'ユーザーID',
					'field'		=> 'uid',
					'rules'		=> 'trim|required|integer|callback_over_1'
			);
		}
		$conf[] = array(
			'label'		=> 'パスワード',
			'field'		=> 'password',
			'rules'		=> ($uid === 0) ? 'trim|required|min_length[5]|max_length[20]|alpha_numeric' : ''
		);

		
		$this->form_validation->set_rules($conf);
	}
	
	function over_1($str)
	{
		if ((int)$str < 1)
		{
			$this->form_validation->set_message('over_1', '%sが正しくありません。');
			return FALSE;
		}
		return TRUE;
	}
	
	function is_already($str)
	{
		if ( ! $this->user_model->check_already_email($str))
		{
			$this->form_validation->set_message('is_already', '入力された%sは既に登録されています。');
			return FALSE;
		}
		return TRUE;
	}
	
	function _already_user($str)
	{
		$uid = (int)$this->input->post('uid');
		
		if ( ! $this->user_model->check_already_username($str, $uid) )
		{
			$this->form_validation->set_message('_already_user', '入力された%sは既に登録されています。');
			return FALSE;
		}
		return TRUE;
	}
	
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_flashdata('sz_ticket', $ticket);
		
		return $ticket;
	}
	
	function _check_ticket($token, $ref_url = FALSE)
	{
		if (!$token || $token != $this->session->flashdata('sz_ticket'))
		{
			exit('access denied');
		}
		if ($ref_url)
		{
			if (strpos($_SERVER['HTTP_REFERER'], $ref_url) === FALSE)
			{
				exit('access denied');
			}
		}
		$this->session->keep_flashdata('sz_ticket');
	}
}