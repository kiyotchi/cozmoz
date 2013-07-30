<?php
/**
 * ========================================================================================
 * 
 * Seezoo member list manage Controller
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */

class Member_list extends SZ_Controller
{
	
	public $page_title = 'メンバー一覧/検索';
	public $page_description = 'サイト登録されているメンバーが確認できます。';
	
	public $msg;
	public $page = 'members';
	
	public $limit = 20;
	
	protected $upload_dir =  'files/members/';
	protected $allowed_types = 'gif|jpg|jpeg|png';
	protected $export_maxlength = 30;
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model(array('auth_model', 'member_model'));
		
		// try make directory
		if ( ! is_dir(FCPATH . rtrim($this->upload_dir, '/')) )
		{
			@mkdir(FCPATH . rtrim($this->upload_dir, '/'));
		}
		
		$this->add_header_item(build_css('css/dashboard/members.css'));
		//$this->add_header_item(build_javascript('js/members.js'));
	}
	
	function index($offset = 0)
	{
		// check msg
		if ( $this->session->flashdata('msg_flag') !== FALSE )
		{
			$this->msg = $this->_create_message((int)$this->session->flashdata('msg_flag'));
		}
		$data->members = $this->member_model->get_all_members($this->limit, $offset);
		
		$total = $this->member_model->get_all_members_count();
		
		$endoftotal = (($offset+ $this->limit) > $total) ? $total : ($offset + $this->limit);
		if($total)
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
		
		// does your admin account has exproted?
		$data->is_exported = $this->member_model->is_admin_exproted($this->user_id);
		
		$this->load->view('dashboard/members/list', $data);
	}
	
	function export_account()
	{
		$nick_name = $this->input->post('nick_name');
		
		if ( $nick_name === '' )
		{
			$flag = 1;
		}
		else if ( mb_strlen($nick_name) > $this->export_maxlength )
		{
			$flag = 2;
		}
		else 
		{
			$flag = $this->member_model->export_account($nick_name, $this->user_id);
			$flag = (int)$flag + 3;
		}
		
		$this->session->set_flashdata('msg_flag', $flag);
		redirect('dashboard/members/member_list');
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
		redirect('dashboard/members/member_list/search/' . rawurlencode($u) . '/' . rawurlencode($email));
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
		
		$data->search_result = $this->member_model->search_member($username, $email, $this->limit, (int)$offset);
		
		$total = $this->member_model->search_result_count($username, $email);
		
		$endoftotal = (($offset+ $this->limit) > $total) ? $total : ($offset + $this->limit);
		if($total)
		{
			 $data->total = $total . '件中' . ($offset + 1) . '-' . $endoftotal . '件表示';
		}
		else
		{
			$data->total = '';
		}
		
		$path = page_link() . 'dashboard/members/search/' . rawurlencode($username) . '/' . rawurlencode($email) . '/';
		$data->pagination = $this->_pagination($path, $total, 5, $this->limit);
		
		$data->username_q = (!empty($username)) ? $username : '-';
		$data->email_q = (!empty($email)) ? $email : '-';
		
		
		$this->load->view('dashboard/members/search_result', $data);
		
	}
	
	function profile_image_form($member_id = 0)
	{
		$data->member_id = $member_id;
		
		$this->load->view('dashboard/members/upload_form', $data);
	}
	
	function profile_upload()
	{
				// target member_id
		$member_id = (int)$this->input->post('member_id');
		
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
			$data->member_id = $member_id;
			$this->load->view('dashboard/members/upload_form', $data);
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
				'source_image'	=> $upload_data['full_path'],
				'create_thumb'	=> FALSE,
				'width'			=> 100,
				'height'			=> 100,
				'maintain_ratio'	=> TRUE
			);
			
			$this->load->library('image_lib', $image_conf);
			
			if ( ! $this->image_lib->resize() )
			{
				$data->error = '画像の縮小に失敗しました';
				$this->load->view('dashboard/members/upload_form', $data);
				return;
			}
		}
		
		$res = $this->member_model->update_profile_image($db_data, $member_id);
		
		$data->member_id = $member_id;
		$data->image = $db_data['image_data'];
		
		$this->load->view('dashboard/members/upload_form', $data);
	}
	
	function delete_profile_image($member_id = 0)
	{
		if (!$member_id)
		{
			return;
		}
		
		$data = array(
			'image_data' => null
		);
		$ret = $this->member_model->update_profile_image($data, $member_id);
		
		echo ($ret) ? 'complete' : 'error';
	}
	
	function ajax_unlock_member($mid = 0, $token = FALSE)
	{
		if (!$mid || !$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		
		$ret = $this->member_model->unlock_member_account($mid);
		
		echo ( $ret ) ? 'success' : 'process_error';
		//redirect('dashboard/members/member_list');
	}
	
	// from ajax
	function detail($mid = FALSE)
	{
		if (!$mid)
		{
			echo 'access denied.';
		}
		
		$data->member = $this->member_model->get_member_detail((int)$mid);
		$data->attribute_values = $this->member_model->get_member_attributes_data($mid);
		
		$data->ticket = $this->session->userdata('sz_token');
		
		$this->load->view('dashboard/members/ajax_detail', $data);
	}
	
	// from ajax
	function delete($uid, $token = FALSE, $is_redirect = null)
	{
		if (!$token || $this->session->userdata('sz_token') !== $token)
		{
			exit('access_denied.');
		}
		
		$ret = $this->member_model->delete_member_one($uid);
		
		if ( ! is_null($is_redirect) )
		{
			redirect('dashboard/members/member_list');
		}
		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
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
	
	function _create_message($flag)
	{
		switch ( $flag )
		{
			case 1:
				return 'ニックネームは空欄にはできません。';
			case 2:
				return 'ニックネームは' . $this->export_maxlength . '文字以内で入力してください。';
			case 3:
				return 'アカウントのエクスポートに失敗しました。';
			case 4:
				return 'アカウントをエクスポートしました。';
			default:
				return '';
		}
	}
}