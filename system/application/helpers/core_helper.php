<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * Seezoo Core utility functions
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==============================================================
 */


// ----------------- CMS utility functions ---------------------

// flint_execute : Flint.js書き出し
if ( ! function_exists('flint_execute'))
{
	function flint_execute($pid = 0)
	{
		$CI   =& get_instance();
		$mode = $CI->config->item('final_output_mode');
		$lib  = FALSE;
		
		if ( $mode === 'pc' || $CI->is_login === TRUE )
		{
			$lib = ( ADVANCE_UA === 'ie6' ) ? 'flint.dev.min.js' : 'flint.dev2.min.js';
		}
		else if ( $mode === 'sp' )
		{
			$lib = 'flint.mobile.min.js';
		}
		
		if ( $lib )
		{
			return '<script type="text/javascript" src="' . file_link() . 'index.php/flint/flint_config/' . $pid . '" charset="UTF-8"></script>' . "\n"
					.'<script type="text/javascript" src="' . file_link() . 'js/' . $lib . '" charset="UTF-8"></script>';
		}
		return '';
	}
}

// write favicon : favicon書き出し
if ( ! function_exists('write_favicon'))
{
	function write_favicon($to_link = FALSE)
	{
		if (file_exists(FCPATH . 'files/favicon/favicon.ico'))
		{
			if ( $to_link )
			{
				return '<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="' . file_link() . 'files/favicon/favicon.ico" />' . "\n"
						. '<link rel="icon" type="image/vnd.microsoft.icon" href="' . file_link() . 'files/favicon/favicon.ico" />';
			}
			else {
				//'<img src="' . file_link() . 'files/favicon/favicon.ico" />';
				return '<span>faviconが設定されています</span>';
			}
		}
		else
		{
			return ($to_link) ? '' : 'なし';
		}
	}
}

// xml_define : IE6,7以外の場合はxml宣言を出力する
if ( ! function_exists('xml_define'))
{
	function xml_define()
	{
		if (!isset($_SERVER['HTTP_USER_AGENT']))
		{
			return '';
		}
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);

		if (!$ua || $ua == '' || preg_match('/msie\s[6|7]\.0/', $ua))
		{
			return '';
		}
		return '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	}
}

// set_image : 画像パス生成
// @note IE6の場合、png->gifのコンバートが走る
if ( ! function_exists('set_image'))
{
	function set_image($filepath, $with_tag = FALSE)
	{
		$CI =& get_instance();
		
		// replace file png to gif
		if (ADVANCE_UA === 'ie6' && preg_match('/\.png$/u', $filepath))
		{
			$ie6_path = preg_replace('/\.png$/', '.gif', $filepath);
			$exp = explode('/', $ie6_path);
			$filename = end($exp);
			if (!file_exists(FCPATH . 'files/ie6/' . $filename))
			{
				$CI->load->library('image_lib');
				$conf = array(
					'convert'      => 'gif',
					'source_image' => FCPATH . 'images/' . $filepath,
					'new_image'    => FCPATH . 'files/ie6/'
				);
				$CI->image_lib->initialize($conf);
				$CI->image_lib->convert();
			}
			$path = file_link() . 'files/ie6/' . $filename;
		}
		else
		{
			$path = file_link() . 'images/' . $filepath;
		}
		if ($with_tag === TRUE)
		{
			return '<img src="' . $path . '" alt="" />';
		}
		else
		{
			return $path;
		}
	}
}

// set_file_info : ファイルマネージャ用ファイル情報整形
if ( ! function_exists('set_file_info'))
{
	function set_file_info($s, $w, $h)
	{
		$out[] = $s . 'KB';
		if ($w && $h)
		{
			$out[] = $w . 'px&nbsp;x&nbsp;' . $h . 'px';
		}
		return implode('<br />', $out);
	}
}

// has_icon_ext : アイコン画像のある拡張子かどうかの判定
if ( ! function_exists('has_icon_ext'))
{
	function has_icon_ext($ext)
	{
		return file_exists('images/icons/files/' . strtolower($ext) . '.png');
	}
}

// image_ext : 画像系の拡張子かどうかを判定
if ( ! function_exists('image_ext'))
{
	function image_ext($ext)
	{
		$img_ext = array('gif','jpg','jpeg','png','bmp','tiff');
		if (in_array($ext, $img_ext))
		{
			return TRUE;
		}
		return FALSE;
	}
}

// get_file : ファイルIDからファイルデータ取得
if ( ! function_exists('get_file'))
{
	function get_file($file = 0, $return_path = FALSE)
	{
		if (!$file)
		{
			return FALSE;
		}
		$CI =& get_instance();
		$CI->load->model('file_model');
		$file = $CI->file_model->get_file_data($file);
		
		if (!$file)
		{
			return FALSE;
		}
		
		return ($return_path) ? make_file_path($file) : $file;
		
	}
}

// make_file_path : ファイルデータのDB結果セットからファイルパスを生成
if ( ! function_exists('make_file_path'))
{
	function make_file_path($file, $type = '', $to_abs = FALSE)
	{
		if (!$file)
		{
			return '';
		}
		$sub = (!empty($type)) ? $type . '/' : '';
		$abs = ($to_abs) ? file_link() : '';
		if (is_object($file))
		{
			return $abs . 'files/' . $sub . $file->crypt_name . '.' . $file->extension;
		}
		else if (is_array($file))
		{
			return $abs . 'files/' . $sub . $file['crypt_name'] . '.' . $file['extension'];
		}
	}
}

// is_image : 画像かどうかを判定
if ( ! function_exists('is_image'))
{
	function is_image($img)
	{
		if (!(@getimagesize($img)))
		{
			return FALSE;
		}
		// swf file returns info for getimagesize
		if (substr($img, strrpos($img, '.') + 1) == 'swf')
		{
			return FALSE;
		}
		return TRUE;
	}
}

// virtual_to_realpath : ファイルマネージャUI上のパスを実ファイルパスに変換
if ( ! function_exists('virtual_to_realpath') )
{
	function virtual_to_realpath($virtual_path, $abs = FALSE)
	{
		if ( empty($filepath) )
		{
			return FALSE;
		}
		// trim slash and explode
		$dirs = explode('/', trim($filepath, '/'));
		// filename is last pointer
		$file = array_pop($dirs);
		// split name and extension
		$exp  = explode('.', $file);
		if ( ! $exp || count($exp) < 2 )
		{
			return FALSE;
		}
		$extension = array_pop($exp);
		$filebody  = implode('', $exp);
		$current_directory_id = 1;
		
		$CI    =& get_instance();
		$sql   = 'SELECT file_id, directories_id FROM files WHERE file_name = ? AND extension = ?';
		$query = $CI->db->query($sql, array($filebody, $extension));

		// filedata exists?
		if ( ! $query || ! $query->row() )
		{
			return FALSE;
		}
		
		// tmp stacked file-directory id.
		$filedata = $query->row();
		
		// direcotry_path build
		$build_path = array();
		$sql        = 'SELECT directories_id FROM directories WHERE parent_id = ? AND path_name = ?';
		foreach ( $dirs as $dir )
		{
			$query = $CI->db->query($sql, array($current_directory_id, $dir));
			if ( ! $query || ! $query->row() )
			{
				return FALSE;
			}
			$result = $query->row();
			$current_directory_id = (int)$result->directories_id;
		}
		
		// compare directory_id
		if ( (int)$filedata->directories_id === $current_directory_id )
		{
			$real_path = 'files/' . $result->crypt_name . '.' . $result->extension;
			return ( $abs === TRUE ) ? file_link() . $real_path : $real_path;
		}
		return FALSE;
	}
}


// ------------------ Common security functions -------------------

// generate_ticket : ワンタイムトークン生成
if ( ! function_exists('ticket_generate'))
{
	function ticket_generate($ticket_name = 'ticket')
	{
		$CI =& get_instance();
		$ticket = md5(uniqid(mt_rand(), TRUE));
		$CI->session->set_userdata($ticket_name, $ticket);
		return $ticket;
	}
}

// check_ticket : ワンタイムトークン照合
if ( ! function_exists('ticket_check'))
{
	function ticket_check($ticket_name = 'ticket', $input = 'post')
	{
		$CI =& get_instance();
		$ticket = $CI->input->{$input}($ticket_name);
		if ( !$ticket || $ticket != $CI->session->userdata($ticket_name))
		{
			exit('チケット照合失敗');
		}
	}
}

// destroy_ticket : ワンタイムトークンセッション削除
if ( ! function_exists('destroy_ticket') )
{
	function destroy_ticket($ticket_name = 'ticket')
	{
		$CI =& get_instance();
		$CI->session->unset_userdata($ticket_name);
	}
}

// check_ajax_token : AJAXからのワンタイムトークンをチェック
if ( ! function_exists('check_ajax_token'))
{
	function check_ajax_token($token = FALSE)
	{
		$CI =& get_instance();
		if (!$token || $token != $CI->session->userdata('sz_token'))
		{
			exit('access_denied');
		}
	}
}

// ref_check : リファラーチェック リファラーを送信しない、またはリファラーが自サイトのアドレスでないとexitする。
if ( ! function_exists('ref_check'))
{
	function ref_check()
	{
		if ( ! isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], file_link()) === FALSE)
		{
			show_403();
		}
	}
}

// kill_nullbyte : ヌルバイト削除
if ( ! function_exists('kill_nullbyte'))
{
	function kill_nullbyte($str)
	{
		if (is_array($str))
		{
			return array_map('kill_nullbyte', $str);
		}
		return str_replace('\0', '', $str);
	}
}

// kill traversal : ディレクトリトラバーサルの危険性のある文字列を削除
if ( ! function_exists('kill_traversal'))
{
	function kill_traversal($str)
	{
		if (is_array($str))
		{
			return array_map('kill_traversal', $str);
		}
		$str = str_replace('../', '', $str);
		$paths = explode('/', $str);
		$ret = array();
		
		foreach ($paths as $path)
		{
			$ret[] = basename(kill_nullbyte($path));
		}
		return implode('/', $ret);
	}
}

// password_stretching : パスワードのストレッチング
if ( ! function_exists('password_stretch') )
{
	function password_stretch($hash, $password, $algorithm = FALSE)
	{
		if ( ! $algorithm )
		{
			// default encrypt
			return md5($hash . $password);
		}
		$times = ( defined('SEEZOO_PASSWORD_STRETCH_TIMES') ) ? SEEZOO_PASSWORD_STRETCH_TIMES : 1000;
		for ( $i = 0 ; $i < $times; $i++ )
		{
			$password = ( function_exists($algorithm) )
			              ? $algorithm($hash . $password)
			              : _stretch_with_hash_function($algorithm, $hash . $password);
		}
		return $password;
	}
}

if ( ! function_exists('_stretch_with_hash_function') )
{
	function _stretch_with_hash_function($algorithm, $hash_password)
	{
		if ( function_exists('hash') )
		{
			return hash($algorithm, $hahs_password);
		}
		// If hash function is not exists ( PHP5.1.2 lower ), return sha1 crypted...
		return sha1($hahs_password);
	}
}

// ---------------------- Other utility function -----------------------

// sort_asc : 配列を昇順に並び替えるコールバック関数
if ( ! function_exists('sort_asc'))
{
	function sort_asc($a, $b)
	{
		return ((int)$a['time'] < (int)$b['time']) ? 1 : -1;
	}
}

// sort_by_segment : ページパスの浅い順にソートするusortコールバック
if ( ! function_Exists('sort_by_segment'))
{
	function sort_by_segment($a, $b)
	{
		$sp_a = explode('/', $a);
		if ( ! $sp_a)
		{
			$cnt_a = 1;
		}
		else
		{
			$cnt_a = count($sp_a);
		}

		$sp_b = explode('/', $b);
		if ( ! $sp_b)
		{
			$cnt_b = 1;
		}
		else
		{
			$cnt_b = count($sp_b);
		}
		return ($cnt_a < $cnt_b) ? -1 : 1;
	}
}

// object_to_array : オブジェクトを配列に変換
if ( ! function_exists('object_to_array'))
{
	function object_to_array($obj)
	{
		return (is_object($obj)) ? get_object_vars($obj) : $obj;
	}
}

// array_to_object : 連想配列をオブジェクトに変換
if ( ! function_exists('array_to_object'))
{
	function array_to_object($ary)
	{
		$obj = new StdClass;
		foreach ((array)$ary as $key => $v)
		{
			$obj->{$key} = $v;
		}
		return $obj;
	}
}

// output_css : スタックに入れられたCSSをまとめて追加
if ( ! function_exists('output_css'))
{
	function output_css($type = 'header')
	{
		$CI =& get_instance();
		if ($type == 'header')
		{
			if (isset($CI->additional_header_css)
				&& is_array($CI->additional_header_css)
				&& count($CI->additional_header_css) > 0)
				{
					echo implode("\n", $CI->additional_header_css);
				}
		}
		else if ($type == 'footer')
		{
			if (isset($CI->additional_footer_css)
				&& is_array($CI->additional_footer_css)
				&& count($CI->additional_footer_css) > 0)
				{
					echo implode("\n", $CI->additional_footer_css);
				}
		}
	}
}

// output_javascript : スタックに入れられたJSをまとめて追加
if ( ! function_exists('output_javascript'))
{
	function output_javascript($type = 'header')
	{
		$CI =& get_instance();
		if ($type == 'header')
		{
			if (isset($CI->additional_header_javascript)
				&& is_array($CI->additional_header_javascript)
				&& count($CI->additional_header_javascript) > 0)
				{
					echo implode("\n", $CI->additional_header_javascript);
				}
		}
		else if ($type == 'footer')
		{
			if (isset($CI->additional_footer_javascript)
				&& is_array($CI->additional_footer_javascript)
				&& count($CI->additional_footer_javascript) > 0)
				{
					echo implode("\n", $CI->additional_footer_javascript);
				}
		}
	}
}


// parse_db_schema : XML-DBスキーマをパースしてCI::dbforgeの形式にフォーマット
// @param string $path リレーションするクラス名
if ( ! function_exists('parse_db_schema'))
{
	function parse_db_schema($path, $prefix_dir)
	{
		// load db_structures xml
		$XML = simplexml_load_file($prefix_dir . 'schemas/db/' . $path . '.xml');
		$dbst = array();

		// parse db schema
		foreach ($XML->table as $table)
		{
			$table_name = (string)$table['name'];
			$columns = array();

			foreach ($table->children() as $column)
			{
				$name = (string)$column['name'];
				$c = array(
					'type' => strtoupper((string)$column['type'])
				);

				if (isset($column['size']))
				{
					$c['constraint']	= (string)$column['size'];
				}

				if (isset($column->key))
				{
					$c['key'] = TRUE;
				}
				if (isset($column->auto_increment))
				{
					$c['auto_increment'] = TRUE;
				}
				$c['null'] = (isset($column->null)) ? TRUE : FALSE;
				
				if (isset($column->index))
				{
					$c['index'] = $name;
				}

				if (isset($column->default))
				{
					$c['default'] = (string)$column->default['value'];
				}
				$columns[$name] = $c;
			}
			$dbst[$table_name] = $columns;
		}
		return $dbst;
	}
}

// dataスキーマを解析してCI::DB::Active_Recordのフォーマットに整形
if ( ! function_exists('parse_db_schema_records'))
{
	function parse_db_schema_records($path, $prefix_dir)
	{
		$XML = simplexml_load_file($prefix_dir . 'schemas/data/' . $path . '.xml');
		$ins = array();

		foreach ($XML->table as $table)
		{
			$table_name = (string)$table['name'];
			$records = array();
			foreach ($table->children() as $record)
			{
				$line = array();
				foreach ($record->children() as $data)
				{
					$line[$data->getName()] = (string)$data;
				}
				$records[] = $line;
			}
			$ins[$table_name] = $records;
		}

		return $ins;
	}
}

// sz_form_build_pref_list : 都道府県のリストを生成
if ( ! function_exists('sz_form_build_pref_list') )
{
	function sz_form_build_pref_list($return_array = FALSE)
	{
		$pref_list = array(
			'北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県',
			'埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
			'岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
			'鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県',
			'佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
		);
		return ( ! $return_array ) ? implode(':', $pref_list) : $pref_list;
	}
}

// sz_form_build_month_list : 月のリストを生成
if ( ! function_exists('sz_form_build_month_list') )
{
	function sz_form_build_month_list($return_array = FALSE)
	{
		$month_list = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
		return ( ! $return_array ) ? implode(':', $month_list) : $month_list;
	}
}

// sz_form_build_dat_list : 日のリストを生成
if ( ! function_exists('sz_form_build_day_list') )
{
	function sz_form_build_day_list($return_array = FALSE)
	{
		$day_list = array();
		for ( $i = 1; $i < 32; $i++ )
		{
			$day_list[] = $i;
		}
		return ( ! $return_array ) ? implode(':', $day_list) : $day_list;
	}
}

// sz_form_build_birth_year_list : 誕生日等の西暦を取得
if ( ! function_exists('sz_form_build_birth_year_list') )
{
	function sz_form_build_birth_year_list($range = 60, $return_array = FALSE)
	{
		$i = (int)date('Y');
		$y = $i - (int)$range;
		while ( $i >= $y )
		{
			$year_list[] = $y++;
		}
		return ( ! $return_array ) ? implode(':', $year_list) : $year_list;
	}
}

// sz_form_build_hour_list : 24時間形式のリストを生成
if ( ! function_exists('sz_form_build_hour_list') )
{
	function sz_form_build_hour_list($return_array = FALSE)
	{
		$hour_list = array();
		for ( $i = 1; $i < 25; $i++ )
		{
			$hour_list[] = $i;
		}
		return ( ! $return_array ) ? implode(':', $hour_list) : $hour_list;
	}
}

// sz_form_build_minute_list : 分のリストを生成
if ( ! function_exists('sz_form_build_minute_list') )
{
	function sz_form_build_minute_list($return_array = FALSE)
	{
		$minute_list = array();
		for ( $i = 0; $i < 61; $i++ )
		{
			$minute_list[] = ( $i < 10 ) ? '0' . $i : $i;
			$i++;
		}
		return ( ! $return_array ) ? implode(':', $minute_list) : $minute_list;
	}
}

// get_member_profile_image : メンバーのプロフィール画像パスを取得
if ( ! function_exists('get_member_profile_image') )
{
	function get_member_profile_image($image_data)
	{
		if ( preg_match('/^http/', $image_data) )
		{
			return $image_data;
		}
		else
		{
			return file_link() . 'files/members/' . $image_data;
		}
	}
}

// show_token_error : ワンタイムトークンのエラー表示
if ( ! function_exists('show_token_error') )
{
	function show_token_error($redirect_url = '')
	{
		if ( empty($redirect_url) )
		{
			$CI =& get_instance();
			if ( strpos(trim($CI->uri->uri_string(), '/'), 'dashboard') === 0 )
			{
				$redirect_url = page_link('dashboard/panel');
			}
			else
			{
				$redirect_url = page_link();
			}
		}
		$EXP =& load_class('Exceptions');
		echo $EXP->show_error($redirect_url, '', 'error_token', 403);
		exit;
	}
}

// generate_google_analytics_mobile_tag : GAのフィーチャーフォン用のアクセス解析タグ発行
if ( ! function_exists('generate_google_analytics_mobile_tag') )
{
	function generate_google_analytics_mobile_tag($pc_tracking_code = '')
	{
		// If tracking code not exists or GA library file doesn't exists, return empty string
		if ( empty($pc_tracking_code) || ! file_exists(FCPATH . 'ga.php') )
		{
			return '';
		}
		// profile ID exists?
		if ( ! preg_match('/UA(\-[0-9]+\-[0-9]+)/us', $pc_tracking_code, $match) )
		{
			return '';
		}
		$mb_code  = 'MO' . $match[1];
		$CI       =& get_instance();
		// get server parameter
		$ref      = $CI->input->server('HTTP_REFERER');
		$query    = $CI->input->server('QUERY_STRING');
		$path     = $CI->input->server('REQUEST_URI');
		$title    = ( isset($CI->page_data) ) ? $CI->page_data['page_title'] : '';
		
		// generate URI parameter
		$ga_uri   = array(file_link() . 'ga.php?utmac=' . $mb_code);
		$ga_uri[] = 'utmn=' . rand(0, 0x7fffffff);
		if ( ! $ref )
		{
			$ref = '-';
		}
		$ga_uri[] = 'utmr=' . $ref;
		if ( $path )
		{
			$ga_uri[] = 'utmp=' . urlencode($path);
		}
		$ga_uri[] = 'utmdt=' . urlencode($title);
		$ga_uri[] = 'guid=ON';
		return '<img src="' . implode('&amp;', $ga_uri) . '" />';
	}
}

