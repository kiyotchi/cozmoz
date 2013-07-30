<?php
/**
 * ========================================================================================
 * 
 * Seezoo member attribute manage Controller
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */
class Attributes extends SZ_Controller
{
	
	public $page_title = 'メンバー項目設定';
	public $page_description = 'メンバーの追加項目を設定します。';
	
	public $msg;
	
	public $limit = 20;
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model(array('member_model'));
		
		$this->add_header_item(build_css('css/dashboard/members.css'));
		//$this->add_header_item(build_javascript('js/members.js'));
	}
	
	/**
	 * Default method
	 */
	function index()
	{
		$data->attributes = $this->member_model->get_member_attributes(TRUE, TRUE);
		$data->attribute_types = $this->member_model->get_member_attribute_types();

		// flag exists?
		if ( $this->session->flashdata('att_flag') !== FALSE)
		{
			switch ($this->session->flashdata('att_flag'))
			{
				case 1:
					$this->msg =  'メンバー項目を追加/編集しました。';
					break;
				case 0:
					$this->msg = 'メンバー項目の追加/更新にしました';
					break;
				case 'notfound':
					$this->msg = '編集対象の項目が見つかりませんでした。';
					break;
				case 'delete_att':
					$this->msg = 'メンバー項目を削除しました。';
					break;
				case 'delete_att_miss':
					$this->msg = 'メンバー項目の削除に失敗しました。';
					break;
			}
		}
		
		$this->load->view('dashboard/members/attribute', $data);
	}
	
	/**
	 * 項目追加/編集
	 * @param $att_id
	 */
	function edit($att_id = 0)
	{
		$data->att_id = $att_id;
		$data->ticket = $this->_set_ticket();
		$data->attributes = $this->member_model->get_member_attributes(TRUE);
		$data->attribute_types = $this->member_model->get_member_attribute_types();
		
		if ( $data->att_id > 0 )
		{
			$data->att = $this->member_model->get_member_attribute_setting($att_id);
			if ( $data->att !== FALSE )
			{
				// parse and assign
				$data->v_rules = array_filter(explode('|', $data->att->validate_rule));
				$data->options = array_filter(explode(':', $data->att->options));
				
				$data->max_length = preg_match('/max_length\[(0-9)+\]/', $data->att->validate_rule)
										? preg_replace('/max_length\[(0-9)+\]/', '$1', $data->att->validate_rule)
										: '';
				$data->min_length = preg_match('/min_length\[(0-9)+\]/', $data->att->validate_rule)
										? preg_replace('/min_length\[(0-9)+\]/', '$1', $data->att->validate_rule)
										: '';
				$this->load->view('dashboard/members/edit_attribute', $data);
			}
			else
			{
				// attribute not found
				$this->session->set_flashdata('att_flag', 'notfound');
				redirect('dashboard/members/attributes');
			}

		}
		else 
		{
			$this->load->view('dashboard/members/add_attribute', $data);
		}
	}
	
	/**
	 * 項目保存
	 */
	function do_edit()
	{
		ref_check();
		$this->_check_ticket($this->input->post('ticket'));
		$id = (int)$this->input->post('att_id');
		
		// regist
		$post = array(
			'is_inputable'		=> (int)$this->input->post('is_inputable'),
			'attribute_name'		=> $this->input->post('attribute_name', TRUE),
			'attribute_type'		=> $this->input->post('attribute_type', TRUE)
		);
		// etc values
		$post['rows'] = ($post['attribute_type'] === 'textarea')
								? (int)$this->input->post('rows')
								: 0;
		$post['cols'] = ($post['attribute_type'] === 'textarea')
								? (int)$this->input->post('cols')
								: 0;
		// options
		$option = array();
		foreach ( $this->input->post('options') as $opt )
		{
			if ( $opt !== '' ) {
				$option[] = $opt;
			}
		}
		$post['options'] = implode(':', $option);
		
		// validate_rule
		$post['validate_rule'] = $this->_make_validate_rule_from_postdata();
		
		if ( $id > 0 )
		{
			// update
			$ret = $this->member_model->update_member_attribute($post, $id);
		}
		else
		{
			// insert
			$post['is_use'] = 1;
			$post['display_order'] = $this->member_model->get_member_attribute_max_display_order();
			
			$ret = $this->member_model->insert_attribute($post);
		}
		
		$this->session->set_flashdata('att_flag', (int)$ret);
		redirect('dashboard/members/attributes');
	}
	
	/**
	 * Ajax応答：項目順番並び替え
	 * @param unknown_type $token
	 */
	function change_display_order($token = FALSE)
	{
		if ( !$token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied:iligal token.');
		}
		
		$from = (int)$this->input->post('from');
		$to   = (int)$this->input->post('to');
		
		if ( $this->member_model->update_attribute_display_order($from, $to) === TRUE )
		{
			echo 'success';
		}
		else 
		{
			echo 'Database Error';
		}
		
	}
	
	/**
	 * Ajax応答：メンバー追加項目の使用/未使用切り替え
	 * @param $type
	 * @param $att_id
	 */
	function setuse($type = 'nouse', $att_id)
	{		
		if ( $type === 'nouse' )
		{
			$is_use = 0;
		}
		else if ( $type === 'douse' )
		{
			$is_use = 1;
		}
		else
		{
			exit('invalid parameters.');
		}
		
		$ret = $this->member_model->update_member_attribute_use($is_use, $att_id);
		
		echo ( $ret ) ? 'success' : 'database error';
		
	}
	
	function delete($att_id, $token = FALSE)
	{
		if ( !$token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied:iligal token.');
		}
		else if ( ! $att_id )
		{
			exit('invalid parameters');
		}
		
		$ret = $this->member_model->delete_member_attribute($att_id);
		if ( $ret )
		{
			$this->session->set_flashdata('att_flag', 'delete_att');
		}
		else 
		{
			$this->session->set_flashdata('att_flag', 'delete_att_miss');
		}
		
		redirect('dashboard/members/attributes');
		
	}
	
	/**
	 * 項目編集のPOSTデータから検証ルール文字列生成
	 */
	function _make_validate_rule_from_postdata()
	{
		$rules = array();
		
		if ( (int)$this->input->post('required') > 0 )
		{
			$rules[] = 'required';
		}
		if ( (int)$this->input->post('integer') > 0 )
		{
			$rules[] = 'integer';
		}
		if ( (int)$this->input->post('valid_url') > 0 )
		{
			$rules[] = 'callback_is_valid_url'; // special case
		}
		if ( (int)$this->input->post('min_length') > 0 )
		{
			$rules[] = 'min_length[' . (int)$this->input->post('min_length') . ']';
		}
		if ( (int)$this->input->post('max_length') > 0 )
		{
			$rules[] = 'max_length[' . (int)$this->input->post('max_length') . ']';
		}
		
		return ( count($rules) > 0 ) ? implode('|', $rules) : '';
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