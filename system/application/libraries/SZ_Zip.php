<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 *Extended CodeIgniter builtin Zipr Class
 *
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * @additional works:
 *   read_file method can set archived file name
 *  =========================================================
 */
class SZ_Zip extends CI_Zip
{
	function SZ_Zip()
	{
		parent::CI_Zip();
	}

	/**
	 * overrride method read_file
	 */
	function read_file($path, $preserve_filepath = FALSE, $add_name = '')
	{
		if ( ! file_exists($path))
		{
			return FALSE;
		}

		if ($add_name != '' && $preserve_filepath === FALSE)
		{
			// check filename format
			if ( !preg_match('/.+\.[0-9a-zA-Z]+$/', $add_name))
			{
				return FALSE;
			}
			// get extension
			$name_ext = substr($add_name, strrpos($add_name, '.'));
			$data_ext = substr($path, strrpos($path, '.'));

			if ($name_ext != $data_ext)
			{
				return FALSE;
			}

			$filename_overwrite = TRUE;
		}
		else
		{
			$filename_overwrite = FALSE;
		}

		if (FALSE !== ($data = file_get_contents($path)))
		{
			if ($filename_overwrite)
			{
				$name = $add_name;
			}
			else
			{
				$name = str_replace("\\", "/", $path);
			}

			if ($preserve_filepath === FALSE)
			{
				$name = preg_replace("|.*/(.+)|", "\\1", $name);
			}

			$this->add_data($name, $data);
			return TRUE;
		}
		return FALSE;
	}
}
