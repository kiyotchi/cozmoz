<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * レポート管理用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */

class Report_model extends Model
{
	protected $form_table = 'sz_bt_forms';
	protected $question_table = 'sz_bt_questions';
	protected $answer_table = 'sz_bt_question_answers';

	function __construct()
	{
		parent::Model();
	}

	function get_all_forms()
	{
		$sql = 'SELECT '
			.		'FM.block_id, '
			.		'FM.form_title, '
			.		'FM.question_key '
			.	'FROM '
			.		'sz_bt_forms as FM '
			.	'JOIN ( '
			.		'SELECT '
			.			'MAX(block_id) as block_id, '
			.			'question_key '
			.		'FROM '
			.			'sz_bt_forms as F '
			.		'WHERE '
			.			'EXISTS ( '
			.				'SELECT '
			.					'block_id '
			.				'FROM '
			.					'block_versions '
			.				'WHERE '
			.					'block_id = F.block_id '
			.				')'
			.		'GROUP BY question_key '
			.	') as MFM ON ('
			.		'MFM.block_id = FM.block_id '
			.		'AND MFM.question_key = FM.question_key '
			.	') '
			.	'ORDER BY FM.block_id ASC'
			;
		$query = $this->db->query($sql);

		$sub_sql = 'SELECT '
			.		'ar.page_id '
			.	'FROM '
			.		'areas as ar '
			.		'LEFT OUTER JOIN block_versions as bv '
			.			'USING(area_id) '
			.	'WHERE '
			.		'bv.block_id = ? '
			.		'LIMIT 1'
			;
		$cnt_sql = 'SELECT '
			.		'DISTINCT(post_date) '
			.	'FROM '
			.		'sz_bt_question_answers '
			.	'WHERE '
			.		'question_key = ?'
			;
		$ret = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $value)
			{
				$sub_q = $this->db->query($sub_sql, array($value['block_id']));
				$result = $sub_q->row();
				if ($result)
				{
					$value['page_id'] = $result->page_id;
				}
				$cnt_q = $this->db->query($cnt_sql, array($value['question_key']));
				if ($cnt_q->row())
				{
					$result = $cnt_q->row();
					$value['count'] = $cnt_q->num_rows();
				}
				else
				{
					$value['count'] = 0;
				}
				$ret[] = $value;
			}
		}
		return $ret;
	}

	function get_all_form_data($key)
	{
		$ret = array();
		$sql =
				'SELECT '
			.		'* '
			.	'FROM '
			.		'sz_bt_questions '
			.	'RIGHT OUTER JOIN '
			.		'sz_bt_question_answers '
			.	'USING(question_id) '
			.	'WHERE '
			.		'sz_bt_questions.question_key = ? '
			.	'ORDER BY '
			.		'sz_bt_question_answers.post_date DESC, '
			.		'sz_bt_question_answers.question_id ASC'
			;
		$query = $this->db->query($sql, array($key));

		$result = $query->result_array();

		foreach ($result as $value)
		{
			if (array_key_exists($value['post_date'], $ret))
			{
				$ret[$value['post_date']][] = $value;
			}
			else
			{
				$ret[$value['post_date']] = array($value);
			}
		}

		return $ret;
	}

	function get_form_data_by_key($key)
	{
		$sql = 'SELECT '
			.		'FM.block_id, '
			.		'FM.form_title, '
			.		'FM.question_key '
			.	'FROM '
			.		'sz_bt_forms as FM '
			.	'JOIN ( '
			.		'SELECT '
			.			'MAX(block_id) as block_id, '
			.			'question_key '
			.		'FROM '
			.			'sz_bt_forms as F '
			.		'WHERE '
			.			'question_key = ? '
			.		'AND '
			.			'EXISTS ( '
			.				'SELECT '
			.					'block_id '
			.				'FROM '
			.					'block_versions '
			.				'WHERE '
			.					'block_id = F.block_id '
			.				')'
			.		'GROUP BY question_key '
			.	') as MFM ON ('
			.		'MFM.block_id = FM.block_id '
			.		'AND MFM.question_key = FM.question_key '
			.	') '
			.	'LIMIT 1 ';
			;

		$query = $this->db->query($sql, array($key));

		$result = $query->row();
		return $result;
	}

	function build_csv_strings($key)
	{
		$data = $this->get_all_form_data($key);

		$out = array();
		$q = '"';

		// make csv strings
		foreach ($data as $key => $value)
		{
			$dat = array();
			//$line = array('"投稿日時","' . $key . '"');
			foreach ($value as $v)
			{
				//$dat = '"' . $v['question_name'] . '",';
//				if ($v['question_type'] === 'textarea')
//				{
//					$dat .= '"' . $v['answer_text'] . '"';
//				}
//				else
//				{
//					$dat .= '"' . $v['answer'] . '"';
//				}
				$answer = str_replace($q, $q.$q, format_question_answer($v, FALSE));
				$dat[] = $q . $answer . $q;
				//$line[] = $dat;
			}
			$out[] = implode(',', $dat);
		}

		$csv = implode("\n", $out);

		return mb_convert_encoding($csv, 'cp932', 'UTF-8');
	}

	function build_excel_strings($key)
	{
		$data = $this->get_all_form_data($key);

		$out = array('<table>');
		$out[] = "\t<tr>";
		$out[] = "\t\t<td colspan=\"2\"><b>送信日時</b></td>";
		$out[] = "\t</tr>";
		$out[] = "\t<tr>";
		$out[] = "\t\t<td><b>質問名</b></td><td><b>回答</b></td>";
		$out[] = "\t</tr>";

		// make exel strings
		foreach ($data as $key => $value)
		{
			$line = array("\t<tr>", "\t\t<td colspan=\"2\">" . $key . "</td>", "\t</tr>");

			foreach ($value as $v)
			{
				$dat = "\t<tr>\r\n\t\t<td>" . $v['question_name'] . "</td>";
//				if ($v['question_type'] === 'textarea')
//				{
//					$dat .= "<td>" . $v['answer_text'] . "</td>\r\n";
//				}
//				else
//				{
//					$dat .= "<td>" . $v['answer'] . "</td>\r\n";
//				}
				$dat .= "<td>" . format_question_answer($v) ."</td>\r\n";
				$dat .= "\t</tr>";
				$line[] = $dat;
			}
			$out[] = implode("\r\n", $line);
		}
		$out[] = "</table>";

		$excel = implode("\r\n", $out);

		return mb_convert_encoding($excel, 'cp932', 'UTF-8');
	}
	
	function delete_answer_by_date($date, $key)
	{
		$this->db->where('question_key', $key);
		$this->db->where('post_date', $date);
		$this->db->delete('sz_bt_question_answers');
		
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
	function delete_report($key)
	{
		$this->db->where('question_key', $key);
		$this->db->delete('sz_bt_question_answers');
		
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
}
