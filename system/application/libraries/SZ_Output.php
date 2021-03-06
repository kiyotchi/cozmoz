<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 *Extended CodeIgniter builtin Output Class
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 *   we override methods:
 *      _display : Controller's _output hooks returnable this class.
 *                 Because we will cache _output() method result.
 *  =========================================================
 */

class SZ_Output extends CI_Output
{
	function SZ_Output()
	{
		parent::CI_Output();
	}

	/**
	 * Display Output
	 *
	 * All "view" data is automatically put into this variable by the controller class:
	 *
	 * $this->final_output
	 *
	 * This function sends the finalized output data to the browser along
	 * with any server headers and profile data.  It also stops the
	 * benchmark timer so the page rendering speed and memory usage can be shown.
	 * override method of _display
	 * delay cache process after Controller's _output() method called.
	 * and _output() method allows returnable value.
	 *
	 * @access	public
	 * @return	mixed
	 */
	function _display($output = '', $is_not_hook = FALSE)
	{
		// Note:  We use globals because we can't use $CI =& get_instance()
		// since this function is sometimes called by the caching mechanism,
		// which happens before the CI super object is available.
		global $BM, $CFG;

		// Set the output data
		if ($output == '')
		{
			$output =& $this->final_output;
		}

		// Parse out the elapsed time and memory usage,
		// then swap the pseudo-variables with the data
		$elapsed = $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end');
		$output = str_replace('{elapsed_time}', $elapsed, $output);

		$memory = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
		$output = str_replace('{memory_usage}', $memory, $output);

		// Is compression requested?
		if ($CFG->item('compress_output') === TRUE)
		{
			if (extension_loaded('zlib'))
			{
				if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
				{
					ob_start('ob_gzhandler');
				}
			}
		}

		// Are there any server headers to send?
		if (count($this->headers) > 0)
		{
			foreach ($this->headers as $header)
			{
				@header($header[0], $header[1]);
			}
		}

		// Does the get_instance() function exist?
		// If not we know we are dealing with a cache file so we'll
		// simply echo out the data and exit.
		if ( ! function_exists('get_instance'))
		{
			echo $output;
			log_message('debug', "Final output sent to browser");
			log_message('debug', "Total execution time: ".$elapsed);
			return TRUE;
		}

		// Grab the super object.  We'll need it in a moment...
		$CI =& get_instance();

		// Do we need to generate profile data?
		// If so, load the Profile class and run it.
		if ($this->enable_profiler == TRUE)
		{
			$CI->load->library('profiler');

			$profiler_value = $CI->profiler->run();
		}
		else
		{
			$profiler_value = '';
		}

		$is_get_request = ($CI->input->server('REQUEST_METHOD') === 'GET') ? TRUE : FALSE;

		if ( isset($CI->session) && $CI->config->item('is_mobile') )
		{
			// セッションの置換はやらないといけない
			$pair = $CI->session->get_mobile_sess_pair();
			$output = str_replace($pair['grep'], $pair['sed'], $output);
		}

		// Does the controller contain a function named _output()?
		// If so send the output there.  Otherwise, echo it.
		if (method_exists($CI, '_output') && $is_not_hook === FALSE)
		{
			// allows returnable value
			$output = $CI->_output($output);

			// Does output data is returned, do cache
			if ($output)
			{
				if ($this->cache_expiration > 0 && $is_get_request)
				{
					$this->_write_cache($output);
				}
				$this->_final_format($output, $profiler_value);
			}
		}
		else
		{
			if ($this->cache_expiration > 0 && $is_get_request)
			{
				$this->_write_cache($output);
			}
			$this->_final_format($output, $profiler_value);
		}

		log_message('debug', "Final output sent to browser");
		log_message('debug', "Total execution time: ".$elapsed);
	}

	// --------------------------------------------------------------------

	/**
	 * Write a Cache File
	 *
	 * override method of _write_cache
	 * We changes index_page of configure value.
	 * So we use EWRITED_INDEX_PAGE instead ofConfig::item('index_page')
	 * defined by pre_system hook function.
	 * @see system/application/hooks/define_mod_rewrite.php
	 * @access	public
	 * @return	void
	 */

	function _write_cache($output)
	{
		$CI =& get_instance();
		$path = $CI->config->item('cache_path');

		$cache_path = ($path == '') ? BASEPATH.'cache/' : $path;
		$suffix     = (string)$CI->config->item('final_output_mode');

		if ( ! is_dir($cache_path) OR ! is_really_writable($cache_path))
		{
			return;
		}

		// set original cache_name
		$uri         = 'Seezoo_output_cache_' . $suffix . $CI->uri->uri_string();
		$cache_path .= md5($uri);

		if ( ! $fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE))
		{
			log_message('error', "Unable to write cache file: ".$cache_path);
			return;
		}

		$expire = time() + ($this->cache_expiration * 60);

		if (flock($fp, LOCK_EX))
		{
			fwrite($fp, $expire.'TS--->'.$output);
			flock($fp, LOCK_UN);
		}
		else
		{
			log_message('error', "Unable to secure a file lock for file at: ".$cache_path);
			return;
		}
		fclose($fp);
		@chmod($cache_path, DIR_WRITE_MODE);

		log_message('debug', "Cache file written: ".$cache_path);
	}

	/**
	 * Update/serve a cached file
	 *
	 * override method of _write_cache
	 * We changes index_page of configure value.
	 * So we use REWRITED_INDEX_PAGE instead ofConfig::item('index_page')
	 * defined by pre_system hook function.
	 * @see system/application/hooks/sz_pre_process.php
	 * @access	public
	 * @return	void
	 */
	function display_cache(&$CFG, &$URI, $pid = 0)
	{
		$cache_path = ($CFG->item('cache_path') == '')
		               ? BASEPATH.'cache/'
		               : $CFG->item('cache_path');
		$suffix     = (string)$CFG->item('final_output_mode');		

		if ( ! is_dir($cache_path) OR ! is_really_writable($cache_path))
		{
			return FALSE;
		}

		// Build the file path.  The file name is an MD5 hash of the full URI
		// original cache name
		$uri      = 'Seezoo_output_cache_' . $suffix . $URI->uri_string();
		$filepath = $cache_path.md5($uri);

		if ( ! @file_exists($filepath))
		{
			return FALSE;
		}

		if ( ! $fp = @fopen($filepath, FOPEN_READ))
		{
			return FALSE;
		}

		flock($fp, LOCK_SH);

		$cache = '';
		if (filesize($filepath) > 0)
		{
			$cache = fread($fp, filesize($filepath));
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		// Strip out the embedded timestamp
		if ( ! preg_match("/(\d+TS--->)/", $cache, $match))
		{
			return FALSE;
		}

		// Has the file expired? If so we'll delete it.
		if (time() >= trim(str_replace('TS--->', '', $match['1'])))
		{
			@unlink($filepath);
			log_message('debug', "Cache file has expired. File deleted");
			return FALSE;
		}

		// trim a timestamp section
		$cache = str_replace($match['0'], '', $cache);

		// update form block token
		preg_match_all('/<input type="hidden" name="sz_form_ticket_([0-9]+)" value="([0-9a-z]+)" \/>/u', $cache, $matches, PREG_SET_ORDER);
		if ( count($matches) > 0 )
		{
			$CI =& get_instance();
			foreach ( $matches as $match )
			{
				$hash      = sha1(uniqid(mt_rand(), TRUE));
				$new_value = form_hidden('sz_form_ticket_' . $match[1], $hash);
				$cache     = str_replace($match[0], $new_value, $cache);
				$CI->session->set_userdata('sz_form_ticket_' . $match[1], $hash);
			}
		}

		// Display the cache
		$this->_display($cache, TRUE);
		log_message('debug', "Cache file is current. Sending it to browser.");
		return TRUE;
	}

	/**
	 * added function _final_format
	 * stacked profiler value (or empty string) replace and send browser!
	 *
	 * @param string $output
	 * @param string $profiler_value
	 */
	function _final_format($output, $profiler_value = '')
	{
		// first : stacked profiler value (or empty string) replace
		if (preg_match("|</body>.*?</html>|is", $output))
		{
			$output  = preg_replace("|</body>.*?</html>|is", '', $output);
			$output .= $profiler_value;
			$output .= '</body></html>';
		}
		else
		{
			$output .= $profiler_value;
		}

		echo $output;
	}
}
