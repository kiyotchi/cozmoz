<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// set_require_mark
// 検証ルールにrequiredが含まれていれば必須マークを出力
if ( ! function_exists('set_require_mark') )
{
	function set_require_mark($rule)
	{
		if ( ! $rule )
		{
			$rule = '';
		}
		if ( strpos('|' . $rule . '|', '|required|') !== FALSE )
		{
			return '<span class="r_need">*</span>';
		}
		return '';
	}
}

// set_attribute_value
// 追加項目のvalueセット
if ( ! function_exists('set_attribute_value') )
{
	function set_attribute_value($name, $val_list)
	{
		foreach ( $val_list as $v )
		{
			if ( $v->name === $name )
			{
				return $v->value;
			}
		}
		return FALSE;
	}
}

// build_registration_form_parts
// メンバー登録用の入力フォームパーツ出力
if ( ! function_exists('build_registration_form_parts') )
{
	function build_registration_form_parts($att, $is_confirm = FALSE, $value = FALSE)
	{
		$CI =& get_instance();
		$name = 'attribute_' . $att->sz_member_attributes_id;
		if ( ! $value )
		{
			$value = ( $CI->input->post($name) !== FALSE ) ? $CI->input->post($name) : '';
		}
		
		switch ( $att->attribute_type )
		{
			case 'text':
				return ( ! $is_confirm ) ? form_input(array('name' => $name, 'value' => $value, 'id' => $name)) : set_value($name);
			case 'checkbox':
				return build_registration_form_parts_checkbox($att, $name, $value, $is_confirm);
			case 'selectbox':
				return build_registration_form_parts_selectbox($att, $name, $value, $is_confirm);
			case 'radio':
				return build_registration_form_parts_radio($att, $name, $value, $is_confirm);
			case 'textarea':
				return ( ! $is_confirm )
							? form_textarea(
										array(
											'name'  => $name,
											'value' => $value,
											'rows'  => $att->rows,
											'cols'  => $att->cols,
											'id'    => $name
										)
									)
							: nl2br(set_value($name));
			case 'pref':
				$arr->options = sz_form_build_pref_list();
				return build_registration_form_parts_selectbox( $arr, $name, $value, $is_confirm );
			default:
				return '';
		}
	}
}

// build_registration_form_parts_checkbox
// チェックボックス生成
// @access private
// @depend build_registration_form_parts
if ( ! function_exists('build_registration_form_parts_checkbox') )
{
	function build_registration_form_parts_checkbox($att, $name, $value, $is_confirm = FALSE)
	{
		$values = ( ! is_array($value) ) ? explode(':', $value) : $value;
		$options = explode(':', $att->options);
		$ret = array();
		if ( ! $is_confirm )
		{
			foreach ( $options as $key => $option )
			{
				$ret[] = '<label>' . form_checkbox($name . '[]', $key, in_array((string)$key, $values, TRUE), 'class="box_btn"') . "&nbsp;" . prep_str($option) . "</label>";
			}
			return implode('&nbsp;', $ret);
		}
		else 
		{
			foreach ( $values as $v )
			{
				if ( isset($options[$v]) )
				{
					$ret[] = prep_str($options[$v]);
				}
			}
			return implode(',&nbsp;', $ret);
		}
	}
}

// build_registration_form_parts_selectbox
// プルダウンメニュー生成
// @access private
// @depend build_registration_form_parts
if ( ! function_exists('build_registration_form_parts_selectbox') )
{
	function build_registration_form_parts_selectbox($att, $name, $value, $is_confirm = FALSE)
	{
		$options = explode(':', $att->options);
		if ( ! $is_confirm )
		{
			return form_dropdown($name, $options, $value);
		}
		else 
		{
			return ( isset($options[$value]) ) ? $options[$value] : '';
		}
	}
}

// build_registration_form_parts_radio
// ラジオボタン生成
// @access private
// @depend build_registration_form_parts
if ( ! function_exists('build_registration_form_parts_radio') )
{
	function build_registration_form_parts_radio($att, $name, $value, $is_confirm = FALSE)
	{
		$ret = array();
		$options = explode(':', $att->options);
		
		if ( ! $is_confirm )
		{
			foreach ( $options as $key => $option )
			{
				$ret[] = '<label>' . form_radio($name, $key, ( $key == $value ) ? TRUE : FALSE, 'class="box_btn"') . "&nbsp;" . prep_str($option) . "</label>";
			}
			return implode('&nbsp;', $ret);
		}
		else 
		{
			if ( isset($options[$value]) )
			{
				$ret[] = prep_str($options[$value]);
			}
			return implode(',&nbsp;', $ret);
		}
	}
}
