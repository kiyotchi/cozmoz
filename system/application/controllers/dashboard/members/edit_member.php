<?php
/**
 * ========================================================================================
 * 
 * Seezoo Add/Edit member Controller
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */

class Edit_member extends SZ_Controller
{
	public $page_title = 'メンバー追加/編集';
	public $page_description = 'メンバーを追加/編集します。';
	
	public $msg;
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model(array('auth_model', 'member_model'));
		
		$this->add_header_item(build_css('css/dashboard/members.css'));
		
	}
	
	function index($mid = 0)
	{
		if ($this->input->post('mid'))
		{
			$mid = $this->input->post('mid');
		}

		$data->ticket = $this->_set_ticket();
		
		$this->_validation($mid);
		
		$this->form_validation->run();
		
		$data->is_validated = (bool)$this->input->post('modify');
		
		if ((int)$mid > 0 || $this->input->post('mid'))
		{
			$data->member = $this->member_model->get_member_one($mid);
		}
		
		$data->mid = $mid;
		
		$this->load->view('dashboard/members/add', $data);
	}
	
	
	function confirm()
	{
		$this->_check_ticket($this->input->post('ticket'));
		
		$data->ticket = $this->input->post('ticket');
		
		$mid = (int)$this->input->post('mid');
		
		$this->_validation($mid);
		
		if ($this->form_validation->run() === FALSE)
		{
			$data->is_validated = TRUE;
			$data->mid = (int)$this->input->post('mid');
			
			$this->load->view('dashboard/members/add', $data);
		}
		else
		{
			$data->hidden = array(
				'nick_name'		=> $this->input->post('nick_name'),
				'email'			=> $this->input->post('email'),
				'password'		=> $this->input->post('password')
			);
			
			$data->mid = $mid;
			$this->load->view('dashboard/members/confirm', $data);
		}
	}
	
	function regist()
	{
		ref_check();
		$this->_check_ticket($this->input->post('ticket'));
		
		$mid = (int)$this->input->post('mid');
		
		$this->_validation($mid);
		
		if ($this->form_validation->run() === FALSE)
		{
			show_error('データの相違があったので処理を中断しました。');
		}
		else
		{
			$post = array(
				'nick_name'	=> $this->input->post('nick_name'),
				'email'		=> $this->input->post('email')
			);
			
			$new_pass = $this->input->post('password');
			
			if ($mid === 0)
			{
				$passwords = $this->dashboard_model->enc_password($new_pass);
				$post['password'] = $passwords['password'];
				$post['hash'] = $passwords['hash'];
				$post['is_activate']	= 1;
				$post['activation_code'] = '';
				$post['relation_site_user']	= 0;
				$post['joined_date'] = db_datetime();
				
				
				$ret = $this->member_model->regist_member($post);
				if ($ret)
				{
					$data->msg = '新しくメンバーを追加しました。';
				}
				else
				{
					$data->msg = '<span class="error">メンバーの追加に失敗しました。</span>';
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
				
				$ret = $this->member_model->update_member($this->input->post('mid'), $post);
				if ($ret)
				{
					$data->msg = 'メンバーを編集しました。';
				}
				else
				{
					$data->msg = '<span class="error">メンバーの編集に失敗しました。</span>';
				}
			}
			$this->load->view('dashboard/members/complete', $data);
		}
	}

	function _validation($mid)
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$conf = array(
			array(
				'label'		=> 'ニックネーム',
				'field'		=> 'nick_name',
				'rules'		=> 'trim|required|min_length[3]|max_length[30]'
			),
			array(
				'label'		=> 'メールアドレス',
				'field'		=> 'email',
				'rules'		=> 'trim|required|min_length[3]|max_length[100]|valid_email' . (($mid  === 0) ? '|callback_is_already' : '')
			)
		);
		if ($mid > 0)
		{
			$conf[] = array(
					'label'		=> 'メンバーーID',
					'field'		=> 'mid',
					'rules'		=> 'trim|required|integer|callback_over_1'
			);
		}
		$conf[] = array(
			'label'		=> 'パスワード',
			'field'		=> 'password',
			'rules'		=> ($mid === 0) ? 'trim|required|min_length[5]|max_length[20]|alpha_numeric' : ''
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
		if ( ! $this->member_model->check_already_email($str))
		{
			$this->form_validation->set_message('is_already', '入力された%sは既に登録されています。');
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