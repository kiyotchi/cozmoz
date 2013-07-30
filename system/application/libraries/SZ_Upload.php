<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ====================================================
 * Extended CodeIgniter builtin Upload Class
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  ====================================================
*/

class SZ_Upload extends CI_Upload
{
	function __construct($props = array())
	{
		parent::CI_Upload($props);
	}
	
	/**
	 * Extract the file extension ( Override )
	 * @note seezoo treats extension always lowercase!
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function get_extension($filename)
	{
		$x = explode('.', $filename);
		return '.'.strtolower(end($x));
	}
}