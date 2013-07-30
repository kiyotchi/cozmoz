<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SZ_Session extends CI_Session
{
	private $mobile;
	private $is_mobile = FALSE;

	// marking properties (for mobile)
	//public $mobile_session_key   = 'guid';
	//public $mobile_session_value = 'on';
	public $mobile_session_key   = '<!--{$MOBILE_SESS_KEY}-->';
	public $mobile_session_value = '<!--{$MOBILE_SESS_VALUE}-->';

	// stored flag
	private $session_stored     = FALSE;
	private $current_session_id = null;

	function SZ_Session($param = array())
	{
		$this->mobile = Mobile::get_instance();
		$this->is_mobile = $this->mobile->is_mobile();

		if ( ! $this->is_mobile )
		{
			$param['sess_match_ip'] = FALSE;
		}

		parent::CI_Session($param);
	}

	/**
	 * Create a new session
	 * Overide super class method,
	 * when create session value, "user_data" column insert empty string.
	 * mysql causes error "TEXT column doesn't default value"...
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_create()
	{
		$sessid = '';
		while (strlen($sessid) < 32)
		{
			$sessid .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		$sessid .= $this->CI->input->ip_address();

		$this->userdata = array(
							'session_id'        => md5(uniqid($sessid, TRUE)),
							'session_mobile_id' => substr($sessid, 0, 30),
							'ip_address'        => $this->CI->input->ip_address(),
							'user_agent'        => substr($this->CI->input->user_agent(), 0, 50),
							'last_activity'     => $this->now
							);

		// Consider Your mysql-server work with STRICT-MODE?
		$this->userdata['user_data'] = '';

		// Save the data to the DB if needed
		if ($this->sess_use_database === TRUE)
		{
			$this->CI->db->query($this->CI->db->insert_string($this->sess_table_name, $this->userdata));
		}

		// Write the cookie
		$this->_set_cookie();
	}

	/**
	 * all_flashdata
	 * Get  all marked or added flash session data
	 * @return mixed FALSE or Array
	 */
	function all_flashdata()
	{
		$regex = '/' . preg_quote($this->flashdata_key, '/') . ':(old|new):/u';

		if ( ( $sessions = $this->all_userdata()) !== FALSE )
		{
			foreach ( $sessions as  $key => $session )
			{
				if ( preg_match($regex, $key) )
				{
					$key = preg_replace($regex, '', $key);
					$ret[$key] = $session;
				}
			}
		}
		return ( isset($ret) ) ? $ret : FALSE;
	}

	/**
	 * keep_all_flashdata
	 * keeps all flash session data
	 */
	function keep_all_flashdata()
	{
		if ( ( $flash = $this->all_flashdata() ) !== FALSE )
		{
			foreach ( $flash as $key => $value )
			{
				$this->keep_flashdata($key);
			}
		}
	}

	/**
	 * override parent class method
	 *
	 * set cookie with HttpOnly when PHP 5.2.0+
	 *
	 * @access	public
	 * @return	void
	 */
	function _set_cookie($cookie_data = NULL)
	{
		if (is_null($cookie_data))
		{
			$cookie_data = $this->userdata;
		}

		if ( ! $this->is_mobile )
		{
			// Serialize the userdata for the cookie
			$cookie_data = $this->_serialize($cookie_data);

			if ($this->sess_encrypt_cookie == TRUE)
			{
				$cookie_data = $this->CI->encrypt->encode($cookie_data);
			}
			else
			{
				// if encryption is not used, we provide an md5 hash to prevent userside tampering
				$cookie_data = $cookie_data.md5($cookie_data.$this->encryption_key);
			}

			// Set the cookie with HttpOnly when PHP5.2.0+
			if ( version_compare(PHP_VERSION, '5.2.0', '>') )
			{
				setcookie(
						$this->sess_cookie_name,
						$cookie_data,
						$this->sess_expiration + time(),
						$this->cookie_path,
						$this->cookie_domain,
						0,
						TRUE
					);
			}
			else
			{
				setcookie(
						$this->sess_cookie_name,
						$cookie_data,
						$this->sess_expiration + time(),
						$this->cookie_path,
						$this->cookie_domain,
						0
					);
			}
		}
		else
		{
			$this->current_session_id = $cookie_data['session_mobile_id'];
			$this->_add_mobile_session_query();//$cookie_data['session_mobile_id']);
			//$this->CI->config->set_item('mobile_session_id', $cookie_data['session_mobile_id']);
		}
	}

	protected function _add_mobile_session_query()
	{
		if ( $this->session_stored === FALSE )
		{
			$this->CI->uri->add_query_string_suffix(
													$this->mobile_session_key,
													$this->mobile_session_value,
													TRUE
												);
			$this->session_stored = TRUE;
		}
	}

	public function get_mobile_sess_pair()
	{
		if ( empty($this->current_session_id) )
		{
			return FALSE;
		}
		return array(
			'grep' => array($this->mobile_session_key, $this->mobile_session_value),
			'sed'  => array($this->sess_cookie_name, $this->current_session_id)
		);
	}

	/**
	 * Override sess_read for mobile
	 * @see seezoo/src/system/libraries/CI_Session::sess_read()
	 */
	public function sess_read()
	{
		if ( ! $this->is_mobile )
		{
			return parent::sess_read();
		}

		// Fetch the cookie
		$session = $this->CI->input->get($this->sess_cookie_name);

		// No cookie?  Goodbye cruel world!...
		if ($session === FALSE)
		{
			log_message('debug', 'A session cookie was not found.');
			return FALSE;
		}

//		// Decrypt the cookie data
//		if ($this->sess_encrypt_cookie == TRUE)
//		{
//			$session = $this->CI->encrypt->decode($session);
//		}
//		else
//		{
//			// encryption was not used, so we need to check the md5 hash
//			$hash	 = substr($session, strlen($session)-32); // get last 32 chars
//			$session = substr($session, 0, strlen($session)-32);
//
//			// Does the md5 hash match?  This is to prevent manipulation of session data in userspace
//			if ($hash !==  md5($session.$this->encryption_key))
//			{
//				log_message('error', 'The session cookie data did not match what was expected. This could be a possible hacking attempt.');
//				$this->sess_destroy();
//				return FALSE;
//			}
//		}

		// Unserialize the session array
		$session = $this->_unserialize_mobile($session);

		// Is the session data we unserialized an array with the correct format?
		if ( ! is_array($session) OR ! isset($session['session_id']) OR ! isset($session['ip_address']) OR ! isset($session['user_agent']) OR ! isset($session['last_activity']))
		{
			$this->sess_destroy();
			return FALSE;
		}

		// Is the session current?
		if (($session['last_activity'] + $this->sess_expiration) < $this->now)
		{
			$this->sess_destroy();
			return FALSE;
		}

		// Mobile does not matching IP
		if ($this->sess_match_ip == TRUE AND $session['ip_address'] != $this->CI->input->ip_address())
		{
			$this->sess_destroy();
			return FALSE;
		}

		// Does the User Agent Match?
		if ($this->sess_match_useragent == TRUE AND trim($session['user_agent']) != trim(substr($this->CI->input->user_agent(), 0, 50)))
		{
			$this->sess_destroy();
			return FALSE;
		}

		// Is there a corresponding session in the DB?
		if ($this->sess_use_database === TRUE)
		{
			$this->CI->db->where('session_id', $session['session_id']);

			if ($this->sess_match_ip == TRUE)
			{
				$this->CI->db->where('ip_address', $session['ip_address']);
			}

			if ($this->sess_match_useragent == TRUE)
			{
				$this->CI->db->where('user_agent', $session['user_agent']);
			}

			$query = $this->CI->db->get($this->sess_table_name);

			// No result?  Kill it!
			if ($query->num_rows() == 0)
			{
				$this->sess_destroy();
				return FALSE;
			}

			// Is there custom data?  If so, add it to the main session array
			$row = $query->row();
			if (isset($row->user_data) AND $row->user_data != '')
			{
				$custom_data = $this->_unserialize($row->user_data);

				if (is_array($custom_data))
				{
					foreach ($custom_data as $key => $val)
					{
						$session[$key] = $val;
					}
				}
			}
		}

		// Session is valid!
		$this->userdata = $session;
		unset($session);

		return TRUE;
	}

	protected function _unserialize_mobile($sess_id)
	{
		$sql =
			'SELECT '
			.	'session_id, '
			.	'user_agent, '
			.	'session_mobile_id, '
			.	'ip_address, '
			.	'last_activity '
			.'FROM '
			.	$this->sess_table_name . ' '
			.'WHERE '
			.	'session_mobile_id = ?'
			.'LIMIT 1';
		$query = $this->CI->db->query($sql, array($sess_id));

		if ( ! $query || ! $query->row() )
		{
			return FALSE;
		}
		return $query->row_array();
	}

	/**
	 * Update an existing session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_update()
	{
		if ( ! $this->is_mobile )
		{
			parent::sess_update();
			return;
		}

		// We only update the session every five minutes by default
		if (($this->userdata['last_activity'] + $this->sess_time_to_update) >= $this->now)
		{
//			$new_sessid                          = substr(sha1(uniqid(mt_rand(), TRUE)), 0, 32);
//			$data                                = array('session_mobile_id' => $new_sessid);
//			$this->userdata['session_mobile_id'] = $new_sessid;
//
//			if ( $this->sess_use_database === TRUE )
//			{
//				$this->CI->db->where('session_id', $this->userdata['session_id']);
//				$this->CI->db->update($this->sess_table_name, $data);
//				$this->_set_cookie($data);
//			}
			return;
		}

		// Save the old session id so we know which record to
		// update in the database if we need it
		$old_sessid = $this->userdata['session_id'];
		$new_sessid = '';
		while (strlen($new_sessid) < 32)
		{
			$new_sessid .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		$new_sessid .= $this->CI->input->ip_address();

		// Turn it into a hash
		$new_sessid = md5(uniqid($new_sessid, TRUE));

		// and make mobile session
		$new_mobile_sessid = substr($new_sessid, 0, 30);

		// Update the session data in the session data array
		$this->userdata['session_id']        = $new_sessid;
		$this->userdata['session_mobile_id'] = $new_mobile_sessid;
		$this->userdata['last_activity']     = $this->now;

		// _set_cookie() will handle this for us if we aren't using database sessions
		// by pushing all userdata to the cookie.
		$cookie_data = NULL;

		// Update the session ID and last_activity field in the DB if needed
		if ($this->sess_use_database === TRUE)
		{
			// set cookie explicitly to only have our session data
			$cookie_data = array();
			foreach (array('session_id','session_mobile_id','ip_address','user_agent','last_activity') as $val)
			{
				$cookie_data[$val] = $this->userdata[$val];
			}

			$this->CI->db->query($this->CI->db->update_string($this->sess_table_name, array('last_activity' => $this->now, 'session_id' => $new_sessid), array('session_id' => $old_sessid)));
		}

		// Write the cookie
		$this->_set_cookie($cookie_data);
	}

	/**
	 * Override write the session data
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_write()
	{
		if ( ! $this->is_mobile )
		{
			parent::sess_write();
			return;
		}
		// Are we saving custom data to the DB?  If not, all we do is update the cookie
		if ($this->sess_use_database === FALSE)
		{
			$this->_set_cookie();
			return;
		}

		// set the custom userdata, the session data we will set in a second
		$custom_userdata = $this->userdata;
		$cookie_userdata = array();

		// Before continuing, we need to determine if there is any custom data to deal with.
		// Let's determine this by removing the default indexes to see if there's anything left in the array
		// and set the session data while we're at it
		foreach (array('session_id','session_mobile_id','ip_address','user_agent','last_activity') as $val)
		{
			unset($custom_userdata[$val]);
			$cookie_userdata[$val] = $this->userdata[$val];
		}

		// Did we find any custom data?  If not, we turn the empty array into a string
		// since there's no reason to serialize and store an empty array in the DB
		if (count($custom_userdata) === 0)
		{
			$custom_userdata = '';
		}
		else
		{
			// Serialize the custom data array so we can store it
			$custom_userdata = $this->_serialize($custom_userdata);
		}

		// Run the update query
		$this->CI->db->where('session_id', $this->userdata['session_id']);
		$this->CI->db->update($this->sess_table_name, array('last_activity' => $this->userdata['last_activity'], 'user_data' => $custom_userdata));

		// Write the cookie.  Notice that we manually pass the cookie data array to the
		// _set_cookie() function. Normally that function will store $this->userdata, but
		// in this case that array contains custom data, which we do not want in the cookie.
		$this->_set_cookie($cookie_userdata);
	}

	/**
	 * Destroy the current session ( Override )
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_destroy()
	{
		if ( $this->is_mobile )
		{
			parent::sess_destroy();
			return;
		}
		// Kill the session DB row
		if ($this->sess_use_database === TRUE AND isset($this->userdata['session_id']))
		{
			$this->CI->db->where('session_id', $this->userdata['session_id']);
			$this->CI->db->delete($this->sess_table_name);
		}

		// Kill the cookie
//		setcookie(
//					$this->sess_cookie_name,
//					addslashes(serialize(array())),
//					($this->now - 31500000),
//					$this->cookie_path,
//					$this->cookie_domain,
//					0
//				);
		$this->current_session_id = null;
	}



//	/**
//	 * get MySQL ENV
//	 * Detect Your MySQL work-mode at aviod Error on STRICT-MODE.
//	 *
//	 * @access private
//	 * @return bool
//	 */
//	function _is_mysql_strict_mode()
//	{
//		$sql =
//					'SELECT '
//					.	'CASE '
//					.	'WHEN @@global.sql_mode REGEXP "STRICT_(ALL|TRANS)_TABLES" THEN 1 '
//					.	'ELSE 0 '
//					.	'END AS is_strict '
//					;
//	}
}