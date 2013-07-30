<?php
/**
 * Seezoo 一般ページコントローラ
 */

class Page_list extends SZ_Controller
{
	public $page_title = '一般ページ管理';
	public $page_description = 'システムから生成されたページリストを表示します。';

	public $msg = '';

	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model('sitemap_model');
	}

	function index()
	{
		$this->load->helper('cookie_helper');

		$system = get_cookie('sitemap_view_system');

		$data->pages = $this->sitemap_model->get_page_structures();

		if ($system)
		{
			$data->system_pages = $this->sitemap_model->get_page_structures_system();
		}

		$this->load->view('dashboard/pages/structure', $data);
	}

	### not implement...
//	function with_system()
//	{
//		$system = (int)$this->input->post('with_system');
//
//		$this->load->helper('cookie_helper');
//		if ($system === 1)
//		{
//			set_cookie(array(
//				'name'		=> 'sitemap_view_system',
//				'value' 	=> 1,
//				'domain' 	=> '',
//				'expire'	=> $system ? 1200 : 0,
//				'path' 	=> '/',
//				'prefix' 	=> ''
//			));
//		}
//		else
//		{
//			delete_cookie('sitemap_view_system');
//		}
//
//		redirect('dashboard/pages/page_list');
//	}

	function delete($pid, $token = FALSE)
	{
		if (!$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$ret = $this->sitemap_model->delete_page((int)$pid);

		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
		}

	}

	function ajax_get_child($pid)
	{
		$data->childs = $this->sitemap_model->get_child_pages($pid);

		$this->load->view('dashboard/pages/ajax_child_list', $data);
	}

	function ajax_get_child_block($pid)
	{
		$data->childs = $this->sitemap_model->get_child_pages((int)$pid);

		$this->load->view('dashboard/pages/ajax_child_list_block', $data);
	}

	function ajax_arrange_moveto()
	{
		$ticket = $this->input->post('token');
		if (!$ticket || $ticket !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$from = (int)$this->input->post('from');
		$to = (int)$this->input->post('to');

		$ret = $this->sitemap_model->move_page($from, $to);

		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
		}
	}

	function ajax_arrange_aliasto()
	{
		$ticket = $this->input->post('token');
		if (!$ticket || $ticket != $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$from = (int)$this->input->post('from');
		$to   = (int)$this->input->post('to');
		$ret  = $this->sitemap_model->copy_page($from, $to, TRUE, $this->user_id);

		if ( $ret )
		{
			echo ( $ret === 'already' ) ? $ret : 'complete';
		}
		else
		{
			echo 'error';
		}
	}

	function ajax_arrange_copyto()
	{
		$ticket = $this->input->post('token');
		if (!$ticket || $ticket !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$from      = (int)$this->input->post('from');
		$to        = (int)$this->input->post('to');
		$recursive = ( $this->input->post('recursive') > 0 ) ? TRUE : FALSE;

		$ret = $this->sitemap_model->copy_page($from, $to, FALSE, $this->user_id, $recursive);

		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
		}
	}

	function ajax_arrange_copyto_same_level()
	{
		$ticket = $this->input->post('token');
		if (!$ticket || $ticket !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$from      = (int)$this->input->post('from');
		$to        = (int)$this->input->post('to');
		$recursive = ( $this->input->post('recursive') > 0 ) ? TRUE : FALSE;

		$ret = $this->sitemap_model->copy_page_same($from, $to, $this->user_id, $recursive);

		if ($ret && is_array($ret))
		{
			echo json_encode($ret);
		}
		else
		{
			echo 'error';
		}
	}

	function move_page_one($token = FALSE)
	{
		if (!$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$from = (int)$this->input->post('from');
		$to = (int)$this->input->post('to');
		$method = $this->input->post('method');

		$ret = $this->sitemap_model->move_page_order($from, $to, $method);

		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
		}
	}

//	function refresh($token = FALSE)
//	{
//		if (!$token || $token !== $this->session->userdata('sz_token'))
//		{
//			exit('access denied');
//		}
//
//		// pre open tree
//		$open = $this->input->post('open');
//
//		$data->pages = $this->sitemap_model->get_page_structures_all();
//		$data->is_open = explode('|', $open);
//
//		$this->load->view('dashboard/pages/refreshed_structure', $data);
//
//	}

	function refresh($token = FALSE)
	{
		if (!$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		//$data->pages = $this->sitemap_model->get_all_page();
		$data->pages = $this->sitemap_model->get_page_structures();

		$open = $this->input->post('open');
		$data->is_open = explode('|', $open);
		
		$this->load->view('dashboard/pages/page_structure', $data);
	}

	function sort_display_order($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$parent = ($this->input->post('master'));
		$order = $this->input->post('order'); // array

		$ret = $this->sitemap_model->do_sort_display_order($parent, $order);

		if ($ret)
		{
			exit('complete');
		}
		else
		{
			exit('error');
		}
	}
}