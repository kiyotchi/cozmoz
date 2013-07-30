<?php
/**
 * ===============================================================================
 * 
 * Seezoo Gadget_Ajaxコントローラ
 * 
 * ガジェットの応答系コントローラ
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Gadget_ajax extends SZ_Controller
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller(FALSE);
		
		// this class works ajax request only.
		// check Request Header by Flint.js posted X-Requsted-With
		if (!$this->_is_ajax_request())
		{
			exit('access_denied.');
		}
		
		$this->load->model(array('ajax_model'));
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');
	}
	
	/**
	 * Ajaxリクエストチェック
	 * @access private
	 */
	function _is_ajax_request()
	{
		// if User Agent is IE6, also, same value of XMLHttpRequest created by Flint.js.
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? TRUE : FALSE;
	}
	
	/**
	 * ガジェット操作用Ajaxトークンチェック
	 * @access private
	 * @param string $name
	 * @param string $val
	 */
	function _token_check($name, $val)
	{
		if (!$this->session->userdata($name) || $this->session->userdata($name) !== $val)
		{
			exit('access denied.');
		}
	}

	/**
	 * 使用できるガジェットリスト取得
	 * @access public
	 * @param $token
	 */
	function get_gadget_list($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$gadget = $this->ajax_model->get_gadget_list();
		echo json_encode($gadget);
		exit;
	}

	/**
	 * ガジェット追加
	 * @access public
	 * @param $gid
	 * @param $token
	 */
	function add_gadget($gid, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$ret = $this->ajax_model->add_new_gadget((int)$gid);

		if (!$ret || !is_array($ret))
		{
			echo 'error';
		}
		else
		{
			echo json_encode($ret);
		}
	}

	/**
	 * ガジェットのロード
	 * @access public
	 * @param string $token
	 */
	function load_gadgets($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$gadgets = $this->ajax_model->get_user_gadgets((int)$this->session->userdata('user_id'));

		if ($gadgets === FALSE)
		{
			echo 'none';
		}
		else
		{
			echo json_encode($gadgets);
		}
	}

	/**
	 * ガジェット並び順変更
	 * @access public
	 * @param $token
	 */
	function sort_gadget($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$data = array();
		foreach ($_POST as $key => $val)
		{
			if (intval($key) > 0)
			{
				$data[$val] = (int)$key;
			}
		}

		$this->ajax_model->do_sort_gadget($data);
	}

	/**
	 * ガジェット削除
	 * @access public
	 * @param $gmid
	 * @param $token
	 */
	function delete_gadget($gmid, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		if (!ctype_digit($gmid))
		{
			echo 'error';
		}

		$ret = $this->ajax_model->delete_gadget($gmid);

		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * memoガジェットデータ取得
	 * @access public
	 * @param $key
	 * @param $token
	 */
	function get_gadget_memo($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$data = $this->ajax_model->get_gadget_data_memo($key);

		if ($data)
		{
			$out = array(
				'update_time'	=> $data->update_time,
				'data'			=> form_prep($data->data)
			);
			echo json_encode($out);
		}
		else
		{
			echo 'error';
		}
	}

	/**
	 * memoガジェットデータ保存
	 * @access public
	 * @param $key
	 * @param $token
	 */
	function save_memo($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$post = array('data' => $this->input->post('data'));
		$this->ajax_model->save_memo_data($key, $post);
	}

	/**
	 * weatherガジェットデータ取得
	 * @access public
	 * @param string $key
	 * @param string $token
	 */
	function get_gadget_weather($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$uid = (int)$this->session->userdata('user_id');

		$data = $this->ajax_model->get_gadget_data_weather($key);

		if ($data)
		{
			echo json_encode($data);
		}
		else
		{
			echo 'error';
		}
	}

	/**
	 * weatherガジェット：表示地方変更
	 * @param $key
	 * @param $token
	 */
	function gadget_weather_change_area($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$city = $this->input->post('city_id');

		if (!ctype_digit($city))
		{
			exit('error');
		}

		$ret = $this->ajax_model->gadget_weather_update($key, (int)$city);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

//	function get_gmail($key, $token)
//	{
////		$this->_token_check('sz_token', $token);
//
//		$d = $this->input->post('last_date');
//
//		if (!$this->session->userdata('gmail_hash'))
//		{
//			exit('need_login');
//		}
//
//		//$this->session->keep_flashdata('gmail_logged_in');
//		$ac = $this->session->userdata('gmail_hash');
//
//		$data = $this->ajax_model->get_gmail_data($ac, $d);
//
//		if ($data === FALSE)
//		{
//			echo 'error';
//		}
//		else if ($data == 'no_account')
//		{
//			echo 'need_login';
//		}
//		else
//		{
//			echo json_encode($data);
//		}
//	}
//
//	function gadget_gmail_login($token = FALSE)
//	{
//		$this->_token_check('sz_token', $token);
//
//		$email = $this->input->post('email');
//		$pass = $this->input->post('password');
//
//		$ret = $this->ajax_model->do_gmail_login($email, $pass);
//
//		if ($ret)
//		{
//			$hash = md5(uniqid(mt_rand(), TRUE));
//			$this->session->set_userdata('gmail_hash', $ret);
//			echo 'complete';
//		}
//		else
//		{
//			echo 'error';
//		}
//		//$this->session->set_userdata('gmail_logged_in', base64_encode($email .':' . $pass));
//	}

	/**
	 * twitterガジェットデータ取得
	 * @access public
	 * @param $key
	 * @param $token
	 */
	function get_gadget_twitter($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$ret = $this->ajax_model->get_twitter_gadget_data($key);

		if (!$ret)
		{
			echo 'error';
		}
		else
		{
			echo json_encode($ret);
		}
	}

	/**
	 * twitterガジェット設定変更
	 * @param $key
	 * @param $token
	 */
	function gadget_twitter_config($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$n = $this->input->post('account_name', TRUE);
		$u = $this->input->post('update_time', TRUE);
		$c = $this->input->post('show_count', TRUE);

		if ($n == '' || $n == '最新')
		{
			$n = '';
		}
		// pre_validate
		if (!ctype_digit($u) || !ctype_digit($c))
		{
			echo 'error';
			exit;
		}
		$data = array(
			'account_name' => $n,
			'update_time'	=> $u,
			'show_count'	=> $c
		);

		$ret = $this->ajax_model->update_twitter_config($key, $data);

		if ($ret)
		{
			echo json_encode($data);
		}
		else
		{
			echo 'error';
		}
	}

	/**
	 * RSSガジェットデー取得
	 * @access public
	 * @param $key
	 * @param $token
	 */
	function get_gadget_rss($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$data = $this->ajax_model->get_gadget_rss_data($key);

		$this->load->library('rss');

		$ret = $this->rss->load($data, 'desc'); // return  array
		$ret['rss_url'] = $data;

		echo json_encode($ret);
	}

	/**
	 * RSSガジェット設定変更
	 * @access public
	 * @param $key
	 * @param $token
	 */
	function gadget_rss_config($key, $token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$data = array(
			'rss_url'	=> $this->input->post('rss_url', TRUE)
		);

		$ret = $this->ajax_model->update_gadget_rss_config($key, $data);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * BBSガジェットデータ取得
	 * @access public
	 * @param $token
	 */
	function gadget_bbs_get_data($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$data['users'] = $this->ajax_model->get_user_name_list();
		$data['data'] = $this->ajax_model->get_recent_bbs();

		echo json_encode($data);
	}

	/**
	 * BBSガジェット投稿
	 * @access public
	 * @param $token
	 */
	function gadget_bbs_submit($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$data = array(
			'body'				=>	nl2br($this->input->post('body', TRUE)),
			'posted_user_id'	=> (int)$this->session->userdata('user_id'),
			'post_date'			=> date('Y-m-d H:i:s', time())
		);

		$ret = $this->ajax_model->insert_bbs_data($data);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * BBSガジェット再読み込み
	 * @access public
	 * @param $token
	 */
	function gadget_bbs_update($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$last = $this->input->post('last_update', TRUE);

		$data['users'] = $this->ajax_model->get_user_name_list();
		$data['data'] = $this->ajax_model->get_since_bbs($last);

		echo json_encode($data);
	}

	/**
	 * 翻訳ガジェット翻訳実行
	 * @param $token
	 */
	function gadget_do_translate($token = FALSE)
	{
		$this->_token_check('sz_token', $token);

		$query = $this->input->post('translate_value', TRUE);
		$type = (int)$this->input->post('translate_to');

		$ret = $this->ajax_model->get_translated_data($query, $type);
		echo ($ret) ? $ret : 'error';
	}
}
