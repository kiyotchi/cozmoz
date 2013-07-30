<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ========================================================================================
 * 
 * CodeIgniter Google Oauth Client
 * 
 * @package CodeIgniter Library
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */
// depends base OAuth Class
require_once(APPPATH . 'libraries/oauth/oauth.php');

class Google extends Oauth
{
	const REQUEST_BASE        = 'https://accounts.google.com/o/oauth2';
	const AUTHORIZE_PATH      = '/auth';
	const ACCESS_TOKEN_PATH   = '/token';
	const ACCOUNT_REQUEST_URI = 'https://www.googleapis.com/oauth2/v1/userinfo';
	
	protected $auth_name = 'google';
	protected $scope     = 'https://www.googleapis.com/auth/userinfo.profile';
	protected $client_id;
	protected $client_secret;
	
	
	public function __construct()
	{
		parent::__construct();
		$this->initialize(array(
			'authorize_uri'    => self::REQUEST_BASE . self::AUTHORIZE_PATH,
			'access_token_uri' => self::REQUEST_BASE . self::ACCESS_TOKEN_PATH
		));
	}
	
	public function auth($code = '')
	{
		// not authorized
		if ( empty($code) && ! isset($_GET['code']) )
		{
			if ( ! $this->client_id )
			{
				$this->_set_error('Client ID is not found.');
				return FALSE;
			}
			else if ( ! $this->callback_url )
			{
				$this->_set_error('Callback URI is not found.');
				return FALSE;
			}
			// not authorized
			$uri = $this->authorize_uri
			        . '?client_id='    . $this->client_id
			        . '&redirect_uri=' . rawurlencode($this->callback_url)
			        . '&scope='        . rawurlencode($this->scope)
			        . '&response_type=code'; 

			redirect($uri);
		}
		else 
		{
			$uri  = $this->access_token_uri;
			$post =   'code='           . $_GET['code']
			        . '&client_id='     . $this->client_id
			        . '&client_secret=' . $this->client_secret
			        . '&redirect_uri='  . rawurlencode($this->callback_url)
			        . '&grant_type=authorization_code';
			
			$this->request_method = 'POST';
			$resp = $this->request($uri, array(), $post);
			if ( $resp->status !== 200 )
			{
				$this->_set_error('OAuth Request Faild.');
				return FALSE;
			}
			
			$this->request_tokens = json_decode($resp->body);
			$this->request_tokens = object_to_array($this->request_tokens);
			$this->request_tokens['authorized'] = TRUE;
			$this->_save_token();
			return TRUE;
			
		}
	}
	
	public function get_member_data()
	{
		if ( ! $this->is_authorized() )
		{
			$this->_set_error('Unauthorized on get_member_data method.');
			return FALSE;
		}

		$this->request_method = 'GET';
		$uri = self::ACCOUNT_REQUEST_URI . '?access_token=' . $this->get_item('access_token');
		$resp = $this->request($uri);
		
		if ( $resp->status !== 200 )
		{
			$this->_set_error('Request Faild.');
			return FALSE;
		}
		$data = json_decode($resp->body);
		//$data->image = self::REQUEST_BASE . '/' . $data->id . '/picture?type=large'; 
		return $data;
	}
}