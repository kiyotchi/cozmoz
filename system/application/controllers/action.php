<?php
/**
 * ===============================================================================
 * 
 * Seezoo Actionコントローラ
 * 
 * block actionキャッチ用コントローラ
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Action extends SZ_Controller
{
	public $block_path = 'blocks/';
	public $block_action_name = '';
	public $block_id = 0;
	private $referer = '';
	private $action_token = '';

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller(FALSE);
		
		// アクションは直アクセスを禁止するので、リファラーチェック
		$this->referer = $this->input->server('HTTP_REFERER', TRUE);
		if (strpos($this->referer, $this->config->slash_item('base_url')) === FALSE)
		{
			show_404();
		}
	}

	/**
	 * アクション実行
	 * @note
	 * 　プライベートメソッドだが、下記_remapメソッドにより呼び出される
	 * 　パラメータチェックも_remapで行う
	 */
	private function _do_action()
	{
		$block_name = $this->block_action_name;
		$path = $this->block_path . $block_name . '/' . $block_name . '.php';
		if (!file_exists(SZ_EXT_PATH . $path)
					&& !file_exists(FCPATH . $path))
		{
			exit('no_action.');
		}

		// ブロックのロード
		$block = $this->load->block($block_name, $this->block_id, TRUE);
		$block->action($this->action_token);
		// アクション実行後は呼び出し元にリダイレクト
		redirect($this->referer);
	}
	
	/**
	 * ファイルID指定によるファイルダウンロード
	 * @note
	 *  wget受け入れのため、直アクセスを許可しているので注意
	 */
	function download_file()
	{
		$file_id = $this->uri->segment(3, '');
		if ( empty($file_id) || ! ctype_digit($file_id) )
		{
			show_404();
		}
		// strict filename
		$file = get_file($file_id);
		if ( ! $file )
		{
			show_404();
		}
		force_download_reg($file->file_name . '.' . $file->extension, make_file_path($file));
	}
	
	/**
	 * ページの表示モードを変更するメソッド
	 */
	function change_view_mode()
	{
		if ( ! $this->is_login )
		{
			show_404();
		}
		$page_id = $this->uri->segment(3, 1);
		$mode    = $this->uri->segment(4, 'pc');
		
		// session set
		$this->session->set_userdata('viewmode', $mode);
		// and redirect
		redirect($page_id);
	}
	
	function profiler_sql_exec()
	{
		if ( ! $this->is_login )
		{
			show_404();
		}
		$token = $this->uri->segment(3, 0);
		$xhr = $this->input->server('HTTP_X_REQUESTED_WITH');
		if ( $this->input->server('REQUEST_METHOD') !== 'POST'
			|| ! $xhr
			|| $xhr !== 'XMLHttpRequest'
			|| ! $token
			|| $token != $this->session->userdata('sz_token')
		)
		{
			exit();
		}
		
		$sql   = trim($this->input->post('query'));
		$query = $this->db->query($sql);
		
		if ( ! $query )
		{
			echo 'SQL FAILED';
		}
		else
		{
			var_dump($query->result());
		}
		
	}

	/**
	 * メソッド再マッピング
	 * @param string $method
	 */
	function _remap($method = 'index')
	{
		if ( method_exists($this, $method) )
		{
			$this->{$method}();
			return;
		}
		$this->block_action_name = $method;
		$this->block_id = $this->uri->segment(3, 0);
		$this->action_token = $this->uri->segment(4, 0);

		// アクション用のトークンチェック
		$action_session = $this->session->userdata('action_' . $method . '_' . $this->block_id);
		if (!$this->action_token || !$action_session || $this->action_token != $action_session)
		{
			show_404();
		}
		$this->_do_action();
	}
}