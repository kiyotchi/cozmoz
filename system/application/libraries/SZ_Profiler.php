<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SZ_Profiler extends CI_Profiler
{
	function __construct()
	{
		parent::CI_Profiler();
	}
	
	public function run()
	{
		$CI =& get_instance();
		
//		if ( get_parent_class($CI) !== 'SZ_Controller' )
//		{
//			return parent::run();
//		}
		$output = "<div id='codeigniter_profiler' style='clear:both;background-color:#fff;padding:10px;margin:0;height:100%'>";
		$output .= '<ul class="cp_tabs">';
		$output .= '<li><a href="#" class="active">システムデータ</a></li>';
		$output .= '<li><a href="#">入力パラメータ</a></li>';
		$output .= '<li><a href="#">データベース</a></li>';
		$output .= '</ul>';
		
		$output .= '<div class="cp_content">';

		$output .= $this->_compile_uri_string();
		$output .= $this->_compile_controller_info();
		$output .= $this->_compile_memory_usage();
		$output .= $this->_compile_benchmarks();
		
		$output .= '</div>';
		$output .= '<div class="cp_content ih">';
		
		$output .= $this->_compile_get();
		$output .= $this->_compile_post();
		//$output .= $this->_compile_cookie();
		
		$output .= '</div>';
		$output .= '<div class="cp_content ih">';
		$output .= '<p style="margin-top : 0;">セルをクリックでSQL実行</p>';
		$output .= '<div id="query_result" class="ih">';
		$output .= '<pre></pre></div>';
		
		$output .= $this->_compile_queries();
		
		$output .= '</div>';
		$output .= '</div>';
		
		// escape single quote
		$output = preg_replace(array('/\n/', "/'/"), array('', "\'"), $output);
		$base_url = file_link();
		$time     = time();
		
		$script = <<<END
<script type="text/javascript">
if ( window.getInstance ) {
(function() {
var win = window.open("{$base_url}js/index.html?t={$time}", "CodeIgniterProfiler", "width=800,height=600,left=0,top=0,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes"),
	doc = win.document,
	head,
	link,
	script;

doc.open('text/html');
doc.write('{$output}');
doc.close();

doc.title = 'seezoo profiler';
head = win.document.getElementsByTagName('head')[0];
link = win.document.createElement('link');
link.type = 'text/css';
link.rel  = 'stylesheet';
link.href = getInstance().config.baseUrl() + 'css/seezoo_profiler.css?t={$time}';
script = win.document.createElement('script');
script.type = 'text/javascript';
script.src  = getInstance().config.baseUrl() + 'js/seezoo_profiler.js?t={$time}';
script.charset= 'UTF-8';

head.appendChild(link);
head.appendChild(script);
})();
}
</script>
END;
		return $script;
		
	}
	
	function _compile_queries()
	{
		$dbs = array();

		// Let's determine which databases are currently connected to
		foreach (get_object_vars($this->CI) as $CI_object)
		{
			if (is_object($CI_object) && is_subclass_of(get_class($CI_object), 'CI_DB') )
			{
				$dbs[] = $CI_object;
			}
		}
					
		if (count($dbs) == 0)
		{
			$output  = "\n\n";
			$output .= '<fieldset style="border:1px solid #B6D4A7;margin:0 0 20px 0;padding:0;background-color:#d4f8c3">';
			$output .= "\n";
			$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
			$output .= '<p style="color:#0000FF;border-bottom:1px dotted #0000FF;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_queries').'&nbsp;&nbsp;</p>';
			$output .= "\n";		
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
			$output .= "<tr><td width='100%' style='color:#0000FF;font-weight:normal;background-color:transparent;'>".$this->CI->lang->line('profiler_no_db')."</td></tr>\n";
			$output .= "</table>\n";
			$output .= "</fieldset>";
			
			return $output;
		}
		
		// Load the text helper so we can highlight the SQL
		$this->CI->load->helper('text');

		// Key words we want bolded
		$highlight = array('SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR', 'HAVING', 'OFFSET', 'NOT&nbsp;IN', 'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')');

		$output  = "\n\n";
			
		foreach ($dbs as $db)
		{
			$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0;margin:0 0 20px 0;background-color:#d4f8c3">';
			$output .= "\n";
			$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
			$output .= '<p style="color:#0000FF;border-bottom:1px dotted #0000FF;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_database').':&nbsp; '.$db->database.'&nbsp;&nbsp;&nbsp;'.$this->CI->lang->line('profiler_queries').': '.count($this->CI->db->queries).'&nbsp;&nbsp;&nbsp;</p>';
			$output .= "\n";		
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%' class='queries'>\n";
		
			if (count($db->queries) == 0)
			{
				$output .= "<tr><td width='100%' style='color:#0000FF;font-weight:normal;background-color:transparent;'>".$this->CI->lang->line('profiler_no_queries')."</td></tr>\n";
			}
			else
			{
				$cnt = 0;
				foreach ($db->queries as $key => $val)
				{					
					$time = number_format($db->query_times[$key], 4);
					
					$str_val = htmlspecialchars($val, ENT_QUOTES, config_item('charset'));
					$val = highlight_code($val, ENT_QUOTES);
					
	
					foreach ($highlight as $bold)
					{
						$val = str_replace($bold, '<strong>'.$bold.'</strong>', $val);	
					}
					
					if ( ++$cnt == count($db->queries) )
					{
						$border = 'none';
					}
					else 
					{
						$border = '1px solid #ccc';
					}
					
					$output .= "<tr><td width='1%' valign='top' style='border-bottom:{$border};color:#990000;font-weight:normal;background-color:transparent;'>"
								.$time
								."&nbsp;&nbsp;</td><td style='border-bottom:{$border};border-left:1px solid #ccc;padding-left:10px;color:#000;font-weight:normal;background-color:transparent;' class='sql_cell'>"
								.$val
								."<input type='hidden' value='{$str_val}' />"
								."</td></tr>\n";
				}
			}
			
			$output .= "</table>\n";
			$output .= "</div>";
			$output .= "</fieldset>";
			
		}
		
		return $output;
	}
	
	function _compile_uri_string()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0px;margin:0 0 20px 0;background-color:#d4f8c3">';
		$output .= "\n";
		$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
		$output .= '<p style="color:#000;border-bottom:1px dotted #000;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_uri_string').'&nbsp;&nbsp;</p>';
		$output .= "\n";
		
		if ($this->CI->uri->uri_string == '')
		{
			$output .= "<div style='color:#000;font-weight:normal;padding:15px 8px'>".$this->CI->lang->line('profiler_no_uri')."</div>";
		}
		else
		{
			$output .= "<div style='color:#000;font-weight:normal;padding:15px 8px'>".$this->CI->uri->uri_string."</div>";
		}
		
		$output .= "</div>";
		$output .= "</fieldset>";

		return $output;	
	}
	
	function _compile_controller_info()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0;margin:0 0 20px 0;background-color:#d4f8c3">';
		$output .= "\n";
		$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
		$output .= '<p style="color:#cd6f00;border-bottom:1px dotted #cd6f00;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_controller_info').'&nbsp;&nbsp;</p>';
		$output .= "\n";
		
		$output .= "<div style='color:#cd6f00;font-weight:normal;padding:15px 8px'>".$this->CI->router->fetch_class()."/".$this->CI->router->fetch_method()."</div>";				

		$output .= "</div>";
		$output .= "</fieldset>";

		return $output;	
	}
	
	function _compile_memory_usage()
	{
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0;margin:0 0 20px 0;background-color:#d4f8c3">';
		$output .= "\n";
		$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
		$output .= '<p style="color:#5a0099;border-bottom:1px dotted #5a0099;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_memory_usage').'&nbsp;&nbsp;</p>';
		$output .= "\n";
		
		if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '')
		{
			$output .= "<div style='color:#5a0099;font-weight:normal;padding:15px 8px'>".number_format($usage).' bytes</div>';
		}
		else
		{
			$output .= "<div style='color:#5a0099;font-weight:normal;padding:15px 8px'>".$this->CI->lang->line('profiler_no_memory_usage')."</div>";				
		}
		
		$output .= "</div>";
		$output .= "</fieldset>";

		return $output;
	}
	
 	function _compile_benchmarks()
 	{
  		$profile = array();
 		foreach ($this->CI->benchmark->marker as $key => $val)
 		{
 			// We match the "end" marker so that the list ends
 			// up in the order that it was defined
 			if (preg_match("/(.+?)_end/i", $key, $match))
 			{ 			
 				if (isset($this->CI->benchmark->marker[$match[1].'_end']) AND isset($this->CI->benchmark->marker[$match[1].'_start']))
 				{
 					$profile[$match[1]] = $this->CI->benchmark->elapsed_time($match[1].'_start', $key);
 				}
 			}
 		}

		// Build a table containing the profile data.
		// Note: At some point we should turn this into a template that can
		// be modified.  We also might want to make this data available to be logged
	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0;margin:0 0 20px 0;background-color:#d4f8c3">';
		$output .= "\n";
		$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
		$output .= '<p style="color:#990000;border-bottom:1px dotted #990000;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_benchmarks').'&nbsp;&nbsp;</p>';
		$output .= "\n";			
		$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
		
		$cnt = 0;
		foreach ($profile as $key => $val)
		{
			$key = ucwords(str_replace(array('_', '-'), ' ', $key));
			if ( ++$cnt == count($profile) )
			{
				$border = 'none';
			}
			else 
			{
				$border = '1px solid #ccc';
			}
			$output .= "<tr><td width='50%' style='border-bottom:{$border};color:#000;font-weight:bold;font-size:0.9em;background-color:#d4f8c3;'>".$key."&nbsp;&nbsp;</td><td width='50%' style='border-left:1px solid #ccc;border-bottom:{$border};padding-left:10px;color:#990000;font-weight:normal;background-color:#d4f8c3;'>".$val."</td></tr>\n";
			
		}
		
		$output .= "</table>\n";
		$output .= "</div>";
		$output .= "</fieldset>";
 		
 		return $output;
 	}
	
	function _compile_post()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0;margin:0 0 20px 0;background-color:#d4f8c3">';
		$output .= "\n";
		$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
		$output .= '<p style="color:#009900;border-bottom:1px dotted #990000;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_post_data').'&nbsp;&nbsp;</p>';
		$output .= "\n";
				
		if (count($_POST) == 0)
		{
			$output .= "<div style='color:#009900;font-weight:normal;padding:15px 8px'>".$this->CI->lang->line('profiler_no_post')."</div>";
		}
		else
		{
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
			
			$cnt = 0;
			foreach ($_POST as $key => $val)
			{
				if ( ! is_numeric($key))
				{
					$key = "'".$key."'";
				}
				if ( ++$cnt == count($_POST) )
				{
					$border = 'none';
				}
				else 
				{
					$border = '1px solid #ccc';
				}
			
				$output .= "<tr><td width='50%' style='border-bottom:{$border};color:#000;background-color:transparent;'>&#36;_POST[".$key."]&nbsp;&nbsp; </td><td width='50%' style='border-left:solid 1px #ccc;border-bottom:{$border};color:#009900;padding-left:10px;font-weight:normal;background-color:transparent;'>";
				if (is_array($val))
				{
					$output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, true))) . "</pre>";
				}
				else
				{
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>\n";
			}
			
			$output .= "</table>\n";
		}
		$output .= '</div>';
		$output .= "</fieldset>";

		return $output;	
	}
	
	function _compile_get()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0;margin:0 0 20px 0;background-color:#d4f8c3">';
		$output .= "\n";
		$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
		$output .= '<p style="color:#cd6e00;border-bottom:1px dotted #cd6e00;margin:0;padding:3px">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_get_data').'&nbsp;&nbsp;</p>';
		$output .= "\n";
				
		if (!$_GET || count($_GET) == 0)
		{
			$output .= "<div style='color:#cd6e00;font-weight:normal;padding:15px 8px'>".$this->CI->lang->line('profiler_no_get')."</div>";
		}
		else
		{
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
		
			$cnt = 0;
			foreach ($_GET as $key => $val)
			{
				if ( ! is_numeric($key))
				{
					$key = "'".$key."'";
				}
				if ( ++$cnt == count($_GET) )
				{
					$border = 'none';
				}
				else 
				{
					$border = '1px solid #ccc';
				}
			
				$output .= "<tr><td width='50%' style='border-bottom:{$border};color:#000;background-color:transparent;'>&#36;_GET[".$key."]&nbsp;&nbsp; </td><td width='50%' style='border-left:solid 1px #ccc;border-bottom:{$border};padding-left:10px;color:#009900;font-weight:normal;background-color:transparent;'>";
				if (is_array($val))
				{
					$output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, true))) . "</pre>";
				}
				else
				{
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>\n";
			}
			
			$output .= "</table>\n";
		}
		$output .= '</div>';
		$output .= "</fieldset>";

		return $output;	
	}
	
	function _compile_cookie()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #B6D4A7;padding:0;margin:0 0 20px 0;background-color:#d4f8c3">';
		$output .= "\n";
		$output .= '<div style="border:1px solid #DBFFC9;padding:1px;">';
		$output .= '<p style="color:#c3c;border-bottom:1px dotted #cc33cc;margin:0;padding:3px">&nbsp;&nbsp;Cookieデータ&nbsp;&nbsp;</p>';
		$output .= "\n";
				
		if (!$_COOKIE || count($_COOKIE) == 0)
		{
			$output .= "<div style='color:#cd6e00;font-weight:normal;padding:15px 8px'>Cookieデータはありません</div>";
		}
		else
		{
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
		
			$cnt = 0;
			foreach ($_COOKIE as $key => $val)
			{
				if ( ! is_numeric($key))
				{
					$key = "'".$key."'";
				}
				if ( ++$cnt == count($_COOKIE) )
				{
					$border = 'none';
				}
				else 
				{
					$border = '1px solid #ccc';
				}
			
				$output .= "<tr><td width='50%' style='border-bottom:{$border};color:#000;background-color:transparent;'>&#36;_COOKIE[".$key."]&nbsp;&nbsp; </td><td width='50%' style='border-left:solid 1px #ccc;border-bottom:{$border};padding-left:10px;color:#009900;font-weight:normal;background-color:transparent;'>";
				if (is_array($val))
				{
					$output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, true))) . "</pre>";
				}
				else
				{
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>\n";
			}
			
			$output .= "</table>\n";
		}
		$output .= '</div>';
		$output .= "</fieldset>";

		return $output;	
	}
	
}
