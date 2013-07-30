<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * Emoji Utility functions
 *
 * @author Y.Paku
 * ==============================================================
 */
if ( ! function_exists('emoji_convert'))
{
	function emoji_convert($body)
	{
		$retBody = $body;

		$mobile = Mobile::get_instance();

		preg_match_all('/\[m:([0-9]+)\]/u', $body, $data);
		for($i = 0; $i < count($data[0]); $i++) {
			$retBody = str_replace($data[0][$i],$mobile->convert_emoji($data[1][$i]),$retBody);
		}

		return $retBody;
	}
}

?>
