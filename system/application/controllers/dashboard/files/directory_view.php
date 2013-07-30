<?php
/**
 * ===============================================================================
 *
 * Seezoo dashboard ファイル管理コントローラ
 *
 * @note ファイルをディレクトリビューで管理
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */

class Directory_view extends SZ_Controller
{
	public static $page_title = 'ファイル管理';
	public static $description = 'ファイルをディレクトリビューで管理します。';

	// 初期表示ディレクトリ階層
	public $init_dir = '/';

	// アップロードを許可する拡張子
	protected $allowed_types = 'gif|jpg|jpeg|png|bmp|tiff|zip|txt|csv|doc|rtf|xls|pdf|swf|fla|flv|gz|html|css|js|php|mp4';

	// オリジナルデータ保存先
	protected $upload_dir = 'files/';

	// サムネイル保存先
	protected $upload_thumbnail_dir = 'files/thumbnail/';
	
	// 不要なDBファイルリスト
	protected $ignore_db_files = array('__MAXOSX', 'Thumbs.db');

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		/*
		 * note: 
		 * ファイルマネージャのアップロードはフロントの編集画面からもこのメソッドにアクセスすることがある。
		 * この時、クラス自体にアクセス権限が発行されていない場合はログインページにリダイレクトされてしまう。
		 * これを回避するため、親クラスの権限チェックをフックして、index以外のメソッドをコールした際に、
		 * forceなトークンがある場合のみ権限判定を一時的にスキップさせる。
		 */
		parent::SZ_Controller(FALSE);
		
		// force token exists?
		if ( $this->router->fetch_method() === 'index'
				|| ! $this->session->userdata('sz_force_file_manager_token'))
		{
			$this->_set_ci_controller_page_status();
		}
		else 
		{
			// else, force set outpu content-type
			$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		}
		$this->load->model('file_model');
	}

	/**
	 * デフォルトメソッド
	 */
	function index()
	{
		$did = $this->file_model->get_directory_id($this->init_dir);

		$data->dirs = $this->file_model->get_directories($did);
		$data->files = $this->file_model->get_files_from_directory($did);
		$data->tree = array(1 => $this->init_dir);
		$data->ticket = $this->_set_ticket();

		$this->load->view('dashboard/files/directory_view', $data);
	}
	
	function ajax_api_upload()
	{
		if ( ! isset($_SERVER['HTTP_X_REQUESTED_WITH'])
		      || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest' )
		{
			exit('acess_denied');
		}
		
		$i       = 0;
		$did     = (int)$this->input->post('directory_id');
		$uploads = array();
		
		while ( isset($_FILES['upload_file' . ++$i]) )
		{
			$up = $this->_do_upload($did, TRUE, 'upload_file' . $i);
			$up['is_icon'] = ( has_icon_ext($up['extension']) ) ? TRUE : FALSE;
			
			$uploads[] = $up;
		}
		
		//var_dump($uploads);
		
		echo json_encode($uploads);
	}
	
	/**
	 * Ajax応答：zipファイル展開
	 * @param int $file_id
	 * @param int $dir_id
	 * @param string $token
	 */
	function extract_archive($file_id, $dir_id, $token = FALSE)
	{
		if ( ! $token || $token !== $this->session->userdata('sz_token') )
		{
			exit('access_denied');
		}
		else if ( ! class_exists('ZipArchive') )
		{
			exit('System does not support ZipArchive class');
		}
		
		$file = $this->file_model->get_file_data($file_id);
		if ( strtolower($file->extension) !== 'zip' )
		{
			exit('File does not zip archive');
		}
		
		$source      = FCPATH  . 'files/' . $file->crypt_name . '.' . $file->extension;
		$extract_dir = FCPATH . 'files/upload_tmp/';
		$stats       = array();
		$now         = db_datetime();
		$dir_stack   = array();
		$file_stack  = array();
		$rmdir_stack = array();
		
		// Get archived fileinfo
		$zip = new ZipArchive();
		if ( $zip->open($source) !== TRUE )
		{
			exit('zip open error');
		}
		
		for ( $i = 0; $i < $zip->numFiles; ++$i )
		{
			$stat      = $zip->statIndex($i);
			$filepath  = $stat['name'];
			$filename  = basename($filepath);
			if ( in_array($filename, $this->ignore_db_files) )
			{
				// skip __MAXOSX, Thumbs.db file
				continue;
			}
			
			// stat data is a directory?
			$is_dirstat = preg_match('#/$#', $filepath);
			$ext_dot    = strrpos($filename, '.');
			$directory  = ltrim(trim(dirname($filepath), '.'), '/');
			$dirs       = explode('/', $directory);
			$stat_dir   = $file->directories_id;
			
			// create path to directory
			foreach ( $dirs as $key => $dir )
			{
				$exists = $this->file_model->directory_exists($dir, $stat_dir);
				if ( $exists !== FALSE )
				{
					$stat_dir = $exists;
				}
				else
				{
					$stat_dir = $this->file_model->add_new_directory_from_did($dir, $stat_dir); 
					if ( $key === 0 )
					{
						$dir_stack[] = array('directory_id' => $stat_dir, 'dir_name' => $dir);
					} 
				}
				
				if ( $key === 0 )
				{
					$rmdir_stack[] = $dir;
				} 
			}
			if ( ! $is_dirstat )
			{
				$stats[] = array(
					'file_name'      => substr($filename, 0, $ext_dot),
					'crypt_name'     => md5(uniqid(mt_rand(), TRUE)),
					'extension'      => substr($filename, $ext_dot + 1),
					'added_date'     => $now,
					'width'          => 0,
					'height'         => 0,
					'directories_id' => $stat_dir,
					'file_group'     => '',
					'path'           => $filepath,
					'current'        => ( empty($directory) ) ? TRUE : FALSE
				);
			}
		}

		$zip->extractTo($extract_dir);
		
		foreach ( $stats as $stat )
		{
			$file    = $extract_dir . $stat['path'];
			$newfile = FCPATH . 'files/' . $stat['crypt_name'] . '.' . $stat['extension'];
			
			if ( ! file_exists($file) )
			{
				continue;
			}
			// try move file
			if ( ! rename($file, $newfile) )
			{
				continue;
			}
			
			// database insert
			// file is image?
			$img = @getimagesize($newfile);
			if ( $img )
			{
				$stat['width']  = $img[0];
				$stat['height'] = $img[1];
			}
			// detect filesize
			$stat['size'] = round(filesize($newfile) / 1024);
			$isCurrent    = $stat['current'];
			unset($stat['path']);
			unset($stat['current']);
			$fid = $this->file_model->insert_new_image($stat);
			if ( $fid && $isCurrent === TRUE )
			{
				$stat['file_id'] = $fid;
				$file_stack[]    = $stat;
			}
		}
		
		// cleanup temporary extracted directory
		foreach ( $rmdir_stack as $rmdir )
		{
			if ( is_dir($extract_dir . $rmdir) )
			{
				@rmdir($extract_dir . $rmdir);
			}
		}
		
		echo json_encode(array('directory' => $dir_stack, 'files' => $file_stack));
	}
	
	/**
	 * AJax応答：ファイル検索
	 * @param $token
	 */
	function search_files($token = FALSE)
	{
		if (!$token ||  $this->session->userdata('sz_token') !== $token)
		{
			exit('access_denied');
		}

		$name = $this->input->post('name');
		$ext = $this->input->post('ext');

		if ($ext == 'all')
		{
			$ext = '';
		}
		$group = (int)$this->input->post('group');
		$uid = $this->session->userdata('user_id');

		$data->files = $this->file_model->search_file($name, $ext, $group, $uid);

		$this->load->view('dashboard/files/ajax_search_view', $data);
	}

	/**
	 * iframeアップロードセットアップ
	 */
	function upload_init()
	{
		$this->load->view('dashboard/files/upload');
	}

	/**
	 * 複数ファイルアップロードフォームセットアップ
	 */
	function multiple_upload()
	{
		$this->load->view('dashboard/files/multiple_upload');
	}

	/**
	 * 複数ファイルアップロード実行
	 */
	function multiple_piece()
	{
		// if handle is posted, do upload
		if ($this->input->post('upload_handle'))
		{
			if (is_uploaded_file($_FILES['upload_data']['tmp_name']) === TRUE)
			{
				// upload target directory_id
				$did = $this->input->post('upload_path') ? (int)$this->input->post('upload_path') : 1;

				$up = $this->_do_upload($did);

				if ($up)
				{
					// uploaded file extension has icon?
					if (has_icon_ext($up['extension']))
					{
						$up['is_icon'] = true;
					}
					else
					{
						$up['is_icon'] = false;
					}
					$data->complete = 1; // success
					$data->data = json_encode($up);
				}
				else
				{
					$data->complete = 0; // error
				}
			}
			else
			{
				$data->complete = 2; // no upload
			}
		}
		else
		{
			$data = new stdClass();
		}
		$this->load->view('dashboard/files/multiple_piece', $data);
	}

	/**
	 * iframe応答アップロード
	 */
	function ajax_upload()
	{
		$did = $this->input->post('upload_path') ? (int)$this->input->post('upload_path') : 1;

		$up = $this->_do_upload($did);

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
			$error = $this->upload->display_errors('', '\n');
			// Does Image_lib library loaded?
			if ( isset($this->image_lib) )
			{
				$error .= $this->image_lib->display_errors('', '\n');
			}
			$data->error = $error;
			$this->load->view('dashboard/files/upload', $data);
		}
	}

	/**
	 * 既存ファイル差し替え対象のファイルアップロード
	 */
	function ajax_replace_upload()
	{
		// temporary overwrite upload dir for tempoarary
		$this->upload_dir = 'files/temporary/';
		$this->upload_thumbnail_dir = 'files/temporary/thumbnail/';

		// if temp filename is posted, try delete
		if ($this->input->post('temp_file_name'))
		{
			$temp = $this->input->post('temp_file_name');
			if (file_exists($this->upload_dir . $temp))
			{
				unlink($this->upload_dir . $temp);
			}
			if (file_exists($this->upload_thumbnail_dir . $temp))
			{
				unlink($this->upload_thumbnail_dir . $temp);
			}
		}

		// replaced file is temporary. not save to DB
		$up = $this->_do_upload(0, FALSE);

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

			$up['old_file_id'] = (int)$this->input->post('old_file_id');

			$data->file = $up;

		}
		$data->file_id = $up['old_file_id'];

		$this->load->view('dashboard/files/replace_upload', $data);
	}

	/**
	 * ファイル差し替え実行
	 * @param string $token
	 */
	function do_replace_file($token = FALSE)
	{
		if (!$token ||  $this->session->userdata('sz_token') !== $token)
		{
			exit('access_denied');
		}

		foreach (array('crypt_name', 'file_name', 'size', 'width', 'height', 'extension', 'added_date') as $v)
		{
			$data[$v] = $this->input->post($v, TRUE);
		}

		$replace_target_id = (int)$this->input->post('old_file_id');

		$ret = $this->file_model->do_replace_file($replace_target_id, $data);

		// if replace is succeed, move file and delelte old file.
		if (!$ret)
		{
			exit('replace_error');
		}

		// move new file to files/
		if (file_exists('./files/temporary/' . $data['crypt_name'] . '.' . $data['extension']))
		{
			// rename?
			$mv1 = @copy('./files/temporary/' . $data['crypt_name'] . '.' . $data['extension'], './files/' . $data['crypt_name'] . '.' . $data['extension']); // original file
			unlink('./files/temporary/' . $data['crypt_name'] . '.' . $data['extension']);
		}
		// thumbnail file create image file only.
		if (file_exists('./files/temporary/thumbnail/' . $data['crypt_name'] . '.' . $data['extension']))
		{
			// rename?
			$mv2 = @copy('./files/temporary/thumbnail/' . $data['crypt_name'] . '.' . $data['extension'], './files/thumbnail/' . $data['crypt_name'] . '.' . $data['extension']); // tuhmbnail
			unlink('./files/temporary/thumbnail/' . $data['crypt_name'] . '.' . $data['extension']);
		}
		else
		{
			$mv2 = TRUE;
		}


		if ($mv1 && $mv2)
		{
			// delete original files
			$del1 = @unlink('./files/' . $ret->crypt_name . '.' . $ret->extension);
			if (file_exists('./files/thumbnail/' . $ret->crypt_name . '.' . $ret->extension))
			{
				$del2 = @unlink('./files/thumbnail/' . $ret->crypt_name . '.' . $ret->extension);
			}
			else
			{
				$del2 = TRUE;
			}
		}
		else
		{
			exit('rename_error');
		}

		if ($del1 && $del2)
		{
			echo 'complete';
		}
		else
		{
			echo 'delete_error';
		}
		exit;
	}

	/**
	 * 差し替えファイルをアップロードしたが、差し替えを実行しなかったときの一時ファイル削除
	 */
	function delete_temp_file()
	{
		// try delete temporary file
		$file_name = $this->input->post('file_name');
		$file_ext =  $this->input->post('ext');

		// traversal check
		if ( ! preg_match('/^[a-zA-Z0-9]+$/', $file_name) || ! preg_match('/^[a-zA-Z0-9]+$/'))
		{
			exit('traversal error!');
		}

		// try delete
		if (file_exists('./files/temporary/' . $file_name . '.' . $file_ext))
		{
			unlink('./files/temporary/' . $file_name . '.' . $file_ext);
		}
		if (file_exists('./files/temporary/thumbnail/' . $file_name . '.' . $file_ext))
		{
			unlink('./files/temporary/thumbnail/' . $file_name . '.' . $file_ext);
		}

	}

	/**
	 * 	Ajax応答：ファイル詳細表示
	 * @param int $fid
	 * @param string $token
	 */
	function ajax_file_view($fid, $token)
	{
		if (!$fid ||  $this->session->userdata('sz_token') !== $token)
		{
			exit('access_denied');
		}

		$data->file = $this->file_model->get_file_data((int)$fid);

		$dl_token = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_dl_token', $dl_token);
		$data->ticket = $dl_token;
		$data->fid = (int)$fid;

		$this->load->view('dashboard/files/ajax_file_view', $data);
	}

	/**
	 * ファイルアップロード
	 * @access private
	 * @param int $did
	 * @param bool $save_db
	 */
	function _do_upload($did, $save_db = TRUE, $field = 'upload_data')
	{
		// 2010/05/03 modified
		// use CodeIgniter builtin library
		$this->load->library('upload');

		$data = array();

		// upload config
		$config = array(
			'upload_path'			=> $this->upload_dir,
			'allowed_types'		=> $this->allowed_types,
			'overwrite'			=> FALSE,
			'encrypt_name'		=> TRUE,
			'remove_spaces'		=> TRUE
		);
		
		// set max filesize of memory_limit
		$ini_max = ini_get('memory_limit');
		if ( (int)$ini_max > 0 )
		{
			$mem_limit = (int)substr($ini_max, -1, 1) * 1024; // KB
			$config['max_size'] = $mem_limit;
		}

		$this->upload->initialize($config);

		// try upload
		$result = $this->upload->do_upload($field);

		if (!$result)
		{
			return FALSE;
		}

		$ret = $this->upload->data();

		// get data for DB insert
		$data['file_name']		= substr($ret['orig_name'], 0, strrpos($ret['orig_name'], '.'));
		$data['crypt_name']		= $ret['raw_name'];
		$data['extension']		= substr($ret['file_ext'], 1);
		$data['size']				= $ret['file_size'];
		$data['width']			= ($ret['is_image'] == 1) ? (int)$ret['image_width'] : 0;
		$data['height']			= ($ret['is_image'] == 1) ? (int)$ret['image_height'] : 0;
		$data['added_date']		= db_datetime();
		$data['directories_id']	= $did;
		$data['file_group'] 		= '';

		// create thumbnail if upload file is image
		if ($ret['is_image'] > 0)
		{
			// Does uploaded image width/height over 60 pixel?
			if ($ret['image_width'] > 60 || $ret['image_height'] > 60)
			{
				$this->load->library('image_lib');
				// load image_lib library
				$conf = array(
					'source_image'		=> $ret['full_path'],
					'create_thumb'		=> TRUE,
					'new_image'			=> $this->upload_thumbnail_dir,
					'thumb_marker'		=> '',
					'width'				=> 60,
					'height'				=> 60,
					'maintain_ratio'		=> TRUE
				);
				$this->image_lib->initialize($conf);

				if (! $this->image_lib->resize())
				{
					echo $conf['source_image'];
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

		if ($save_db === TRUE)
		{
			$res = $this->file_model->insert_new_image($data);
		}
		else
		{
			$res = TRUE;
		}

		if ($res)
		{
			$data['file_id'] = $res;
			$data['thumbnail_path'] = file_link() . 'files/thumbnail/' . $data['crypt_name'] . '.' . $data['extension'];

			return $data;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * トークン生成
	 */
	function _set_ticket()
	{
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$this->session->set_userdata('sz_file_token', $ticket);
		return $ticket;
	}

	/**
	 * トークンチェック
	 */
	function _check_ticket()
	{
		$ticket = $this->input->post('sz_file_token');
		if (!$ticket || $ticket !== $this->session->userdata('sz_file_token'))
		{
			exit('不正な操作です。');
		}
	}

	/**
	 * Ajax応答：ファイル削除
	 * @param $id
	 * @param $token
	 */
	function delete_file($id, $token)
	{
		if (!$id || $token !== $this->session->userdata('sz_token'))
		{
			exit('access denied');
		}

		$ret = $this->file_model->delete_file_one($id);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * ポップアップ画面からのファイルダウンロード
	 * @param unknown_type $fid
	 * @param unknown_type $token
	 */
	function file_download_from_popup($fid, $token = FALSE)
	{
		if (!$token || $this->session->userdata('sz_token') !== $token)
		{
			exit('access_denied.');
		}
		$file = $this->file_model->get_file_data((int)$fid);
		$file_path = FCPATH . 'files/' . $file->crypt_name . '.' . $file->extension;
		$file_name = $file->file_name . '.' . $file->extension;
		$data = file_get_contents($file_path);

		$this->load->helper('download');
		force_download($file_name, $data);
//		force_download_reg($file_name, $file_path);
	}

	/**
	 * ポップアップビューからのファイルダウンロード
	 */
	function file_download_from_view()
	{
		$fid = (int)$this->input->post('file_id');
		$token = $this->input->post('ticket');

		if (!$token || $this->session->userdata('sz_dl_token') !== $token)
		{
			exit('access_denied.');
		}
		$file = $this->file_model->get_file_data((int)$fid);
		$file_path = FCPATH . 'files/' . $file->crypt_name . '.' . $file->extension;
		$file_name = $file->file_name . '.' . $file->extension;
		$data = file_get_contents($file_path);

		$this->load->helper('download');
		force_download($file_name, $data);
//		force_download_reg($file_name, $file_path);
	}

	/**
	 * 選択したファイル群をzipにまとめてダウンロード
	 */
	function multiple_download()
	{
		$token = $this->input->post('sz_file_token');
		if (!$token || $token != $this->session->userdata('sz_file_token'))
		{
			exit('access_denied.');
		}

		$file_ids = $this->input->post('archive_files');
		$dir_ids = $this->input->post('archive_directories');

		if ($file_ids)
		{
			$fids = explode(':', $file_ids);
			if ( ! $fids)
			{
				$fids = array($file_ids);
			}
		}
		else
		{
			$fids = array();
		}

		if ($dir_ids)
		{
			$dids = explode(':', $dir_ids);
			if ( ! $dids)
			{
				$dids = array($dir_ids);
			}

		}
		else
		{
			$dids = array();
		}
		$total_files = $this->file_model->merge_file_ids_array_for_zip($fids, $dids);

		if (count($total_files) === 0)
		{
			return;
		}
		// load zip library
		$this->load->library('zip');
		foreach ($total_files as $files)
		{
			if ( ! is_array($files))
			{
				continue;
			}
			$this->zip->read_file($files[0], FALSE, $files[1]);
		}
		$this->zip->download('files' . date('YmdHis', time()) . '.zip');
	}

	/*
	 * ===========================================================================
	 * ディレクトリ操作系AJax応答メソッド
	 * ===========================================================================
	 */

	/**
	 * 対象のディレクトリに属するファイル群を取得
	 * @param $token
	 */
	function ajax_get_dir_files($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$did = (int)$this->input->post('did');

		$data->dirs = $this->file_model->get_directories($did);
		$data->files = $this->file_model->get_files_from_directory($did);

		$ret = array(
			'response' => $this->load->view('parts/files/dirs', $data, TRUE),
			'did'		=> $did
		);

		echo json_encode($ret);
	}

	/**
	 * 新規ディレクトリ追加
	 * @param $token
	 */
	function ajax_add_directory($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$dir_name = $this->input->post('new_path');
		$current = $this->input->post('target');

		$ret = $this->file_model->add_new_directory($dir_name, $current);
		echo ($ret) ? $ret : 'error';
		exit;
	}

	/**
	 * ディレクトリ複製
	 * @param $token
	 */
	function ajax_clone_directory($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$dir_name = $this->input->post('new_name');
		$current = (int)$this->input->post('target');

		$ret = $this->file_model->clone_directory($dir_name, $current);
		echo ($ret) ? $ret : 'error';
		exit;
	}

	/**
	 * ディレクトリをディレクトリに移動
	 * @param $token
	 */
	function move_dir_to_dir($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$from = (int)$this->input->post('from');
		$to = (int)$this->input->post('to');

		$ret = $this->file_model->move_directory_to_directory($from, $to);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * ファイルをディレクトリに移動
	 * @param $token
	 */
	function move_file_to_dir($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$from = (int)$this->input->post('from');
		$to = (int)$this->input->post('to');

		$ret = $this->file_model->move_file_to_directory($from, $to);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * ファイル、またはディレクトリを削除
	 * @param $token
	 */
	function delete_file_or_dir($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$del = (int)$this->input->post('del_id');
		$mode = $this->input->post('mode');

		if ($mode === 'dir')
		{
			$ret = $this->file_model->delete_dir($del);
		}
		else if ($mode === 'file')
		{
			$ret = $this->file_model->delete_file_one($del);
		}

		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * ディレクトリ名変更
	 * @param $token
	 */
	function update_dirname($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$dir_id = (int)$this->input->post('target');
		$new_name = $this->input->post('new_name');

		$ret = $this->file_model->update_directory_name($dir_id, $new_name);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * 対象ディレクトリのパーミッション取得
	 * @param $did
	 * @param $token
	 */
	function show_dir_permission($did, $token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$this->load->model('user_model');

		$data->users = $this->user_model->get_user_list();
		$data->permission = $this->file_model->get_dir_data_one((int)$did);
		$data->did = $did;

		$this->load->view('dashboard/files/dir_permissions', $data);
	}

	/**
	 * ディレクトリパーミッション変更
	 * @param $token
	 */
	function update_permission($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$did = (int)$this->input->post('did');
		$recursive = $this->input->post('recursive');
		$is_recursive = reset($recursive);

		$per = $this->input->post('dir_permission');
		if (!$per)
		{
			$per = array(1); // master user is always allowed.
		}
		else if (!in_array(1, $per))
		{
			array_unshift($per, 1);
		}

		//array_unshift($per, 1);

		$ret = $this->file_model->update_directory_permission($did, $per, $is_recursive);
		echo ($ret) ? 'complete' : 'error';
		exit;
	}

	/**
	 * ファイル差し替え
	 * @param $fid
	 * @param $token
	 */
	function replace_file($fid, $token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}

		$data->file = $this->file_model->get_file_data($fid);

		$this->load->view('dashboard/files/replace_view', $data);
	}

	/**
	 * 差し替えファイルアップロードフォーム出力
	 * @param $fid
	 */
	function replace_upload_init($fid)
	{
		$this->load->view('dashboard/files/replace_upload', array('file_id' => $fid));
	}
	
	function rename_file_init($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
		
		$fid  = (int)$this->input->post('val');
		$file = $this->file_model->get_file_data($fid);
		
		if ( ! $file ) {
			exit('file not found');
		}
		
		$this->load->view('dashboard/files/rename_init', array('file' => $file));
	}
	
	function do_rename_file($token = FALSE)
	{
		if (!$token || $token != $this->session->userdata('sz_token'))
		{
			echo 'access_denied';
			return;
		}
		$fid = (int)$this->input->post('file_id');
		$newname = $this->input->post('new_filename');
		$file = $this->file_model->get_file_data($fid);
		
		if ( ! $file )
		{
			echo '更新対象のファイルが見つかりませんでした。';
			return;
		}
		
		// simple validation
		if ( ! preg_match('/\A[a-zA-Z0-9_\-+\.]+\Z/u', $newname) )
		{
			echo 'ファイル名に不正な文字が含まれています。';
			return;
		}
		
		$ret = $this->file_model->update_filename($fid, $newname);
		
		if ( ! $ret )
		{
			echo 'error';
			return;
		}
		
		echo $newname . '.' . $file->extension;
		
		
	}
}