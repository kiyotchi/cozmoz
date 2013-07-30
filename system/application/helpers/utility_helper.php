<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ====================================================================
 * 
 * Seezoo utility helper functions
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ====================================================================
 */

// -------------  Date-Time managing utirities  --------------

// db_datetime : DB登録用のDATETIME生成
if ( ! function_exists('db_datetime'))
{
	function db_datetime()
	{
		return date('Y-m-d H:i:s', time());
	}
}

// set_public_datetime : DATETIME形式からフォーマットで出力
if ( ! function_exists('set_public_datetime'))
{
	function set_public_datetime($format, $datetime)
	{
		// PHP5.1.0以前はstrtotimeは-1を返却するのでここで吸収
		if ((int)strtotime($datetime) < 1)
		{
			return '';
		}
		return date($format, strtotime($datetime));
	}
}

// parse_date : dateの値を整形
if ( ! function_exists('parse_date'))
{
	function parse_date($dt)
	{
		list($y, $m, $d) = explode('-', $dt);
		return $y . '年' . $m . '月' . $d . '日';
	};
}

// perse_datetime : datetimeの値を整形
if ( ! function_exists('parse_datetime'))
{
	function parse_datetime($dt, $format = 'ymd')
	{
		$data = explode(' ', $dt);

		if ($format === 'ymd')
		{
			list($y, $m, $d) = explode('-', $data[0]);
			return $y . '年' . $m . '月' . $d . '日';
		}
		else if ($format === 'his')
		{
			list($h, $i, $s) = explode(':', $data[1]);
			return $h . '時' . $i . '分' . $s . '秒';
		}
		else if ($format == 'hi')
		{
			list($h, $i, $s) = explode(':', $data[1]);
			return $h . '時' . $i . '分';
		}
	}
}

// hour_list : 時間の配列を生成
if ( ! function_exists('hour_list'))
{
	function hour_list()
	{
		for ($i = 0; $i < 24; $i++)
		{
			$key = ($i < 10) ? '0' . $i : (string)$i;
			$list[$key] = $key;
		}
		return $list;
	}
}

// minute_list : 分の配列を生成
if ( ! function_exists('minute_list'))
{
	function minute_list()
	{
		for ($i = 0; $i < 60; $i++)
		{
			$key = ($i < 10) ? '0' . $i : (string)$i;
			$list[$key] = $key;
		}
		return $list;
	}
}


//  -----------------  String utirities  -----------------

// uri_encode_path : "/"を除いてURIエンコードをかける
if ( ! function_exists('uri_encode_path'))
{
	function uri_encode_path($path)
	{
		$p = rawurlencode($path);
		return str_replace(array('%2F', '%3A'), array('/', ':'), $p);
	}
}

// multi_uri_decode : 文字列に「%」が含まれていればデコード
if ( ! function_exists('multi_uri_decode'))
{
	function multi_uri_decode($str)
	{
		if (strpos($str, '%') !== FALSE)
		{
			$str = rawurldecode($str);
		}

		return $str;
	}
}

// multi_uri_encode : 文字列をURIエンコード
if ( ! function_exists('multi_uri_encode'))
{
	function multi_uri_encode($str)
	{
		return rawurlencode($str);
	}
}

// prep_str : htmlspecialcharsのショートカット
if ( ! function_exists('prep_str'))
{
	function prep_str($str)
	{
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	}
}

// unprep_str : htmlspecialchars_decodeのショートカット
if ( ! function_exists('unprep_str'))
{
	function unprep_str($str)
	{
		if ( function_exists('htmlspecialchars_decode') )
		{
			return htmlspecialchars_decode($str, ENT_QUOTES);
		}
		else 
		{
			return html_entity_decode($str, ENT_QUOTES);
		}
	}
}

// link_format : URL形式のストリングを<a>タグで囲む
if ( ! function_exists(('link_format')))
{
	function link_format($str, $target_blank = TRUE)
	{
		return preg_replace(
							'/(https?:\/\/[\w\.\/]+)([\/|\?]?[\-_.!~\*a-zA-Z0-9\/\?:;@&=+$,%#]+)?/',
							'<a href="$1$2" ' . (($target_blank === TRUE) ? 'target="blank"' : '') . '>$1$2</a>',
							$str
						);
	}
}


// truncate : 文字列を指定文字列でsplitする
if ( ! function_exists('truncate'))
{
	// @reference : http://snippets.dzone.com/posts/show/7125
	function truncate($text, $length = 80, $is_html = TRUE, $suffix = '...')
	{
		$i          = 0;
		$simpleTags = array('br' => TRUE, 'hr' => TRUE, 'input' => TRUE, 'image' => TRUE, 'link' => TRUE, 'meta' => TRUE);
		$tags       = array();
		
		if ( ! $is_html )
		{
			$substr = ( function_exists('mb_substr') ) ? 'mb_substr' : 'substr';
			$strlen = ( function_exists('mb_strlen') ) ? 'mb_strlen' : 'strlen';
			if ( $strlen($text) <= $length )
			{
				return $text;
			}
			return $substr($text, 0, $length, 'UTF-8') . $suffix;
		}
		else
		{
			$text = preg_replace('/<[^>]+>([^<]*)/', '', $text);
			$substr = ( function_exists('mb_substr') ) ? 'mb_substr' : 'substr';
			$strlen = ( function_exists('mb_strlen') ) ? 'mb_strlen' : 'strlen';
			if ( $strlen($text) <= $length )
			{
				return $text;
			}
			return $substr($text, 0, $length, 'UTF-8') . $suffix;
		}
		
		 
		preg_match_all('/<[^>]+>([^<]*)/', $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		foreach( $matches as $match )
		{
			if( $match[0][1] - $i >= $length )
			{
				break;
			}
			$t = substr(strtok($match[0][0], " \t\n\r\0\x0B>"), 1);
			// test if the tag is unpaired, then we mustn't save them
			if( $t[0] !== '/' && ! isset($simpleTags[$t]) )
			{
				$tags[] = $t;
			}
			else if ( end($tags) == substr($t, 1) )
			{
				array_pop($tags);
			}
			$i += $match[1][1] - $match[0][1];
		}
		
		// output without closing tags
		$length = min(strlen($text),  $length + $i);
		$output = substr($text, 0, $length);
		
		// closing tags
		$tags = array_reverse($tags);
		$tmp  = ( count($tags) > 0 ) ? '</' . implode('></', $tags) . '>' : '';
		
		// Find last space or HTML tag (solving problem with last space in HTML tag eg. <span class="new">)
		$pos = (int)end(end(preg_split('/<.*>| /', $output, -1, PREG_SPLIT_OFFSET_CAPTURE)));
		// Append closing tags to output
		$output .= $tmp;

		// Get everything until last space
		$one = substr($output, 0, $pos);
		// Get the rest
		$two = substr($output, $pos, (strlen($output) - $pos));
		// Extract all tags from the last bit
		preg_match_all('/<(.*?)>/s', $two, $tags);
		// Add suffix if needed
		if ( strlen($text) > $length )
		{
			$one .= $suffix;
		}
		// Re-attach tags
		$output = $one . implode($tags[0]);
		
		//added to remove  unnecessary closure
		$output = str_replace('</!-->','',$output); 
		
		return $output;
	}
}


// spacer_gif : spacer.gifを任意のサイズで出力
// ガラケー用の関数なので、PC、スマフォでは非推奨
if ( ! function_exists('spacer_gif') )
{
	function spacer_gif($width = 1, $height = 1)
	{
		$img = '<img src="'
		        . file_link() . 'images/spacer.gif" '
		        . 'width="' . $width . '" '
		        . 'height="' . $height . '" />';
		return $img;
	}
}


// ----------------- request utility function --------------------------
/**
 * common http request
 */
if ( ! function_exists('http_request') )
{
	function http_request(
														$uri,									// request uri
														$method = 'GET',		// request method
														$header = array(),	// request headers array
														$postbody = "")			// post body(encoded)
	{
		if ( is_array($postbody) )
		{
			$stack = array();
			foreach ($postbody as $key => $val)
			{
				if ( is_array($val) )
				{
					foreach ($val as $v)
					{
						$stack[] = rawurlencode($key) . '[]=' . rawurlencode($v);
					}
				}
				else
				{
					$stack[] = rawurlencode($key) . '=' . rawurlencode($val);
				}
			}
			$post = implode('&', $stack);
		}
		else 
		{
			$post = $postbody;
		}
		// CURLモジュールが有効な場合はcurlでリクエスト
		if ( extension_loaded('curl') )
		{
			return _http_request_curl(
															$uri,
															strtoupper($method),
															$header,
															$post
														);
		}
		// それ以外はソケット接続でリクエスト
		else
		{
			return _http_request_socket(
															$uri,
															strtoupper($method),
															$header,
															$post
														);
		}
	}
}

/**
 * curlでリクエスト
 */
if ( ! function_exists('_http_request_curl') )
{
	function _http_request_curl($uri, $method, $header, $post)
	{
		$handle = curl_init();
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		}
		if (count($header) > 0)
		{
			curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
		}
		
		curl_setopt_array($handle, array(
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HEADER => FALSE
		));

		if ($method === 'POST')
		{
			curl_setopt($handle, CURLOPT_POST, TRUE);
			if ($post != '')
			{
				curl_setopt($handle, CURLOPT_POSTFIELDS, $post);
			}
		}
		curl_setopt($handle, CURLOPT_URL, $uri);

		$resp = curl_exec($handle);

		if ( ! $resp )
		{
			$resp = FALSE;
		}
		curl_close($handle);
		
		return $resp;
	}
}

if ( ! function_exists('_http_request_socket') )
{
	function _http_request_socket($uri, $method, $header, $post)
	{
		// parse URLs
		$URL = parse_url($uri);

		$scheme = $URL['scheme'];
		$path   = $URL['path'];
		$host   = $URL['host'];
		$query  = (isset($URL['query'])) ? '?' . $URL['query'] : '';
		$port   = (isset($URL['port']))
		          ? $URL['port'] : ($scheme == 'https' || $_SERVER['HTTPS'] === 'on')
		            ? 443 : 80;

		// build request-line-header
		$request = $method . ' ' . $path . $query . ' HTTP/1.1' . "\r\n"
								. 'Host: ' . $host . "\r\n";
								
		if ( isset($_SERVER['HTTP_USER_AGENT']))
		{
			$request .= 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
		}
		if (count($header) > 0)
		{
			foreach ($header as $head)
			{
				$request .= $head . "\r\n";
			}
		}

		if ($method === 'POST')
		{
			$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			if ( ! empty($post) )
			{
				$request .= 'Content-Length: ' . strlen($post) . "\r\n";
				$request .= "\r\n";
				$request .= $post;
			}
		}
		else
		{
			$request .= "\r\n";
		}

		$fp = @fsockopen($host, $port);

		if ( ! $fp )
		{
			return FALSE;
		}

		// send request
		fputs($fp, $request);

		// get response
		$resp = '';
		while ( ! feof($fp) )
		{
			$resp .= fgets($fp, 4096);
		}
		fclose($fp);

		// split header
		$exp = explode("\r\n\r\n", $resp);

		if (count($exp) < 2)
		{
			return FALSE;
		}

		// returns response body only.
		return implode("\r\n\r\n", array_slice($exp, 1));
	}
}

/**
 * fgetcsv_reg : CSVの読み込みとパース
 * @see http://yossy.iimp.jp/wp/?p=56 (original)
 * @param $handle file resource or filepath
 * @param $length
 * @param $delimiter
 * @param $enc
 * @return mixed bool or array
 */
if ( ! function_exists('fgetcsv_reg') )
{
	function fgetcsv_reg($handle, $length = null, $delimiter = ',', $enc = '"')
	{
		// pre process : Is first argument resource? 
		if ( ! is_resource($handle) )
		{
			if ( ! preg_match('/.+\.csv$/', $handle) || ! file_exists($handle) )
			{
				return FALSE;
			}
			else 
			{
				// open file and call recursive
				return fgetcsv_reg(fopen($handle, 'rb'), $length, $delimiter, $enc);
			}
		}
		// main process
		$delimiter = preg_quote($delimiter);
		$enc = preg_quote($enc);
		$_line = "";
		$eof = FALSE;
		
		// pre:create regexes
		$regex = array(
			'item'   => '/' . $enc . '/',
			'line'   => '/(?:\\r\\n|[\\r\\n])?$/',
			'pattern'=> '/(' . $enc . '[^'. $enc . ']*(?:' . $enc . $enc . '[^'. $enc . ']*)*' . $enc . '|[^' . $delimiter . ']*)' . $delimiter . '/',
			'replace'=> '/^' . $enc . '(.*)' . $enc . '$/s'
		);
		
		while ( $eof !== TRUE && ! feof($handle) )
		{
			$_line .= ( empty($length ) ? fgets($handle) : fgets($handle, $length));
			$itemcnt = preg_match_all($regex['item'], $_line, $dummy);
			if ( $itemcnt % 2 === 0 )
			{
				$eof = TRUE;
			}
		}
		$_csv_line = preg_replace($regex['line'], $delimiter, trim($_line));
		preg_match_all($regex['pattern'], $_csv_line, $_csv_matches);
		$_csv_data = $_csv_matches[1];
		
		for ( $_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++ )
		{
			$_csv_data[$_csv_i] = preg_replace($regex['replace'], '$1', $_csv_data[$_csv_i]);
			$_csv_data[$_csv_i] = str_replace($enc . $enc, $enc, $_csv_data[$_csv_i]);
		}
		return empty($_line) ? FALSE : $_csv_data;
	}
}
