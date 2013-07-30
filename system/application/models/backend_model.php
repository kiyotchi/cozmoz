<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ===============================================================================
 * 
 * Seezoo バックエンドプロセス実行モデルクラス
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ===============================================================================
 */
class Backend_model extends Model
{
	function __construct()
	{
		parent::Model();
	}
	
	function single_run($sbid)
	{
		$process_time = db_datetime();
		
		$sql = 'SELECT backend_handle FROM sz_backend '
				. 'WHERE sz_backend_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($sbid));
		
		if (!$query)
		{
			return array('result' => '実行クラスが見つかりませんでした。', 'last_run' => $process_time);
		}
		
		$result = $query->row();
		$query->free_result();
		
		$class = $result->backend_handle;
		$class_name = ucfirst($class);
		
		if ( ! file_exists(APPPATH . 'backend/' . $class . '.php'))
		{
			return array('result' => '実行ファイルが見つかりませんでした。', 'last_run' => $process_time);
		}
		
		require_once(APPPATH . 'backend/' . $class . '.php');
		
		if ( ! class_exists($class_name))
		{
			return array('result' => '実行クラスが見つかりませんでした。', 'last_run' => $process_time);
		}
		
		$b = new $class_name();
		
		$data = array('result' => $b->run(), 'last_run' => $process_time);
		
		// if process is succeed, update backend status
		$this->db->where('sz_backend_id', $sbid);
		$this->db->update('sz_backend', $data);
		
		return $data;
		
	}
	
	function cron_run()
	{
		$sql = 'SELECT sz_backend_id FROM sz_backend';
		
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $v)
		{
			$this->single_run($v->sz_backend_id);
		}
	}
	
	function get_enable_backend_list()
	{
		$sql = 'SELECT * FROM sz_backend '
			. 'ORDER BY sz_backend_id ASC';
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		return array();
	}
	
	function get_enable_install_list()
	{
		$list = directory_map(APPPATH . 'backend/', TRUE);
		
		$ret = array();
		
		if (!$list)
		{
			return $ret;
		}
		foreach ($list as $v)
		{
			// Does list file is php file?
			if ( ! preg_match('/.+\.php$/', $v))
			{
				continue;
			}
			
			$class_path = substr($v, 0, strrpos($v, '.php'));
			
			$cnt = $this->db->query('SELECT sz_backend_id FROM sz_backend WHERE backend_handle = ? LIMIT 1', array($class_path));
			if ($cnt->row())
			{
				continue;
			}
			$class_name = ucfirst($class_path);
			
			require_once(APPPATH . 'backend/' . $v);
			
			if ( ! class_exists($class_name))
			{
				continue;
			}
			
			$class = new $class_name();
			$ret[] = array(
				'backend_name'	=> $class->get_backend_name(),
				'description'		=> $class->get_description(),
				'backend_handle'	=> $class_path
			);
		}
		
		return $ret;
		
	}
	
	function install_process($handle = '')
	{
		// sanitize and insert database
		
		// to correct basename
		$handle = kill_traversal($handle);
		
		// Does handle name hand allowed character only?
		if ( ! preg_match('/^[_a-zA-Z0-9]+$/', $handle))
		{
			return 'invalid_handle';
		}
		
		// Does handle file exists?
		if ( ! file_exists(APPPATH . 'backend/' . $handle . '.php'))
		{
			return 'file_not_found';
		}
		
		// Does handle process already installed?
		$check = $this->db->query('SELECT sz_backend_id FROM sz_backend WHERE backend_handle = ? LIMIT 1', array($handle));
		if ($check->row())
		{
			return 'already_installed';
		}
		
		require(APPPATH . 'backend/' . $handle . '.php');
		$class = ucfirst($handle);
		// Does handle Class exists?
		if ( ! class_exists($class))
		{
			return 'class_not_found';
		}
		
		$c = new $class();
		
		// Does process use DB?
		if (method_exists($c, 'db'))
		{
			$this->_create_backend_db($c);
		}
		
		$data = array(
			'backend_handle'	=> $handle,
			'backend_name'	=> $c->get_backend_name(),
			'description'		=> $c->get_description()
		);
		
		$ret = $this->db->insert('sz_backend', $data);
		
		return ($ret) ? 'success' : 'error';
		
	}
	
	function uninstall_process($id)
	{
		$this->db->where('sz_backend_id', $id);
		$this->db->delete('sz_backend');
		
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
	function _create_backend_db($c)
	{
		// load database forge class
		$this->load->dbforge();
		
		$dbst = $c->db();
		foreach ($dbst as $key => $field)
		{
			if ($this->db->table_exists($key))
			{
				continue;
			}
			$this->dbforge->add_field($field);
			
			$this->dbforge->create_table($key);
		}
	}
}