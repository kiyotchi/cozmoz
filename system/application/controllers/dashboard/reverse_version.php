<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * seezoo reverse-version script
 * 
 * Delete scrap versions, and set public version to 1
 * block_versions, and page versions
 * 
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 */

class Reverse_version extends SZ_Controller
{
	function __construct()
	{
		// No access check
		parent::SZ_Controller(FALSE);
	}
	
	function index()
	{
		// This script works CLI mode only.
		// So, exit to HTTP access.
		if ( ! $this->config->item('cli_mode') )
		{
			exit('this page can access CLI console only!');
		}
		
		echo 'Reverse version start.' . "\n";
		
		// do reverse!
		$this->_do_reverse_version();
		
		echo 'Version reversed!';
	}
	
	function _do_reverse_version()
	{
		// first, delete unpublished page versions 
		$sql = 
					'DELETE '
					.'FROM '
					.	'page_versions '
					.'WHERE '
					.	'is_public = 0 '
					.'AND '
					.	'is_system_page = 0';
		
		$this->db->query($sql);
		
		// second, select public page areas and that version
		$sql =
					'SELECT '
					.	'P.version_number, '
					.	'A.area_id '
					.'FROM '
					.	'page_versions as P '
					.'JOIN areas as A ON ('
					.	'A.page_id = P.page_id '
					.') '
					.'WHERE '
					.	'is_public = 1 '
					.'AND '
					.	'is_system_page = 0';
					
		$query = $this->db->query($sql);
		
		// prepare query
		$del_query =
					'DELETE '
					.'FROM '
					.	'block_versions '
					.'WHERE '
					.	'area_id = ? '
					.'AND '
					.	'version_number <> ?';
					
		$update_query =
					'UPDATE '
					.	'block_versions '
					.'SET '
					.	'version_number = 1, '
					.	'version_date = ? '
					.'WHERE '
					.	'area_id = ? '
					.'AND '
					.	'version_number = ?';
		
		// set block versions to 1
		foreach ( $query->result() as $row )
		{
			$data = array((int)$row->area_id, (int)$row->version_number);
			$this->db->query($del_query, $data);
			
			array_unshift($data, db_datetime());
			$this->db->query($update_query, $data);
		}

		// finally, update set version to 1.
		$sql =
					'UPDATE '
					.	'page_versions '
					.'SET '
					.	'version_number = 1, '
					.	'version_comment = ?, '
					.	'version_date = ? '
					.'WHERE '
					.	'is_public = 1 '
					.	'AND '
					.	'is_system_page = 0';
					
		$query = $this->db->query($sql, array('初期公開バージョン', db_datetime()));
		
		// success!
		return TRUE;
					
	}
}