<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ====================================================================
 * 
 * OAuth Library for CodeIgniter
 * 
 * @license MIT License
 * @version 0.7.1
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * @usage:
 *    This class is OAuth base class.
 *    Default, we try to Authorize from Default settings auth_name is 'base'.
 *    Customized Authorization:
 *        1. extends this class.
 *        2. set "auth_name" by initialize or default property.
 *        3. If not Authorize yet... ( you can check by is_authorized() method)
 *             3.1 set callback URI in set_callback() method.
 *             3.2 call auth() method with empty argumetns. 
 *             3.3 call auth() method in callback method.
 *             3.4 if returns TRUE, Authorization completed. 
 *             3.5 if you need to save access token, you cal call get_item() method with key name.
 *             
 *           else if user has access token already...
 *             3.1 call auth() method with oauth_token and oauth_token_secret arguments.
 *             3.2 call additional method (API request)
 * ====================================================================
 */

class Oauth
{
		/**
	 * --------------------------------------------------------------
	 * save to
	 * getting access token save to ?
	 *   session        : save to CodeIgniter session
	 *   native_session : save to PHP native session
	 * --------------------------------------------------------------
	 */
	protected $save_to = 'native_session';
	
	/**
	 * --------------------------------------------------------------
	 * OAuth request URI
	 * --------------------------------------------------------------
	 */
	protected $authorize_uri;
	
	/**
	 * --------------------------------------------------------------
	 * OAuth request token URI
	 * --------------------------------------------------------------
	 */
	protected $request_token_uri;
	
	/**
	 * --------------------------------------------------------------
	 * OAuth access token URI
	 * --------------------------------------------------------------
	 */
	protected $access_token_uri;
	
	/**
	 * --------------------------------------------------------------
	 * calback_url
	 * --------------------------------------------------------------
	 */
	protected $callback_url = '';
	
	/**
	 * --------------------------------------------------------------
	 * consumer key
	 * --------------------------------------------------------------
	 */
	protected $consumer_key = '';
	
	/**
	 * --------------------------------------------------------------
	 * consumer secret
	 * --------------------------------------------------------------
	 */
	protected $consumer_secret = '';
	
	/**
	 * --------------------------------------------------------------
	 * signeture method
	 *  default HMAC-SHA1 (twitter)
	 * --------------------------------------------------------------
	 */
	protected $signature_method = 'HMAC-SHA1';
	
	/**
	 * --------------------------------------------------------------
	 * rewuest method
	 * --------------------------------------------------------------
	 */
	protected $request_method = 'GET';
	
	/**
	 * --------------------------------------------------------------
	 * OAuth version
	 * --------------------------------------------------------------
	 */
	protected $version = '1.0';
	
	/**
	 * --------------------------------------------------------------
	 * error stacks
	 * --------------------------------------------------------------
	 */
	protected $_errors = array();
	
	/**
	 * --------------------------------------------------------------
	 * OAuth results
	 * --------------------------------------------------------------
	 */
	protected $request_tokens = array();
	
	/**
	 * --------------------------------------------------------------
	 * Request Quer			exit('KO');y Strings
	 * --------------------------------------------------------------
	 */
	protected $query = '';
	
	/**
	 * --------------------------------------------------------------
	 * OAuth Access token
	 * --------------------------------------------------------------
	 */
	protected $oauth_access_token = FALSE;
	
	/**
	 * --------------------------------------------------------------
	 * OAuth Access token secret
	 * --------------------------------------------------------------
	 */
	protected $oauth_access_token_secret = FALSE;
	
	/**
	 * --------------------------------------------------------------
	 * CodeIgniter Base Instance
	 * --------------------------------------------------------------
	 */
	protected $CI;
	
	/**
	 * --------------------------------------------------------------
	 * OAuth session prefix
	 * --------------------------------------------------------------
	 */
	protected $auth_name = 'base';
	
	/**
	 * --------------------------------------------------------------
	 * Some Request Parameters
	 * --------------------------------------------------------------
	 */
	protected $user_agent = 'CodeIgniter OAuth Client-0.6beta';
	protected $timeout = 30;
	protected $connect_timeout = 30;
	
	/**
	 * ====================================================================
	 * constructor
	 * ====================================================================
	 */
	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->helper('url_helper');
		
		if ( $this->save_to === 'session' && ! isset($this->CI->session) )
		{
			$this->CI->load->library('session');
		}
		
		$this->get_tokens();
		
// some required paramters must be set by initialize method.
	}
	
	/**
	 * ====================================================================
	 * public methods
	 * ====================================================================
	 */
	
	/**
	 * -------------------------------------------------------------------
	 * initialize
	 * set property from array
	 * @param $params
	 * -------------------------------------------------------------------
	 */
	public function initialize($params = array())
	{
		foreach ( $params as $key => $param )
		{
			$this->{$key} = $param;
		}
		//$this->get_tokens();
	}
	
	/**
	 * -------------------------------------------------------------------
	 * set_param
	 * set property from key : value
	 * @param mixed $key
	 * @param $value
	 * -------------------------------------------------------------------
	 */
	public function set_param($key, $value)
	{
		if ( is_array($key) )
		{
			$this->initialize($key);
		}
		else 
		{
			$this->{$key} = $value;
		}
	}
	
	/**
	 * -------------------------------------------------------------------
	 * initialize
	 * set callback uri
	 * @note No need '?' and query_strings.
	 *        Because CI uses segment approche
	 * @param $uri
	 * -------------------------------------------------------------------
	 */
	public function set_callback($uri = '')
	{
		$this->callback_url = $uri;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * display_errors
	 * show errors with prefix
	 * @param string $prefix
	 * @param string $suffix
	 * -------------------------------------------------------------------
	 */
	public function display_errors($prefix = '', $suffix = '')
	{
		$error = '';
		foreach ( $this->_errors as $e )
		{
			$error .= $prefix . $e . $suffix;
		}
		return $error;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * auth
	 * send OAuth request
	 * -------------------------------------------------------------------
	 */
	public function auth(
	                     $oauth_token = null,
	                     $oauth_token_secret = null,
	                     $ext_params = array()
	                       )
	{
		if ( $this->consumer_key === '' || $this->consumer_secret === '' )
		{
			$this->_set_error('undefined consumer_key or consumer_secret.');
			return FALSE;
		}
		else if ( $oauth_token && $oauth_token_secret )
		{
			$this->request_tokens['oauth_token']        = $oauth_token;
			$this->request_tokens['oauth_token_secret'] = $oauth_token_secret;
			return TRUE;
		}

		if ( ! $this->get_item('authorized'))
		{
			// If "oauth_token" Query strings exists, do callack Auth.
			// @notice
			//   CodeIgniter kills $_GET parameters 
			//   when enable_query_strings in Config paramters FALSE.
			//   So that, we get $_GET parameters FROM $_SERVER Global variables.
			if ( $this->_is_callback_auth() === TRUE )
			{
				return $this->callback_auth($ext_params);
			}
		}

		// build base string and paramter query
		$this->query = $this->_build_parameter($this->request_token_uri, $ext_params,  TRUE, TRUE);
		
		if ( ! $this->query )
		{
			return FALSE;
		}
		
		// do request
		$resp = $this->request(
		                       $this->request_token_uri,
		                       array('Authorization: OAuth ' . implode(', ', $this->query))
								);
		
		if ( ! $resp->body )
		{
			$this->_set_error('OAuth Request Failed.');
			return FALSE;
		}
		else if ( $resp->status !== 200 )
		{
			$this->_set_error($resp->status . ':' . $resp->body);
			return FALSE;
		}
		
		
		// If response exists, parse to array
		parse_str($resp->body, $this->request_tokens);
		
		// save tokens
		$this->_save_token();
		
		// redirect to request tokens URI.
		redirect(rtrim($this->authorize_uri, '?') 
								. '?oauth_token='
								. $this->request_tokens['oauth_token']
							);
	}
	
	/**
	 * -------------------------------------------------------------------
	 * callback auth
	 * If Permitted oauth request, process callback
	 * -------------------------------------------------------------------
	 */
	public function callback_auth($param = array())
	{
		$q = ( isset($_SERVER['QUERY_STRING']) )
						? $_SERVER['QUERY_STRING']
						: FALSE;
		if ( ! $q )
		{
			return FALSE;
		}
		parse_str($q, $gets);
		
		// set callback parameters
		$verify = ( isset($gets['oauth_verifier']) ) ? $gets['oauth_verifier'] : '';
		$token  = ( isset($gets['oauth_token']) ) ? $gets['oauth_token'] : '';
		
		// Does oauth token match?
		if ( $token !== $this->get_item('oauth_token') )
		{
			return FALSE;
		}
		
		// build query paramters to get Access token
		$data = array(
						'oauth_token'        => $token,
						'oauth_verifier'     => $verify,
						'oauth_token_secret' => $this->get_item('oauth_token_secret')
							);
		$this->query = $this->_build_parameter($this->access_token_uri, $data, FALSE, TRUE);
	
		// get Access token!
		$resp = $this->request(
								$this->access_token_uri,
								array('Authorization: OAuth ' . implode(', ', $this->query))
							);
		
		if ( ! $resp->body )
		{
			$this->_set_error('Access Token Check Faild.');
			return FALSE;
		}
		else if ( $resp->status !== 200 )
		{
			$this->_set_error($resp->status . ':' . $resp->body);
			return FALSE;
		}
		
		parse_str($resp->body, $this->request_tokens);
		
		$this->request_tokens['authorized'] = TRUE;
		// save Access tokens
		$this->_save_token();

		return TRUE;
		
	}
	
	
	/**
	 * -------------------------------------------------------------------
	 * is_authrized
	 * session has authorized token?
	 * -------------------------------------------------------------------
	 */
	public function is_authorized()
	{
		$data = $this->request_tokens;
		return ( isset($data['authorized']) && $data['authorized'] === TRUE ) ? TRUE : FALSE;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * get_item
	 * get paramters if exists
	 * -------------------------------------------------------------------
	 */
	public function get_item($key)
	{
		return ( isset($this->request_tokens[$key]) ) 
								? $this->request_tokens[$key]
								: FALSE;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * get_all_items
	 * get all items
	 * -------------------------------------------------------------------
	 */
	
	public function get_all_items()
	{
		return $this->request_tokens;
	}
	
	/**
	 * ====================================================================
	 * private methods
	 * ====================================================================
	 */
	
	/**
	 * -------------------------------------------------------------------
	 * _encode_rfc3986
	 * encode string to RFC3986 format
	 * @param string $param
	 * -------------------------------------------------------------------
	 */
	protected function _encode_rfc3986($param)
	{
		return str_replace(
							array('+', '%7E'),
							array(' ', '~'),
							rawurlencode($param)
						);
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _build_parameter
	 * build OAuth request Query Strings
	 * @param string $uri ( include query string )
	 * @param array $arr
	 * @param bool $callback
	 * -------------------------------------------------------------------
	 */
	protected function _build_parameter(
											$uri,
											$arr = array(),
											$callback = TRUE,
											$return_array = FALSE
										)
	{
		// Does consumer key exists?
		if ( $this->consumer_key == '' )
		{
			$this->_set_error('undefined consumer key.');
			return FALSE;
		}
		
		// If secret key exists, pick paramter
		if ( isset($arr['oauth_token_secret']) )
		{
			$secret = $arr['oauth_token_secret'];
			unset($arr['oauth_token_secret']);
		}
		else
		{
			$secret = '';
		}
		
		// base paramters create
		$parameters = array(
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_signature_method' => $this->signature_method,
			'oauth_timestamp'        => time(),
			'oauth_nonce'            => md5(uniqid(mt_rand(), TRUE)),
			'oauth_version'          => $this->version,
		);
		
		// merge additional parameters
		// TODO : use array_merge function?
		foreach ( $arr as $key => $val )
		{
			$parameters[$key] = $val;
		}
		
		// If need callback, add paramter
		if ( $callback === TRUE )
		{
			if ( $this->callback_url === '' )
			{
				$this->_set_error('Undefined Callback URI.');
				return FALSE;
			}
			$parameters['oauth_callback'] = $this->callback_url;
		}
		
		// encode RFC3986
		$params = array_map(array($this, '_encode_rfc3986'), $parameters);

		// sort key from strnatcmp
		uksort($params, 'strnatcmp');

		// build oauth signature
		// encrypt HMAC-SHA1
		$signature = hash_hmac(
								'sha1',
								$this->_build_base_string($uri, $params),
								$this->_generate_key($secret),
								TRUE
							);

		$parameters['oauth_signature'] = base64_encode($signature);
		$query_string = array();
		// format query parameter from encoded array
		foreach ( array_map(array($this, '_encode_rfc3986'), $parameters) as $key => $val )
		{
			$query_string[] = $key . '=' . $val;
		}
		
		if ( $return_array )
		{
			return array_map(array($this, '_quote'), $query_string);
		}
		else 
		{
			return implode('&', $query_string);
		}
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _request
	 * send request from CURL or native file stream
	 * @param string $uri_string
	 * @param array $headers
	 * @param string $post ( url-encoded strings )
	 * -------------------------------------------------------------------
	 */
	protected function request($uri_string, $headers = array(), $post = '')
	{
		// Can you use curl extension?
		if ( extension_loaded('curl') )
		{
			return $this->_request_curl($uri_string, $headers, $post);
		}
		// Else, we use native file stream
		else 
		{
			return $this->_request_socket($uri_string, $headers, $post);
		}
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _request_curl
	 * send request CURL method
	 * @param string $url
	 * @param array $header
	 * @param string post
	 * -------------------------------------------------------------------
	 */
	protected function _request_curl($uri, $header, $post)
	{
		$handle = curl_init();
		curl_setopt_array(
				$handle,
				array(
					CURLOPT_USERAGENT      => $this->user_agent,
					CURLOPT_RETURNTRANSFER => TRUE,
					CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
					CURLOPT_TIMEOUT        => $this->timeout,
					CURLOPT_HTTPHEADER     => (count($header) > 0) ? $header : array('Except:'),
					CURLOPT_HEADER         => FALSE
				)
		);
		
		if ( $this->request_method === 'POST' )
		{
			curl_setopt($handle, CURLOPT_POST, TRUE);
			if ( $post != '' )
			{
				curl_setopt($handle, CURLOPT_POSTFIELDS, $post);
			}
		}
		curl_setopt($handle, CURLOPT_URL, $uri);

		$resp = curl_exec($handle);

		if ( ! $resp )
		{
			$this->_set_error(curl_error($handle));
			$resp = FALSE;
		}
		
		$response         = new stdClass;
		$response->status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$response->body   = $resp;
		curl_close($handle);
		
		return $response;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _request_socket
	 * send request by fsockopen
	 * @param string $url
	 * @param array $header
	 * @param string post
	 * -------------------------------------------------------------------
	 */
	protected function _request_socket($uri, $header = array(), $post)
	{
		// parse URLs
		$URL = parse_url($uri);
		
		$scheme = $URL['scheme'];
		$path   = $URL['path'];
		$host   = $URL['host'];
		$query  = (isset($URL['query'])) ? '?' . $URL['query'] : '';
		$port   = (isset($URL['port'])) ? $URL['port'] : ($scheme == 'https') ? 443 : 80;
		
		// build request-line-header
		$request = $this->request_method . ' ' . $path . $query . ' HTTP/1.1' . "\r\n"
						. 'Host: ' . $host . "\r\n"
						. 'User-Agent: ' . $this->user_agent . "\r\n";
		
		if ( count($header) > 0 )
		{
			foreach ( $header as $head )
			{
				$request .= $head . "\r\n";
			}
		}
		
		if ( $this->request_method === 'POST' )
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
		
		$fp = @fsockopen($host, $port, $errno, $errstr, (float)$this->timeout);
		
		if ( ! $fp )
		{
			$this->set_error($errno . ': ' . $errstr);
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
		
		if ( count($exp) < 2 )
		{
			$body   = FALSE;
			$status = FALSE;
			$this->set_error('Nothing Response Body.');
		}
		else 
		{
			$status = preg_replace('#HTTP/[0-9\.]+\s([0-9]+)\s#u', '$1', $exp[0]);
			$body   = implode("\r\n\r\n", array_slice($exp, 1));
		}
		
		$response         = new stdClass;
		$response->status = (int)$status;
		$response->body   = $body;
		
		return $response;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * build_base_string
	 * @param string $uri
	 * @param array $params_array
	 * -------------------------------------------------------------------
	 */
	protected function _build_base_string($uri, $params_array = array())
	{
		$p = array();
		foreach ( $params_array as $key => $val )
		{
			$p[] = $key . '=' . $val;
		}
		
		$ret = array_map(
						array($this, '_encode_rfc3986'),
						array(
							$this->request_method,
							$uri,
							implode('&', $p)
						)
					);
					
		return implode('&', $ret);
	}
	
	/**
	 * -------------------------------------------------------------------
	 * generate authorize key
	 * @param string $secret
	 * -------------------------------------------------------------------
	 */
	protected function _generate_key($secret = '')
	{
		$key = array_map(
						array($this, '_encode_rfc3986'),
						array(
							$this->consumer_secret,
							($secret) ? $secret : ''
						)
					);
					
		return implode('&', $key);
	}
	
	/**
	 * -------------------------------------------------------------------
	 * set_error
	 * @param string $message
	 * -------------------------------------------------------------------
	 */
	protected function _set_error($message)
	{
		$this->_errors[] = $message;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _save_token
	 * save oauth token data
	 * -------------------------------------------------------------------
	 */
	protected function _save_token()
	{
		if ($this->save_to === 'session')
		{
			$this->CI->session->set_userdata($this->auth_name, $this->request_tokens);
		}
		else if ($this->save_to === 'native_session')
		{
			// use PHP native session
			$this->_sess_start();
			$_SESSION[$this->auth_name] = $this->request_tokens;
		}
	}
	
	/**
	 * -------------------------------------------------------------------
	 * get_tokens
	 * get oauth token data
	 * -------------------------------------------------------------------
	 */
	public function get_tokens()
	{
		if ( $this->save_to === 'native_session' )
		{
			$this->_sess_start();
			
			$data = (isset($_SESSION[$this->auth_name]))
			         ? $_SESSION[$this->auth_name]
			         : array();
		}
		else if ( $this->save_to === 'session' )
		{
			if ( $this->CI->session->userdata($this->auth_name) )
			{
				parse_str($this->CI->session->userdata($this->auth_name), $data);
			}
			else
			{
				$data = array();
			}
		}
		if ( count($data) === 0 )
		{
			return FALSE;
		}
		$this->request_tokens = $data;
		
		if ( ! isset($data['authorized']) )
		{
			return $data;
		}
		return FALSE;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _is_callback_auth
	 * check callbacked authorize
	 * -------------------------------------------------------------------
	 */
	protected function _is_callback_auth()
	{
		$q = (isset($_SERVER['QUERY_STRING']))
		      ? $_SERVER['QUERY_STRING']
		      : FALSE;
		
		if ( ! $q )
		{
			return FALSE;
		}
		
		parse_str($q, $get);
		
		return (isset($get['oauth_token']) && isset($get['oauth_verifier']))
		        ? TRUE
		        : FALSE;
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _sess_start
	 * safety sesson start method
	 * -------------------------------------------------------------------
	 */
	protected function _sess_start()
	{
		if ( ! isset($_SESSION) )
		{
			session_start();
			session_regenerate_id(TRUE);
		}
	}
	
	/**
	 * -------------------------------------------------------------------
	 * _quote
	 * parameter quotes
	 * -------------------------------------------------------------------
	 */
	protected function _quote($str)
	{
		return preg_replace('/(.+)=(.+)?/', '$1="$2"', $str);
	}
	
	/**
	 * -------------------------------------------------------------------
	 * unset tokens
	 * delete sesson values
	 * -------------------------------------------------------------------
	 */
	public function unset_tokens()
	{
		if ( $this->save_to === 'session' )
		{
			$this->CI->session->unset_userdata($this->auth_name);
		}
		else if ( $this->save_to === 'native_session' )
		{
			$this->_sess_start();
			
			if ( isset($_SESSION[$this->auth_name]) )
			{
				unset($_SESSION[$this->auth_name]);
			}
		}
	}
}
