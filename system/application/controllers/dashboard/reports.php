<?php
/**
 * ===============================================================================
 * 
 * Seezoo dashboard レポートコントローラ
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Reports extends SZ_Controller
{
	public static $page_title = 'レポート';
	public static $description = 'お問い合わせフォームやアンケートの結果を集計します。';
	
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('report_model');
	}
	
	function index()
	{
		// get all form reports
		$data->forms = $this->report_model->get_all_forms();
		
		$data->token = $this->_set_token();
		
		$this->load->view('dashboard/reports/list', $data);
	}
	
	function delete_report($q_key, $token = FALSE)
	{
		$this->_check_token($token);
		
		$ret = $this->report_model->delete_report($q_key);
		
		echo ($ret) ? 'complete' : 'error';
	}
	
	function detail($key, $msg = '')
	{
		$data->reports = $this->report_model->get_all_form_data($key);
		
		$data->form_data = $this->report_model->get_form_data_by_key($key);
		
		$data->question_key = $key;
		
		$data->dl_format = array(
			'csv'	=> 'CSV',
			'excel'	=> 'Microsoft excel'
		);
		
		$data->token = $this->_set_token();
		
		if ($msg)
		{
			$this->msg = 'ダウンロードフォーマットが対応していません、';
		}
		
		$this->load->view('dashboard/reports/detail', $data);
	}
	
	function delete_answer($timestamp, $q_key, $token = FALSE)
	{
		$this->_check_token($token);
		
		$post_date = date('Y-m-d H:i:s', $timestamp);
		
		$ret = $this->report_model->delete_answer_by_date($post_date, $q_key);
		
		echo ($ret) ? 'complete' : 'error';
	}
	
	function data_dl()
	{
		$key = $this->input->post('key');
		$format = $this->input->post('dl_format');
		
		if (!$key || !$format)
		{
			redirect('dashboard/reports');
		}
		
		$form = $this->report_model->get_form_data_by_key($key);
		
		// @fix なぜかCIのdownload_helperを経由するとファイル名が文字化けするので、
		//       手動でヘッダを組み立て、ダウンロードさせるコードで対応
		if ($format === 'csv')
		{
			$this->_make_csv($key, $form->form_title);
		}
		else if ($format === 'excel')
		{
			$this->_make_excel($key, $form->form_title);
		}
		
		// undefined_format
		redirect('dashboard/reports/detail/' . $key . '/undefiend_format');
	}
	
	function _make_csv($key, $fname)
	{
		$csv = $this->report_model->build_csv_strings($key);
		$filename = $fname . date('YmdHis', time()) . ".csv";
		
		// @fix: IEはダウンロードファイル名のエンコードをSHIFT_JISにしないといけないので分岐する
		$ua = ($this->input->server('HTTP_USER_AGENT'))
							? $this->input->server('HTTP_USER_AGENT')
							: '';
		if (strpos($ua, 'MSIE') !== FALSE)
		{
			$filename = mb_convert_encoding($filename, 'SHIFT_JIS', 'UTF-8');
		}
		header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
		header("Content-Type: application/octet-stream");
		header("Content-Transfer-Encoding: binary");
		exit($csv);
	}
	
	function _make_excel($key, $fname)
	{
		$excel = $this->report_model->build_excel_strings($key);
		$filename = $fname . date('YmdHis', time()) . ".xls";
		
		// @fix: IEはダウンロードファイル名のエンコードをSHIFT_JISにしないといけないので分岐する
		$ua = ($this->input->server('HTTP_USER_AGENT'))
							? $this->input->server('HTTP_USER_AGENT')
							: '';
		if (strpos($ua, 'MSIE') !== FALSE)
		{
			$filename = mb_convert_encoding($filename, 'SHIFT_JIS', 'UTF-8');
		}
		
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Transfer-Encoding: binary");
		header("Cache-control: private");
		header("Pragma: public");
		header("Content-Disposition: attachment; filename=\"" . $filename ."\"");
		header("Content-Title: " . $fname . " Form Data Output - Run On " . date('Ymd', time()));
		exit($excel);
	}
	
	function _set_token()
	{
		$token = sha1(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_report_token', $token);
		return $token;
	}
	
	function _check_token($token = FALSE)
	{
		if ( !$token || $this->session->userdata('sz_report_token') !== $token)
		{
			exit('不正なリクエストがありました。');
		}
	}
}
