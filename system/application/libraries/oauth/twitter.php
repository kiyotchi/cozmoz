<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ========================================================================================
 * 
 * CodeIgniter Twttter Oauth Client
 * 
 * @package CodeIgniter Library
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */

// depends base OAuth Class
require_once(APPPATH . 'libraries/oauth/oauth.php');

class Twitter extends Oauth
{
	const REQUEST_BASE        = 'https://api.twitter.com';
	const REQUEST_TOKEN_PATH  = '/oauth/request_token';
	const AUTHORIZE_PATH      = '/oauth/authorize';
	const AUTHENTICATE_PATH   = '/oauth/authenticate';
	const ACCESS_TOKEN_PATH   = '/oauth/access_token';
	const ACCOUNT_VERIFY_PATH = '/account/verify/credentials.json';
	const UPDATE_PATH         = '/1/statuses/update';
	
	protected $auth_name = 'twitter';
	
	protected $response_format = 'xml';
	
	public function __construct()
	{
		parent::__construct();
		$this->initialize(array(
			'request_token_uri' => self::REQUEST_BASE . self::REQUEST_TOKEN_PATH,
			'authorize_uri'     => self::REQUEST_BASE . self::AUTHORIZE_PATH,
			'access_token_uri'  => self::REQUEST_BASE . self::ACCESS_TOKEN_PATH
		));
	}
	
	public function get_member_data($user_id = '')
	{
		if ( ! $this->is_authorized() )
		{
			$this->_set_error('Not Authorized yet.');
			return FALSE;
		}
		else if ( empty($user_id) )
		{
			$this->_set_error('user_id or screen name is null or undefined');
			return FALSE;
		}
		$headers = $this->_build_parameter(
										"http://twitter.com/users/show/{$user_id}.json",
										array(
											'oauth_token' => $this->get_item('oauth_token'),
											'secret'      => $this->get_item('oauth_token_secret')
										),
										FALSE,
										TRUE
									);
		$header = array(
			'Authorization: OAuth ' . implode(', ', $headers)
		);
		
		$resp = $this->request("http://twitter.com/users/show/{$user_id}.json", $header);
		
		if ( $resp->status !== 200 )
		{
			$this->_set_error($resp->status . ':' . $resp->body);
			return FALSE;
		}
		
		// return decoded json
		return json_decode($resp->body);
	}
	
	public function tweet($message)
	{
		$token  = $this->get_item('oauth_tolen');
		$secret = $this->get_secret();
		
		if ( ! $token || ! $secret )
		{
			$this->_set_error('Not Authorized Yet.');
			return FALSE;
		}
		
		$length = ( function_exists('mb_strlen') )
		            ? mb_strlen($message)
		            : strlen($message);

		if ( $length > 140 )
		{
			$this->_set_error('Invalid message length (Over 140).');
			return FALSE;
		}
		else if ( $length === 0 )
		{
			$this->_set_error('post message must be not empty.');
			return FALSE;
		}
		
		// set signature method to "POST"
		$this->request_method = 'POST';
		
		$headers = $this->_build_parameter(
											self::REQUEST_BASE . self::UPDATE_PATH,
											array(
												'status'      => $message,
												'oauth_token' => $token,
												'secret'      => $secret
											),
											FALSE,
											TRUE
										);
		// remove status section.
		unset($headers[0]);
		
		$header = array(
			'Authrization: OAuth ' . implode(', ', $headers),
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($message)
		);
		$uri = self::REQUEST_BASE . self::UPDATE_PATH . '.' . $this->response_format;
		$resp = $this->_request($uri, $header);
		
		if ( $resp->status !== 200 )
		{
			$this->_set_error($resp->status . ':' . $resp->body);
			return FALSE;
		}
		
		return TRUE;
	}
}