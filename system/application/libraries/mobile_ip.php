<?php
/**
 * ===============================================================================
 * Mobile device detection class
 *
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *
 * Original : Mobileip class
 * @author     Yoshiyuki Kadotani
 * @copyright  Copyright (c) 2009 Yoshiyuki Kadotani <kado@miscast.org>
 * @license    http://www.codeignitor.com/user_guide/license.html
 * @since      Version 1.0
 *
 * thanks!
 *
 * Usage :
 *   create instance of Mobile class.
 *   smpartphone, mobile detection is processed at a constructor.
 *
 *   ------------------------------------------------------
 *    $device = new Mobile();
 *    if ( $device->is_smartphone() ) {
 *      // case smartphone
 *     }
 *     if ( $device->is_mobile() {
 *      // case mobile
 *     }
 *   -------------------------------------------------------
 *
 *  Settings
 *    You can change some parameters.
 *     If you need, set some parameters on MobileConfig class
 *     before Mobile class instanciate.
 *
 *   --------------------------------------------------------
 *    // change cache directory
 *    MobileConfig::set_cache_dir('path/to/dist/');
 *
 *    // change cachefile expiration time
 *    MobileConfig::set_cache_expired_time(86400); // sec digit
 *   --------------------------------------------------------
 *
 * ===============================================================================
 */


/**
 * Setting Class
 * @author sugimoto
 *
 */
class MobileConfig
{
	// cache dir
	private static $_cache_dir = 'cache/';

	// cache filename
	private static $_cache_file_name = 'ip_list.txt';

	// cache expiration time (sec)
	private static $_cache_expired_time = 86400;

	// carrier scraping target page URLs
	private static $_carrier_urls = array(
		'au'       => 'http://www.au.kddi.com/ezfactory/tec/spec/ezsava_ip.html',
		'docomo'   => 'http://www.nttdocomo.co.jp/service/developer/make/content/ip/index.html',
		'softbank' => 'http://creation.mb.softbank.jp/mc/tech/tech_web/web_ipaddress.html',
		'willcom'  => 'http://www.willcom-inc.com/ja/service/contents_service/create/center_info/',
		'emobile'  => 'http://developer.emnet.ne.jp/ipaddress.html'
	);

	// getters =================================

	public static function get_carrier_urls()
	{
		return self::$_carrier_urls;
	}

	public static function get_cache_dir()
	{
		return self::$_cache_dir;
	}

	public static function get_cache_filename()
	{
		return self::$_cache_file_name;
	}

	public static function get_cache_expired_time()
	{
		return self::$_cache_expired_time;
	}

	// setters =================================

	public static function set_cache_dir($path)
	{
		self::$_cache_dir = rtrim($path, '/') . '/';
	}

	public static function add_carrier_url($handle, $url)
	{
		self::$_carrier_urls[$handle] = $url;
	}

	public static function set_cache_filename($filename)
	{
		// trim basename
		self::$_cache_file_name = basename($filename);
	}

	public static function set_cache_expired_time($time)
	{
		self::$_cache_expired_time = (int)$time;
	}

	public static function initialize($params)
	{
		foreach( (array)$params as $key => $value )
		{
			if ( isset(self::${'_' . $key}) )
			{
				self::${'_' . $key} = $value;
			}
		}
	}

}


/**
 * Operation-Interface class
 * @author sugimoto
 *
 */
class Mobile_ip
{
	// Full User-Agent string
	private $_agent;

	// Access IP
	private $_remote_ip;

	// stack instance
	private static $instance;

	// emoji instance
	private $emoji;
	private $emoji_list;



	// Mobile carrier settings ==========================================

	// Mobile carrier flag
	private $_mobile = FALSE;

	// Mobile carrier name
	private $_mobile_carrier = '';

	// Stack of ip list
	private $ip_list = array();




	// Smartphone settings ==============================================

	// Smartphone OS list
	private $_sp_agents = array(
								'iphone'         => 'iPhone',
								'ipad'           => 'iPad',
								'ipod'           => 'iPod',
								'android'        => 'Android',
								'webos'          => 'WebOS',
								'blackberry'     => 'BlackBerry',
								'windows phone'  => 'WindowsPhone',
								'windows ce'     => 'WindowsMobile'
							);

	// Boolean Smartphone flag
	private $_sp = FALSE;

	// OS name
	private $_sp_os = FALSE;

	// OS version
	private $_os_version = FALSE;


	public static function get_instance()
	{
		if ( self::$instance )
		{
			return self::$instance;
		}
		return new Mobile_ip();
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// set a Remote ip_address
		$this->_remote_ip = ( isset($_SERVER['REMOTE_ADDR']) )
		                    ? $_SERVER['REMOTE_ADDR']
		                    : FALSE;

		// set a User-Agent
		$this->_agent     = ( isset($_SERVER['HTTP_USER_AGENT']) )
		                    ? $_SERVER['HTTP_USER_AGENT']
		                    : FALSE;

		if ( ! $this->_remote_ip )
		{
			throw new Exception('Can\'t get Remote IP Address.');
			return;
		}

		// first, detect smartphone
		$this->_detect_sp();

		// sencond, detect mobile if smartphone is not detected.
		if ( $this->_sp === FALSE )
		{
			$this->_detect_mobile();
		}

		self::$instance =& $this;

	}

	// Public methods ================================================

	/**
	 * get Accessed User-Agent
	 * @access public
	 * @return string
	 */
	public function get_agent()
	{
		return $this->_agent;
	}

	/**
	 * Access Agent is mobile carrier?
	 * @access public
	 * @return bool
	 */
	public function is_mobile()
	{
		return $this->_mobile;
	}

	/**
	 * Access Agent is Smartphone carrier?
	 * @access public
	 * @return bool
	 */
	public function is_smartphone()
	{
		return $this->_sp;
	}

	/**
	 * get carrier OS name
	 * @access public
	 * @return string $OS
	 */
	public function os()
	{
		return $this->_sp_os;
	}

	/**
	 * get OS version
	 * @access public
	 * @retrurn float $os_version;
	 */
	public function version()
	{
		return $this->_os_version;
	}

	/**
	 * get mobile carrier name
	 * @access public
	 * @return string _mobile_carrier
	 */
	public function carrier()
	{
		return $this->_mobile_carrier;
	}


	public function convert_emoji($index)
	{
		if ( ! $this->emoji )
		{
			$this->emoji = $this->_get_emoji_instance();
		}
		$list = $this->_get_emoji_list();
		return ( isset($list[$index]) ) ? $list[$index] : '';

	}


	// Private/Protected methods =================================

	/**
	 * _detect_sp
	 * SmartPhone carrier detection from User-Agent
	 * @access private
	 * @return void
	 */
	private function _detect_sp()
	{
		if ( ! $this->_agent )
		{
			return;
		}

		$ua = strtolower($this->_agent);

		// detect sp
		foreach ( $this->_sp_agents as $key => $agent )
		{
			if ( strpos($ua, $key) !== FALSE )
			{
				$this->_sp = TRUE;
				$this->_sp_os = $agent;
				// set carrier flag
				$this->{'_is_' . strtolower($agent)} = TRUE;
				break;
			}
		}

		// If Smartphone access, detect OS version
		if ( $this->_sp === TRUE )
		{
			$this->_detect_os_version();
		}
	}

	/**
	 * Detect carrier OS version
	 * @access private
	 * @return void
	 */
	private function _detect_os_version()
	{
		switch ( $this->_sp_os )
		{
			case 'Android':
				$regex = '|(?:.+)Android ([0-9]+)\.([0-9]+)(?:.+)|u';
				break;
			case 'WindowsPhone':
				$regex = '|(?:.+)Windows Phone OS ([0-9]+)\.([0-9]+)(?:.+)|u';
				break;
			case 'WindowsMobile':
				$regex = '|(?:.+)IEMobile ([0-9]+)\.([0-9]+)(?:.+)|u';
				break;
			case 'BlackBerry':
				$regex = '|^BlackBerry[0-9]+/([0-9+)\.([0-9]+)(?:.+)|u';
				break;
			case 'iPad':
				$regex = '|(?:.+)CPU OS ([0-9]+)_([0-9]+)(?:.+)|u';
				break;
			default:
				$regex = '|(?:.+)iPhone OS ([0-9]+)_([0-9]+)(?:.+)|u';
				break;
		}

		$version = preg_replace($regex, '$1.$2', $this->_agent);

		$this->_os_version = floatval($version);
	}

	/**
	 * Mobile carrier detection
	 * @access private
	 * @return void
	 */
	private function _detect_mobile()
	{
		// Generate mobile ip list
		$cache_filepath = MobileConfig::get_cache_dir()
		                  . MobileConfig::get_cache_filename();

		// Cache file exsits?
		if ( ! file_exists($cache_filepath) )
		{
			$this->_generate_mobile_ip_list();
		}
		else
		{
			$this->get_mobile_ip_list_from_cache($cache_filepath);
		}


		// and, check it
		$this->_check_is_mobile();

		// destroy GC
		$this->ip_list = array();
	}

	/**
	 * Generate carrier ip-band list and create cache
	 */
	private function _generate_mobile_ip_list()
	{
		// do scraping
		foreach ( MobileConfig::get_carrier_urls() as $carrier => $url )
		{
			$this->ip_list[] = "{$carrier}=========================================";
			$data = file_get_contents($url);
			if ( $carrier === 'au' )
			{
				$this->_parse_from_html_au($data);
			}
			else
			{
				$this->_parse_from_html($data);
			}
		}

		// Try create cache
		if ( is_writable(MobileConfig::get_cache_dir()) )
		{
			$fp = fopen(MobileConfig::get_cache_dir() . MobileConfig::get_cache_filename(), "wb");
			if ( $fp )
			{
				fwrite($fp, implode("\n", $this->ip_list));
				fclose($fp);
			}
		}
	}

	/**
	 * Generate ip list from cache
	 * @param $cache_filepath
	 */
	private function get_mobile_ip_list_from_cache($cache_filepath)
	{
		// expired check
		$last_modified = filemtime($cache_filepath);
		$expired       = MobileConfig::get_cache_expired_time();

		if ( (int)$last_modified < time() - $expired )
		{
			$this->_generate_mobile_ip_list();
			return;
		}

		// create from cache
		$this->ip_list = file($cache_filepath);
	}

	/**
	 * parse HTML and get ip_list ( for au )
	 * @param $data
	 */
	private function _parse_from_html_au($data)
	{

		$regex = "/(\d{2,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}).+\n.+(\/\d{2})/";
		if ( preg_match_all($regex, $data, $matches) )
		{
			$cnt = count($matches[0]) - 1;
			for ( $i = 0; $i <= $cnt; $i++ )
			{
				$this->ip_list[] = $matches[1][$i] . $matches[2][$i];
			}
		}
	}

	/**
	 * parse HTML and get ip list
	 * @param $data
	 */
	private function _parse_from_html($data)
	{
		$regex = "/\d{2,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{2}/";
		if ( preg_match_all($regex, $data, $matches) )
		{
			$cnt = count($matches[0]) - 1;
			for ( $i = 0; $i <= $cnt; $i++ )
			{
				$this->ip_list[] = $matches[0][$i];
			}
		}
	}

	/**
	 * Check access carrier is mobile
	 */
	private function _check_is_mobile()
	{
		$current_carrier = '';
		$remote_ip       = $this->_get_ip_bit($this->_remote_ip);

		foreach ( $this->ip_list as $ips )
		{
			if ( ($pos = strpos($ips, '=')) !== FALSE )
			{
				$current_carrier = substr($ips, 0, $pos);
				continue;
			}

			$carrier_ip = explode('/', $ips);
			$mask       = $this->_get_mask_bit($carrier_ip[1]);
			$carrier    = $this->_get_ip_bit($carrier_ip[0]);

			if ( ($carrier & $mask) == ( $remote_ip & $mask) )
			{
				$this->_mobile = TRUE;
				$this->_mobile_carrier = $current_carrier;
				break;
			}
		}
	}

	private function _get_mask_bit($bit)
	{
		$mask = 0;
		for( $i = 1; $i <= $bit; $i++){
			$mask++;
			$mask = $mask << 1;
		}
		$mask = $mask << 32-$bit;
		return $mask;
	}

	private function _get_ip_bit($ip)
	{
		$ips = explode('.', $ip);
		$ipb = (@$ips[0] << 24) | (@$ips[1] << 16) | (@$ips[2] << 8) | (@$ips[3]);
		return $ipb;
	}

	private function _get_emoji_list()
	{
		if ( ! $this->emoji_list )
		{
			$list = array(
					0 => "[none]",
					1 => "\xee\x81\x8a",
					2 => "\xee\x81\x89",
					3 => "\xee\x81\x8b",
					4 => "\xee\x81\x88",
					5 => "\xee\x84\xbd",
					6 => "\xee\x91\x83",
					7 => "[霧]",
					8 => "\xee\x90\xbc",
					9 => "\xee\x88\xbf",
					10 => "\xee\x89\x80",
					11 => "\xee\x89\x81",
					12 => "\xee\x89\x82",
					13 => "\xee\x89\x83",
					14 => "\xee\x89\x84",
					15 => "\xee\x89\x85",
					16 => "\xee\x89\x86",
					17 => "\xee\x89\x87",
					18 => "\xee\x89\x88",
					19 => "\xee\x89\x89",
					20 => "\xee\x89\x8a",
					21 => "[ｽﾎﾟｰﾂ]",
					22 => "\xee\x80\x96",
					23 => "\xee\x80\x94",
					24 => "\xee\x80\x95",
					25 => "\xee\x80\x98",
					26 => "\xee\x80\x93",
					27 => "\xee\x90\xaa",
					28 => "\xee\x84\xb2",
					29 => "[ﾎﾟｹｯﾄﾍﾞﾙ]",
					30 => "\xee\x80\x9e",
					31 => "\xee\x90\xb4",
					32 => "\xee\x90\xb5",
					33 => "\xee\x85\x9a",
					34 => "\xee\x90\xae",
					35 => "\xee\x85\x99",
					36 => "\xee\x88\x82",
					37 => "\xee\x80\x9d",
					38 => "\xee\x80\xb6",
					39 => "\xee\x80\xb8",
					40 => "\xee\x85\x93",
					41 => "\xee\x85\x95",
					42 => "\xee\x85\x8d",
					43 => "\xee\x85\x94",
					44 => "\xee\x85\x98",
					45 => "\xee\x85\x96",
					46 => "\xee\x80\xba",
					47 => "\xee\x85\x8f",
					48 => "\xee\x85\x8e",
					49 => "\xee\x85\x91",
					50 => "\xee\x81\x83",
					51 => "\xee\x81\x85",
					52 => "\xee\x81\x84",
					53 => "\xee\x81\x87",
					54 => "\xee\x84\xa0",
					55 => "\xee\x84\xbe",
					56 => "\xee\x8c\x93",
					57 => "\xee\x80\xbc",
					58 => "\xee\x80\xbd",
					59 => "\xee\x88\xb6",
					60 => "[遊園地]",
					61 => "\xee\x8c\x8a",
					62 => "\xee\x94\x82",
					63 => "\xee\x94\x83",
					64 => "[ｲﾍﾞﾝﾄ]",
					65 => "\xee\x84\xa5",
					66 => "\xee\x8c\x8e",
					67 => "\xee\x88\x88",
					68 => "\xee\x80\x88",
					69 => "\xee\x84\x9e",
					70 => "\xee\x85\x88",
					71 => "\xee\x8c\x94",
					72 => "\xee\x84\x92",
					73 => "\xee\x8d\x8b",
					74 => "\xee\x80\x89",
					75 => "\xee\x80\x8a",
					76 => "\xee\x8c\x81",
					77 => "\xee\x84\xaa",
					78 => "[ｹﾞｰﾑ]",
					79 => "\xee\x84\xa6",
					80 => "\xee\x80\xa2",
					81 => "\xee\x88\x8e",
					82 => "\xee\x88\x8d",
					83 => "\xee\x88\x8f",
					84 => "\xee\x90\x99",
					85 => "\xee\x90\x9b",
					86 => "\xee\x80\x90",
					87 => "\xee\x80\x91",
					88 => "\xee\x80\x92",
					89 => "\xee\x88\xb8",
					90 => "\xee\x88\xb7",
					91 => "\xee\x94\xb6",
					92 => "\xee\x80\x87",
					93 => "[ﾒｶﾞﾈ]",
					94 => "\xee\x88\x8a",
					95 => "\xee\x81\x8c",
					96 => "\xee\x81\x8c",
					97 => "\xee\x81\x8c",
					98 => "\xee\x81\x8c",
					99 => "[満月]",
					100 => "\xee\x81\x92",
					101 => "\xee\x81\x8f",
					102 => "\xee\x80\x9c",
					103 => "\xee\x80\xb3",
					104 => "\xee\x88\xb9",
					105 => "\xee\x84\x84",
					106 => "\xee\x84\x83",
					107 => "\xee\x80\x8b",
					108 => "[iﾓｰﾄﾞ]",
					109 => "[iﾓｰﾄﾞ]",
					110 => "\xee\x84\x81",
					111 => "[ﾄﾞｺﾓ]",
					112 => "[ﾄﾞｺﾓﾎﾟｲﾝﾄ]",
					113 => "￥",
					114 => "[FREE]",
					115 => "\xee\x88\xa9",
					116 => "\xee\x80\xbf",
					117 => "[事項有]",
					118 => "[CLR]",
					119 => "\xee\x84\x94",
					120 => "\xee\x88\x92",
					121 => "[位置情報]",
					122 => "\xee\x88\x91",
					123 => "\xee\x88\x90",
					124 => "[Q]",
					125 => "\xee\x88\x9c",
					126 => "\xee\x88\x9d",
					127 => "\xee\x88\x9e",
					128 => "\xee\x88\x9f",
					129 => "\xee\x88\xa0",
					130 => "\xee\x88\xa1",
					131 => "\xee\x88\xa2",
					132 => "\xee\x88\xa3",
					133 => "\xee\x88\xa4",
					134 => "\xee\x88\xa5",
					135 => "\xee\x89\x8d",
					136 => "\xee\x80\xa2",
					137 => "\xee\x8c\xa7",
					138 => "\xee\x80\xa3",
					139 => "\xee\x8c\xa7",
					140 => "\xee\x81\x97",
					141 => "\xee\x81\x99",
					142 => "\xee\x81\x98",
					143 => "\xee\x90\x87",
					144 => "\xee\x90\x86",
					145 => "\xee\x88\xb6",
					146 => "\xee\x80\xbe",
					147 => "\xee\x84\xa3",
					148 => "[ｶﾜｲｲ]",
					149 => "\xee\x80\x83",
					150 => "\xee\x8c\xae",
					151 => "\xee\x84\x8f",
					152 => "\xee\x8c\xb4",
					153 => "\xee\x80\x8d",
					154 => "\xee\x8c\x91",
					155 => "\xee\x8c\xa6",
					156 => "\xee\x88\xb8",
					157 => "\xee\x84\xbc",
					158 => "\xee\x80\xa1",
					159 => "?",
					160 => "!!",
					161 => " [ﾄﾞﾝｯ]",
					162 => "\xee\x8c\xb1",
					163 => "\xee\x8c\xb1",
					164 => "\xee\x8c\xb0",
					165 => "?",
					166 => "?",
					167 => "\xee\x8c\xa4",
					167 => "[ふくろ]",
					168 => "[ﾍﾟﾝ]",
					169 => "[人影]",
					170 => "\xee\x84\x9f",
					171 => "\xee\x81\x8c",
					172 => "[SOON]",
					173 => "[ON]",
					174 => "[end]",
					175 => "\xee\x80\xad",
					176 => "[iｱﾌﾟﾘ]",
					177 => "[iｱﾌﾟﾘ]",
					178 => "[none]",
					179 => "\xee\x80\x86",
					180 => "[財布]",
					181 => "\xee\x8c\x9c",
					182 => "[ｼﾞｰﾝｽﾞ]",
					183 => "[ｽﾉﾎﾞ]",
					184 => "\xee\x8c\xa5",
					185 => "[ﾄﾞｱ]",
					186 => "\xee\x84\xaf",
					187 => "\xee\x80\x8c",
					188 => "\xee\x84\x83\xee\x80\xa2",
					189 => "[ﾚﾝﾁ]",
					190 => "\xee\x8c\x81",
					191 => "\xee\x84\x8e",
					192 => "\xee\x80\xb4",
					193 => "[砂時計]",
					194 => "\xee\x84\xb6",
					195 => "\xee\x8c\xb8",
					196 => "[腕時計]",
					197 => "\xee\x90\x83",
					198 => "\xee\x90\x8a",
					199 => "\xee\x90\x95\xee\x8c\xb1",
					200 => "\xee\x84\x88",
					201 => "\xee\x90\x96",
					202 => "\xee\x90\x8e",
					203 => "\xee\x84\x86",
					204 => "\xee\x80\x8e",
					205 => "\xee\x84\x85",
					206 => "\xee\x90\x85",
					207 => "\xee\x90\x8a",
					208 => "\xee\x90\x86",
					209 => "\xee\x90\x82",
					210 => "\xee\x90\x91",
					211 => "\xee\x90\x93",
					212 => "[NG]",
					213 => "[ｸﾘｯﾌﾟ]",
					214 => "\xee\x89\x8e",
					215 => "\xee\x94\xb7",
					216 => "\xee\x84\x95",
					217 => "\xee\x8c\x95",
					218 => "[ﾘｻｲｸﾙ]",
					219 => "\xee\x89\x8f",
					220 => "\xee\x89\x92",
					221 => "[禁]",
					222 => "\xee\x88\xab",
					223 => "[合]",
					224 => "\xee\x88\xaa",
					225 => "⇔",
					226 => "↑↓",
					227 => "\xee\x85\x97",
					228 => "\xee\x90\xbe",
					229 => "\xee\x80\xbb",
					230 => "\xee\x84\x90",
					231 => "[ﾁｪﾘｰ]",
					232 => "\xee\x8c\x84",
					233 => "[ﾊﾞﾅﾅ]",
					234 => "\xee\x8d\x85",
					235 => "\xee\x84\x90",
					236 => "\xee\x84\x98",
					237 => "\xee\x80\xb0",
					238 => "\xee\x8d\x82",
					239 => "\xee\x81\x86",
					240 => "\xee\x8c\x8b",
					241 => "\xee\x8d\x80",
					242 => "\xee\x8c\xb9",
					243 => "[ｶﾀﾂﾑﾘ]",
					244 => "\xee\x94\xa3",
					245 => "\xee\x81\x95",
					246 => "\xee\x80\x99",
					247 => "\xee\x81\x96",
					248 => "\xee\x90\x84",
					249 => "\xee\x80\x9a",
					250 => "\xee\x84\x8b",
					251 => "\xee\x81\x84",
					252 => "\xee\x84\x87"
				);
			$this->emoji_list = $this->emoji->convertCarrier($list);
		}

		return $this->emoji_list;
	}

	private function _get_emoji_instance()
	{
		require_once(APPPATH . 'libraries/thirdparty/Emoji.php');
		$emoji = HTML_Emoji::getInstance();
		$emoji->setImageUrl(file_link() . 'images/emoji/');
		return $emoji;
	}

}
