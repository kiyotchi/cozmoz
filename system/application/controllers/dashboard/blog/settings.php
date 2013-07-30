<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard ブログ設定情報管理コントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Settings extends SZ_Controller
{
	public $page_title = 'ブログ設定情報管理';
	public $page_description = 'ブログ設定情報を管理します。';
	
	public $msg;
	public $ticket_name = 'sz_ticket';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model(array('permission_model', 'blog_model'));
		
		$this->info = $this->blog_model->get_blog_info();
	}
	
	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$data->info = $this->info;
		$data->rss_types = $this->_generate_rss_types();
		
		$this->load->view('dashboard/blog/info', $data);
	}
	
	/**
	 * 設定情報編集
	 */
	function edit()
	{
		$data->ticket = $this->_set_ticket();
		$data->info = $this->info;
		$data->templates = $this->blog_model->get_enable_template_list();
		$data->rss_types = $this->_generate_rss_types();
		
		$this->_validation_settings();
		
		$this->load->view('dashboard/blog/settings', $data);
	}
	
	/**
	 * 設定変更を実行
	 */
	function do_settings()
	{
		$this->_check_ticket();
		$this->_validation_settings();
		
		if (!$this->form_validation->run())
		{
			$data->ticket = $this->_set_ticket();
			$data->again = 1;
			$this->msg = '設定項目に誤りがあります。';
			
			$this->load->view('dashboard/blog/settings');
		}
		else
		{
			$post = array(
				'page_title'		=> $this->input->post('page_title'),
				'entry_limit'		=> $this->input->post('entry_limit'),
				'comment_limit'	=> $this->input->post('comment_limit'),
				'is_enable'		=> (int)$this->input->post('is_enable'),
				'is_need_captcha'	=> (int)$this->input->post('is_need_captcha'),
				'is_auto_ping'	=> (int)$this->input->post('is_auto_ping'),
				'zenback_code'	=> $this->input->post('zenback_code'),
				'rss_format'	 => (int)$this->input->post('rss_type')
			);
			
			if ($this->input->post('template_id'))
			{
				$post['template_id'] = (int)$this->input->post('template_id');
			}
			
			$ret = $this->blog_model->update_settings($post);
			$this->msg = ($ret) ? 'ブログの設定情報を更新しました。' : '情報更新に失敗しました。';

			$this->load->view('dashboard/blog/complete');
		}
	}
	
	/**
	 * 設定情報用バリデーションルールセット
	 */
	function _validation_settings()
	{
		$this->load->library('form_validation');
		
		$conf = array(
			array(
					'field'		=> 'page_title',
					'label'		=> 'ブログページのタイトル',
					'rules'		=> 'trim|required|max_length[255]'
			),
			array(
					'field'		=> 'entry_limit',
					'label'		=> '記事数',
					'rules'		=> 'trim|required|callback_int_num'
			),
			array(
					'field'		=> 'comment_limit',
					'label'		=> 'コメント数',
					'rules'		=> 'trim|required|callback_int_num'
			),
			array(
					'field'		=> 'is_enable',
					'label'		=> 'ブログ利用可能チェック',
					'rules'		=> 'callback_ctype'
			),
			array(
					'field'		=> 'is_need_captcha',
					'label'		=> 'コメント画像認証チェック',
					'rules'		=> 'callback_ctype'
			)
		);
		
		$this->form_validation->set_rules($conf);
	}
	
	/**
	 * 独自バリデーション数値判定
	 * int_numとはエラーメッセージの違いと空値を許可するかどうか
	 * @param $str
	 */
	function ctype($str = '')
	{
		if (!empty($str))
		{
			if (!ctype_digit($str))
			{
				$this->form_validation->set_message('ctype', '%sに不正なデータが入っています。');
				return FALSE;
			}
		}
	}
	
	/**
	 * 独自バリデーション正の整数判定
	 * @param $str
	 */
	function int_num($str)
	{
		if (!ctype_digit($str))
		{
			$this->form_validation->set_message('int_num', '%sは数値で入力してください。');
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * トークン生成
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata($this->ticket_name, $ticket);
		return $ticket;
	}
	
	/**
	 * トークンチェック
	 * @param $ticket
	 */
	function _check_ticket($ticket = FALSE)
	{
		if (!$ticket)
		{
			$ticket = $this->input->post($this->ticket_name);
		}
		if (!$ticket || $ticket != $this->session->userdata($this->ticket_name))
		{
			exit('不正な操作です。また、リロードは禁止されています。');
		}
	}
	
	/**
	 * RSSフォーマット配列生成
	 * @param $index
	 */
	function _generate_rss_types($index = FALSE)
	{
		$arr = array(
			0 => '配信しない',
			1 => 'RSS1.0',
			2	=> 'RSS2.0',
			3	=> 'Atom'
		);
		
		return ( $index ) ? $arr[$index] : $arr;
	}
}
