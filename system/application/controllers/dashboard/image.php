<?php
/**
 * ===============================================================================
 *
 * Seezoo dashboard 画像編集コントローラ
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * ===============================================================================
 */
class Image extends SZ_Controller
{
	public static $page_title = '画像の編集';
	public static $description = '簡単な画像の加工を行います。';

	protected $user_id;
	protected $file_dir = 'files/';

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		parent::SZ_Controller();
		$this->load->model('file_model');
	}

	// デフォルトメソッド
	function index()
	{
		$this->load->view('dashboard/image/edit');
	}

	function edit($fid = 0)
	{
		if ($fid === 0)
		{
			redirect('dashboard/image');
		}
		$file = $this->file_model->get_file_data($fid);

		// filedata exists?
		if (!$file || !file_exists(make_file_path($file)))
		{
			show_error('編集対象のファイルが見つかりませんでした。');
		}

		if ($file->width == 0 || $file->height == 0 || !is_image(make_file_path($file)))
		{
			show_error('対象ファイルは画像ではありません。');
		}

		$data->file = $file;
		$this->load->view('dashboard/image/edit', $data);

	}

	/**
	 * 画像編集リクエストキャッチ用メソッド
	 */
	function do_edit()
	{
		check_ajax_token($this->input->post('token'));

		$this->_do_edit_image();
		exit;
	}

	/**
	 * 画像編集実行
	 * @access private
	 */
	function _do_edit_image()
	{
		$id = $this->input->post('file_id');
		$file = $this->file_model->get_file_data($id);
		$overwrite = (int)$this->input->post('overwrite');

		// filedata exists?
		if (!$file || !file_exists(make_file_path($file)))
		{
			exit('error : no file exists');
		}

		// file is true image?
		if ($file->width == 0 || $file->height == 0 || !is_image(make_file_path($file))) // swf file has getimagesize() value
		{
			exit('error : file is not image!');
		}


		$filename = make_file_path($file);

		// set image_lib config
		$width	 = (int)$this->input->post('w');
		$height = (int)$this->input->post('h');
		$x_axis = (int)$this->input->post('x');
		$y_axis = (int)$this->input->post('y');
		$sh		 = (int)$this->input->post('sh');
		$sw		 = (int)$this->input->post('sw');
		$flip_h = (int)$this->input->post('flipH');
		$flip_v = (int)$this->input->post('flipV');
		$deg 	 = (int)$this->input->post('deg');
		$new_name = $this->input->post('name');

		// load image_lib library
		$this->load->library('image_lib');

		// sanytize new filename
		if ($overwrite === 0)
		{
			$new_name = $this->image_lib->clean_file_name($new_name);
			$hash_name = md5(uniqid(mt_rand(), TRUE));
			$new_name_body = substr($new_name, 0, strrpos($new_name, '.'));
			$new_name_ext = substr($new_name, strrpos($new_name, '.') + 1);
		}
		else
		{
			$hash_name = $file->crypt_name;
			$new_name_body = $file->file_name;
			$new_name_ext = $file->extension;
		}
		// process method build
		$process = array();

		if ($x_axis > 0 || $y_axis > 0 && ($sh && $sw))
		{
			$process[] = 'crop'; // do crop method
		}
		if ($width > 0 && $height > 0 && $width != $file->width && $height != $file->height)
		{
			$process[] = 'resize'; // do resize method
		}
		if ($flip_h > 0)
		{
			$process[] = 'flip_h'; // do flip_h method
		}
		if ($flip_v > 0)
		{
			$process[] = 'flip_v'; // do flip_v method
		}
		if ($deg != 0)
		{
			$process[] = 'rotate'; // do rotate
		}

//
//		if ($overwrite > 0)
//		{
//			$new_filename = $this->file_dir . $file->file_name . '.' . $file->extension;
//			$thumbnail_path = $this->file_dir . 'thumbnail/' . $file->file_name . '.' . $file->extension;
//		}
//		else
//		{
			$new_filename = $this->file_dir . $hash_name . '.' . $new_name_ext;
			$thumbnail_path = $this->file_dir . 'thumbnail/' . $hash_name . '.' . $new_name_ext;
//	}

		$conf = array(
			'source_image'		=> $filename,
			'create_thumb'		=> FALSE,
			'new_image'			=> $new_filename,
			'thumb_marker'		=> '',
			'width'				=> $width,
			'height'				=> $height,
			'dynamic_output'		=> FALSE, // always FALSE
			'maintain_ratio'		=> FALSE, // always FALSE
			'x_axis'				=> $x_axis,
			'y_axis'				=> $y_axis,
			'rotation_angle'		=> $deg,
			'slash_w'				=> $sw,
			'slash_h'				=> $sh
		);

		$this->image_lib->initialize($conf);

		// create resized image
		$ret = $this->image_lib->multiple_process($process);

		if (!$ret)
		{
			echo $this->image_lib->display_errors();
			exit('error : edit image missed.');
		}

		// create thumbnail for new image
		$this->image_lib->clear();

		// set new config for thubmnail if width/height larger than 60
		if ($width > 60 || $height > 60) {
			$n_conf = array(
				'source_image'		=> $new_filename,
				'create_thumb'		=> TRUE,
				'new_image'			=> $thumbnail_path,
				'thumb_marker'		=> '',
				'width'				=> 60,
				'height'				=> 60,
				'dynamic_output'		=> FALSE, // always FALSE
				'maintain_ratio'		=> TRUE
			);

			$this->image_lib->initialize($n_conf);
			$ret = $this->image_lib->resize();

			if (!$ret)
			{
				exit('error : create thumbnail missed.');
			}
		}
		else
		{
			// width/height less than 60, simple copyfile
			if (!copy($new_filename, $thumbnail_path))
			{
				exit('error : copy file missed');
			}
		}

		// edit image, create thumbnail is success, insert db
		$data = array(
			'file_name'		=> $new_name_body,
			'crypt_name'		=> $hash_name,
			'extension'		=> $new_name_ext,
			'width'			=> $width,
			'height'			=> $height,
			'added_date'		=> db_datetime(),
			'directories_id'	=> $file->directories_id,
			'file_group'		=> ''
		);

		$fsize = filesize($new_filename);

		if ($fsize > 0)
		{
			$data['size'] = round($fsize / 1024, 2);
		}
		else
		{
			$data['size'] = 0;
		}

		if ($overwrite === 0)
		{
			$ret = $this->file_model->insert_new_image($data);
		}
		else
		{
			$ret = $this->file_model->update_image($data, $file->file_id);
		}

		if (!$ret)
		{
			echo 'error : db insert missed.';
		}
		else
		{
			echo 'complete';
		}
	}
}