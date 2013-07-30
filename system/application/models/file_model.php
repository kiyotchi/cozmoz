<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * ファイル管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class File_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function get_file_exts($image_only = FALSE)
	{
		$ret =  array(
			'all'		=> '全て',
			'gif'		=> 'gif',
			'jpg'		=> 'jpg',
			'jpeg'		=> 'jpeg',
			'png'		=> 'png'
		);
		if ($image_only)
		{
			return $ret;
		}
		else
		{
			$ret = array_merge($ret, array(
				'doc'		=> 'doc',
				'bmp'		=> 'bmp',
				'zip'		=> 'zip',
				'txt'		=> 'txt',
				'html'		=> 'html',
				'css'		=> 'css',
				'xls'		=> 'xls',
				'csv'		=> 'csv',
				'pdf'		=> 'pdf',
				'swf'		=> 'swf',
				'fla'		=> 'fla',
				'flv'		=> 'flv'
			));
			return $ret;
		}
	}

	function get_all_files()
	{
		$sql = 'SELECT * FROM files ORDER BY file_id DESC';
		$query = $this->db->query($sql);

		return $query->result();
	}

	function get_files($limit, $offset)
	{
		$sql = 'SELECT * FROM files ORDER BY added_date DESC LIMIT ? OFFSET ?';
		$query = $this->db->query($sql, array($limit, $offset));

		return $query->result();
	}

	function get_all_file_count()
	{
		$sql = 'SELECT COUNT(file_id) as total FROM files';
		$query = $this->db->query($sql);

		$result = $query->row();
		return $result->total;
	}

	function get_file_groups($limit, $offset)
	{
		$sql = 'SELECT * FROM file_groups ORDER BY created_date DESC LIMIT ? OFFSET ?';
		$query = $this->db->query($sql, array($limit, $offset));

		return $query->result();
	}

	function get_file_groups_all($to_array = FALSE)
	{
		$sql = 'SELECT * FROM file_groups ORDER BY created_date DESC';
		$query = $this->db->query($sql);

		if (!$to_array)
		{
			return $query->result();
		}
		else
		{
			$ret = array();
			foreach ($query->result() as $v)
			{
				$ret[$v->file_groups_id] = $v->group_name;
			}
			return $ret;
		}
	}

	function get_file_groups_count()
	{
		$sql = 'SELECT COUNT(file_groups_id) as total FROM file_groups';
		$query = $this->db->query($sql);

		$result = $query->row();
		return $result->total;
	}
	
	function get_file_group($fid)
	{
		$sql = 'SELECT file_group FROM files WHERE file_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$fid));
		
		$result = $query->row();
		return $result->file_group;
	}

	function get_all_image_files($limit, $offset)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'files '
			.	'WHERE '
			.		'extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.	'ORDER BY file_id DESC '
			.	'LIMIT ? '
			.	'OFFSET ?'
			;
		$query = $this->db->query(
			$sql,
			array(
				'gif',
				'jpg',
				'jpeg',
				'png',
				'bmp',
				'tiff',
				$limit,
				(int)$offset
			)
		);

		return $query->result();
	}

	function get_all_image_files_count()
	{
		$sql = 'SELECT '
			.		'COUNT(file_id) as total '
			.	'FROM '
			.		'files '
			.	'WHERE '
			.		'extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.		'OR extension = ? '
			.	'ORDER BY file_id DESC '
			.	'LIMIT 1';
		$query = $this->db->query(
			$sql,
			array(
				'gif',
				'jpg',
				'jpeg',
				'png',
				'bmp',
				'tiff'
			)
		);

		$result = $query->row();
		return $result->total;
	}

	function insert_new_image($data)
	{
		$ret = $this->db->insert('files', $data);

		if ($ret)
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	function update_image($data, $fid)
	{
		$this->db->where('file_id', $fid);
		return $this->db->update('files', $data);
	}

	function insert_new_file_group($post)
	{
		$sql = 
					'SELECT '
					.	'file_groups_id '
					.'FROM '
					.	'file_groups '
					.'WHERE '
					.	'group_name = ?';
		$query = $this->db->query($sql, array($post['group_name']));
		if ($query && $query->num_rows() > 0)
		{
			return FALSE;
		}
		$res = $this->db->insert('file_groups', $post);
		if ($res)
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	function do_replace_file($repid, $data)
	{
		// get replace target data
		$sql = 'SELECT '
			.		'crypt_name, '
			.		'extension '
			.	'FROM '
			.		'files '
			.	'WHERE '
			.		'file_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($repid));

		$result = $query->row();

		// update files data
		$this->db->where('file_id', $repid);
		$res = $this->db->update('files', $data);

		if ($res)
		{
			return $result;
		}
		else
		{
			return FALSE;
		}
	}


	function update_file_group($post, $cid)
	{
		$this->db->where('file_groups_id', $cid);
		return $this->db->update('file_groups', $post);
	}

	function delete_file_group($cid)
	{
		$this->db->where('file_groups_id', $cid);
		$this->db->delete('file_groups');
		return $this->db->affected_rows() > 0;
	}

	function set_file_group($group_ids, $file_ids)
	{
		$files = (explode(':', $file_ids) === FALSE)
		            ? array($file_ids)
		            : explode(':', $file_ids);
		$g = array('file_group' => ':' . implode(':', $group_ids) . ':');
		foreach ($files as $file)
		{
			$this->db->where('file_id', $file);
			$r = $this->db->update('files', $g);
			if (!$r)
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	function search_file($name, $ext, $group, $uid)
	{
		$where = array();
		$bind = array();

		if (!empty($name))
		{
			$where[] = ' f.file_name LIKE ? ';
			$bind[] = '%' . $name . '%';
		}

		if (!empty($ext))
		{
			$where[] = ' f.extension = ? ';
			$bind[] = $ext;
		}

		if ($group)
		{
			$where[] = ' f.file_group LIKE ? ';
			$bind[] = '%:' . $group . ':%';
		}

		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'files as f '
			.		'RIGHT OUTER JOIN directories as d ON('
			.			'f.directories_id = d.directories_id'
			.		') '
			.	'WHERE 1'
			;
		if (count($where) > 0)
		{
			$sql .= ' AND ' . implode(' AND ' , $where);
		}
		$sql .= ' ORDER BY f.added_date DESC';
		$query = $this->db->query($sql, $bind);

		if ($query->num_rows() > 0)
		{
			$ret = array();
			foreach($query->result() as $v)
			{
				if ( ! $v->file_id)
				{
					continue; // file is directory
				}
				if (
					strpos($v->access_permission, ':' . $uid . ':') !== FALSE
					|| $uid == 1
					|| $v->access_permission == ''
				)
				{
					$ret[] = $v;
				}
			}
			return $ret;
		}

		return array();
	}

	function search_file_data($s, $limit, $offset)
	{
		$where = array();
		$bind = array();
		if ($s['name'] != '-')
		{
			$where[] = ' file_name LIKE ? ';
			$bind[] = '%' . $s['name'] . '%';
		}

		if ($s['ext'] != '-')
		{
			$where[] = ' extension = ? ';
			$bind[] = $s['ext'];
		}

		if ($s['group'] > 0)
		{
			$where[] = ' file_group LIKE ? ';
			$bind[] = '%:' . $s['group'] . ':%';
		}

		$bind[] = $limit;
		$bind[] = $offset;

		$sql = 'SELECT * FROM files WHERE 1';
		if (count($where) > 0)
		{
			$sql .= ' AND ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY added_date DESC LIMIT ? OFFSET ?';
		$query = $this->db->query($sql, $bind);

		return $query->result();
	}

	function search_file_data_count($s)
	{
		$where = array();
		$bind = array();
		if ($s['name'] != '-')
		{
			$where[] = ' file_name LIKE ? ';
			$bind[] = '%' . $s['name'] . '%';
		}

		if ($s['ext'] != '-')
		{
			$where[] = ' extension = ? ';
			$bind[] = $s['ext'];
		}

		if ($s['group'] > 0)
		{
			$where[] = ' file_group LIKE ? ';
			$bind[] = '%:' . $s['group'] . ':%';
		}

		$sql = 'SELECT COUNT(file_id) AS total FROM files WHERE 1';
		if (count($where) > 0)
		{
			$sql .= ' AND ' . implode(' AND ', $where);
		}
		$query = $this->db->query($sql, $bind);

		$result = $query->row();
		return $result->total;
	}

	function delete_file_data($fids)
	{
		foreach ($fids as $v)
		{
			$this->db->or_where('file_id', (int)$v);
		}

		$this->db->select('crypt_name, extension');
		$query = $this->db->get('files');

		foreach ($query->result() as $value)
		{
			@unlink('files/' . $value->crypt_name . '.' . $value->extension);
			@unlink ('files/thumbnail/'. $value->crypt_name . '.' . $value->extension);
		}

		$query->free_result();

		foreach ($fids as $v)
		{
			$this->db->or_where('file_id', (int)$v);
		}
		$this->db->delete('files');

		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function delete_file_one($id)
	{
		$sql = 'SELECT '
			.		'crypt_name, '
			.		'extension '
			.	'FROM '
			.		'files '
			.	'WHERE '
			.		'file_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array((int)$id));

		$result = $query->row();

		// try unlink file
		@unlink('files/' . $result->crypt_name . '.' . $result->extension);
		@unlink ('files/thumbnail/'. $result->crypt_name . '.' . $result->extension);

		$sql = 'DELETE FROM files WHERE file_id = ? LIMIT 1';
		$this->db->query($sql, array((int)$id));
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	function get_file_data($fid)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'files '
			.	'WHERE '
			.		'file_id = ? '
			.	'LIMIT 1'
			;
		$query = $this->db->query($sql, array($fid));

		return $query->row();
	}

	/* ===================== directory method ================================== */


	function get_directory_id($dir_path = '/')
	{
		if ($dir_path == '/')
		{
			return 1; // short cut
		}

		$sql = 'SELECT directories_id FROM directories WHERE path_name = ?';
		$query = $this->db->query($sql, array($dir_path));

		if ($query->row())
		{
			$result = $query->row();
			return $result->directories_id;
		}

		return 0;
	}

	function get_directories($did)
	{

		$sql = 'SELECT * FROM directories WHERE parent_id = ? ORDER BY created_date DESC';
		$query = $this->db->query($sql, array($did));

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		return array();
	}

	function get_files_from_directory($did)
	{
		$sql = 'SELECT * FROM files WHERE directories_id = ? ORDER BY added_date DESC';
		$query = $this->db->query($sql, array($did));

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		return array();
	}
	
	function directory_exists($dirname, $parent_did)
	{
		$sql =
				'SELECT '
				.	'directories_id '
				.'FROM '
				.	'directories '
				.'WHERE '
				.	'dir_name = ? '
				.'AND '
				.	'parent_id = ?'
				;
		$query = $this->db->query($sql, array($dirname, (int)$parent_did));
		if ( $query && $query->num_rows() > 0 )
		{
			$result = $query->row();
			return $result->directories_id;
		}
		return FALSE;
	}
	
	function add_new_directory_from_did($dirname, $parent_did = 1)
	{
		$data = array(
			'path_name'			=> $dirname,
			'parent_id'			=> $parent_did,
			'dir_name'				=> $dirname,
			'created_date'		=> db_datetime(),
			'access_permission'	=> ''
		);

		$ret = $this->db->insert('directories', $data);

		if ($ret)
		{
			return $this->db->insert_id();
		}
		return FALSE;
	}

	function add_new_directory($dir_name, $current_path = '/')
	{
		$did = $this->get_directory_id($current_path);

		$data = array(
			'path_name'			=> $dir_name,
			'parent_id'			=> $did,
			'dir_name'				=> $dir_name,
			'created_date'		=> db_datetime(),
			'access_permission'	=> ''
		);

		$ret = $this->db->insert('directories', $data);

		if ($ret)
		{
			return $this->db->insert_id();
		}
		return FALSE;
	}

	function clone_directory($dir_name, $target_id)
	{
		// get target directory
		$sql = 'SELECT parent_id FROM directories WHERE directories_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($target_id));

		$result = $query->row();
		// first, create new directory
		$data = array(
			'path_name'		=> $dir_name,
			'parent_id'		=> $result->parent_id,
			'dir_name'			=> $dir_name,
			'created_date'	=> db_datetime()
		);

		$ret = $this->db->insert('directories', $data);

		$new_did = $this->db->insert_id();

		// second, copy inner files recursive
		$this->_clone_files_recursive($target_id, $new_did);

		return $new_did;
	}

	function _clone_files_recursive($did, $ndid)
	{
		// clone files recursive
		// first, files that current directories_id has all copy
		$sql = 'SELECT * FROM files WHERE directories_id = ?';
		$query = $this->db->query($sql, array($did));

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $file)
			{
				$this->_copy_file($file, $ndid);
			}
		}

		// second, copy inner diretories all
		$sql = 'SELECT '
			.		'directories_id, '
			.		'path_name '
			.	'FROM '
			.		'directories '
			.	'WHERE '
			.		'parent_id = ?'
			;
		$query = $this->db->query($sql, array($did));

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $dir)
			{
				$data = array(
					'path_name'		=> $dir->path_name,
					'parent_id'		=> $ndid,
					'dir_name'			=> $dir->path_name,
					'created_date'	=> db_datetime()
				);

				$this->db->insert('directories', $data);

				$new_did = $this->db->insert_id();

				$this->_clone_files_recursive($dir->directories_id, $new_did);
			}
		}
	}

	function _copy_file($file, $ndid)
	{
		$new_name = sha1(uniqid(mt_rand(), TRUE));
		$orig_path = 'files/' . $file['crypt_name'] . '.' . $file['extension'];
		$new_path = 'files/' . $new_name . '.' . $file['extension'];
		// first, copy same file
		// original file
		$cp = @copy($orig_path, $new_path);

		// if copy is missed, return
		if (!$cp)
		{
			return;
		}

		// if file is image, copy also thummbnail
		// width and height data is exists, this files is image!
		if (
			$file['width'] > 0
			&& $file['height']
			&& file_exists('files/thumbnail/' . $file['crypt_name'] . '.' . $file['extension'])
		)
		{
			$orig_thumb_path = 'files/thumbnail/' . $file['crypt_name'] . '.' . $file['extension'];
			$new_thumb_path = 'files/thumbnail/' . $new_name . '.' . $file['extension'];

			$cp2 = @copy($orig_thumb_path, $new_thumb_path);

			if (!$cp2)
			{
				return;
			}
		}

		// this process, copy file is all success, insert DB
		unset($file['file_id']);
		$file['crypt_name'] = $new_name;
		$file['added_date'] = db_datetime();
		$file['directories_id'] = $ndid;
		$file['file_group'] = '';

		$this->db->insert('files', $file);
	}


	function move_directory_to_directory($from, $to)
	{
		$this->db->where('directories_id', $from);
		return $this->db->update('directories', array('parent_id' => $to));
	}

	function move_file_to_directory($from, $to)
	{
		$this->db->where('file_id', $from);
		return $this->db->update('files', array('directories_id' => $to));
	}

	function delete_dir($dir_id)
	{
		// first, delelte inner files
		$sql = 'SELECT file_id FROM files WHERE directories_id = ?';
		$query = $this->db->query($sql, array($dir_id));

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $f)
			{
				$this->delete_file_one($f->file_id);
			}
		}

		// second, delete inner directory
		$sql = 'SELECT directories_id FROM directories WHERE parent_id = ?';
		$query = $this->db->query($sql, array($dir_id));

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $d)
			{
				// delete recursive
				$this->delete_dir($d->directories_id);
			}
		}

		// finaly, delete current directory
		$this->db->where('directories_id', $dir_id);
		$this->db->delete('directories');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	function update_directory_name($did, $new_name)
	{
		$this->db->where('directories_id', $did);
		return $this->db->update('directories', array('dir_name' => $new_name, 'path_name' => $new_name));
	}

	function get_dir_data_one($did)
	{
		$sql = 'SELECT * FROM directories WHERE directories_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($did));

		return $query->row();
	}

	function update_directory_permission($did, $perms, $is_recursive = 0)
	{
		$set_perm = ':' . implode(':', $perms) . ':';

		$this->db->where('directories_id', $did);
		$base = $this->db->update('directories', array('access_permission' => $set_perm));

		// if base directory permission set and recursive flag on,
		// set same permission to child directory recursive.
		if ($base && $is_recursive > 0)
		{
			$this->_update_directory_permission_recursive($did, $set_perm);
		}

		return TRUE;
	}

	function _update_directory_permission_recursive($did, $permission_str)
	{
		// first, get child directories
		$sql = 'SELECT directories_id FROM directories WHERE parent_id = ?';
		$query = $this->db->query($sql, array($did));

		if ($query->num_rows() > 0)
		{
			// second, child directory set permission
			foreach ($query->result() as $d)
			{
				$this->db->where('directories_id', $d->directories_id);
				$ret = $this->db->update(
					'directories',
					array('access_permission' => $permission_str)
				);

				// set_recursive
				$this->_update_directory_permission_recursive(
					$d->directories_id,
					$permission_str
				);
			}
		}
	}

	function merge_file_ids_array_for_zip($fid_array, $did_array, $directory_prefix = '')
	{
		$ret = array();
		// first, file_ids array add
		foreach ($fid_array as $fid)
		{
			$file = $this->get_file_data((int)$fid);
			if ($file)
			{
				$ret[] = array(
					make_file_path($file),
					$directory_prefix . $file->file_name . '.' . $file->extension
				);
			}
		}

		// second, directry files add
		foreach ($did_array as $did)
		{
			$directory_data = $this->get_dir_data_one($did);
			$new_prefix = $directory_prefix . $directory_data->dir_name . '/';
			$files = $this->get_files_from_directory($did);
			$dirs = $this->get_directories($did);
			$f = array();
			$d = array();
			if ($files)
			{
				foreach ($files as $v)
				{
					$f[] = $v->file_id;
				}
			}
			if ($dirs)
			{
				foreach ($dirs as $v)
				{
					$d[] = $v->directories_id;
				}
			}
			$sub = $this->merge_file_ids_array_for_zip(
				$f,
				$d,
				$new_prefix
			);
			foreach ($sub as $s)
			{
				$ret[] = $s;
			}
		}
		return $ret;
	}
	
	function update_filename($fid, $new_name)
	{
		$this->db->where('file_id', $fid);
		return $this->db->update('files', array('file_name' => $new_name));
	}
}
