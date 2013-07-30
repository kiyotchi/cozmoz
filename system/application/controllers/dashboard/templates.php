<?php
/**
 * ===============================================================================
 *
 * Seezoo dashboard テンプレート管理コントローラ
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */
class Templates extends SZ_Controller
{
	public $template_dir = 'templates/';
	public $msg;

	public static $page_title = 'テンプレート管理';
	public static $description = 'テンプレートのインストールや削除を行います。';

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->helper(array('file_helper', 'directory'));
		$this->load->model('template_model');
	}
	
	// test
	function get_template($page = 0)
	{
		// sample response
		$out = array(
			'<ul>',
			'<li><div>aaa</div></li>',
			'<li><div>bbb</div></li>',
			'<li><div>ccc</div></li>',
			'<li><div>ddd</div></li>',
			'<li><div>eee</div></li>',
			'<li><div>fff</div></li>',
			'<li><div>ggg</div></li>',
			'</ul>'
		);
		
		echo implode('', $out);
	}

	/**
	 * デフォルトメソッド
	 * インストール済みとインストール可能なものを取得して表示
	 */
	function index($method = '', $result = '')
	{
		$list = array();
		$installed_list = array();

		$dirs = directory_map($this->template_dir, TRUE);
		foreach ($dirs as $dir)
		{
			if (!is_dir($this->template_dir . $dir))
			{
				continue;
			}
			$has = $this->template_model->is_already_installed($dir);
			if ($has)
			{
				// installed template has thumbnail image?
				if (file_exists($this->template_dir . $dir . '/image.jpg'))
				{
					$has['image'] = TRUE;
				}
				else
				{
					$has['image'] = FALSE;
				}
				$installed_list[] = $has;
				continue;
			}

			// template has necessary files?
			$path = $this->template_dir . $dir . '/';
			if (file_exists($path . 'view.php'))
			{
				if (file_exists($path . 'attribute.php'))
				{
					require($path . 'attribute.php');
				}
				else
				{
					$attribute = null;
				}

				if (isset($attribute) && is_array($attribute))
				{
					$l = array(
						'name'			=> $attribute['name'],
						'description'	=> $attribute['description'],
						'handle'		=> $dir
					);
				}
				else
				{
					$l = array(
						'name'			=> 'no name',
						'description'	=> '',
						'handle'		=> $dir
					);
				}

				if (file_exists($path . 'image.jpg'))
				{
					$l['image'] = TRUE;
				}
				else
				{
					$l['image'] = FALSE;
				}
				$list[] = $l;
			}
		}

		$data->list = $list;
		$data->installed_list = $installed_list;
		$data->ticket = $this->_set_ticket();
		$data->dtid = $this->template_model->get_default_template_id();

		$this->_create_process_message($method, $result);

		$this->load->view('dashboard/templates/list', $data);
	}

	/**
	 * テンプレートのインストール
	 * @param $handle
	 * @param $token
	 */
	function install($handle = FALSE, $token = FALSE)
	{
		if (!$handle || !$token)
		{
			$this->index();
			return;
		}

		$this->_check_ticket($token);

		$ret = $this->template_model->install_template($handle);

		if ($ret === 'NOT_ENOUGH')
		{
			$ret = 'not_enough';
		}
		else if ((int)$ret > 0)
		{
			$ret = 'success';
		}
		else
		{
			$ret = 'error';
		}

		redirect('dashboard/templates/index/install/' . $ret);
	}
	
	function reload($template_id, $token = FALSE)
	{
		if ( ! $template_id || ! $token )
		{
			$result = 'error';
		}
		
		$this->_check_ticket($token);
		
		$ret = $this->template_model->reload_template($template_id);
		if ( $ret === TRUE )
		{
			$result = 'success';
		}
		else if ( $ret === 'NOTFOUND' )
		{
			$result = 'notfound';
		}
		else
		{
			$result = 'error';
		}
		
		redirect('dashboard/templates/index/reload/' . $result);
	}

	function uninstall($tid = FALSE, $token = FALSE)
	{
		if (!$tid || !$token)
		{
			$result = 'undefined';
		}
		else
		{
			$this->_check_ticket($token);

			$ret = $this->template_model->do_uninstall((int)$tid);
			if ($ret)
			{
				$result = 'success';
			}
			else
			{
				$result = 'error';
			}
		}

		redirect('dashboard/templates/index/uninstall/' . $result);
	}

	/**
	 * テンプレート拡張CSS保存
	 */
	function set_advance_css()
	{
		$tid = $this->input->post('template_id');
		$ad_css = $this->input->post('custom_css');

		if (!$tid)
		{
			$result = 'undefined';
		}
		else
		{
			$ret = $this->template_model->additional_css($tid, $ad_css);
			if ($ret)
			{
				$result = 'success';
			}
			else
			{
				$result = 'error';
			}

		}

		redirect('dashboard/templates/index/advancecss/' . $result);
	}

	/**
	 * テンプレートプレビュー
	 * @param unknown_type $handle
	 * @param unknown_type $token
	 */
	function preview($handle = FALSE, $token = FALSE)
	{
		if (!$handle || !$token)
		{
			exit('access denied.');
		}

		$this->_check_ticket($token);

		$this->load->model('init_model');
		$this->load->model('page_model');

		// tmp set for preview
		$this->page_id = 1;
		$this->is_preview = TRUE;
		$this->arrange_mode = FALSE;
		$this->edit_mode = 'NO_EDIT';
		$this->is_login = FALSE;
		$this->version_number = 1;
		$this->cms_mode = TRUE;
		$this->page_data = $this->page_model->get_page_object(1, 1);

		$data->template_path = file_link() . 'templates/' . $handle . '/';
		$data->_relative_template_path = $this->relative_template_path = 'templates/' . $handle . '/';
		$this->_rel_template_path = $handle;
		$this->site_data = $this->init_model->get_site_info();

		$this->load->template_view($handle . '/view', $data);

	}

	/**
	 * 拡張CSS入力フィールド生成
	 * @param int $tid
	 * @param string $token
	 */
	function custom_css($tid = FALSE, $token = FALSE)
	{
		if (!$tid || !$token)
		{
			echo 'access_denied';
		}

		$this->_check_ticket($token);

		$data->custom_css = $this->template_model->get_custom_css_by_id($tid);
		$data->template_id = $tid;

		$this->load->view('dashboard/templates/custom_css', $data);
	}

	/**
	 * デフォルトテンプレートにセット
	 * @param $tid
	 * @param $token
	 */
	function set_default($tid = FALSE, $token = FALSE)
	{
		if (!$tid || !$token)
		{
			echo 'access_denied';
		}

		$this->_check_ticket($token);

		$ret = $this->template_model->set_default_template($tid);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * トークンセット
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('ticket', $ticket);

		return $ticket;
	}

	/**
	 * トークンチェック
	 * @param $token
	 */
	function _check_ticket($token)
	{
		if (!$token || $token !== $this->session->userdata('ticket'))
		{
			exit('リロードはキャンセルされました。');
		}
	}

	function _create_process_message($method, $result)
	{
		if ($method == '' && $result == '')
		{
			$this->msg = '';
			return;
		}

		$msg = '';
		if ($method == 'install')
		{
			switch ($result)
			{
				case 'success':
					$msg = 'テンプレートをインストールしました';
					break;
				case 'error':
					$msg = 'テンプレートのインストールに失敗しました。';
					break;
				case 'not_enough':
					$msg = 'テンプレートの構成に必要なファイルが不足しています。';
					break;
				default:
					$msg = '';
					break;
			}
		}
		else if ($method == 'uninstall')
		{
			switch ($result)
			{
				case 'success':
					$msg = 'テンプレートをアンインストールしました';
					break;
				case 'error':
					$msg = 'テンプレートのアンインストールに失敗しました。';
					break;
				case 'undefined':
					$msg = '対象のテンプレートが見つかりませんでした。';
					break;
				default:
					$msg = '';
					break;
			}
		}
		else if ($method == 'advancecss')
		{
			switch ($result)
			{
				case 'success':
					$msg = 'カスタムCSSを保存しました。';
					break;
				case 'error':
					$msg = 'カスタムCSSの保存に失敗しました。';
					break;
				case 'undefined':
					$msg = '対象のテンプレートが見つかりませんでした。';
					break;
				default:
					$msg = '';
					break;
			}
		}
		else if ( $method === 'reload' )
		{
			switch ( $result )
			{
				case 'success':
					$msg = 'テンプレートを再読み込みしました。';
					break;
				case 'notfound':
					$msg = 'テンプレートファイルが見つかりませんでした。ファイルを確認して下さい。';
					break;
				case 'error':
					$msg = 'テンプレートの再読み込みに失敗しました';
					break;
				default:
					$msg = '';
					break;
			}
		}
		$this->msg = $msg;
	}

}