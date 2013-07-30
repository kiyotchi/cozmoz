<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ===========================================================================================
 * Backendprocess: generate search_index_page Class
 * 
 * create search index_page and save to DB from backed
 * @package Seezoo
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  
 * ===========================================================================================
 */
class Generate_search_index extends Backend
{
	protected $backend_name = '検索ページインデックス';
	protected $description = '検索用に公開ページのインデックスを作成します。';
	
	private $table = 'sz_search_index';
	private $base_path ;
	private $ssl_base_path;
	
	private $ingore_indexes = array('login', 'logout');
	
	public function run()
	{
		$CI =& get_instance();
		
		if ( ! $CI->db->table_exists($this->table))
		{
			return '検索インデックス保存用のDBが見つかりませんでした。';
		}
		
		$sql = 
			'SELECT '
			.	'pv.page_id, '
			.	'pv.page_title, '
			.	'pv.meta_title, '
			.	'pv.is_ssl_page, '
			.	'pv.meta_keyword, '
			.	'pv.meta_description, '
			.	'pp.page_path, '
			.	'per.allow_access_user '
			.'FROM '
			.	'page_versions as pv '
			.'RIGHT OUTER JOIN '
			.	'page_paths as pp '
			.'ON ('
			.	'pv.page_id = pp.page_id '
			.') '
			.'LEFT OUTER JOIN '
			.	'page_permissions as per '
			.'ON ('
			.	'pv.page_id = per.page_id '
			.') '
			.'WHERE '
			.	'pv.is_public = 1 '
			.'AND ( '
			.	'pv.is_system_page = 0 OR pp.page_path NOT LIKE ?) '
			.'ORDER BY pv.page_id ASC';
		$query = $CI->db->query($sql, array('dashboard/%'));
		
		if ( ! $query || $query->num_rows() === 0)
		{
			return '検索インデックス対象のページが見つかりませんでした。';
		}
		
		// if index targets exists, trancate already records
		$CI->db->query('TRUNCATE TABLE ' . $this->table);
		$num = 0;
		
		foreach ($query->result() as $p)
		{
			$p = $this->_get_and_parse($p);
			if ($p !== FALSE)
			{
				$CI->db->insert($this->table, $p);
				$num++;
			}
		}
		
		return $num . 'ページがインデックスされました。';
	}
	
	public function db()
	{
		$dbst = array(
			'page_id'			=> array(
									'type'			=> 'INT',
									'constraint'	=> 11
								),
			'page_path'		=> array(
									'type'			=> 'VARCHAR',
									'constraint'	=> 255,
									'null'			=> TRUE
								),
			'page_title'		=> array(
									'type'			=> 'VARCHAR',
									'contraint'	=> 255,
									'null'			=> TRUE
								),
			'indexed_words'	=> array(
									'type'			=> 'TEXT',
									'null'			=> TRUE
								)
		);
		
		return array($this->table  => $dbst);
	}
	
	protected function _get_and_parse($p)
	{
		if (in_array($p->page_path, $this->ingore_indexes))
		{
			return FALSE; // no necessary indexes.
		}
		// get page data
		if ( $p->is_ssl_page > 0 )
		{
			$page_output = http_request(ssl_page_link($p->page_id), 'GET');
		}
		else 
		{
			$page_output = http_request(page_link($p->page_id), 'GET');
		}
		
		if ( ! $page_output)
		{
			return FALSE;
		}
		// scrape page_output and add data
		$indexed_words = $p->page_title
						.	$p->meta_title
						.	$p->meta_keyword
						.	$p->meta_description
						.	$this->_scrape($page_output);
		// returns format
		return array(
			'page_id'			=> $p->page_id,
			'page_path'		=> ($p->is_ssl_page > 0) ? ssl_page_link($p->page_path) : page_link($p->page_path),
			'page_title'		=> ( ! empty($p->meta_title)) ? $p->meta_title : $p->page_title,
			'indexed_words'	=> $indexed_words
		);
	}
	
	// scrape simply
	protected function _scrape($str)
	{
		// $str is HTML strings that created by CMS.
		
		$regs = array(
			'/[\r\n|\r|\n|\t]/',                      // trim linefeeds
			'/(.*)<body([^>].+)?>(.+)<\/body>(.*)/',  // trim <body>str</body>
			'/<([^>]+)>/',                            // trim tags, attributes
			'/&[a-zA-Z0-9]+;/'                        // trim html entity
		);
		$reps = array(
			'', '$3', '', ''
		);
		
		return preg_replace($regs, $reps, $str);
	}
}