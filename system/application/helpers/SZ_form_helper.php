<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ==============================================================
 * extended CodeIgniter form Helper
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==============================================================
 */

/**
 * Form Declaration(extended)
 *
 * Creates the opening portion of the form.
 * @note consider SSL protocol per page.
 *
 * @access	public
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */	
if ( ! function_exists('form_open'))
{
	function form_open($action = '', $attributes = '', $hidden = array())
	{
		$CI =& get_instance();

		if ($attributes == '')
		{
			$attributes = 'method="post"';
		}

		// If page is ssl mode, use ssl base_url
		if ($CI->config->item('ssl_mode') === TRUE)
		{
			$action = ( strpos($action, '://') === FALSE) ? ssl_page_link($action) : $action;
		}
		else 
		{
			$action = ( strpos($action, '://') === FALSE) ? page_link($action) : $action;
		}

		$form = '<form action="'.$action.'"';
	
		$form .= _attributes_to_string($attributes, TRUE);
	
		$form .= '>';

		if (is_array($hidden) AND count($hidden) > 0)
		{
			$form .= form_hidden($hidden);
		}

		return $form;
	}
}

/**
 * Form Prep ( Security patched )
 *
 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
 *
 * @access	public
 * @param	string
 * @return	string
 * 
 * this patch from CodeIgniter-Reactor.
 * @see https://bitbucket.org/matsuu/codeigniter-reactor/changeset/20ca07f73bc3
 * 
 */
if ( ! function_exists('form_prep'))
{
	function form_prep($str = '', $field_name = '')
	{
		static $prepped_fields = array();
		
		// if the field name is an array we do this recursively
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = form_prep($val);
			}

			return $str;
		}

		if ($str === '')
		{
			return '';
		}

		// we've already prepped a field with this name
		// @todo need to figure out a way to namespace this so
		// that we know the *exact* field and not just one with
		// the same name
		//if (isset($prepped_fields[$field_name]))
		if (isset($prepped_fields[$field_name]) && $prepped_fields[$field_name] == $str)
		{
			return $str;
		}
		
		$str = htmlspecialchars($str);

		// In case htmlspecialchars misses these.
		$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);

		if ($field_name != '')
		{
			$prepped_fields[$field_name] = $str;
		}
		
		return $str;
	}
}
