<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ====================================================
 * Extended CodeIgniter builtin Hooks Class
 *
 *@package Seezoo Core
 *@author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ====================================================
 */

class SZ_Hooks extends CI_Hooks
{
	function __construct()
	{
		parent::CI_Hooks();
	}
	
	public function regist_hook($point, $exec_func)
	{
		if ( isset($this->hooks[$point]) )
		{
			$this->hooks[$point][] = $exec_func;
		}
		else 
		{
			$this->hooks[$point] = array($exec_func);
		}
	}
	
	public function call_action($point = '')
	{
		if ( ! $this->enabled OR ! isset($this->hooks[$point]) || ! is_array($this->hooks[$point]) )
		{
			return FALSE;
		}

		foreach ($this->hooks[$point] as $val)
		{
			$this->_run_cms_hook($val);
		}

		return TRUE;
	}
	
	private function _run_cms_hook($data)
	{
		// -----------------------------------
		// Safety - Prevents run-away loops
		// -----------------------------------

		// If the script being called happens to have the same
		// hook call within it a loop can happen

		if ($this->in_progress == TRUE)
		{
			return;
		}
		
		$this->in_progress = TRUE;
		
		if ( is_array($data) )
		{
			if ( is_object($data[0]) )
			{
				if ( method_exists($data[0], $data[1]) )
				{
					call_user_func($data);
				}
			}
			else if ( is_string($data[0]) )
			{
				if ( class_exists($data[0]) && method_exists($data[0], $data[1]) )
				{
					call_user_func($data);
				}
			}
		}
		else 
		{
			if ( function_exists($data) )
			{
				call_user_func($data);
			}
		}

		$this->in_progress = FALSE;
		return TRUE;
	}
}