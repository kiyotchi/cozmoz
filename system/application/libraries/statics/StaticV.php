<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 *Seezoo Static Variables Class
 *
 * output of static values  defined by static.XML
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class StaticV
{
	private $CI;

	function __construct()
	{
		$this->CI =& get_instance();
	}

	public function base_path()
	{
		return file_link();
	}

	public function site_path()
	{
		return page_link();
	}

	public function top_path()
	{
		$sql = 'SELECT page_path FROM page_paths WHERE page_id = 1 LIMIT 1';
		$query = $this->CI->db->query($sql);

		$result = $query->row();
		return page_link() . $result->page_path . '/';
	}

	public function static_path()
	{
		return file_link() . 'statics/';
	}

	public function jQuery()
	{
		return '<script type="text/javascript" src="' . file_link() . 'js/jquery.min.js" charset="UTF-8"></script>';
	}
	
	public function site_title()
	{
		return (defined('SITE_TITLE')) ? SITE_TITLE : '';
	}
}