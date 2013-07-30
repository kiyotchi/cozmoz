<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ====================================================
 * Seezoo Extended CodeIgniter builtin Image_lib Class
 *
 * @note 拡張したメソッド
 *    image_proccess_gd - GIFとPNGはアルファチャンネルを保持するようにした
 *    multiple_process - 一括して画像変換を実行できるようにした
 *        (ex: resize->crop->rotate)
 *    flip_h - 単純に上下反転するメソッド
 *    flip_v - 単純に左右反転するメソッド
 *  @package Seezoo Core or CodeIgniter Extensions
 *  @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  ====================================================
*/
class SZ_Image_lib extends CI_Image_lib
{
	private $temp_img;
	public $slash_w = 0; // pre crop width for multiple process
	public $slash_h = 0; // pre crop height for multiple process
	
	// convert flag
	public $convert = FALSE;
	// keep alpha-chanel
	public $keep_alpha = TRUE;

	function SZ_Image_lib($props = array())
	{
		parent::CI_Image_lib($props);
	}

	/**
	 * Image Resize
	 *
	 * This is a wrapper function that chooses the proper
	 * resize function based on the protocol specified
	 *
	 * @access	public
	 * @return	bool
	 *  Modified by Yoshiaki Sugimoto
	 * @param bool $is_return
	 *
	 */
	function resize($is_return  = FALSE)
	{
		$protocol = 'image_process_'.$this->image_library;

		if (preg_match('/gd2$/i', $protocol))
		{
			$protocol = 'image_process_gd';
		}

		return $this->$protocol('resize', $is_return);
	}

	/**
	 * Image Crop
	 *
	 * This is a wrapper function that chooses the proper
	 * cropping function based on the protocol specified
	 *
	 * @access	public
	 * @return	bool
	 * Modified by Yoshiaki Sugimoto
	 * @param bool $is_return
	 *	 * Modified by Yoshiaki Sugimoto
	 * @param bool $is_return

	 */
	function crop($is_return  = FALSE)
	{
		$protocol = 'image_process_'.$this->image_library;

		if (preg_match('/gd2$/i', $protocol))
		{
			$protocol = 'image_process_gd';
		}

		return $this->$protocol('crop', $is_return);
	}

	/**
	 * Image Rotate
	 *
	 * This is a wrapper function that chooses the proper
	 * rotation function based on the protocol specified
	 *
	 * @access	public
	 * @return	bool
	 * Modified by Yoshiaki Sugimoto
	 * @param bool $is_return
	 *
	 */
	function rotate($is_return  = FALSE)
	{
		// Allowed rotation values
		$degs = array(90, 180, 270, 'vrt', 'hor');

		if ($this->rotation_angle == '' OR ! in_array($this->rotation_angle, $degs))
		{
			$this->set_error('imglib_rotation_angle_required');
			return FALSE;
		}

		// Reassign the width and height
		if ($this->rotation_angle == 90 OR $this->rotation_angle == 270)
		{
			$this->width	= $this->orig_height;
			$this->height	= $this->orig_width;
		}
		else
		{
			$this->width	= $this->orig_width;
			$this->height	= $this->orig_height;
		}


		// Choose resizing function
		if ($this->image_library == 'imagemagick' OR $this->image_library == 'netpbm')
		{
			$protocol = 'image_process_'.$this->image_library;

			return $this->$protocol('rotate');
		}

		if ($this->rotation_angle == 'hor' OR $this->rotation_angle == 'vrt')
		{
			return $this->image_mirror_gd();
		}
		else
		{
			return $this->image_rotate_gd($is_return);
		}
	}

	// additional methods flip_h and flip_v
	function flip_h($is_return = FALSE)
	{
		$height = $this->orig_height;
		$width = $this->orig_width;

		for ($i = 0; $i < $height; $i++)
		{
			$left  = 0;
			$right = $width-1;

			while ($left < $right)
			{
				$cl = imagecolorat($this->temp_img, $left, $i);
				$cr = imagecolorat($this->temp_img, $right, $i);

				imagesetpixel($this->temp_img, $left, $i, $cr);
				imagesetpixel($this->temp_img, $right, $i, $cl);

				$left++;
				$right--;
			}
		}

	}

	function flip_v($is_return = FALSE)
	{
		$height = $this->orig_height;
		$width = $this->orig_width;

		for ($i = 0; $i < $width; $i++)
		{
			$top = 0;
			$bot = $height-1;

			while ($top < $bot)
			{
				$ct = imagecolorat($this->temp_img, $i, $top);
				$cb = imagecolorat($this->temp_img, $i, $bot);

				imagesetpixel($this->temp_img, $i, $top, $cb);
				imagesetpixel($this->temp_img, $i, $bot, $ct);

				$top++;
				$bot--;
			}
		}
	}

	/**
	 * Image Rotate Using GD
	 *
	 * @access	public
	 * @return	bool
	 * Modified by Yoshiaki Sugimoto
	 * @param bool $is_return
	 */
	function image_rotate_gd($is_return = FALSE)
	{
		// Is Image Rotation Supported?
		// this function is only supported as of PHP 4.3
		if ( ! function_exists('imagerotate'))
		{
			$this->set_error('imglib_rotate_unsupported');
			return FALSE;
		}

		//  Create the image handle if not exsits
		if (is_resource($this->temp_img))
		{
			$src_img = $this->temp_img;
		}
		else
		{
			if ( ! ($src_img = $this->image_create_gd()))
			{
				return FALSE;
			}
		}

		// Set the background color
		// This won't work with transparent PNG files so we are
		// going to have to figure out how to determine the color
		// of the alpha channel in a future release.
		$white	= imagecolorallocate($src_img, 255, 255, 255);

		//  Rotate it!
		$dst_img = imagerotate($src_img, $this->rotation_angle, $white);

		if ($is_return)
		{
			$tmp = $this->orig_height;
			$this->orig_height = $this->orig_width;
			$this->orig_width = $tmp;
			imagedestroy($src_img);
			$this->temp_img = $dst_img;
			return;
		}

		//  Save the Image
		if ($this->dynamic_output == TRUE)
		{
			$this->image_display_gd($dst_img);
		}
		else
		{
			// Or save it
			if ( ! $this->image_save_gd($dst_img))
			{
				return FALSE;
			}
		}

		//  Kill the file handles
		imagedestroy($dst_img);
		imagedestroy($src_img);

		// Set the file to 777
		@chmod($this->full_dst_path, DIR_WRITE_MODE);

		return true;
	}

	/**
	 * Image Process Using GD/GD2
	 *
	 * This function will resize or crop
	 * overwridde parent function image_process_gd
	 * to keep alpha channel for gif or png
	 * modified by Yoshiaki Sugimoto @ 2010/05/12
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function image_process_gd($action = 'resize', $is_returnable_resouce = FALSE)
	{
		$v2_override = FALSE;

		// If the target width/height match the source, AND if the new file name is not equal to the old file name
		// we'll simply make a copy of the original with the new name... assuming dynamic rendering is off.
		if ($this->dynamic_output === FALSE)
		{
			if ($this->orig_width == $this->width AND $this->orig_height == $this->height AND $is_returnable_resouce === FALSE)
			{
				if ($this->source_image != $this->new_image)
				{
					if (@copy($this->full_src_path, $this->full_dst_path))
					{
						@chmod($this->full_dst_path, DIR_WRITE_MODE);
					}
				}
				return TRUE;
			}
		}

		// Let's set up our values based on the action
		if ($action == 'crop')
		{
			//  Reassign the source width/height if cropping
			//  Modified if precrop, set slash_w/h
			$this->orig_width  = $this->width;
			$this->orig_height = $this->height;

			// GD 2.0 has a cropping bug so we'll test for it
			if ($this->gd_version() !== FALSE)
			{
				$gd_version = str_replace('0', '', $this->gd_version());
				$v2_override = ($gd_version == 2) ? TRUE : FALSE;
			}
		}
		else
		{
			// If resizing the x/y axis must be zero
			$this->x_axis = 0;
			$this->y_axis = 0;
		}

		//  Create the image handle if temp resource is not exists
		if (is_resource($this->temp_img))
		{
			$src_img = $this->temp_img;
		}
		else
		{
			if ( ! ($src_img = $this->image_create_gd()))
			{
				return FALSE;
			}
		}

		//  Create The Image
		if ($this->image_library == 'gd2' AND function_exists('imagecreatetruecolor'))
		{
			$create	= 'imagecreatetruecolor';
			$copy	= 'imagecopyresampled';
		}
		else
		{
			$create	= 'imagecreate';
			$copy	= 'imagecopyresized';
		}

		$dst_img = $create($this->width, $this->height);

		/**
		 * Modified:
		 * if mime-type equal GIF or PNG, keep alpha chanel
		 */
		if ($this->image_type === 1 || $this->image_type === 3)
		{
			$trans = imagecolortransparent($src_img);

			if ($trans >= 0)
			{
				if ( $trans < 255 )
				{
					$trans_color = imagecolorsforindex($src_img, $trans);
	
					$trans = imagecolorallocate($dst_img, $trans_color['red'], $trans_color['green'], $trans_color['blue']);
	
					imagefill($dst_img, 0, 0, $trans);
	
					imagecolortransparent($dst_img, $trans);
				}
				else
				{
					// jpg image backgroundcolor set while
					$color = imagecolorallocate($dst_img, 255, 255, 255);

					imagefill($dst_img, 0, 0, $color);
				}
			}
			else if ($this->image_type === 3)
			{
				imagealphablending($dst_img, FALSE);

				$color = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);

				imagefill($dst_img, 0, 0, $color);

				imagesavealpha($dst_img, TRUE);
			}
		}
		else // case jpeg
		{
			// jpg image backgroundcolor set while
			$color = imagecolorallocate($dst_img, 255, 255, 255);

			imagefill($dst_img, 0, 0, $color);
		}

		$copy($dst_img, $src_img, 0, 0, $this->x_axis, $this->y_axis, $this->width, $this->height, $this->orig_width, $this->orig_height);

		if ($is_returnable_resouce)
		{
			return $dst_img;
		}

		//  Show the image
		if ($this->dynamic_output == TRUE)
		{
			$this->image_display_gd($dst_img);
		}
		else
		{
			// Or save it
			if ( ! $this->image_save_gd($dst_img))
			{
				return FALSE;
			}
		}

		//  Kill the file handles
		imagedestroy($dst_img);
		imagedestroy($src_img);

		// Set the file to 777
		@chmod($this->full_dst_path, DIR_WRITE_MODE);

		return TRUE;
	}

	/**
	 * multiple_process
	 * multiple image_edit
	 * do resize and crop and rotate in this method
	 * @author Yoshiaki Sugimoto
	 * @param array $actions like:
	 *
	 *		$key  => array $action_values
	 *
	 * $key is 'resize' or 'crop' or 'rotate' or 'flip_v/h' only
	 * $action_values must be an array and some parameters
	 */
	function multiple_process($actions = array('resize'))
	{

		if (!is_array($actions))
		{
			return FALSE;
		}

		// create temporary parametrs
		$tmps = new stdClass();

		//  Create the image handle
		if ( ! ($src_img = $this->image_create_gd()))
		{
			return FALSE;
		}
		$this->temp_img = $src_img;

		// first, crop src image
		if (in_array('crop', $actions))
		{
			// temp stack slashes
			$tmps->width = $this->width;
			$tmps->height = $this->height;
			$this->width = $this->slash_w;
			$this->height = $this->slash_h;
			$res_img = $this->crop(TRUE);
			if (!is_resource($res_img))
			{
				$this->set_error('ファイルの切り抜きに失敗しました。');
				return FALSE;
			}
			else
			{
				$this->_transfer($res_img);
				$this->width = $tmps->width;
				$this->height = $tmps->height;
				$this->orig_width = $this->slash_w;
				$this->orig_height = $this->slash_h;
			}
		}

		// second, resize src_image
		if (in_array('resize', $actions))
		{
			$res_img = $this->resize(TRUE);
			if (!is_resource($res_img))
			{
				$this->set_error('ファイルのリサイズに失敗しました。');
				return FALSE;
			}
			else
			{
				$this->_transfer($res_img);
				$this->orig_width = $this->width;
				$this->orig_height = $this->height;
			}
		}

		// third, rotate src image
		if (in_array('rotate', $actions))
		{
			$this->rotate(TRUE);
		}

		// finally, flip vertical or flip horizontal
		// is_flip horizontal?
		if (in_array('flip_h', $actions))
		{
			$this->flip_h(TRUE);
		}
		// is flip_vertical?
		if (in_array('flip_v', $actions))
		{
			$this->flip_v(TRUE);
		}

		//  Show the image
		if ($this->dynamic_output == TRUE)
		{
			$this->image_display_gd($this->temp_img);
		}
		else
		{
			// Or save it
			if ( ! $this->image_save_gd($this->temp_img))
			{
				return FALSE;
			}
		}

		//  Kill the file handles
		imagedestroy($this->temp_img);

		// Set the file to 777
		@chmod($this->full_dst_path, DIR_WRITE_MODE);

		return TRUE;
	}

	function _transfer($dst)
	{
		$this->temp_img =& $dst;
	}

	// sanitize filename like Upload Class
	function clean_file_name($filename)
	{
		$bad = array(
			"<!--",
			"-->",
			"'",
			"<",
			">",
			'"',
			'&',
			'$',
			'=',
			';',
			'?',
			'/',
			"%20",
			"%22",
			"%3c",		// <
			"%253c", 	// <
			"%3e", 		// >
			"%0e", 		// >
			"%28", 		// (
			"%29", 		// )
			"%2528", 	// (
			"%26", 		// &
			"%24", 		// $
			"%3f", 		// ?
			"%3b", 		// ;
		"%3d"		// =
		);

		$filename = str_replace($bad, '', $filename);

		return stripslashes($filename);
	}
	
	/** ======================== convert methods =========================== **/
	
	function convert()
	{
		if (!$this->convert)
		{
			$this->set_error('nothing To Do.');
			return FALSE;
		}
		$src_img = $this->image_create_gd();
		
		// detect imagecopy, imagecreate function
			if ($this->image_library == 'gd2' AND function_exists('imagecreatetruecolor'))
		{
			$create	= 'imagecreatetruecolor';
			$copy	= 'imagecopyresampled';
		}
		else
		{
			$create	= 'imagecreate';
			$copy	= 'imagecopyresized';
		}
		
		$dst_img = $create($this->width, $this->height);
		$full_path = $this->full_dst_path;
		
		// convert case
		if ($this->convert === 'png')
		{
			if ($this->keep_alpha)
			{
				$trans = imagecolortransparent($src_img);

				if ($trans >= 0)
				{
					$trans_color = imagecolorsforindex($src_img, $trans);
					
					$trans = imagecolorallocate($dst_img, $trans_color['red'], $trans_color['green'], $trans_color['blue']);
					
					imagefill($dst_img, 0, 0, $trans);
					
					imagecolortransparent($dst_img, $trans);
				}
				else
				{
					imagealphablending($dst_img, FALSE);
					
					$color = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
					
					imagefill($dst_img, 0, 0, $color);
					
					imagesavealpha($dst_img, TRUE);
				}
			}
			$this->image_type = 3;
			$full_path = substr($full_path, 0, strrpos($full_path, '.')) . '.png';
		}
		else if ($this->convert === 'jpg' || $this->convert === 'jpeg')
		{
			// jpg image backgroundcolor set white
			$color = imagecolorallocate($dst_img, 255, 255, 255);

			imagefill($dst_img, 0, 0, $color);
//			
			$this->image_type = 2;
			$full_path = substr($full_path, 0, strrpos($full_path, '.')) . '.jpg';
		}
		else if ($this->convert === 'gif')
		{
			//imagetruecolortopalette($src_img, 1, 256);
			
			$color = imagecolorallocate($dst_img, 255, 255, 255);
			imagefill($dst_img, 0, 0, $color);
			
//			if ($this->keep_alpha === TRUE)
//			{
//				$trans = imagecolortransparent($src_img);
//				
//				if ($trans >= 0)
//				{
//					$trans_color = imagecolorsforindex($src_img, $trans);
//					
//					$trans = imagecolorallocatealpha($dst_img, $trans_color['red'], $trans_color['green'], $trans_color['blue'], $trans['alpha']);
//					
//					imagefill($dst_img, 0, 0, $trans);
//					
//					imagecolortransparent($dst_img, $trans);
//				}
//				else if ($this->image_type === 3)
//				{
//					imagealphablending($dst_img, FALSE);
//					
//					$color = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
//					
//					imagefill($dst_img, 0, 0, $color);
//					
//					imagesavealpha($dst_img, TRUE);
//				}
//			}
			$this->image_type = 1;
			$full_path = substr($full_path, 0, strrpos($full_path, '.')) . '.gif';
		}
		
		$this->full_dst_path = $full_path;
		
		$copy($dst_img, $src_img, 0, 0, 0, 0, $this->width, $this->height, $this->orig_width, $this->orig_height);
		
		if ($this->dynamic_output === TRUE)
		{
			$this->image_display_gd($dst_img);
		}
		else
		{
			$this->image_save_gd($dst_img);
		}
		
		imagedestroy($src_img);
		imagedestroy($dst_img);
		
		return TRUE;
	}
	
	
	function _create_image_resource()
	{
		if ($this->image_type === 1 && function_exists('imagecreatefromgif')) // gif
		{
			return imagecreatefromgif($this->source_image);
		}
		else if ($this->image_type === 2 && unction_exists('imagecreatefromjpeg')) // jpeg
		{
			return imagecreatefromjpeg($this->source_image);
		}
		else if ($this->image_type === 3 && function_exists('imagecreatefrompng')) // png
		{
			return imagecreatefrompng($this->source_image);
		}
		$this->set_error('disable image functions.');
		return FALSE;
	}
}
