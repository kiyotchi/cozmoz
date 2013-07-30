<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  =========================================================
 * ブログ用モデルクラス
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 *  =========================================================
 */
class Blog_model extends Model
{
	protected $table = 'sz_blog';

	function __construct()
	{
		parent::Model();
	}

	function get_blog_info()
	{
		$sql = 'SELECT * FROM blog_info LIMIT 1';
		$query = $this->db->query($sql);

		return $query->row();
	}

	function get_template_path($tid)
	{
		$sql = 'SELECT template_handle FROM templates WHERE template_id = ? LIMIT 1';
		$query = $this->db->query($sql, array((int)$tid));

		$result = $query->row();
		return $result->template_handle . '/';
	}

	function get_enable_template_list()
	{
		$sql = 'SELECT '
			.		'template_id, '
			.		'template_name, '
			.		'template_handle, '
			.		'description '
			.	'FROM '
			.		'templates '
			;
		$query = $this->db->query($sql);

		$ret = array();
		foreach ($query->result() as $value)
		{
			// [system_view.php] file exists?
			if (file_exists('templates/' . $value->template_handle . '/blog_view.php'))
			{
				$ret[] = $value;
			}
		}
		return $ret;
	}

	function get_recent_entry($limit, $offset)
	{
		$sql =
					'SELECT '
					.	'B.sz_blog_id as sz_blog_id, '
					.	'B.sz_blog_category_id, '
					.	'B.title, '
					.	'B.body, '
					.	'B.entry_date, '
					.	'B.permalink, '
					.	'U.user_name, '
					.	'C.comment_count '
					.'FROM '
					.		$this->table . ' AS B '
					.'LEFT JOIN '
					.	'users AS U '
					.	'ON ('
					.		'B.user_id = U.user_id '
					.	') '
					.'JOIN ( '
					.	'SELECT '
					.		'COUNT(sz_blog_comment_id) as comment_count '
					.	'FROM '
					.		'sz_blog_comment as COM '
					.	'WHERE '
					.		'COM.sz_blog_id = sz_blog_id '
					.') as C '
					.'WHERE '
					.		'B.is_public = 1 '
					.	'AND '
					.		'B.show_datetime < NOW() '
					.	'ORDER BY entry_date DESC '
					.	'LIMIT ? OFFSET ? '
					;
		$query = $this->db->query($sql,array((int)$limit, (int)$offset));

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			return array();
		}
	}

	function get_category()
	{
		$sql =
				'SELECT '
				.	'sz_blog_category_id, '
				.	'category_name '
				.'FROM '
				.	'sz_blog_category '
				.'WHERE '
				.	'is_use = 1 '
				.'ORDER BY '
				.	'display_order ASC';
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			return array();
		}
	}

	function get_category_array()
	{
		$sql =
				'SELECT '
				.	'sz_blog_category_id, '
				.	'category_name '
				.'FROM '
				.	'sz_blog_category '
				.'WHERE '
				.	'is_use = 1 '
				.'ORDER BY '
				.	'display_order ASC';
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			$ret = array();
			foreach ($query->result() as $q)
			{
				$ret[$q->sz_blog_category_id] = $q->category_name;
			}
			return $ret;
		}
		else
		{
			return array();
		}
	}

	function get_user_array()
	{
        $sql = 'SELECT '
			.	'user_id, '
			.	'user_name '
			.'FROM '
			.	'users AS U '
			.'WHERE '
			.	'EXISTS ( '
			.		'SELECT '
			.				'1 '
			.			'FROM '
			.				'sz_blog '
			.			'WHERE '
			.				'user_id = U.user_id '
			.	')';
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			$ret = array();
			foreach($query->result() as $u)
			{
				$ret[$u->user_id] = $u->user_name;
			}
			return $ret;
		}
		else
		{
			return array();
		}
	}

	function get_recent_comments($limit)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'sz_blog_comment '
			.	'ORDER BY post_date '
			.	'LIMIT ?'
			;
		$query = $this->db->query($sql, array((int)$limit));

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			return array();
		}
	}

	function get_all_entries($limit = FALSE, $offset = FALSE, $is_get_draft = FALSE)
	{
		$sql = 'SELECT '
			.		'B.*, '
			.		'U.user_name '
			.	'FROM '
			.		'sz_blog as B '
			.	'LEFT OUTER JOIN users as U ON ( '
			.		'B.user_id = U.user_id '
			.	') '
			.'WHERE '
			.	'is_public = ? '
			.'ORDER BY entry_date DESC ';
		$bind = array(($is_get_draft) ? 0 : 1);
		if ( $limit )
		{
			$sql .= 'LIMIT ? ';
			$bind[] = (int)$limit;
		}
		if ( $offset )
		{
			$sql .= 'OFFSET ? ';
			$bind[] = (int)$offset;
		}

		$query = $this->db->query($sql, $bind);

		return $query->result();
	}

	function get_entry_count($is_get_draft = FALSE, $cid = FALSE)
	{
		$sql = 'SELECT '
			.		'sz_blog_id '
			.	'FROM '
			.		'sz_blog '
			.	'WHERE '
			.		'is_public = ? '
			.	'AND '
			.		'show_datetime < NOW() '
			;
		$query = $this->db->query($sql, array(($is_get_draft) ? 0 : 1));

		return $query->num_rows();
	}

	function get_entry_count_by_category($cid, $is_get_draft = FALSE)
	{
		$sql = 'SELECT '
					.	'B.sz_blog_id '
					.'FROM '
					.	'sz_blog as B '
					.'WHERE '
					.	'B.sz_blog_category_id = ? '
					.'AND '
					.	'B.is_public = ? '
					.'AND '
					.	'B.show_datetime <= NOW() ';
		$query = $this->db->query($sql, array((int)$cid, ($is_get_draft) ? 0 : 1));

		return $query->num_rows();
	}

	function get_entry_count_by_author($uid, $is_get_draft = FALSE)
	{
		$sql = 'SELECT '
					.	'B.sz_blog_id '
					.'FROM '
					.	'sz_blog as B '
					.'WHERE '
					.	'B.user_id = ? '
					.'AND '
					.	'B.is_public = ? '
					.'AND '
					.	'B.show_datetime <= NOW() ';
		$query = $this->db->query($sql, array((int)$uid, ($is_get_draft) ? 0 : 1));

		return $query->num_rows();
	}

	function get_entry_one($eid, $pubic_only = FALSE)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'sz_blog AS B '
			.	'LEFT JOIN '
			.		'users AS U '
			.	'ON '
			.		'B.user_id = U.user_id '
			.	'WHERE ( '
			.		'B.sz_blog_id = ? '
			.	'OR '
			.		'B.permalink = ? '
			.	') ';

		if ( $pubic_only )
		{
			$sql .= 'AND B.is_public = 1 AND B.show_datetime < NOW() ';
		}

		$sql .= 'LIMIT 1';

		$query = $this->db->query($sql, array($eid, $eid));

		return ( $query && $query->row() ) ? $query->row() : FALSE;
	}

	function get_comment_to_entry($eid)
	{
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'sz_blog_comment '
			.	'WHERE '
			.		'sz_blog_id = ? '
			.	'ORDER BY post_date DESC'
			;
		$query = $this->db->query($sql, array($eid));

		return $query->result();
	}

	function get_ping_list()
	{
		$sql = 'SELECT * FROM sz_blog_ping_list';
		$query = $this->db->query($sql);

		return $query->result();
	}

	function add_new_ping($arr)
	{
		$this->db->insert('sz_blog_ping_list', $arr);

		return $this->db->insert_id();
	}

	function update_ping_data($pid, $data)
	{
		$this->db->where('sz_blog_ping_list_id', $pid);
		return $this->db->update('sz_blog_ping_list', $data);
	}

	function delete_ping_data($pid)
	{
		$this->db->where('sz_blog_ping_list_id', $pid);
		$this->db->delete('sz_blog_ping_list');

		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function get_ping_one($pid)
	{
		$sql = 'SELECT * FROM sz_blog_ping_list WHERE sz_blog_ping_list_id = ? LIMIT 1';
		$query = $this->db->query($sql, array($pid));

		return $query->row();
	}

	function do_ping_all($ping_data)
	{
		$pings = $this->get_ping_list();

		$results = array();

		// load xmlrpc library
		$this->load->library('xmlrpc');

		foreach ($pings as $value)
		{
			$this->xmlrpc->server($value->ping_server, 80);
			$this->xmlrpc->method('weblogUpdates.ping');

			$this->xmlrpc->request($ping_data);
			if ($this->xmlrpc->send_request() === TRUE)
			{
				$results[] = array($value->ping_name, '送信成功', TRUE);
			}
			else
			{
				$results[] = array($value->ping_name, '送信失敗', FALSE);
			}
		}

		return $results;
	}

	function do_ping($pid, $data)
	{
		$ping = $this->get_ping_one($pid);

		if (!$ping)
		{
			return FALSE;
		}

		// load xmlrpc library
		$this->load->library('xmlrpc');

		$this->xmlrpc->server($ping->ping_server, 80);
		$this->xmlrpc->method('weblogUpdates.ping');

		$this->xmlrpc->request($data);

		return $this->xmlrpc->send_request();
	}

	function update_settings($post)
	{
		return $this->db->update('blog_info', $post);
	}

	function insert_new_category($post)
	{
		// get max display_order
		$sql    = 'SELECT MAX(display_order) as m FROM sz_blog_category LIMIT 1';
		$query  = $this->db->query($sql);
		$result = $query->row();
		$post['display_order'] = (int)$result->m + 1;
		$res = $this->db->insert('sz_blog_category', $post);
		if ($res)
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}
	
	function insert_new_entry($post)
	{
		if ( $this->db->insert('sz_blog', $post) )
		{
			return $this->db->insert_id();
		}
		return FALSE;
	}

	function update_entry($post, $eid)
	{
		$target_id = $eid;
		// UPDATE前に、下書き保存元のエントリーがあるかどうかを検索
		$sql =
					'SELECT '
					.	'drafted_by, '
					.	'is_public '
					.'FROM '
					.	'sz_blog '
					.'WHERE '
					.	'sz_blog_id = ? '
					.'LIMIT 1';
		$query = $this->db->query($sql, array((int)$eid));

		if ( $query && $query->row() )
		{
			$result = $query->row();
			if ( $result->is_public < 1 && $result->drafted_by > 0 )
			{
				$target_id = $result->drafted_by;

				// 下書きレコードを削除
				$this->db->where('sz_blog_id', $eid);
				$this->db->delete('sz_blog');
			}
		}
		$this->db->where('sz_blog_id', $target_id);
		if ( $this->db->update('sz_blog', $post) )
		{
			return $target_id;
		}
		return FALSE;
	}

	function update_category($post, $cid)
	{
		$this->db->where('sz_blog_category_id', $cid);
		return $this->db->update('sz_blog_category', $post);
	}
	
	function update_category_sort_order($id, $order)
	{
		$this->db->where('sz_blog_category_id', $id);
		return $this->db->update('sz_blog_category', array('display_order' => $order));
	}

	function delete_category($cid)
	{
		$this->db->where('sz_blog_category_id', $cid);
		return $this->db->update('sz_blog_category', array('is_use' => 0));
	}

	function insert_comment($post)
	{
		return $this->db->insert('sz_blog_comment', $post);
	}

	function create_draft($post, $eid)
	{
		if ( $eid > 0 )
		{
			$by =  $this->_detect_already_public_entry($eid);
			if ( $by !== FALSE )
			{
				// create related entry
				$post['drafted_by'] = $by;
				$this->db->insert('sz_blog', $post);
				return (int)$this->db->insert_id();
			}
			else
			{
				$this->db->where('sz_blog_id', $eid);
				return ( $this->db->update('sz_blog', $post) ) ? $eid : FALSE;
			}
		}
		else
		{
			$this->db->insert('sz_blog', $post);
			return (int)$this->db->insert_id();
		}
	}

	function _detect_already_public_entry($eid)
	{
		$sql =
					'SELECT '
					.	'sz_blog_id '
					.'FROM '
					.	'sz_blog '
					.'WHERE '
					.	'sz_blog_id = ? '
					.'AND '
					.	'is_public = 1';
		$query = $this->db->query($sql, array($eid));

		if ( $query && $query->num_rows() > 0 )
		{
			$result = $query->row();
			return (int)$result->sz_blog_id;
		}
		return FALSE;
	}

	function get_posted_comments($limit, $offset)
	{
		$cache = array();
		$sql = 'SELECT '
			.		'* '
			.	'FROM '
			.		'sz_blog_comment '
			.	'ORDER BY post_date DESC '
			.	'LIMIT ? '
			.	'OFFSET ?'
			;
		$query = $this->db->query($sql, array($limit, $offset));

		return $query->result();
	}

	function get_posted_comments_count()
	{
		$sql = 'SELECT '
			.		'COUNT(sz_blog_comment_id) as total '
			.	'FROM '
			.		'sz_blog_comment'
			;
		$query = $this->db->query($sql);

		$result = $query->row();
		return $result->total;
	}


	function get_entry_titles()
	{
		$ret = array();
		$sql = 
			'SELECT '
			.	'sz_blog_id, '
			.	'title '
			.'FROM '
			.	'sz_blog '
			;
		$query = $this->db->query($sql);

		foreach ($query->result() as $v)
		{
			$ret[$v->sz_blog_id] = $v->title;
		}

		return $ret;
	}

	function delete_comment_one($cid)
	{
		$this->db->where('sz_blog_comment_id', $cid);
		$this->db->delete('sz_blog_comment');

		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function delete_comment($dels)
	{
		foreach ($dels as $v)
		{
			if (!ctype_digit($v))
			{
				continue;
			}
			$this->db->where('sz_blog_comment_id', $v);
			$this->db->delete('sz_blog_comment');
		}

		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	function delete_entry($eid)
	{
		$this->db->where('sz_blog_id', $eid);
		$this->db->delete('sz_blog');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
	function delete_drafted_entry($draft_id)
	{
		$this->db->where('sz_blog_id', $draft_id);
		$this->db->where('is_public', 0); // guard where
		$this->db->delete('sz_blog');
		
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	function get_blog_menu_data()
	{
		$sql =
					'SELECT '
					.	'* '
					.'FROM '
					.	'sz_blog_menu '
					.'ORDER BY '
					.	'display_order '
					.'ASC';
		$query = $this->db->query($sql);
		return $query->result();
	}

	function update_menu_settings($post)
	{
		// prepare statement
		$sql =
					'UPDATE '
					.	'sz_blog_menu '
					.'SET '
					.	'is_hidden = ?, '
					.	'display_order = ? '
					.'WHERE '
					.	'sz_blog_menu_id = ?';

		foreach ($post as $p)
		{
			list($id, $order, $hidden) = explode(':', $p);
			$ret = $this->db->query($sql, array((int)$hidden, (int)$order, (int)$id));
			if ( ! $ret ) {
				return FALSE;
			}
		}
		return TRUE;
	}

	function get_menu_data_form_frotend()
	{
		$sql =
					'SELECT '
					.	'menu_type '
					.'FROM '
					.	'sz_blog_menu '
					.'WHERE '
					.	'is_hidden = 0 '
					.'ORDER BY '
					.	'display_order ASC';
		$query = $this->db->query($sql);

		return $query->result();
	}

	function get_entry_by_date($st, $ed)
	{
		$sql =
					'SELECT '
					.	'B.sz_blog_id, '
					.	'B.sz_blog_category_id, '
					.	'B.title, '
					.	'B.body, '
					.	'B.entry_date, '
					.	'B.permalink, '
					.	'U.user_name '
					.'FROM '
					.	'sz_blog AS B '
					.'LEFT JOIN '
					.	'users AS U '
					.'ON '
					.'B.user_id = U.user_id '
					.'WHERE '
					.	'entry_date >= ? '
					.'AND '
					.	'entry_date < ? '
					.'AND '
					.	'is_public = 1 '
					.'AND '
					.	'show_datetime < NOW() '
					.'ORDER BY '
					.	'entry_date DESC '
					;
		$query = $this->db->query($sql, array($st, $ed));

		return $query->result();
	}

	function get_entry_by_date_of_id($timestamp)
	{
		$sql =
					'SELECT '
					.	'DISTINCT sz_blog_id, '
					.	'entry_date '
					.'FROM '
					.	'sz_blog '
					.'WHERE '
					.	'entry_date >= ? '
					.'AND '
					.	'entry_date < ?'
					.'AND '
					.	'is_public = 1 '
					;
		$query = $this->db->query($sql, array(date('Y-m-01 00:00:00', $timestamp), date('Y-m-01 00:00:00', strtotime('+1 month', $timestamp))));

		$ret = array();
		foreach ($query->result() as $entry)
		{
			$t = strtotime($entry->entry_date);
			$d = date('j', $t);
			if ( ! isset($ret[$d]) )
			{
				$ret[$d] = page_link('blog/postdate/' . date('Y/m/d', $t));
			}
		}
		return $ret;
	}

	function search_article($q, $limit, $offset)
	{
		$bind = array();
		$like = array();
		$sql =
					'SELECT '
					.	'sz_blog_id, '
					.	'title, '
					.	'body, '
					.	'permalink, '
					.	'entry_date '
					.'FROM '
					.	'sz_blog '
					.'WHERE '
					.	'is_public = 1 '
					.'AND '
					.	'show_datetime < NOW() '
					.'AND ';
		foreach ($q as $v)
		{
			$like[] = $this->_add_like();
			$this->_add_bind($bind, $v);
		}

		if (count($like) > 0)
		{
			$sql .= ' ( ' . implode(' OR ', $like) . ' ) ';
		}

		$sql .= 'GROUP BY sz_blog_id ';
		$sql .= 'LIMIT ? OFFSET ?';
		$bind[] = (int)$limit;
		$bind[] = (int)$offset;

		$query = $this->db->query($sql, $bind);
		return $query->result();
	}

	function _add_like()
	{
		$sql = array(
			'title LIKE ?',
			'body LIKE ?'
		);

		return ' ( ' . implode(' OR ', $sql) . ' ) ';
	}

	function _add_bind(&$bind, $v)
	{
		for ($i = 0; $i < 2; ++$i)
		{
			$bind[] = '%' . mysql_real_escape_string($v) . '%';
		}
	}

	function get_search_article_count($q)
	{
		$bind = array();
		$like = array();
		$sql =
					'SELECT '
					.	'sz_blog_id, '
					.	'title, '
					.	'body, '
					.	'entry_date '
					.'FROM '
					.	'sz_blog '
					.'WHERE '
					.	'is_public = 1 '
					.'AND '
					.	'show_datetime < NOW() '
					.'AND ';
		foreach ($q as $v)
		{
			$like[] = $this->_add_like();
			$this->_add_bind($bind, $v);
		}

		if (count($like) > 0)
		{
			$sql .= ' ( ' . implode(' OR ', $like) . ' ) ';
		}

		$sql .= 'GROUP BY sz_blog_id ';

		$query = $this->db->query($sql, $bind);
		return $query->num_rows();
	}

	function get_recent_entry_from_category($cid, $limit, $offset)
	{
		$sql =
					'SELECT '
					.	'B.sz_blog_id as sz_blog_id, '
					.	'B.sz_blog_category_id, '
					.	'B.title, '
					.	'B.body, '
					.	'B.entry_date, '
					.	'B.permalink, '
					.	'U.user_name, '
					.	'C.comment_count '
					.'FROM '
					.	'sz_blog AS B '
					.'LEFT JOIN '
					.	'users AS U '
					.'ON '
					.	'B.user_id = U.user_id '
					.'JOIN ( '
					.	'SELECT '
					.		'COUNT(sz_blog_comment_id) as comment_count '
					.	'FROM '
					.		'sz_blog_comment as COM '
					.	'WHERE '
					.		'COM.sz_blog_id = sz_blog_id '
					.') as C '
					.'WHERE '
					.	'B.sz_blog_category_id = ? '
					.'AND '
					.	'B.is_public = 1 '
					.'AND '
					.	'B.show_datetime < NOW() '
					.'ORDER BY '
					.	'entry_date DESC '
					.'LIMIT ? OFFSET ?';
		$query = $this->db->query($sql, array((int)$cid, (int)$limit, (int)$offset));

		return $query->result();
	}

	function get_recent_entry_from_author($uid, $limit, $offset)
	{
		$sql =
				'SELECT '
				.	'B.sz_blog_id as sz_blog_id, '
				.	'B.sz_blog_category_id, '
				.	'B.title, '
				.	'B.body, '
				.	'B.entry_date, '
				.	'B.permalink, '
				.	'U.user_name, '
				.	'C.comment_count '
				.'FROM '
				.	'sz_blog AS B '
				.'LEFT JOIN '
				.	'users AS U '
				.'ON '
				.	'B.user_id = U.user_id '
				.'JOIN ( '
				.	'SELECT '
				.		'COUNT(sz_blog_comment_id) as comment_count '
				.	'FROM '
				.		'sz_blog_comment as COM '
				.	'WHERE '
				.		'COM.sz_blog_id = sz_blog_id '
				.	') as C '
				.'WHERE '
				.	'B.user_id = ? '
				.'AND '
				.	'B.is_public = 1 '
				.'AND '
				.	'B.show_datetime < NOW() '
				.'ORDER BY '
				.	'entry_date DESC '
				.'LIMIT ? OFFSET ? '
				;
		$query = $this->db->query($sql, array((int)$uid, (int)$limit, (int)$offset));

		return $query->result();
	}
	/**
	 * エントリーにつけられたトラックバック取得
	 */
	function get_trackbacks_by_entry($eid)
	{
		$sql =
					'SELECT '
					.	'title, '
					.	'url, '
					.	'blog_name, '
					.	'excerpt, '
					.	'received_date '
					.'FROM '
					.	'sz_blog_trackbacks '
					.'WHERE '
					.	'sz_blog_id = ? '
					.'AND '
					.	'is_allowed = 1 '
					.'ORDER BY '
					.	'received_date DESC';

		$query = $this->db->query($sql, array($eid));

		if ( $query )
		{
			return $query->result();
		}
		return array();
	}

	/**
	 * 対象エントリーがトラックバック受信を許可しているかをチェック
	 * @note 追加で同じリモートから同じ記事IDに対して連続受信しないようガードする
	 *        最後の受信から30分は受信拒否する
	 * @param $eid
	 * @param $ip
	 */
	function check_accept_trackback($eid, $ip)
	{
		$sql =
					'SELECT '
					.	'B.sz_blog_id, '
					.	'B.is_accept_trackback '
					.'FROM '
					.	'sz_blog as B '
//					.'LEFT OUTER JOIN ( '
//					.		'SELECT '
//					.			'sz_blog_id, '
//					.			'MAX(received_date) as received_date '
//					.		'FROM '
//					.			'sz_blog_trackbacks '
//					.		'WHERE '
//					.			'sz_blog_id = ? '
//					.		'AND '
//					.			'ip_address = ? '
//					.	') as TB ON ( '
//					.		'TB.sz_blog_id = B.sz_blog_id '
//					.	') '
					.'WHERE '
					.	'B.sz_blog_id = ? '
					.'LIMIT 1';

		$query = $this->db->query($sql, array($eid, $ip, $eid));

		if ( $query )
		{
			$result = $query->row();
			// 記事自体がトラックバックを許可していなければNG
			if ( $result->is_accept_trackback < 1 )
			{
				return FALSE;
			}
			// トラックバック送信履歴のあるリモートの場合、最終受信日時+30分間は受信拒否する
			$sql =
						'SELECT '
						.	'received_date '
						.'FROM '
						.	'sz_blog_trackbacks '
						.'WHERE '
						.	'sz_blog_id = ? '
						.'AND '
						.	'ip_address = ? '
						.'ORDER BY '
						.	'received_date DESC '
						.'LIMIT 1';
			$query = $this->db->query($sql, array($eid, $ip));

			if ( $query && $query->row() )
			{
				$result = $query->row();
				$last_tmst = strtotime($result->received_date);
				$now       = strtotime("-30 minute");

				if ( $now > $last_tmst )
				{
					return TRUE;
				}
			}
			else
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * トラックバックデータ登録
	 */
	function receive_trackback($data)
	{
		return $this->db->insert('sz_blog_trackbacks', $data);
	}

	/**
	 * トラックバックリスト取得
	 * @param bool $no_allowed - 未承認のリクエストも取得するかどうか
	 */
	function get_requested_trackbacks($no_allowed = FALSE, $limit, $offset = 0)
	{
		if ( $no_allowed === TRUE )
		{
			$where = '';
		}
		else
		{
			$where = 'WHERE TB.is_allowed = 1 ';
		}

		$sql =
					'SELECT '
					.	'TB.sz_blog_trackbacks_id, '
					.	'TB.sz_blog_id, '
					.	'TB.title, '
					.	'TB.url, '
					.	'TB.blog_name, '
					.	'TB.excerpt, '
					.	'TB.received_date, '
					.	'TB.is_allowed, '
					.	'B.title as blog_title '
					.'FROM '
					.	'sz_blog_trackbacks as TB '
					.'JOIN '
					.	'sz_blog as B ON ( '
					.	'B.sz_blog_id = TB.sz_blog_id '
					.') '
					.	$where
					.'ORDER BY received_date DESC '
					.'LIMIT ? '
					.'OFFSET ?';

		$query = $this->db->query($sql, array($limit, $offset));
		return $query->result();
	}

	/**
	 * トラックバックリスト件数のみ取得
	 * @param bool $no_allowed - 未承認のリクエストも取得するかどうか
	 * @return int
	 */
	function get_requested_trackbacks_count($no_allowed = FALSE)
	{
		if ( $no_allowed === TRUE )
		{
			$where = '';
		}
		else
		{
			$where = 'WHERE TB.is_allowed = 1 ';
		}

		$sql =
					'SELECT '
					.	'COUNT(sz_blog_trackbacks_id) as total '
					.'FROM '
					.	'sz_blog_trackbacks '
					. $where;
		$query = $this->db->query($sql);
		$result = $query->row();
		return (int)$result->total;
	}

	/**
	 * トラックバック承認
	 */
	function update_allow_trackback($tb_id, $is_allow = FALSE)
	{
		$this->db->where('sz_blog_trackbacks_id', $tb_id);
		return $this->db->update('sz_blog_trackbacks', array('is_allowed' => (int)$is_allow));
	}

	/**
	 * トラックバック一括削除
	 */
	function delete_trackback($dels)
	{
		foreach ($dels as $v)
		{
			if (!ctype_digit($v))
			{
				continue;
			}
			$this->db->where('sz_blog_trackbacks_id', $v);
			$this->db->delete('sz_blog_trackbacks');
		}

		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * blogページのmeta情報取得
	 */
	function get_blog_meta()
	{
		$sql =
					'SELECT '
					. 'PV.page_title,'
					. 'PV.meta_title,'
					. 'PV.meta_keyword,'
					. 'PV.meta_description '
					.'FROM '
					. 'page_paths AS PP '
					.'JOIN '
					. 'page_versions AS PV '
					.'ON '
					. 'PP.page_id = PV.page_id '
					.'WHERE '
					. 'PV.is_public = 1 '
					.'AND '
					. 'PP.page_path = ? '
					.'LIMIT 1';

		$query = $this->db->query($sql, array('blog'));

		if($query->num_rows() > 0)
		{
			return $query->row();
		}
		else
		{
			return array();
		}
	}

	/**
	 * 現在の日付に対して次の記事を取得
	 * @param $current_date
	 */
	function get_next_article($current_date)
	{
		$sql =
			'SELECT '
			.	'sz_blog_id, '
			.	'permalink '
			.'FROM '
			.	'sz_blog '
			.'WHERE '
			.	'entry_date > ? '
			.'AND '
			.	'is_public = 1 '
			.'AND '
			.	'show_datetime < NOW() '
			.'ORDER BY '
			.	'entry_date ASC '
			.'LIMIT 1';

		$query = $this->db->query($sql, array($current_date));

		if ( $query && $query->row() )
		{
			return $query->row();
		}
		return FALSE;
	}

	/**
	 * 現在の日付に対して前の記事を取得
	 * @param $current_date
	 */
	function get_previous_article($current_date)
	{
		$sql =
			'SELECT '
			.	'sz_blog_id, '
			.	'permalink '
			.'FROM '
			.	'sz_blog '
			.'WHERE '
			.	'entry_date < ? '
			.'AND '
			.	'is_public = 1 '
			.'AND '
			.	'show_datetime < NOW() '
			.'ORDER BY '
			.	'entry_date DESC '
			.'LIMIT 1';

		$query = $this->db->query($sql, array($current_date));

		if ( $query && $query->row() )
		{
			return $query->row();
		}
		return FALSE;
	}
}
