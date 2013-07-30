<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * extended CodeIgniter URL Helper
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==============================================================
 */
if ( ! function_exists('redirect_path'))
{
	function redirect_path($page_id)
	{
		$CI =& get_instance();
		$CI->load->model('init_model');
		$page = $CI->init_model->get_page_path_and_system_page($page_id);
		$prefix = ($page->is_ssl_page > 0) ? ssl_page_link() : page_link();
		if ($page && (int)$page->is_system_page > 0)
		{
			redirect($prefix . $page->page_path);
		}
		else
		{
			redirect($prefix . $page_id);
		}
	}
}