<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Install_hospital
{
	const TPL_PATH = 'templates/hospital_top/';
	protected $use_areas = array('navi', 'main_img', 'left', 'main', 'right', 'footer_navi');
	protected $required_images = array(
										array('appearance.jpg', 'jpg'),
										array('main-img.png', 'png')
									);
	protected $added_images = array();
	protected $added_areas  = array();
	protected $max_orders   = array();
	protected $CI;
	protected $now;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model(array('template_model', 'sitemap_model', 'dashboard_model', 'file_model'));
		$this->now = db_datetime();
	}
	
	public function run()
	{
		// 一旦テンプレートを削除
		$this->CI->db->query("TRUNCATE TABLE templates");
		// TOPテンプレートインストール
		$template_top = $this->CI->template_model->install_template('hospital_top');
		// 中ページテンプレートインストール
		$template_inner = $this->CI->template_model->install_template('hospital_inner');
		
		// 必要なファイルをファイルマネージャに登録
		$this->add_required_template_images();
		
		// TOPページのタイトルを「ホーム」に
		$sql = 'UPDATE page_versions SET page_title = ? WHERE page_id = 1';
		$query = $this->CI->db->query($sql, array('ホーム'));
		
		// デフォルトテンプレートIDを中ページに変更
		$sql = 'UPDATE site_info SET default_template_id = ?';
		$this->CI->db->query($sql, array($template_inner));
		
		// 既存のシステムページのテンプレートIDを1にセット
		$sql = 'UPDATE page_versions SET template_id = ? WHERE page_id IN (30, 39, 40, 45)';
		$query = $this->CI->db->query($sql, array($template_inner));
		
		// エリアとブロックの内容を初期化
		$this->_truncate_contents();
		
		// 改めて必要なものをINSERT
		$this->_add_area();
		$this->_add_block();
		
		// complete!
	}
	
	protected function add_required_template_images()
	{
		$this->CI->load->library('image_lib');
		
		foreach ( $this->required_images as $image )
		{
			$path = FCPATH . self::TPL_PATH . 'images/' . $image[0];
			$info = getimagesize($path);
			$dat = array(
				'file_name' => $image[0],
				'crypt_name' => md5(uniqid(mt_rand(), TRUE) . $image[0]),
				'extension' => $image[1],
				'size'  => round(filesize($path) / 1024),
				'width' => $info[0],
				'height' => $info[1],
				'added_date' => $this->now,
				'directories_id' => 1,
				'file_group' => ''
			);
			$conf = array(
				'source_image'		=> $path,
				'create_thumb'		=> TRUE,
				'new_image'			=> FCPATH . 'files/thumbnail/',
				'thumb_marker'		=> '',
				'width'				=> 60,
				'height'				=> 60,
				'maintain_ratio'		=> TRUE
			);
			$this->CI->image_lib->initialize($conf);
			$ret1 = $this->CI->image_lib->resize();
			
			$dest_path = FCPATH . 'files/' . $dat['crypt_name'] . '.' . $dat['extension']; 
			$ret2 = copy($path, $dest_path);
			
			if ( $ret1 && $ret2 )
			{
				$id = $this->CI->file_model->insert_new_image($dat);
				$this->added_images[$image[0]] = $id; 
			}
			
		}
	}
	
	protected function _truncate_contents()
	{
		$this->CI->db->simple_query('TRUNCATE TABLE areas');
		$this->CI->db->simple_query('TRUNCATE TABLE blocks');
		$this->CI->db->simple_query('TRUNCATE TABLE block_versions');
		
		$this->CI->db->simple_query('TRUNCATE TABLE sz_bt_textcontent');
		$this->CI->db->simple_query('TRUNCATE TABLE sz_bt_image_block');
		$this->CI->db->simple_query('TRUNCATE TABLE sz_bt_head_block');
		$this->CI->db->simple_query('TRUNCATE TABLE sz_bt_auto_navigation');
	}
	
	protected function _add_area()
	{
		foreach ( $this->use_areas as $area )
		{
			$dat = array(
				'area_name' => $area,
				'page_id'   => 1,
				'created_date' => $this->now
			);
			$this->CI->db->insert('areas', $dat);
			$this->added_areas[$area] = $this->CI->db->insert_id();
			$this->max_orders[$area]  = 0;
		}
	}
	
	protected function _add_block()
	{
		// main image
		$id = $this->_add_block_master('image', 'main_img');
		$sql = "INSERT INTO `sz_bt_image_block` (`block_id`, `file_id`, `alt`, `link_to`, `action_method`, `action_file_id`) VALUES (?, ?, NULL, NULL, NULL, NULL);";
		$query = $this->CI->db->query($sql, array($id, $this->added_images['main-img.png']));
		
		// auto navigation
		$id = $this->_add_block_master('auto_navigation', 'navi');
		$sql = "INSERT INTO `sz_bt_auto_navigation` (`block_id`, `sort_order`, `based_page_id`, `subpage_level`, `manual_selected_pages`, `handle_class`, `display_mode`, `show_base_page`, `current_class`) VALUES (?, 1, 1, 1, '0', NULL, 2, 1, 'current');";
		$query = $this->CI->db->query($sql, array($id));
		
		// left area
		// head
		$id = $this->_add_block_master('head', 'left');
		$sql = "INSERT INTO `sz_bt_head_block` (`block_id`, `head_level`, `class_name`, `text`) VALUES (?, 2, 'item2', '診療内容');";
		$query = $this->CI->db->query($sql, array($id));
		
		// main
		// head
		$id = $this->_add_block_master('head', 'main');
		$sql = "INSERT INTO `sz_bt_head_block` (`block_id`, `head_level`, `class_name`, `text`) VALUES (?, 2, 'item1', '診療時間');";
		$query = $this->CI->db->query($sql, array($id));
		// textcontent
		$id = $this->_add_block_master('textcontent', 'main');
		$body = <<<END
		<table> 
			<tr> 
				<th>診療時間</th> 
				<td>月</td> 
				<td>火</td> 
				<td>水</td> 
				<td>木</td> 
				<td>金</td> 
				<td class="saturday">土</td> 
				<td class="sunday">日・祝</td> 
			</tr> 
			<tr> 
				<th>午前&nbsp;9:00〜12:00</th> 
				<td>○</td> 
				<td>○</td> 
				<td>○</td> 
				<td>○</td> 
				<td>○</td> 
				<td>休</td> 
				<td>休</td> 
			</tr> 
			<tr> 
				<th>午後&nbsp;14:00〜17:00</th> 
				<td>○</td> 
				<td>×</td> 
				<td>○</td> 
				<td>○</td> 
				<td>×</td> 
				<td>休</td> 
				<td>休</td> 
			</tr> 
		</table> 
	
		<p class="caution">※年末年始・お盆はお休みをいただいております。</p>
END;
		$sql = "INSERT INTO `sz_bt_textcontent` (`block_id`, `body`) VALUES (?, ?)";
		$query = $this->CI->db->query($sql, array($id, $body));
		// head
		$id = $this->_add_block_master('head', 'main');
		$sql = "INSERT INTO `sz_bt_head_block` (`block_id`, `head_level`, `class_name`, `text`) VALUES (?, 2, 'item3', 'お知らせ');";
		$query = $this->CI->db->query($sql, array($id));
		
		// right area
		// head
		$id = $this->_add_block_master('head', 'right');
		$sql = "INSERT INTO `sz_bt_head_block` (`block_id`, `head_level`, `class_name`, `text`) VALUES (?, 2, 'item4', '当院の案内');";
		$query = $this->CI->db->query($sql, array($id));
		// image
		$id = $this->_add_block_master('image', 'right');
		$sql = "INSERT INTO `sz_bt_image_block` (`block_id`, `file_id`, `alt`, `link_to`, `action_method`, `action_file_id`) VALUES (?, ?, NULL, NULL, NULL, NULL);";
		$query = $this->CI->db->query($sql, array($id, $this->added_images['appearance.jpg']));
		// textcontent
		$id = $this->_add_block_master('textcontent', 'right');
		$body = <<<END
		<ul> 
			<li>音生総合病院</li> 
			<li>〒000-0000</li> 
			<li>愛知県名古屋市中区金山32-1</li> 
			<li>tel:000-0000-0000</li> 
			<li>fax:000-0000-0000</li> 
		</ul> 
END;
		$sql = "INSERT INTO `sz_bt_textcontent` (`block_id`, `body`) VALUES (?, ?)";
		$query = $this->CI->db->query($sql, array($id, $body));
		
		// footer area
		//autonavigation
		$id = $this->_add_block_master('auto_navigation', 'footer_navi');
		$sql = "INSERT INTO `sz_bt_auto_navigation` (`block_id`, `sort_order`, `based_page_id`, `subpage_level`, `manual_selected_pages`, `handle_class`, `display_mode`, `show_base_page`, `current_class`) VALUES (?, 1, 1, 1, '0', NULL, 2, 1, 'current');";
		$query = $this->CI->db->query($sql, array($id));
	}
	
	protected function _add_block_master($handle, $area_name)
	{
		$this->CI->db->insert('blocks',
								array(
									'collection_name' => $handle,
									'is_active' => 1,
									'created_time' => $this->now
								)
							);
		$id = $this->CI->db->insert_id();
//		$sql = 'SELECT area_id FROM areas WHERE area_name = ? AND page_id = 1';
//		$query = $this->CI->db->query($sql, array($area_name));
//		$result = $query->result();
//		$area_id = $result->area_id;
		$area_id = $this->added_areas[$area_name];
		$this->CI->db->insert('block_versions',
								array(
									'block_id' => $id,
									'collection_name' => $handle,
									'area_id' => $area_id,
									'display_order' => ++$this->max_orders[$area_name],
									'is_active' => 1,
									'version_date' => $this->now,
									'version_number' => 2,
									'ct_handle' => ''
								)
							);
		return $id;
	}
}