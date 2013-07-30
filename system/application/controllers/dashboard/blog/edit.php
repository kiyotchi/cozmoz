<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ブログエントリ投稿用コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */

class Edit extends SZ_Controller
{
	public $page_title = '新規投稿';
	public $page_description = '新規エントリーを投稿します。';
	
	public $msg;
	public $ticket_name = 'sz_ticket';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('blog_model');
		
		$this->info = $this->blog_model->get_blog_info();
	}

	/**
	 * デフォルトメソッド
	 * @param int $eid
	 */
	function index($eid = FALSE)
	{
		$this->_enable_check();
		
		if (!$eid)
		{
			// エントリーIDが投げられてない場合、postデータがあれば再編集
			if ($this->input->post('modify'))
			{
				$p = new stdClass();
				$p->title               = $this->input->post('title');
				$p->sz_blog_category_id = $this->input->post('sz_blog_category_id');
				$p->body                = $this->input->post('body');
				$p->is_accept_comment   = (int)$this->input->post('is_accept_comment');
				$p->is_accept_trackback = (int)$this->input->post('is_accept_trackback');
				$p->show_date           = $this->input->post('show_date');
				$p->show_hour           = $this->input->post('show_hour');
				$p->show_minute         = $this->input->post('show_minute');
				$p->permalink           = $this->input->post('permalink');
				$data->entry            = $p;
				$data->entry_id         = $this->input->post('sz_blog_id');
			}
			// それ以外は新規投稿
			else
			{
				$data->entry_id = 0;
			}
		}
		// 投稿編集
		else
		{
			$entry = $this->blog_model->get_entry_one($eid);
			$ymd = explode(' ', $entry->show_datetime);
			$his = explode(':', $ymd[1]);
			
			$entry->show_date   = $ymd[0];
			$entry->show_hour   = $his[0];
			$entry->show_minute = $his[1];
			
			$data->entry    = $entry;
			$data->entry_id = $eid;
			if (!$data->entry)
			{
				redirect('dashboard/blog/entries');
			}

		}
		
		$this->_validation();
		
		$data->is_validated = FALSE;
		$data->category = $this->blog_model->get_category_array();
		$data->ticket = $this->_set_ticket();
		
		$this->load->view('dashboard/blog/edit', $data);
	}
	
	/**
	 * 投稿確認画面
	 */
	function confirm()
	{
		$this->_check_ticket();
		$this->_validation();
		
		if (!$this->form_validation->run())
		{
			// バリデーション失敗時はパラメータをセットしてviewに渡す
			$p = new stdClass();
			$p->title               = $this->input->post('title');
			$p->sz_blog_category_id = $this->input->post('sz_blog_category_id');
			$p->body                = $this->input->post('body');
			$p->show_date           = $this->input->post('show_date');
			$p->show_hour           = $this->input->post('show_hour');
			$p->show_minute         = $this->input->post('show_minute');
			$p->is_accept_comment   = (int)$this->input->post('is_accept_comment');
			$p->is_accept_trackback = (int)$this->input->post('is_accept_trackback');
			$p->permalink           = $this->input->post('permalink');
			
			$data->entry        = $p;
			$data->ticket       = $this->_set_ticket();
			$data->category     = $this->blog_model->get_category_array();
			$data->is_validated = TRUE;
			$data->entry_id     = (int)$this->input->post('sz_blog_id');
			
			$this->load->view('dashboard/blog/edit', $data);
		}
		else
		{
			$data->ticket   = $this->_set_ticket();
			$data->category = $this->blog_model->get_category_array();
			$data->hidden   = array(
				'title'               => $this->input->post('title'),
				'sz_blog_category_id' => $this->input->post('sz_blog_category_id'),
				'body'                => $this->input->post('body'),
				'show_date'           => $this->input->post('show_date'),
				'show_hour'           => $this->input->post('show_hour'),
				'show_minute'         => $this->input->post('show_minute'),
				'is_accept_comment'   => (int)$this->input->post('is_accept_comment'),
				'is_accept_trackback' => (int)$this->input->post('is_accept_trackback'),
				'sz_blog_id'          => (int)$this->input->post('sz_blog_id'),
				'permalink'           => $this->input->post('permalink')
			);
			
			$this->load->view('dashboard/blog/edit_confirm', $data);
		}
	}
	
	/**
	 * Ajax投稿下書き保存
	 */
	function ajax_save_to_draft()
	{
		$this->_validation();
		
		if ( ! $this->form_validation->run() )
		{
			exit('validationerror');
		}

		$time = db_datetime();

		$post = array(
			'title'               => $this->input->post('title'),
			'sz_blog_category_id' => (int)$this->input->post('sz_blog_category_id'),
			'body'                => $this->input->post('body'),
			'is_accept_comment'   => (int)$this->input->post('is_accept_comment'),
			'is_accept_trackback' => (int)$this->input->post('is_accept_trackback'),
			//'entry_date'          => $time,
			'show_datetime'       => sprintf(
			                              '%s %s:%s:00',
			                              $this->input->post('show_date'),
			                              $this->input->post('show_hour'),
			                              $this->input->post('show_minute')
			                             ),
			'permalink'           => $this->input->post('permalink'),
			'update_date'         => $time,
			'is_public'           => 0,
			'user_id'             => (int)$this->session->userdata('user_id')
		);
		
		$eid = (int)$this->input->post('sz_blog_id');
		
		$ret = $this->blog_model->create_draft($post, $eid);
		
		echo ( $ret ) ? $ret : 'nosave';
	}
	
	/**
	 * 投稿保存
	 */
	function do_edit()
	{
		$this->_check_ticket();
		$this->_validation();
		
		if (!$this->form_validation->run())
		{
			exit('データの不整合が起きました。');
		}
		else
		{
			// this process is add entry?
			$blog_id = (int)$this->input->post('sz_blog_id');
			$is_new_entry = ($blog_id === 0) ? TRUE : FALSE;
			
			$post = array(
				'title'					=> $this->input->post('title'),
				'sz_blog_category_id'	=> $this->input->post('sz_blog_category_id'),
				'body'						=> htmlspecialchars_decode($this->input->post('body'), ENT_QUOTES),
				'is_accept_comment'		=> (int)$this->input->post('is_accept_comment'),
				'is_accept_trackback'	=> (int)$this->input->post('is_accept_trackback'),
				'permalink'				=> $this->input->post('permalink'),
				'is_public' 				=> 1
				
			);

			$time = db_datetime();
			
			if (!$is_new_entry)
			{
				$draft = $this->blog_model->get_entry_one((int)$blog_id);
				if((int)$draft->is_public === 0 && (int)$draft->drafted_by === 0)
				{
					//If the Entry is drafted and now publish, entry_date is initialized on publish date.
					$post['entry_date'] = $time; 
				}
				$post['update_date'] = $time;
				$ret = $this->blog_model->update_entry($post, (int)$this->input->post('sz_blog_id'));
			}
			else
			{
				// 新規投稿
				$post['user_id']     = (int)$this->session->userdata('user_id');
				$post['entry_date']  = $time;
				$post['update_date'] = $time;
				$ret = $this->blog_model->insert_new_entry($post);
			}
			
			if (!$ret)
			{
				$this->msg = '投稿の編集に失敗しました。';
				$this->load->view('dashboard/blog/complete');
				return;
			}
			
			if ($is_new_entry) // add entry only
			{
				// if ping is auto send, do ping
				if ((int)$this->info->is_auto_ping > 0)
				{
					$data->is_ping = TRUE;
					$data->ping    = $this->blog_model->do_ping_all(
																	array(
																		$post['title'],
																		page_link('blog/article/' . $ret)
																	)
																);
				}
				else
				{
					$data->is_ping = FALSE;
					$data->ping = $this->blog_model->get_ping_list();
					$data->title = $this->input->post('title');
					$data->sz_blog_id = $ret;
				}
				$this->msg = '新規投稿を追加しました。';
				$this->load->view('dashboard/blog/complete', $data);
			}
			else
			{
				$this->msg = '投稿の編集が完了しました。';
				$this->load->view('dashboard/blog/complete');
			}
		}
	}
	
	/**
	 * 一件ずつping送信
	 */
	function send_ping_single()
	{
		$token = $this->input->post('token');
		if (!$token || $token !== $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		
		$data = array(
					form_prep(
						$this->input->post('title'),
						page_link('blog/article/' . (int)$this->input->post('id'))
					)
				);
		
		$id = (int)$this->input->post('ping_id');
		$ret = $this->blog_model->do_ping($id, $data);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}
	
	/**
	 * ブログが利用可能かどうか判定
	 */
	function _enable_check()
	{
		// if blog id unabled, redirect index
		if ((int)$this->info->is_enable === 0)
		{
			redirect('dashboard/blog/settings');
		}
	}
	
	/**
	 * 投稿用バリデーションセット
	 */
	function _validation()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
		$conf = array(
			array(
					'field'		=> 'title',
					'label'		=> '記事タイトル',
					'rules'		=> 'trim|required|max_length[255]'
			),
			array(
					'field'		=> 'body',
					'label'		=> '投稿本文',
					'rules'		=> 'trim|required'
			),
			array(
					'field'		=> 'sz_blog_category_id',
					'label'		=> '投稿カテゴリ',
					'rules'		=> 'trim|required|numeric'
			),
			array(
					'field'		=> 'show_date',
					'label'		=> '公開日',
					'rules'		=> 'trim|date_format'
			),
			array(
					'field'		=> 'show_hour',
					'label'		=> '公開日（時間）',
					'rules'		=> 'trim|integer|range[0:24]'
			),
			array(
					'field'		=> 'show_minute',
					'label'		=> '公開日（分）',
					'rules'		=> 'trim|range[0:60]'
			),
			array(
					'field'		=> 'permalink',
					'label'		=> 'URIパス',
					'rules'		=> 'trim|alpha_dash'
			)
		);
		
		$this->form_validation->set_rules($conf);
	}
	
	/**
	 * ワンタイムトークンセット
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata($this->ticket_name, $ticket);
		return $ticket;
	}
	
	/**
	 * トークンチェック
	 * @param string $ticket
	 */
	function _check_ticket($ticket = FALSE)
	{
		if (!$ticket)
		{
			$ticket = $this->input->post($this->ticket_name);
		}
		if (!$ticket || $ticket !== $this->session->userdata($this->ticket_name))
		{
			exit('不正な操作です。また、リロードは禁止されています。');
		}
	}
}
