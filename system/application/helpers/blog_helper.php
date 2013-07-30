<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('code_format'))
{
	function code_format($str)
	{
		$CI =& get_instance();
		$CI->load->helper('text_helper');
		$str = preg_replace_callback(
			'%(?:\[|<)code(?:\]|>)(.*?)(?:\[|<)/code(?:\]|>)%s'
			, '_split_code_section'
			, $str
		);
		return nl2br($str);//return str_replace('[sep]', '<br />', $str);
	}
}

if ( ! function_exists('_split_code_section'))
{
	function _split_code_section($matches)
	{
		// document.execCommand replaces <?php to <!--? at Google Chrome.
		// so we reverse to strict php tag.
		$str = str_replace(array('<!--?php', '?-->', '--->'), array('<?php', '?>', '->'), $matches[1]);
		$code = highlight_code($str);
		return '<div class="code_section">' . preg_replace('/\n/', '', $code) . '</div>';
	}
}

if ( ! function_exists('article_link') )
{
	function article_link($entry)
	{
		$segment = $entry->sz_blog_id;
		if ( ! empty($entry->permalink) )
		{
			$segment = $entry->permalink;
		}
		return page_link('blog/article/' . $segment);
	}
}