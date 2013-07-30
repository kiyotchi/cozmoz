<?php
/**
 * Seezoo ファイルグループ管理ページクラス
 */

class File_groups extends SZ_Controller
{
	public static $page_title = 'グループ管理';
	public static $description = 'システムで使用するファイルをグループに分別します。';

	// pagination per page
	public $limit = 20;

	protected $upload_dir = 'files/';
	protected $allowed_types = 'gif|jpg|jpeg|png|bmp|tiff|zip|txt|csv|doc|rtf|xls|pdf|swf|fla|tar.gz|html|css|js|php';
	protected $upload_thumbnail_dir = 'files/thumbnail/';

	public $page = 'files';
	public $msg = '';
	public $ticket_name = 'sz_ticket';

	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('file_model');
	}

	function index($offset = 0)
	{

		//$data->files = $this->file_model->get_all_files();
		$data->file_groups = $this->file_model->get_file_groups($this->limit, (int)$offset);

		$total = $this->file_model->get_file_groups_count();

		$endoftotal = (($offset+ $this->limit) > $total) ? $total : ($offset + $this->limit);
		if($total)
		{
			 $data->total = $total . '件中' . ($offset + 1) . '-' . $endoftotal . '件表示';
		}
		else
		{
			$data->total = '';
		}

		$path = page_link('dashboard/files/file_groups/index/');

		$data->pagination = $this->_pagination($path, $total, 4, $this->limit);

		$data->ticket = $this->_set_ticket();

		$this->load->view('dashboard/files/file_groups_list', $data);
	}

	function ajax_add_group($token)
	{
		if (!$this->session->userdata('sz_token') || $this->session->userdata('sz_token') !== $token)
		{
			exit('error');
		}

		// keep main flash token
		$this->session->keep_flashdata($this->ticket_name);

		$post = array(
			'group_name'		=> $this->input->post('group_name', TRUE),
			'created_date'	=> db_datetime()
		);
		$ret = $this->file_model->insert_new_file_group($post);

		if ($ret && is_numeric($ret))
		{
			$data = array(
				'file_groups_id'	=> $ret
			);
			echo json_encode($data);
		}
		else
		{
			echo 'error';
		}
	}

	function ajax_update_file_group($token)
	{
		if (!$this->session->userdata('sz_token') || $this->session->userdata('sz_token') !== $token)
		{
			exit('error');
		}

		$post = array(
			'group_name'	=> $this->input->post('group_name', TRUE)
		);

		$ret = $this->file_model->update_file_group($post, $this->input->post('file_groups_id'));

		if ($ret)
		{
			echo $post['group_name'];
		}
		else
		{
			echo 'error';
		}
	}

	function ajax_delete_file_group($cid, $token)
	{
		if (!$this->session->userdata('sz_token') || $this->session->userdata('sz_token') !== $token || !$cid)
		{
			exit('error');
		}

		$ret = $this->file_model->delete_file_group((int)$cid);

		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
		}
	}

	function search()
	{
		$name = $this->input->post('file_name');
		$ext = $this->input->post('file_ext');

		if ($ext === 'all')
		{
			$ext = '';
		}

		$data->files = $this->file_model->search_file_data($name, $ext);

		$data->ticket = $this->_set_ticket();

		$data->ext_list = $this->file_model->get_file_exts();

		$this->load->view('dashboard/files/file_list', $data);
	}

	function edit()
	{

		$this->_check_ticket();

		$mode = $this->input->post('method');

		if ($mode === 'dl')
		{
			$this->_multiple_download();
			exit;
		}
		else if ($mode === 'delete')
		{
			$ret = $this->file_model->delete_file_data($this->input->post('file_ids'));

			redirect('dashboard/files/index');
//			if ($ret)
//			{
//				$this->msg = 'ファイルを削除しました。';
//			}
//			else
//			{
//				$this->msg = 'ファイル削除に失敗しました。';
//			}
//			$this->index();

		}
		else
		{
			redirect('dashboard/files');
			//return;
		}
	}

	function upload_init()
	{
		$this->load->view('dashboard/files/upload');
	}

	function multiple_upload()
	{
		$this->load->view('dashboard/files/multiple_upload');
	}

	function multiple_piece()
	{
		// is handle is posted, do upload
		if ($this->input->post('upload_handle'))
		{
			if (is_uploaded_file($_FILES['upload_data']['tmp_name']) === TRUE)
			{
				$up = $this->_do_upload();

				if ($up)
				{
					if (has_icon_ext($up['extension']))
					{
						$up['is_icon'] = true;
					}
					else
					{
						$up['is_icon'] = false;
					}

					$data->complete = 1;
					$data->data = json_encode($up);
				}
				else
				{
					$data->complete = 0;
				}
			}
			else
			{
				$data->complete = 2;
			}
		}
		else
		{
			$data = array();
		}
		$this->load->view('dashboard/files/multiple_piece', $data);
	}

	function ajax_upload()
	{
		$up = $this->_do_upload();

		if ($up)
		{
			if (has_icon_ext($up['extension']))
			{
				$up['is_icon'] = true;
			}
			else
			{
				$up['is_icon'] = false;
			}
			$this->load->view('dashboard/files/upload_complete', array('file' => json_encode($up)));
		}
		else
		{
			//$this->load->view('dashboard/files/upload_error');
		}

	}

	function ajax_file_view($fid, $token)
	{
		if (!$fid ||  $this->session->userdata('sz_token') !== $token)
		{
			exit('access_denied');
		}

		$data->file = $this->file_model->get_file_data((int)$fid);

		$dl_token = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_flashdata('sz_dl_token', $dl_token);
		$data->ticket = $dl_token;
		$data->fid = (int)$fid;

		$this->load->view('dashboard/files/ajax_file_view', $data);

	}

	function _do_upload()
	{
		// 2010/05/03 modified
		// use CodeIgniter buildin library
		$this->load->library('upload');

		$data = array();

		// upload config
		$config = array(
			'upload_path'		=> $this->upload_dir,
			'allowed_types'		=> $this->allowed_types,
			'overwrite'			=> FALSE,
			'encrypt_name'		=> TRUE,
			'remove_spaces'		=> TRUE
		);

		$this->upload->initialize($config);

		// try upload
		$result = $this->upload->do_upload('upload_data');

		if (!$result)
		{
			echo ($this->upload->display_errors());
			return FALSE;
		}

		$ret = $this->upload->data();


		// get data for DB insert
		$data['file_name']		= substr($ret['orig_name'], 0, strrpos($ret['orig_name'], '.'));
		$data['crypt_name']		= $ret['raw_name'];
		$data['extension']		= substr($ret['file_ext'], 1);
		$data['size']			= $ret['file_size'];
		$data['width']			= ($ret['is_image'] == 1) ? (int)$ret['image_width'] : 0;
		$data['height']			= ($ret['is_image'] == 1) ? (int)$ret['image_height'] : 0;
		$data['added_date'] = date('Y-m-d H:i:s', time());

		// create thumbnail if upload file is image
		if ($ret['is_image'] > 0)
		{

			if ($ret['image_width'] > 60 || $ret['image_height'] > 60)
			{
				// load image_lib library
				$conf = array(
					'source_image'		=> $ret['full_path'],
					'create_thumb'		=> TRUE,
					'new_image'			=> $this->upload_thumbnail_dir,
					'thumb_marker'		=> '',
					'width'				=> 60,
					'height'			=> 60,
					'maintain_ratio'	=> TRUE
				);

				$this->load->library('image_lib', $conf);

				if (! $this->image_lib->resize())
				{
					return FALSE;
				}
			}
			else
			{
				// simple copy file
				if (!copy($ret['full_path'], $this->upload_thumbnail_dir . $ret['file_name']))
				{
					return FALSE;
				}
			}
		}

		$res = $this->file_model->insert_new_image($data);

		if ($res)
		{
			$data['file_id'] = $res;
			$data['thumbnail_path'] = file_link() . 'files/thumbnail.' . $data['file_name'];

			return $data;
		}
		else
		{
			return FALSE;
		}
	}

	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_file_token', $ticket);

		return $ticket;
	}

	function _check_ticket()
	{
		$ticket = $this->input->post('sz_file_token');
		if (!$ticket || $ticket !== $this->session->userdata('sz_file_token'))
		{
			exit('不正な操作です。');
		}
	}

	// from ajax
	function delete_file($id, $token)
	{
		if (!$id || $token !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$ret = $this->file_model->delete_file_one($id);

		if ($ret)
		{
			echo 'complete';
		}
		else
		{
			echo 'error';
		}
	}

	function file_download_from_popup($fid, $token = FALSE)
	{
		if (!$token || $this->session->userdata('sz_token') !== $token)
		{
			echo 'access_denied.';
		}
		$file = $this->file_model->get_file_data((int)$fid);
		$file_path = 'files/' . $file->crypt_name . '.' . $file->extension;
		$file_name = $file->file_name . '.' . $file->extension;

		$this->load->helper('download');
		force_download($file_name, file_get_contents($file_path));
	}

	function file_download()
	{
		if (!$this->input->post('file_id') || $this->session->flashdata('sz_dl_token') !== $this->input->post('ticket'))
		{
			exit('access_denied');
		}

		$file = $this->file_model->get_file_data((int)$this->input->post('file_id'));
		$file_path = 'files/' . $file->crypt_name . '.' . $file->extension;
		$file_name = $file->file_name . '.' . $file->extension;

		header("Content-Type: application/occet-stream");
		header("Content-Disposition: attachment; filename=\"" . $file_name . "\"");
		header("Content-Length: " . filesize($file_path));
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", FALSE);
		header("Content-Transfer-Encoding: binary");

//		$out = '';
//		$fp = fopen($file_path, 'rb');
//		if ($fp === FALSE)
//		{
//			return FALSE;
//		}
//
//		while(!feof($fp))
//		{
//			$out = fread($fp, 1024 * 1024);
//			echo $out;
//		}
//
//		fclose($fp);
		readfile($file_path);
		exit();
	}

	function _multiple_download()
	{
		$token = $this->input->post('sz_file_token');
		if (!$token || $token != $this->session->userdata('sz_file_token'))
		{
			exit('access_denied.');
		}

		$ids = $this->input->post('file_ids');

		// load zip library
		$this->load->library('zip');
		foreach ($ids as $id)
		{
			if ((int)$id === 0)
			{
				continue;
			}
			$file = $this->file_model->get_file_data($id);
			if ($file)
			{
				$this->zip->read_file(make_file_path($file), FALSE, $file->file_name . '.' . $file->extension);
			}
		}
		$this->zip->download('files' . date('YmdHis', time()) . '.zip');
	}

	function _pagination($path, $total, $segment, $limit)
	{
		$this->load->library('pagination');

		$config = array(
		  'base_url'      => $path,
		  'total_rows'   => $total,
		  'per_page'    => $limit,
		  'uri_segment'=> $segment,
		  'num_links'    => 5,
		  'prev_link'     => '&laquo;前へ',
		  'next_link'     => '次へ&raquo;'
		);
		$this->pagination->initialize($config);

		return $this->pagination->create_links();
	}
}