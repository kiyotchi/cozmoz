<?php
/**
 * ===============================================================================
 *
 * Seezoo blogコントローラ
 *
 * ブログ表示用コントローラ
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */
class Blog extends SZ_Controller
{
	public static $page_title = 'ブログ';
	public static $description = 'ユーザー側に表示するブログページです。';

	public $entry_id;
	public $category_id;
	public $template_path;
	public $rel_template_path;

	public $content_data;
	public $menu_data;

	public $limit = 3;
	protected $blog_info;

	private $blog_commented_flag_name = 'sz_blog_rc_flag';

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model(array('blog_model', 'page_model', 'init_model'));
		$this->load->helper('blog_helper');
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');

		// check blog is enable?
		$this->blog_info = $this->blog_model->get_blog_info();

		if ((int)$this->blog_info->is_enable === 0)
		{
			show_404();
		}

		$this->generate_cms_mode();

		// add RSS link
		$this->_generate_rss_link_tag();
		
		// CSS device detection
		$mobile = Mobile::get_instance();
		if ( ! $mobile->is_mobile() )
		{
			if ( $mobile->is_smartphone() )
			{
				if ( file_exists(FCPATH . 'templates/' . $this->_rel_template_path . 'css/blog_mobile.css') )
				{
					$this->add_header_item(build_css(file_link() . 'templates/' . $this->_rel_template_path . 'css/blog_mobile.css'));
				}
			}
			else
			{
				if ( file_exists(FCPATH . 'templates/' . $this->_rel_template_path . 'css/blog.css') )
				{
					$this->add_header_item(build_css(file_link() . 'templates/' . $this->_rel_template_path . 'css/blog.css'));
				}
			}
			$this->add_header_item(build_javascript(file_link() . 'js/blog.js'));
		}
		

	}

	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		if ( $this->is_ssl_page )
		{
			redirect(ssl_page_link() . 'blog/entries');
		}
		else
		{
			redirect('blog/entries');
		}
	}


	/**
	 * エントリ一覧表示
	 * @access public
	 * @param $offset
	 */
	function entries($offset = 0)
	{
		$data->entry         = $this->blog_model->get_recent_entry($this->blog_info->entry_limit, $offset);
		$data->category_list = $this->blog_model->get_category_array();
		$data->time          = time();
		$data->page_type     = 'entries';

		//Pagination
		$path                = page_link('blog/entries/');
		$total               = $this->blog_model->get_entry_count();
		$data->pagination    = $this->_pagination($path, $total, 3, $this->blog_info->entry_limit);

		$this->_setup_content($data);
	}

	/**
	 * 日付別エントリー表示
	 * @access public
	 * @param $year
	 * @param $month
	 * @param $date
	 * @param $offset
	 */
	function postdate($year = '', $month = '', $date = '')
	{
		if(empty($year) && empty($month))
		{
			$year  = date('Y');
			$month = date('m');
			redirect('blog/postdate/' . $year . '/' . $month);
		}

		if (!empty($month) && !empty($date)) // 日別
		{
			$start = $year . '-' . $month . '-' . $date . ' 00:00:00';
			$end   = $year . '-' . $month . '-' . ($date + 1) . ' 00:00:00';
		}
		elseif(!empty($month) && empty($date))  // 月別
		{
			$start = $year . '-' . $month . '-01 00:00:00';
			$end   = $year . '-' . ($month + 1) . '-01 00:00:00';
			$date  = '00';
		}
		else // 年別
		{
			$start = $year . '-' . '01' . '-01 00:00:00';
			$end   = ($year + 1) . '-' . '01' . '-01 00:00:00';
			$month = '00';
			$date  = '00';
		}

		$data->entry         = $this->blog_model->get_entry_by_date($start, $end);
		$data->category_list = $this->blog_model->get_category_array();
		$data->time          = mktime(0, 0, 0, (int)$month, (int)$date, (int)$year);
		$data->year          = date('Y', $data->time);
		$data->month         = date('m', $data->time);
		$data->date          = date('d', $data->time);
		$data->page_type     = 'postdate';
		
		$this->_setup_content($data);
	}

	/**
	 * カテゴリ別エントリ一覧表示
	 * @access public
	 * @param $cid
	 * @param $offset
	 */
	function category($cid = 0, $offset = 0)
	{
		if ((int)$cid === 0)
		{
			$this->entries();
			return;
		}
		$this->category_id = $cid;

		$data->entry         = $this->blog_model->get_recent_entry_from_category((int)$cid, $this->blog_info->entry_limit, $offset);
		$data->category_list = $this->blog_model->get_category_array();
		$data->page_type     = 'category';

		//Pagination
		$path                = page_link() . 'blog/category/' . $cid;
		$total               = $this->blog_model->get_entry_count_by_category($cid);
		$data->pagination    = $this->_pagination($path, $total, 4, $this->blog_info->entry_limit);

		$this->_setup_content($data);
	}

	/**
	 * 投稿者別エントリ一覧表示
	 * @access public
	 * @param $uid
	 * @param $offset
	 */
	function author($uname = FALSE, $offset = 0)
	{
		if (!$uname)
		{
			$this->entries();
			return;
		}

		$users   = $this->blog_model->get_user_array();
		$user_id = array_search($uname, $users);
		// If user not exists, show 404.
		if(!$user_id)
		{
			show_404();
		}

		$data->user_name     = $uname;
		$data->entry         = $this->blog_model->get_recent_entry_from_author((int)$user_id, $this->blog_info->entry_limit, $offset);
		$data->category_list = $this->blog_model->get_category_array();
		$data->page_type     = 'author';

		//Pagination
		$path                = page_link() . 'blog/author/' . $uname;
		$total               = $this->blog_model->get_entry_count_by_author($user_id);
		$data->pagination    = $this->_pagination($path, $total, 4, $this->blog_info->entry_limit);
		
		$this->_setup_content($data);
	}

	/**
	 * エントリ詳細表示
	 * @access public
	 * @param $bid
	 */
	function article($bid = 0)
	{
		if ( ! $bid )
		{
			$this->entries();
			return;
		}

		$this->_comment_validation();

		$data->is_captcha = (int)$this->blog_info->is_need_captcha;

		if ($data->is_captcha > 0)
		{
			$data->captcha = $this->_create_captcha();
		}

		$data->detail       = $this->blog_model->get_entry_one($bid, TRUE);
		$data->comment      = $this->blog_model->get_comment_to_entry((int)$bid);
		$data->trackbacks   = $this->blog_model->get_trackbacks_by_entry((int)$bid);
		$data->category     = $this->blog_model->get_category_array();
		$data->comments     = $this->blog_model->get_recent_comments($this->blog_info->comment_limit);
		$data->zenback_code = $this->blog_info->zenback_code;
		$data->ticket       = $this->_set_ticket();
		$data->blog_id      = $data->detail->sz_blog_id;
		$data->page_type = 'article';

		// next/prev article exists?
		if ( $data->detail )
		{
			$data->next_article = $this->blog_model->get_next_article($data->detail->entry_date);
			$data->prev_article = $this->blog_model->get_previous_article($data->detail->entry_date);
		}
		else
		{
			$data->next_article = $data->prev_article = FALSE;
		}

		// If trackback is accept, insert header to alternate RSS link.

		$this->_setup_content_one($data);
	}

	/**
	 * コメント投稿
	 * @access public
	 */
	function regist_comment()
	{
		$this->_check_ticket(page_link(trim($this->uri->uri_string(), '/')));

		$this->_comment_validation();

		if ($this->form_validation->run() === FALSE)
		{
			$bid              = (int)$this->input->post('blog_id');
			$data->is_captcha = (int)$this->blog_info->is_need_captcha;

			if ($data->is_captcha > 0)
			{
				$data->captcha = $this->_create_captcha();
			}

			$data->detail       = $this->blog_model->get_entry_one((int)$bid, TRUE);
			$data->comment      = $this->blog_model->get_comment_to_entry((int)$bid);
			$data->category     = $this->blog_model->get_category_array();
			$data->trackbacks   = $this->blog_model->get_trackbacks_by_entry((int)$bid);
			$data->comments     = $this->blog_model->get_recent_comments($this->blog_info->comment_limit);
			$data->zenback_code = $this->blog_info->zenback_code;
			$data->ticket       = $this->_set_ticket();
			$data->blog_id      = $bid;
			$data->page_type    = 'comment';
			
			// next/prev article exists?
			if ( $data->detail )
			{
				$data->next_article = $this->blog_model->get_next_article($data->detail->entry_date);
				$data->prev_article = $this->blog_model->get_previous_article($data->detail->entry_date);
			}
			else
			{
				$data->next_article = $data->prev_article = FALSE;
			}
			

			$this->_setup_content_one($data);
		}
		else
		{
			$post = array(
				'name'         => $this->input->post('name'),
				'comment_body' => $this->input->post('comment_body'),
				'sz_blog_id'   => (int)$this->input->post('blog_id'),
				'post_date'    => date('Y-m-d H:i:s', time())
			);

			if (empty($post['name']))
			{
				$post['name'] = 'No Name';
			}

			$ret = $this->blog_model->insert_comment($post);

			if ($ret)
			{
				$this->session->set_flashdata($this->blog_commented_flag_name, 1);
				redirect('blog/article/' . $post['sz_blog_id']);
			}
		}
	}

	/**
	 * ブログ検索
	 * @access public
	 */
	function search()
	{
		$q = $this->uri->segment(3, '');
		if (!$q)
		{
			$q = $this->input->post('search_query');
		}
		$qs     = $this->_format_search_query($q);
		$offset = $this->uri->segment(4, 0);

		$data->result = $this->blog_model->search_article($qs['value'], $this->limit, $offset);
		$total        = $this->blog_model->get_search_article_count($qs['value']);

		$endoftotal   = ($this->limit > $total) ? $total : $this->limit;
		if($total)
		{
			 $data->total = $total . '件中1-' . $endoftotal . '件表示';
		}
		else
		{
			$data->total = '';
		}
		$data->display_query = $qs['display'];
		$data->page_type     = 'search';

		$data->pagination = $this->_pagination(
												page_link('blog/search/' . $qs['uri'] . '/'),
												$total,
												4,
												$this->limit
											);
		$this->_setup_content_search($data);
	}

	function _format_search_query($q = FALSE)
	{
		if (!$q)
		{
			$q    = '';
			$uriq = '-';
		}
		else
		{
			$uriq = $q;
		}

		$q = str_replace('　', ' ', $q);

		return array(
			'value'   => explode(' ', $q),
			'uri'     => ($uriq === '-') ? '-' : rawurlencode($uriq),
			'display' => ($q == '') ? '全て' : $q
		);
	}

	/**
	 * 出力データ生成[詳細表示用]
	 * @access priovate
	 * @param $data
	 */
	function _setup_content_one($data)
	{
		if ( $this->session->flashdata($this->blog_commented_flag_name) )
		{
			$data->comment_msg = TRUE;
		}

		$this->content_data = $this->load->view('blog/detail', $data, TRUE);
		$time               = ( isset($data->time) ) ? $data->time : time();
		$this->setup_menu($time);
		
		$this->render_view('blog');
	}

	/**
	 * 出力データ生成[一覧表示用]
	 * @access private
	 * @param $data
	 */
	function _setup_content($data)
	{
		$this->content_data = $this->load->view('blog/entries', $data, TRUE);
		$time               = ( isset($data->time) ) ? $data->time : time();
		$this->setup_menu($time);
		
		$this->render_view('blog');
		//$this->load->template_view($this->rel_template_path . 'blog_view', $data);
	}

	/**
	 * 出力データ生成[検索結果表示用]
	 * @access private
	 * @param $data
	 */
	function _setup_content_search($data)
	{
		$this->content_data = $this->load->view('blog/search_result', $data, TRUE);
		$time               = ( isset($data->time) ) ? $data->time : time();
		$this->setup_menu($time);
		
		$this->render_view('blog');
		//$this->load->template_view($this->rel_template_path . 'blog_view', $data);
	}

	/**
	 * メニューデータ生成
	 */
	function setup_menu($time)
	{
		$menu_data = $this->blog_model->get_menu_data_form_frotend();
		$menu      = new BlogMenu($this->blog_info);
		$output    = '';

		foreach ($menu_data as $m)
		{
			if (method_exists($menu, $m->menu_type))
			{
				if ($m->menu_type === 'calendar')
				{
					$output .= $menu->{$m->menu_type}($time);
				}
				else
				{
					$output .= $menu->{$m->menu_type}();
				}
			}
		}
		$this->menu_data = $output;
	}

	/**
	 * RSS/Atomフィード生成
	 * @access public
	 */
	function feed($type = '')
	{
		$format = array(FALSE, 'rss', 'rss2', 'atom');
		$mode   = (int)$this->blog_info->rss_format;

		if($mode == 0)
		{
			// RSS/Atom not send
			show_404();
		}
		elseif($type == '' AND $mode != 0)
		{
			// redirect
			redirect('blog/feed/' . $format[$mode]);
		}

		switch($type)
		{
			case 'rss' :
				$type = 1;
				break;
			case 'rss2' :
				$type = 2;
				break;
			case 'atom' :
				$type = 'A';
				break;
			default :
				show_404();
				break;
		}

		$site_title = $this->init_model->get_site_info()->site_title;
		$blog_title = $this->blog_info->page_title;
		$blog_meta  = $this->blog_model->get_blog_meta()->meta_description;

		//Create site title for feed
		$site_info['site_title']  = (!empty($blog_title) ? $blog_title . ' :: ' : '') . $site_title;
		$site_info['description'] = !empty($blog_meta) ? $blog_meta : '';

		//Generate feed data with RSS Library
		$entries       = $this->blog_model->get_recent_entry($this->blog_info->entry_limit, 0);
		$category_list = $this->blog_model->get_category_array();

		foreach($entries as $entry)
		{
			if(array_key_exists($entry->sz_blog_category_id, $category_list))
			{
				$category = $category_list[$entry->sz_blog_category_id];
			}
			else
			{
				// If category is not exists.
				$category = '_';
			}

			$item[] = array(
							'title'       => prep_str($entry->title),
							'url'         => site_url('blog/article/' . $entry->sz_blog_id),
							'description' => prep_str($entry->body),
							'author'      => ( isset($entry->user_name) ) ? prep_str($entry->user_name) : '-',
							'date'        => $entry->entry_date,
							'category'    => prep_str($category)
							);
		}

		$this->load->library('rss');
		$this->rss->output_rss($type, $item,$site_info);

	}


	/**
	 * ワンタイムトークン生成
	 * @access private
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_blog_comment_token', $ticket);
		return $ticket;
	}

	/**
	 * ワンタイムトークンチェック
	 * @access private
	 */
	function _check_ticket($uri)
	{
		$ticket = $this->input->post('ticket');
		if (!$ticket || $ticket !== $this->session->userdata('sz_blog_comment_token'))
		{
			//exit('不正なデータが送信されました。');
			show_token_error($uri);
		}
	}

	/**
	 * 画像キャプチャ生成
	 * @access private
	 */
	function _create_captcha()
	{
		$this->load->plugin('captcha');
		$this->load->helper('string');
		$data = random_string('alnum', 4);
		$vals = array(
			'word'      => $data,
			'img_path'  => './files/captcha/',
			'img_url'   => file_link() . 'files/captcha/',
			'font_path' => './system/fonts/mikachan.ttf'
		);

		$captcha = create_captcha($vals);

		$this->session->set_userdata('sz_blog_captcha', $captcha['word']);
		return $captcha['image'];
	}

	/**
	 * ページネーション生成
	 * @access private
	 * @param string $path
	 * @param int $total
	 * @param int $segment
	 * @param int $limit
	 */
	function _pagination($path, $total, $segment, $limit)
	{
		$this->load->library('pagination');
		$config = array(
			'base_url'    => $path,
			'total_rows'  => $total,
			'per_page'    => $limit,
			'uri_segment' => $segment,
			'num_links'   => 5,
			'prev_link'   => '&laquo;前へ',
			'next_link'   => '&raquo;次へ'
		);

		$this->pagination->initialize($config);
		return $this->pagination->create_links();
	}

	/**
	 * コメント投稿用バリデーション
	 * @access private
	 */
	function _comment_validation()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');

		$conf = array(
			array(
				'field' => 'name',
				'label' => 'お名前',
				'rules' => 'trim'
			),
			array(
				'field' => 'comment_body',
				'label' => 'コメント',
				'rules' => 'trim|required|max_length[255]'
			)
		);
		// キャプチャを使用する設定の場合はバリデーションルール追加
		if ((int)$this->blog_info->is_need_captcha > 0)
		{
			$conf[] = array(
				'field' => 'captcha',
				'label' => '画像認証',
				'rules' => 'trim|required|alpha_numeric|callback_check_word'
			);
		}
		$this->form_validation->set_rules($conf);
	}

	/**
	 * キャプチャ認証チェック[バリデーションコールバック]
	 * @access public
	 */
	function check_word()
	{
		$word = $this->input->post('captcha');
		if (!$word || $word != $this->session->userdata('sz_blog_captcha'))
		{
			$this->form_validation->set_message('check_word', '%sの値が正しくありません。');
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * RSSリンクタグ生成、アウトプットに追加
	 * @access private
	 * @param none
	 * @return void
	 */
	function _generate_rss_link_tag()
	{
		if ( $this->blog_info->rss_format == 0 )
		{
			return;
		}
		// set feed format and mimetype
		switch ($this->blog_info->rss_format)
		{
			case 1:
				$feed = 'rss1';
				$mime = 'application/rss+xml';
				break;
			case 2:
				$feed = 'rss2';
				$mime = 'application/rss+xml';
				break;
			default:
				$feed = 'atom';
				$mime = 'application/atom+xml';
				break;
		}

		$link = '<link rel="alternate" '
					. 'type="'. $mime .'" '
					. 'title="' . $this->page_data['page_title'] . ' :: ' . $this->site_data->site_title . '" '
					. 'href="' . page_link('blog/feed/' . $feed) . '" />';

		$this->add_header_item($link);
	}

	/**
	 * Ajax応答用カレンダー再構築
	 */
	function adjust_calendar($year, $month, $date = '01')
	{
		$this->load->library('calendar');

		$conf = array(
			'show_next_prev' => TRUE,
			'next_prev_url'  => page_link('blog/adjust_calendar/')
		);

		$this->calendar->initialize($conf);
		$timestamp = mktime(0, 0, 0, (int)$month, (int)$date, (int)$year);
		$entry_data = $this->blog_model->get_entry_by_date_of_id($timestamp);
		
		echo $this->calendar->generate_for_seezoo($year, $month, $entry_data);

	}

	/**
	 * トラックバックリクエスト受信
	 */
	function trackback($entry_id = 0)
	{
		$this->load->library('trackback');
		$ip_address = $this->input->ip_address();

		// Does entry_id exists?
		if ( ! $entry_id )
		{
			$this->trackback->send_error('記事IDが見つかりません。');
		}

		// Does target entry appept trackback?
		if ( ! $this->blog_model->check_accept_trackback($entry_id, $ip_address) )
		{
			$this->trackback->send_error('トラックバックは許可されていません。');
		}

		// Is enable tackbacks parameters?
		if ( ! $this->trackback->receive() )
		{
			$this->trackback->send_error('トラックバックデータに不正なデータが含まれています。');
		}

		// create DB record
		$trackback = array(
			'title'         => $this->trackback->data('title'),
			'url'           => $this->trackback->data('url'),
			'blog_name'     => $this->trackback->data('blog_name'),
			'excerpt'       => truncate($this->trackback->data('excerpt'), 252, '...'),
			'ip_address'    => $this->input->ip_address(),
			'received_date' => db_datetime(),
			'is_allowed'    => 0,
			'sz_blog_id'    => $entry_id
		);

		if ( ! $this->blog_model->receive_trackback($trackback) )
		{
			$this->trackback->send_error('データの受付に失敗しました。');
		}

		$this->trackback->send_success();
	}

	public static function sitemap_index()
	{
		$CI =& get_instance();
		$CI->load->model('blog_model');
		
		// check blog is enable?
		$blog_info = $CI->blog_model->get_blog_info();

		if ((int)$blog_info->is_enable === 0)
		{
			return FALSE;
		}
		// basic pages
		$sitemap = array(
						array(
							'url' => page_link('blog/entries')
						)
					);
		// build entry pages
		foreach ($CI->blog_model->get_all_entries() as $entry )
		{
			$sitemap[] = array(
								'url'        => page_link('blog/article/' . $entry->sz_blog_id),
								'lastmod'    => date('Y-m-d', strtotime($entry->entry_date)),
								'changefreq' => 'monthly'
							);
		}
		return $sitemap;
	}

}


/**
 * Outer Class BlogMenu
 * ブログのメニューデータ生成を受け持つクラス
 */
class BlogMenu
{
	protected $CI;
	protected $blog_info;

	protected $entry_limit = 5;

	function __construct($info)
	{
		$this->blog_info = $info;
		$this->CI =& get_instance();
	}

	public function calendar($timestamp)
	{
		$this->CI->load->library('calendar');

		$conf = array(
			'show_next_prev' => TRUE,
			'next_prev_url'  => page_link('blog/adjust_calendar/')
		);

		$this->CI->calendar->initialize($conf);

		$entry_data            = $this->CI->blog_model->get_entry_by_date_of_id($timestamp);
		$data->calendar_string = $this->CI->calendar->generate_for_seezoo(date('Y', $timestamp), date('m', $timestamp), $entry_data);
		$data->type            = 'calendar';

		return $this->CI->load->view('blog/parts', $data, TRUE);
	}

	public function category()
	{
		$data->category    = $this->CI->blog_model->get_category();
		$data->type        = 'category';
		$data->category_id = $this->CI->category_id;

		return $this->CI->load->view('blog/parts', $data, TRUE);
	}

	public function comment()
	{
		$data->comment = $this->CI->blog_model->get_recent_comments($this->blog_info->comment_limit);
		$data->type    = 'comment';

		return $this->CI->load->view('blog/parts', $data, TRUE);
	}

	public function articles()
	{
		$data->articles = $this->CI->blog_model->get_recent_entry($this->entry_limit, 0);
		$data->type     = 'article';

		return $this->CI->load->view('blog/parts', $data, TRUE);
	}

	public function search()
	{
		$data->type = 'search';

		return $this->CI->load->view('blog/parts', $data, TRUE);
	}
}
