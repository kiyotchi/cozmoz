<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * Seezoo メール送信モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Mail_model extends Model
{
	protected $qdmail;
	protected $log;
	
	function __construct()
	{
		parent::Model();
		require_once(APPPATH . 'libraries/thirdparty/qdmail.php');
		$this->log =& load_class('Log');
	}
	
	/**
	 * テキストメール送信
	 * @param $to
	 * @param $subject
	 * @param $body
	 * @param $from_header
	 */
	function send_text_mail($to, $subject, $body, $from_header)
	{
		return $this->_do_send_mail('text', $to, $subject, $body, $from_header);
	}
	
	/**
	 * HTMLメール送信
	 * @param $to
	 * @param $subject
	 * @param $body
	 * @param $from_header
	 */
	function send_html_mail($to, $subject, $body, $from_header)
	{
		return $this->_do_send_mail('html', $to, $subject, $body, $from_header);
	}
	
	/**
	 * メール送信実行
	 * @param unknown_type $protocol
	 * @param unknown_type $to
	 * @param unknown_type $subject
	 * @param unknown_type $body
	 * @param unknown_type $from_header
	 */
	protected function _do_send_mail($protocol, $to, $subject, $body, $from_header)
	{
		mb_language('ja');
		$from = ( ! is_array($from_header) ) ? array($from_header) : $from_header;
		$result = qd_send_mail($protocol, $to, $subject, $body, $from);
		$this->log->write_mail_log($subject, $to, $body, $result);
		return $result;
	}
	
}
