<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * テンプレート管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */
class Template_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function is_already_installed($dir)
	{
		$sql = 'SELECT * FROM templates WHERE template_handle = ?';
		$query = $this->db->query($sql, array($dir));

		if ($query->row())
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}

	function get_default_template_id()
	{
		$sql = 'SELECT default_template_id FROM site_info LIMIT 1';
		$query = $this->db->query($sql);

		$result = $query->row();

		return $result->default_template_id;
	}

	function set_default_template($tid)
	{
		$data = array('default_template_id' => (int)$tid);

		return $this->db->update('site_info', $data);
	}

	function install_template($handle)
	{
		// to correct basename
		$handle = kill_traversal($handle);
		
		$path = 'templates/' . $handle . '/';

		if (file_exists($path . 'attribute.php'))
		{
			require_once($path . 'attribute.php');
			$att = array(
				'template_name'		=> $attribute['name'],
				'description'		=> $attribute['description'],
				'template_handle'	=> $handle
			);
		}
		else
		{
			$att = array(
				'template_name'		=> 'no name',
				'description'		=> '',
				'template_handle'		=> $handle
			);
		}

		$ret = $this->db->insert('templates', $att);

		if ($ret)
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * テンプレートデータの再読み込み
	 * 
	 * @param $template_id
	 */
	function reload_template($template_id)
	{
		// get template data ( old )
		$sql   = 'SELECT template_handle FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$template_id));
		
		if ( ! $query || ! $query->row() )
		{
			return 'NOTFOUND';
		}
		
		$result = $query->row();
		if ( ! is_dir(FCPATH . 'templates/' . $result->template_handle) )
		{
			return 'NOTFOUND';
		}
		
		$path = FCPATH . 'templates/' . $result->template_handle . '/';
		if (file_exists($path . 'attribute.php'))
		{
			require_once($path . 'attribute.php');
			$att = array(
				'template_name'   => $attribute['name'],
				'description'     => $attribute['description'],
				'template_handle' => $result->template_handle
			);
		}
		else
		{
			$att = array(
				'template_name'   => 'no name',
				'description'     => '',
				'template_handle' => $result->template_handle
			);
		}
		
		$this->db->where('template_id', $template_id);
		return $this->db->update('templates', $att);
	}

	function do_uninstall($tid)
	{
		// get default template_id
		$default_id = $this->get_default_template_id();

		// update all pages using uninstalled-template_id
		$is_update_succeed = $this->_update_uninstalled_template_to_default($tid);
		if ( ! $is_update_succeed )
		{
			return FALSE;
		}

		// delete template_data
		$sql = 'DELETE FROM templates WHERE template_id = ? LIMIT 1';
		$ret = $this->db->query($sql, array($tid));

		return $ret;
	}

	function get_custom_css_by_id($tid)
	{
		$sql = 'SELECT advance_css FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$tid));

		$result = $query->row();
		return $result->advance_css;
	}

	function additional_css($tid, $css)
	{
		if (empty($css) || $css == '')
		{
			$css = null;
		}
		$data = array('advance_css' => $css);

		$this->db->where('template_id', (int)$tid);
		return $this->db->update('templates', $data);
	}

	function _update_uninstalled_template_to_default($uninstalled_template_id)
	{
		$default_template_id = $this->get_default_template_id();

		$sql = "UPDATE "
			.		"pending_pages "
			.	"SET "
			.		"template_id = ? "
			.	"WHERE "
			.		"template_id = ?"
			;
		$query = $this->db->query(
			$sql,
			array($default_template_id, $uninstalled_template_id)
		);
		$results[] = ( $query ) ? TRUE : FALSE;

		$sql = "UPDATE "
			.		"page_versions "
			.	"SET "
			.		"template_id = ? "
			.	"WHERE "
			.		"template_id = ?"
			;
		$query = $this->db->query(
			$sql,
			array($default_template_id, $uninstalled_template_id)
		);
		$results[] = ( $query ) ? TRUE : FALSE;

		$result = TRUE;
		foreach ($results as $value) {
			$result = ($result && $value);
		}
		return $result;
	}
}
