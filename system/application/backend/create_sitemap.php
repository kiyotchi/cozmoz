<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ===========================================================================================
 * Backendprocess: create_sitemap Class
 * 
 * create sitemap.xml from backed
 * @package Seezoo
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  
 * ===========================================================================================
 */
class Create_sitemap extends Backend
{
	protected $CI;
	protected $backend_name = 'サイトマップXML生成';
	protected $description = 'サイトマップ用XMLファイルを生成します。';
	
	// Skip pages
	private $skip_pages = array('login', 'logout');
	
	public function run()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('sitemap_model');
		
		
		$sql = 
			'SELECT '
			.	'PV.version_date, '
			.	'PP.page_path, '
			.	'PV.page_id, '
			.	'PV.is_system_page '
			.'FROM '
			.	'page_versions as PV '
			.'RIGHT JOIN page_paths as PP ON ( '
			.	'PV.page_id = PP.page_id '
			.') '
			.'WHERE '
			.	'PV.is_public = 1 '
			.'AND '
			.	'PP.page_path NOT LIKE ? '
			.'ORDER BY '
			.	'PV.page_id ASC'
			;
		$query = $this->CI->db->query($sql, array('dashboard/%'));
		
		$xml = array(
			'<?xml version="1.0" encoding="UTF-8"?>',
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
		);
		
		foreach ($query->result() as $v)
		{
			$xml[] = $this->_format_sitemap_xml($v);
		}
		
		$xml[] = '</urlset>';
		
		$this->CI->load->helper('file_helper');
		$ret = write_file(FCPATH . 'sitemap.xml', implode("\n", $xml));
		
		if (!$ret)
		{
			return 'sitemap.xmlファイルの生成に失敗しました。';
		}
		else
		{
			return 'sitemap.xmlファイルを生成しました。';
		}
	}
	
	protected function _format_sitemap_xml($v)
	{
		// If target page in skip pages, skip the page.
		if ( in_array($v->page_path, $this->skip_pages) )
		{
			return '';
		}
		
		$xml = array();
		if ((int)$v->page_id == 1)
		{
			$xml[] = $this->_build_xml_format(
									file_link(),
									date('Y-m-d', strtotime($v->version_date)),
									'weekly',
									'1.0'
								);
		}
		else
		{
			$path = ($this->CI->sitemap_model->is_ssl_page_path($v->page_path))
							? ssl_page_link($v->page_path)
							: page_link($v->page_path);
							
			$xml[] = $this->_build_xml_format(
									$path,
									date('Y-m-d', strtotime($v->version_date)),
									'weekly',
									(($v->is_system_page > 0) ? '0.5' : '0.1')
								);
		}
		
		// システムページの場合は、セグメントによるページも検出する
		if ( $v->is_system_page > 0 )
		{
			$xml[] = $this->_detect_systempage_urls_from_segment($v->page_path, date('Y-m-d', strtotime($v->version_date)));
		}

		return implode("\n", $xml);
	}
	
	/**
	 * システムページのセグメント別ページリスト検出
	 * @access private
	 * @param $page_path
	 */
	protected function _detect_systempage_urls_from_segment($page_path, $lastmod = FALSE)
	{
		if ( ! $lastmod )
		{
			$lastmod = date('Y-m-d H:i:s', time());
		}
		$exp = explode('/', $page_path);
		$class_name = ucfirst(end($exp));
		
		// クラス重複include回避
		if ( class_exists($class_name) )
		{
			return '';
		}
		// load the controller
		if ( file_exists(SZ_EXT_PATH . 'controllers/' . $page_path . EXT) )
		{
			require_once(SZ_EXT_PATH . 'controllers/' . $page_path . EXT);
		}
		else if ( file_exists(APPPATH . 'controllers/' . $page_path . EXT) )
		{
			require_once(APPPATH . 'controllers/' . $page_path . EXT);
		}
		else
		{
			return '';
		}
		
		if ( ! class_exists($class_name) )
		{
			return '';
		}
		
		if ( ! method_exists($class_name, 'sitemap_index') )
		{
			return '';
		}
		
		// PHP 5.3+ can call static method from String class variable
		//if ( version_compare(PHP_VERSION, '5.3', '>'))
		//{
		//	$index = $class_name::sitemap_index();
		//}
		//if ( version_compare(PHP_VERSION, '5.2.3', '>') )
		//{
			// PHP5.2.3+ call with double collon.
			//$index = call_user_func(array($class_name, '::sitemap_index'));
		//}
		//else
		//{
			// PHP 5.3- is not, so we call from user_func
			$index = call_user_func(array($class_name, 'sitemap_index'));
		//}
		if ( is_array($index) )
		{
			$xml = array();
			foreach ( $index as $pages )
			{
				$xml[] = $this->_build_xml_format(
												$pages['url'],
												(isset($pages['lastmod'])) ? $pages['lastmod'] : $lastmod,
												(isset($pages['changefreq'])) ? $pages['changefreq'] : 'weekly',
												(isset($pages['priority'])) ? $pages['priority'] : '0.5'
											);
			}
			return implode("\n", $xml);
		}
		return '';
	}
	
	protected function _build_xml_format($url, $lastmod, $changefreq = 'weekly', $priority = '1.0')
	{
		$data = array(
			'  <url>',
			'    <loc>' . $url . '</loc>',
			'    <lastmod>' . $lastmod . '</lastmod>',
			'    <changefreq>' . $changefreq . '</changefreq>',
			'    <priority>' . $priority . '</priority>',
			'  </url>'
		);
		return implode("\n", $data);
	}
}