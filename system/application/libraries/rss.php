<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ====================================================
 * RSS Library
 *generate or parse RSS format utility class
 *
 * depend on SimpleXML functions (PHP 5:parse only)
 * @compare version RSS1.0, RSS2.0, Atom
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @create 2010/05/07
 * @license MIT license
 * @version 0.8
 * ====================================================
 **/
class Rss
{
	// RSS exists uri
	protected  $file_url;
	// CodeIgniter object
	protected $CI;
	// some format RSS
	protected $template;
	// date foramat
	protected $date_format = 'Y-m-d H:i:s';

	function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('date_helper');
		$this->template = new RssTemplate();
	}

	/**
	 * set_file
	 * $file_url setter
	 */
	public function set_file($file_url)
	{
		$this->file_url = $file_url;
	}

	/**
	 * set_date_format
	 * set parse date format
	 * @param $format
	 */
	public function set_date_format($format)
	{
		$this->date_format = $format;
	}

	/**
	 * get_file
	 * $file_url_getter
	 */
	public function get_file()
	{
		return $this->file_url;
	}

	/**
	 * load
	 * get XML data and parse some format
	 * @param stirng $uri : file_url
	 */
	public function load($uri = FALSE, $sort_mode = 'desc')
	{
		if ($uri)
		{
			$this->file_url = $uri;
		}
		$data = http_request($this->file_url);

		if (!$data)
		{
			return FALSE;
		}
		else
		{
			return $this->_parse_xml_format(trim($data), $sort_mode);
		}
	}

	/**
	 * load_from_string
	 * get_XML data from string data and parse some format
	 * @param string $str : xml strings
	 */
	public function load_from_string($str, $sort_mode = 'desc')
	{
		return $this->_parse_xml_format(trim($str), $sort_mode);
	}

	/**
	 * _parse_xml_format
	 * parse some format
	 * @param string $data : xml strings
	 * @param : string $sort_mode : sort by asc or desc
	 * @access private
	 */
	private function _parse_xml_format($data, $sort_mode)
	{
		$XML = new SimpleXMLElement($data);

		if (!$XML)
		{
			return FALSE;
		}

		// get root node name
		$root = strtolower($XML->getName());

		// RSS 1.0 has item data in first property
		if ($root === 'rdf')
		{
			$ret  = $this->_parse_rdf($XML);
		}
		// RSS 2.0 has item data in channel child
		else if ($root === 'rss')
		{
			$ret =  $this->_parse_rss2($XML);
		}
		// Atom feed has feed node
		else if ($root === 'feed')
		{
			$ret = $this->_parse_atom($XML);
		}

		return $ret;
	}

	/**
	 * _parse_rdf
	 * parse to array RSS1.0
	 * @param SimpleXML $XML
	 */
	private function _parse_rdf($XML)
	{
		$ns = $XML->getDocNamespaces();
		array_unshift($ns, '');

		// set first XML data
		$data = array(
			'channel' => array(
				'title'			=> trim((string)$XML->channel->title),
				'link'				=> trim((string)$XML->channel->link),
				'description'	=> trim((string)$XML->channel->description)
			)
		);
		// set item data
		foreach ($XML->item as $v)
		{
			$tmp = array();
			foreach ($ns as $n)
			{
				foreach ($v->children($n) as $c)
				{
					$name = $c->getName();
					if ($name === 'date')
					{
						$tmp[$name] = date($this->date_format, strtotime((string)$c));
					}
					else
					{
						$tmp[$name] = (string)$c;
					}

				}
			}
			$tmp = array_map('trim', $tmp);
			$data['item'][] = $tmp;
		}

		return $data;
	}

	/**
	 * _parse_rss2
	 * parse to array RSS2.0
	 * @param SimpleXMLElement $XML
	 */
	private function _parse_rss2($XML)
	{
		$ns = $XML->getDocNamespaces();
		array_unshift($ns, '');

		// set item data
		$items = array();
		$channels = array();
		foreach ($XML->channel->children() as $v)
		{
			$name = $v->getName();
			if ($name === 'item')
			{
				$items[] = $this->_parse_rss2_item($v, $ns);
			}
			else
			{
				$channels[$name] = (string)$v;
			}
		}
		return array(
			'channel'	=> array_map('trim', $channels),
			'item'			=> $items
		);
	}

	private function _parse_rss2_item($ITEM, $ns)
	{
		$tmp = array();
		foreach ($ns as $n)
		{
			foreach ($ITEM->children($n) as $c)
			{
				$name = $c->getName();
				$tmp[$name] = (string)$c;
			}
		}
		if (isset($tmp['pubDate']))
		{
			$tmp['pubDate'] = date($this->date_format, strtotime($tmp['pubDate']));
		}
		return array_map('trim', $tmp);
	}

	private function _parse_atom($XML)
	{
		$ns = $XML->getDocNamespaces();
		array_unshift($ns, '');

		// set item data
		$entries = array();
		$channels = array();
		foreach ($XML->children() as $v)
		{
			$name = $v->getName();
			if ($name === 'entry')
			{
				$entries[] = $this->_parse_atom_entry($v, $ns);
			}
			else
			{
				$channels[$name] = (string)$v;
			}
		}
		return array(
			'channel'	=> array_map('trim', $channels),
			'item'			=> $entries
		);
	}

	private function _parse_atom_entry($entry, $ns)
	{
		$tmp = array();
		foreach ($ns as $n)
		{
			foreach ($entry->children($n) as $c)
			{
				$name = $c->getName();
				$att = $c->attributes();
				if ($name === 'link')
				{
					$c = $att['href'];
				}
				if (count($c->children()) > 0)
				{
					$tmp[$name] = $this->_parse_atom_entry($c, $ns);
				}
				else
				{
					$tmp[$name] = (string)$c;
				}
			}
		}
		if (isset($tmp['updated']))
		{
			$tmp['updated'] = date($this->date_format, strtotime((string)$c));
		}
		return array_map('trim', $tmp);
	}

	function _sort_desc($a, $b)
	{
		if (isset($a['timestamp']) && isset($b['timestamp']))
		{
			return ((int)$a['timestamp'] < (int)$b['timestamp']) ? 1 : -1;
		}
		return 1;
	}
	function _sort_asc($a, $b)
	{
		if ($a['timestamp'] && $b['timestamp'])
		{
			return ((int)$a['timestamp'] < (int)$b['timestamp']) ? -1 : 1;
		}
		return 1;
	}

	/**
	 * output_rss : create RSS XML from data
	 * @param $version - output RSS version (only 1 or 2)
	 * @param $data - RSS data array
	 * 		- RSS data array fomat :
	 * 			$data = array(
	 * 				array(
	 * 					'title'		=> title string,
	 * 					'url'			=> link url,
	 * 					'description	=> link description,
	 * 					'date'			=> update time string
	 * 				),
	 * 				...
	 * 			)
	 */
	function output_rss($version = 'A', $data = array(), $site_info = array(), $to_file = FALSE)
	{
		$rss = null;

		if ($version == 1) // RSS1.0
		{
			$rss = $this->_build_RSS1($data, $site_info);
		}
		else if ($version == 2) // RSS2.0
		{
			$rss = $this->_build_RSS2($data, $site_info);
		}
		else if ($version == 'A') // Atom0.3
		{
			$rss = $this->_build_Atom($data, $site_info);
		}

		if ($rss)
		{
			if ($to_file)
			{
				$this->CI->load->helper('file');
				write_file('files/feed.atom', $rss);
			}
			else
			{
				/**
				 * RSS header feature
				 * some User-agent unrecognize application/rss+xml currently...
				 * so, set appliation/xml set.
				 */
				header('Content-Type: application/xml; charset=UTF-8');
				echo $rss;
				exit();
			}
		}
	}

	function _build_RSS1($data, $site_info)
	{
		return $this->template->make_RSS1($data, $site_info);
	}

	function _build_RSS2($data, $site_info)
	{
		return $this->template->make_RSS2($data, $site_info);
	}

	function _build_Atom($data, $site_info)
	{
		return $this->template->make_Atom($data, $site_info);
	}
}

/**
 * RssTemplate Class
 * make some RSS formatted XML
 **/
class RssTemplate
{
	public function make_RSS1($rss, $site_info)
	{
		$CI =& get_instance();

		$output = array(
			'<?xml version="1.0" encoding="UTF-8"?>',
			'<rdf:RDF xmlns="http://purl.org/rss/1.0/" ',
			$this->tab() .'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ',
			$this->tab() .'xmlns:dc="http://purl.org/dc/elements/1.1" ',
			$this->tab() .'xmlns:content="http://purl.org/rss/1.0/modules/content/" ',
			$this->tab() .'xml:lang="ja">',
			$this->tab() .'<channel rdf:about="' . $CI->config->site_url('blog/feed/rss') . '">',
			$this->tab(2) .'<title>' . $site_info['site_title'] . '</title>',
			$this->tab(2) .'<link>' . $CI->config->site_url('blog/entries') . '</link>',
			$this->tab(2) .'<description>' . ((array_key_exists('description', $site_info)) ? $site_info['description'] : '') . '</description>',
			$this->tab(2) .'<dc:date>' . date('c', time()) . '</dc:date>',
			$this->tab(2) .'<dc:language>ja</dc:language>',
			$this->tab(2) .'<items>',
			$this->tab(2) .'<rdf:Seq>'
		);
		foreach ($rss as $v)
		{
			$output[] = $this->tab(2) .'<rdf:li rdf:resource="' . $v['url'] . '" />';
		}

		$output[] = $this->tab(2) . '</rdf:Seq>';
		$output[] = $this->tab(2) . '</items>';
		$output[] = $this->tab() . '</channel>';

		foreach ($rss as $item)
		{
			$output[] = $this->tab() .'<item rdf:about="' . $item['url'] . '">';
			$output[] = $this->tab(2) .'<title>' . $item['title'] . '</title>';
			$output[] = $this->tab(2) .'<link>' . $item['url'] . '</link>';
			$output[] = $this->tab(2) .'<description><![CDATA[' . $item['description'] . ']]></description>';
			$output[] = $this->tab(2) .'<dc:date>' . date('c', strtotime($item['date'])) . '</dc:date>';
			$output[] = $this->tab().'</item>';
		}

		$output[] = '</rdf:RDF>';

		return implode("\n", $output);
	}

	public function make_RSS2($rss, $site_info)
	{
		$CI =& get_instance();

		$output = array(
			'<?xml version="1.0" encoding="UTF-8"?>',
			'<rss version="2.0" ',
			$this->tab() . 'xmlns:dc="http://purl.org/dc/elements/1.1/" ',
			$this->tab() . 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" ',
			$this->tab() . 'xmlns:admin="http://webns.net/mvcb/" ',
			$this->tab() . 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">',
			$this->tab() . '<channel>',
			$this->tab(2) . '<title>' . $site_info['site_title'] . '</title>',
			$this->tab(2) . '<link>' . $CI->config->site_url('blog/entries') . '</link>',
			$this->tab(2) . '<description>' . ((array_key_exists('description', $site_info)) ? $site_info['description'] : '') . '</description>',
			$this->tab(2) . '<dc:language>ja</dc:language>',
			$this->tab(2) . '<pubDate>' . date('r', time()) . '</pubDate>'
		);

		foreach ($rss as $item)
		{
			$output[] = $this->tab(2) . '<item>';
			$output[] = $this->tab(3) . '<title>' . $item['title'] . '</title>';
			$output[] = $this->tab(3) . '<link>' . $item['url'] . '</link>';
			$output[] = $this->tab(3) . '<guid isPermaLink="false">' . $item['url'] . '</guid>';
			$output[] = $this->tab(3) . '<description>' . $item['description'] .'</description>';
			$output[] = $this->tab(3) . '<dc:creator>' . ((array_key_exists('author', $item)) ? $item['author'] : '') . '</dc:creator>';
			$output[] = $this->tab(3) . '<pubDate>' . date('r', strtotime($item['date']))  . '</pubDate>';
			$output[] = $this->tab(3) . '<category>' . ((isset($item['category'])) ? $item['category'] : '') . '</category>';
			$output[] = $this->tab(2) . '</item>';
		}

		$output[] = $this->tab() . '</channel>';
		$output[] = '</rss>';

		return implode("\n", $output);
	}

	public function make_Atom($rss, $site_info)
	{
		$CI =& get_instance();

		$output = array(
			'<?xml version="1.0" encoding="UTF-8"?>',
			'<feed xmlns="http://www.w3.org/2005/Atom" ',
			$this->tab() . 'xmlns:thr="http://purl.org/syndication/thread/1.0" ',
			$this->tab() . 'xml:lang="ja">',
			$this->tab() . '<title>' . $site_info['site_title'] . '</title>',
			$this->tab() . '<link rel="alternate" type="text/html" href="' . $CI->config->site_url('blog/entries') .'" />',
			$this->tab() . '<updated>' . date('c', time()) . '</updated>',
			$this->tab() . '<id>' . $CI->config->site_url('blog/feed/atom') . '</id>',
			$this->tab() . '<author>',
			$this->tab(2) . '<name>' . $site_info['site_title'] . '</name>',
			$this->tab() . '</author>'
		);

		foreach ($rss as $item)
		{
			$output[] = $this->tab() . '<entry>';
			$output[] = $this->tab(2) . '<author><name>' . $item['author'] . '</name></author>';
			$output[] = $this->tab(2) . '<title>' . $item['title'] . '</title>';
			$output[] = $this->tab(2) . '<link rel="alternate" type="text/html" href="' . $item['url'] . '" />';
			$output[] = $this->tab(2) . '<id>' . $item['url'] . '</id>';
			$output[] = $this->tab(2) . '<updated>' . date('c', strtotime($item['date'])) . '</updated>';
			$output[] = $this->tab(2) . '<category scheme="' . $CI->config->site_url('blog') . '" term="' . ((isset($item['category'])) ? $item['category'] : '') . '" />';
			$output[] = $this->tab(2) . '<summary type="html"><![CDATA[' . $item['description'] . ']]></summary>';
			$output[] = $this->tab() . '</entry>';
		}

		$output[] = '</feed>';

		return implode("\n", $output);
	}

	public function tab($times = 1)
	{
		$tab = "\t";
		$cnt = 0;
		while(++$cnt < $times)
		{
			$tab .= "\t";
		}
		return $tab;
	}
}
