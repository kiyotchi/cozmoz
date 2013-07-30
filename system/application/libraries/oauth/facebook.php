<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ========================================================================================
 * 
 * CodeIgniter Facebook Oauth Client
 * 
 * @package CodeIgniter Library
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ========================================================================================
 */

// depends base OAuth Class
require_once(APPPATH . 'libraries/oauth/oauth.php');

class Facebook extends Oauth
{
	const REQUEST_BASE      = 'https://graph.facebook.com';
	const AUTHORIZE_PATH    = '/oauth/authorize';
	const ACCESS_TOKEN_PATH = '/oauth/access_token';
	const USER_PATH         = '/me';
	
	protected $auth_name          = 'facebook';
	protected $application_id;
	protected $application_secret;
	protected $scope;
	
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
			if ( ! $this->application_id )
			{
				$this->_set_error('Apprication ID is not found.');
				return FALSE;
			}
			else if ( ! $this->callback_url )
			{
				$this->_set_error('Cakkback URI is not found.');
				return FALSE;
			}
			// not authorized
			$uri = $this->authorize_uri
			        . '?client_id='    . $this->application_id
			        . '&redirect_uri=' . rawurlencode($this->callback_url);
			if ( $this->scope )
			{
				$uri .= '&scope=' . $this->scope;
			}
			redirect($uri);
		}
		else 
		{
			$uri = $this->access_token_uri
			        . '?client_id='     . $this->application_id
			        . '&redirect_uri='  . rawurlencode($this->callback_url)
			        . '&client_secret=' . $this->application_secret
			        . '&code='          . $_GET['code'] ;
			
			$resp = $this->request($uri);
			if ( $resp->status !== 200 )
			{
				$this->_set_error('OAuth Request Faild.');
				return FALSE;
			}
			
			parse_str($resp->body, $this->request_tokens);
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
		
		$uri = self::REQUEST_BASE . self::USER_PATH
		        .'?access_token=' . $this->get_item('access_token');
		
		$resp = $this->request($uri);
		if ( $resp->status !== 200 )
		{
			$this->_set_error('Request Faild.');
			return FALSE;
		}
		$data = json_decode($resp->body);
		$data->image = self::REQUEST_BASE . '/' . $data->id . '/picture?type=large'; 
		return $data;
	}
}