<?php
/**
 * Seezoo ユーザー一覧・検索コントローラ
 */
class User_list extends SZ_Controller
{
	
	public $page_title = 'ユーザー一覧/検索';
	public $page_description = '管理者として登録されているユーザーが確認できます。';
	
	public $msg;
	public $page = 'users';
	
	public $limit = 20;
	
	protected $upload_dir =  'files/members/';
	protected $allowed_types = 'gif|jpg|jpeg|png';
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model(array('auth_model', 'user_model'));
		
		// try make directory
		if ( ! is_dir(FCPATH . rtrim($this->upload_dir, '/')) )
		{
			@mkdir(FCPATH . rtrim($this->upload_dir, '/'));
		}
	}
	
	function index($offset = 0)
	{
		$data->users = $this->user_model->get_all_admin_users($this->limit, $offset);
		$total       = $this->user_model->get_all_admin_users_count();
		$endoftotal  = (($offset+ $this->limit) > $total) ? $total : ($offset + $this->limit);

		if ( $total )
		{
			 $data->total = $total . '件中' . ($offset + 1) . '-' . $endoftotal . '件表示';
		}
		else
		{
			$data->total = '';
		}
		
		$path = $this->config->site_url() . 'dashboard/users/user_list/index/';
		$data->pagination = $this->_pagination($path, $total, 5, $this->limit);
		
		$data->search_token = $this->_set_ticket();
		$data->is_master    = $this->is_master;
		
		$this->load->view('dashboard/users/list', $data);
	}
	
	function search_init()
	{
		//$this->_check_ticket($this->input->post('token'));
		$u = $this->input->post('user_name');
		$email = $this->input->post('email');
		
		// empty strign replace to '-' to use segment
		if ($u == '')
		{
			$u = '-';
		}
		if ($email == '')
		{
			$email = '-';
		}
		
		// redirect search method for delete postrequest
		redirect('dashboard/users/user_list/search/' . rawurlencode($u) . '/' . rawurlencode($email));
	}
	
	function search($username = '-', $email = '-', $offset = 0)
	{
		if ($username == '-')
		{
			$username = '';
		}
		else
		{
			$username = rawurldecode($username);
		}
		
		if ($email == '-')
		{
			$email = '';
		}
		$email = rawurldecode($email);
		
		$data->search_result = $this->user_model->search_user($username, $email, $this->limit, (int)$offset);
		
		$total = $this->user_model->search_result_count($username, $email);
		
		$endoftotal = (($offset+ $this->limit) > $total) ? $total : ($offset + $this->limit);
		if($total)
		{
			 $data->total = $total . '件中' . ($offset + 1) . '-' . $endoftotal . '件表示';
		}
		else
		{
			$data->total = '';
		}
		
		$path = $this->config->site_url() . 'dashboard/users/search/' . rawurlencode($username) . '/' . rawurlencode($email) . '/';
		$data->pagination = $this->_pagination($path, $total, 5, $this->limit);
		
		$data->username_q = (!empty($username)) ? $username : '-';
		$data->email_q = (!empty($email)) ? $email : '-';
		
		
		$this->load->view('dashboard/users/search_result', $data);
		
	}
	
	function profile_image_form($user_id = 0)
	{
		$data->user_id = $user_id;
		
		$this->load->view('dashboard/users/upload_form', $data);
	}
	
	function profile_upload()
	{
				// target member_id
		$user_id = (int)$this->input->post('user_id');
		
		// load the Upload class
		$this->load->library('upload');
		
		// configure
		$config = array(
			'upload_path'		=> $this->upload_dir,
			'allowed_types'	=> $this->allowed_types,
			'max_size'				=> 100,  // KB
			'overwrite'			=> FALSE,
			'encrypt_name'	=> TRUE,
			'remove_spaces'	=> TRUE
		);
		
		$this->upload->initialize($config);
		
		// try upload
		$result = $this->upload->do_upload('image_data');
		
		if (!$result)
		{
			$data->error = $this->upload->display_errors('', '\n');
			$this->load->view('dashboard/users/upload_form', $data);
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
				'source_image'		=> $upload_data['full_path'],
				'create_thumb'		=> FALSE,
				'width'						=> 100,
				'height'						=> 100,
				'maintain_ratio'	=> TRUE
			);
			
			$this->load->library('image_lib', $image_conf);
			
			if ( ! $this->image_lib->resize() )
			{
				$data->error = '画像の縮小に失敗しました';
				$this->load->view('dashboard/users/upload_form', $data);
				return;
			}
		}
		
		$res = $this->user_model->update_profile_image($db_data, $user_id);
		
		$data->user_id = $user_id;
		$data->image = $db_data['image_data'];
		
		$this->load->view('dashboard/users/upload_form', $data);
	}
	
	function delete_profile_image($user_id = 0)
	{
		if (!$user_id)
		{
			return;
		}
		
		$data = array(
			'image_data' => null
		);
		$ret = $this->user_model->update_profile_image($data, $user_id);
		
		echo ($ret) ? 'complete' : 'error';
	}
	
	function relogin_width_other_address($uid, $token = FALSE)
	{
		if (!$uid || !$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		$this->load->model('auth_model');
		
		$ret = $this->auth_model->relogin_with_other_user($uid);
		
		if ($ret)
		{
			$this->session->set_userdata('rollback_user', $this->user_id);
			redirect('dashboard/panel');
		}
		else
		{
			redirect('dashboard/users/user_list');
		}
	}
	
	function unlock_user($uid = 0, $token = FALSE)
	{
		if (!$uid || !$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		
		$ret = $this->user_model->unlock_user_account($uid);
		
		redirect('dashboard/users/user_list');
	}
	
	// from ajax
	function detail($uid = FALSE)
	{
		if (!$uid)
		{
			echo 'access denied.';
		}
		
		$data->user = $this->dashboard_model->get_user_one((int)$uid);
		
		$data->ticket = $this->_set_ticket();
		
		$this->load->view('dashboard/users/ajax_detail', $data);
	}
	
	// from ajax
	function delete($uid, $token = FALSE)
	{
		$from_ajax = FALSE;
		if ( $this->input->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest' )
		{
			$ticket = $this->session->userdata('sz_token');
			$from_ajax = TRUE;
		}
		else
		{
			$ticket = $this->session->flashdata('ticket');
		}
		
		if (!$token || $ticket !== $token)
		{
			exit('access_denied.');
		}
		
		if ((int)$uid <= 1)
		{
			if ( $from_ajax )
			{
				echo 'cannot';
				return;
			}
			redirect('dashboard/users/user_list/index');
		}
		
		$ret = $this->user_model->delete_user_one($uid);
		
		if ($ret)
		{
			if ( $from_ajax )
			{
				echo 'complete';
				return;
			}
			redirect('dashboard/users/user_list/index');
		}
		else
		{
			if ( $from_ajax )
			{
				echo 'error';
				return;
			}
			redirect('dashboard/users/user_list/index');
		}
	}
	
	
	function _pagination($path, $total, $segment, $limit)
	{
		$this->load->library('pagination');
		
		$config = array(
		  'base_url'      => $path,
		  'total_rows'   => $total,
		  'per_page'    => $limit,
		  'uri_segment'=> $segment,
		  'num_links'    => 5,
		  'prev_link'     => '&laquo;前へ',
		  'next_link'     => '次へ&raquo;'
		);
		$this->pagination->initialize($config);
		
		return $this->pagination->create_links();
	}
	
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_flashdata('ticket', $ticket);
		
		return $ticket;
	}
	
	function _check_ticket($token, $ref_url = FALSE)
	{
		if (!$token || $token !== $this->session->flashdata('ticket'))
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
	}
}