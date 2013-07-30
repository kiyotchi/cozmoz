<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ===============================================================================
 * 
 * Seezoo Extend Pagination class
 * 
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * Seezoo treats $_GET paramters when segment approche.
 * So, this class "base_url" has query_string,
 * split and repalce pagination uri of "create_links" returns.
 * 
 * ===============================================================================
 */

class SZ_Pagination extends CI_Pagination
{
	public $query_strings      = '';
	public $get_query_page_key = FALSE;
	public $num_pages = 0;
	
	function __construct($params = array())
	{
		parent::CI_Pagination($params);
	}
	
	/**
	 * Generate the pagination links ( Override )
	 *
	 * @access	public
	 * @return	string
	 */
	function create_links()
	{
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
			return '';
		}

		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);

		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}
		
		$this->num_pages = $num_pages;

		// Determine the current page number.
		$CI =& get_instance();

		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			if ($CI->input->get($this->query_string_segment) != 0)
			{
				$this->cur_page = $CI->input->get($this->query_string_segment);

				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}
		else
		{
			if ( $this->get_query_page_key )
			{
				$this->cur_page = (int)$CI->input->get($this->get_query_page_key);
			}
			else if ($CI->uri->segment($this->uri_segment) != 0)
			{
				$this->cur_page = $CI->uri->segment($this->uri_segment);

				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}

		$this->num_links = (int)$this->num_links;

		if ($this->num_links < 1)
		{
			show_error('Your number of links must be a positive number.');
		}

		if ( ! is_numeric($this->cur_page))
		{
			$this->cur_page = 0;
		}

		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->cur_page > $this->total_rows)
		{
			$this->cur_page = ($num_pages - 1) * $this->per_page;
		}

		$uri_page_number = $this->cur_page;
		$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

		// Is pagination being used over GET or POST?  If get, add a per_page query
		// string. If post, add a trailing slash to the base URL if needed
		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			$this->base_url = rtrim($this->base_url).'&amp;'.$this->query_string_segment.'=';
		}
		else
		{
			// add process parse query string
			$q = strpos($this->base_url, '?');
			if ( $q === FALSE )
			{
				if ( $this->get_query_page_key )
				{
					$this->base_url = rtrim($this->base_url, '/') .'/?' . $this->get_query_page_key . '=';
				}
				else
				{
					$this->base_url = rtrim($this->base_url, '/') .'/';
				}
			}
			else 
			{
				if ( $this->get_query_page_key )
				{
					$this->query_strings = '&amp;' . trim(substr($this->base_url, $q), '?');
					$this->base_url = rtrim(substr($this->base_url, 0, $q), '/') . '/?' . $this->get_query_page_key . '=';
				}
				else 
				{
					$this->query_strings = substr($this->base_url, $q - 1) . '&amp;' . $this->get_query_page_key . '=';
					$this->base_url = rtrim(substr($this->base_url, 0, $q), '/') . '/';
				}
			}
		}

  		// And here we go...
		$output = '';

		// Render the "First" link
		if  ($this->cur_page > ($this->num_links + 1))
		{
			$output .= $this->first_tag_open.'<a href="'.$this->base_url.$this->query_strings.'">'.$this->first_link.'</a>'.$this->first_tag_close;
		}

		// Render the "previous" link
		if  ($this->cur_page != 1)
		{
			$i = $uri_page_number - $this->per_page;
			if ($i == 0) $i = '';
			$output .= $this->prev_tag_open.'<a href="'.$this->base_url.$i.$this->query_strings.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
		}

		// Write the digit links
		for ($loop = $start -1; $loop <= $end; $loop++)
		{
			$i = ($loop * $this->per_page) - $this->per_page;

			if ($i >= 0)
			{
				if ($this->cur_page == $loop)
				{
					$output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
				}
				else
				{
					$n = ($i == 0) ? '' : $i;
					$output .= $this->num_tag_open.'<a href="'.$this->base_url.$n.$this->query_strings.'">'.$loop.'</a>'.$this->num_tag_close;
				}
			}
		}

		// Render the "next" link
		if ($this->cur_page < $num_pages)
		{
			$output .= $this->next_tag_open.'<a href="'.$this->base_url.($this->cur_page * $this->per_page).$this->query_strings.'">'.$this->next_link.'</a>'.$this->next_tag_close;
		}

		// Render the "Last" link
		if (($this->cur_page + $this->num_links) < $num_pages)
		{
			$i = (($num_pages * $this->per_page) - $this->per_page);
			$output .= $this->last_tag_open.'<a href="'.$this->base_url.$i.$this->query_strings.'">'.$this->last_link.'</a>'.$this->last_tag_close;
		}

		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;

		return $output;
	}
}