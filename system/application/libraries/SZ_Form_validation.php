<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ===============================================================================
 * SZ_Form_validation Class (override)
 * 
 * 検証ルール追加
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto
 * ===============================================================================
 */

class SZ_Form_validation extends CI_Form_validation
{
	protected $_keep_default_post = FALSE;
	
	function __construct($rules = array())
	{
		parent::CI_Form_validation($rules);
		
		// set error messages of additional rules
		$this->set_message('date_format', '%sの日付形式が正しくありません。');
		$this->set_message('past_date',   '%sに過去の日付は指定できません。');
		$this->set_message('hiragana',    '%sはひらがなで入力してください。');
		$this->set_message('tel_number',  '%sの形式が正しくありません。');
		$this->set_message('format',      '%sの形式が正しくありません。');
	}
	
	/**
	 * yyyy-mm-ddのフォーマットであるかどうかチェック
	 * Enter description here ...
	 * @param $str
	 */
	public function date_format($str)
	{
		return ( preg_match('/\A[0-9]{4}\-[0-9]{2}\-[0-9]{2}\Z/u', $str) ) ? TRUE : FALSE;
	}
	
	public function past_date($str)
	{
		$now = (int)date('Ymd');
		$ipt = (int)str_replace('-', '', $str);
		
		return ( $now <= $ipt ) ? TRUE : FALSE;
	}
	
	public function hiragana($str)
	{
		return ( preg_match('/\A[ぁ-ん]+\Z/u', $str) ) ? TRUE : FALSE;
	}
	
	public function tel_number($str)
	{
		return ( preg_match('/\A[0-9]{2,4}\-?[0-9]{2,4}\-?[0-9]{3,4}\Z/u', $str) ) ? TRUE : FALSE;
	}
	
	public function range($str, $range)
	{
		$ranges = explode(':', $range);
		$value  = (int)$str;
		if ( $value >= (int)$ranges[0] && (int)$ranges[1] >= $value )
		{
			return TRUE;
		}
		else
		{
			$this->set_message('range', '%sは' . $ranges[0] . 'から' . $ranges[1] . 'の間で入力してください。');
			return FALSE;
		}
	}
	
	public function format($str, $regex)
	{
		return ( preg_match('/' . str_replace('/', '\/', $regex) . '/u', $str) ) ? TRUE : FALSE;
	}
	
	/**
	 * Executes the Validation routines ( Override )
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	integer
	 * @return	mixed
	 */	
	function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		// If the $_POST data is an array we will run a recursive call
		if (is_array($postdata))
		{ 
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $cycles);
				$cycles++;
			}
			
			return;
		}
		
		// --------------------------------------------------------------------

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;
		if ( ! in_array('required', $rules) AND is_null($postdata))
		{
			// Before we bail out, does the rule contain a callback?
			if (preg_match("/(callback_\w+)/", implode(' ', $rules), $match))
			{
				$callback = TRUE;
				$rules = (array('1' => $match[1]));
			}
			else
			{
				return;
			}
		}

		// --------------------------------------------------------------------
		
		// Isset Test. Typically this rule will only apply to checkboxes.
		if (is_null($postdata) AND $callback == FALSE)
		{
			if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
			{
				// Set the message type
				$type = (in_array('required', $rules)) ? 'required' : 'isset';
			
				if ( ! isset($this->_error_messages[$type]))
				{
					if (FALSE === ($line = $this->CI->lang->line($type)))
					{
						$line = 'The field was not set';
					}							
				}
				else
				{
					$line = $this->_error_messages[$type];
				}
				
				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']));

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;
				
				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}
			}
					
			return;
		}

		// --------------------------------------------------------------------

		// Cycle through each rule and run it
		foreach ($rules As $rule)
		{
			$_in_array = FALSE;
			
			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}
			
				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				$postdata = $this->_field_data[$row['field']]['postdata'];
			}

			// --------------------------------------------------------------------
	
			// Is the rule a callback?			
			$callback = FALSE;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}
			
			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
// ---- modified section
			if (preg_match("/(.*?)\[(.*?)\]$/", $rule, $match))
// ---- modified section end
			{
				$rule	= $match[1];
				$param	= $match[2];
			}
			
			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				if ( ! method_exists($this->CI, $rule))
				{ 		
					continue;
				}
				
				// Run the function and grab the result
				$result = $this->CI->$rule($postdata, $param);

				// Re-assign the result to the master data array
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
			
				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
				{
					continue;
				}
			}
			else
			{				
				if ( ! method_exists($this, $rule))
				{
					// If our own wrapper function doesn't exist we see if a native PHP function does. 
					// Users can use any native PHP function call that has one param.
					if (function_exists($rule))
					{
						$result = $rule($postdata);
											
						if ($_in_array == TRUE)
						{
							$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
						}
						else
						{
							$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
						}
					}
					
					continue;
				}

				$result = $this->$rule($postdata, $param);

				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
			}
							
			// Did the rule test negatively?  If so, grab the error.
			if ($result === FALSE)
			{			
				if ( ! isset($this->_error_messages[$rule]))
				{
					if (FALSE === ($line = $this->CI->lang->line($rule)))
					{
						$line = 'Unable to access an error message corresponding to your field name.';
					}						
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}
				
				// Is the parameter we are inserting into the error message the name
				// of another field?  If so we need to grab its "field label"
				if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
				{
					$param = $this->_field_data[$param]['label'];
				}
				
				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;
				
				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}
				
				return;
			}
		}
	}
	
	public function keep_default_post($flag = TRUE)
	{
		$this->_keep_default_post = $flag;
	}
	
	public function _reset_post_array()
	{
		if ( $this->_keep_default_post === TRUE )
		{
			return;
		}
		parent::_reset_post_array();
	}
}
