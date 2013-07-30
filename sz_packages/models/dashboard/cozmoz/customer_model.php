<?php
/* =============================================================================
 * 顧客モデル
 * ========================================================================== */
class customer_model extends Model
{
	
	//カスタマーテーブル
	public $customer_table_name = 'cozmoz_customer';
	
	//事件簿テーブル
	public $matter_table_name = 'cozmoz_matter';
	
	//課税項目
	public $matter_detail_tax_table_name = 'cozmoz_matter_detail_tax';
	
	//非課税項目
	public $matter_detail_notax_table_name = 'cozmoz_matter_detail_notax';
	
	//業務種別
	public $business_type_table_name = 'cozmoz_business_type';
	
	//受注ステータス
	public $order_status_table_name = 'cozmoz_order_status';
	
	//事務所情報
	public $office_info_table_name = 'cozmoz_office_info';
	
	//報酬額定義テーブル
	public $remuneration_table_name = 'cozmoz_remuneration';
	
	//議事録テーブル
	public $meeting_minutes_table_name = 'cozmoz_meeting_minutes';
	
	//ドキュメントリスト
	public $document_list_table_name = 'cozmoz_document_list';
	
	//キャシュレジスター
	public $cache_register_table_name = 'cozmoz_cache_register';
	
	//キャシュレジスター明細行
	public $cache_register_detail_table_name = 'cozmoz_cache_register_detail';
	
	//キャッシュレジスターオーダー状態
	public $cache_register_order_status_table_name = 'cozmoz_cache_register_order_status';
	
	//キャッシュレジスター支払い方法
	public $cache_register_payment_method_table_name = 'cozmon_cache_register_payment_method';
	
	//公的書類請求番号管理
	public $public_document_table_name = 'public_document';
	
	// ----------------------------------------------------
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
	}
	
	// ----------------------------------------------------
	
	/**
	 * テーブルチェック
	 */
	public function table_check()
	{
		$this->check_cutomer_table();
		$this->check_matter_table();
		$this->check_matter_detail_tax_table();
		$this->check_matter_detail_notax_table();
		$this->check_business_type_table();
		$this->order_status_table_check();
		$this->office_info_table_check();
		$this->remuneration_table_chack();
		$this->meeting_minutes_table_check();
		$this->document_list_table_check();
		$this->cache_register_table_check();
		$this->cache_register_detail_table_check();
		$this->regi_order_status_table_check();
		$this->regi_payment_method_table_check();
		$this->public_document_table_check();
	}
	
	// ----------------------------------------------------
	
	/**
	 * 顧客テーブルをチェック
	 */
	public function check_cutomer_table()
	{
		if( $this->db->table_exists( $this->customer_table_name ) )
		{
			return;
		}
		
		$table = 'CREATE  TABLE IF NOT EXISTS `' . $this->customer_table_name . '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `company` VARCHAR(120) NULL ,
			  `honorific` VARCHAR(45) NOT NULL ,
			  `name1` VARCHAR(45) NOT NULL ,
			  `name2` VARCHAR(45) NOT NULL ,
			  `kana1` VARCHAR(45) NOT NULL ,
			  `kana2` VARCHAR(45) NOT NULL ,
			  `tel` VARCHAR(80) NULL ,
			  `fax` VARCHAR(80) NULL ,
			  `mailaddress` VARCHAR(256) NULL ,
			  `zip` VARCHAR(45) NULL ,
			  `pref` VARCHAR(45) NULL ,
			  `address1` TEXT NULL ,
			  `address2` TEXT NULL ,
			  `address3` TEXT NULL ,
			  `create_date` DATETIME NULL ,
			  `update_date` DATETIME NULL ,
			  PRIMARY KEY (`id`) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		
		$this->db->query( $table );
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * 事件簿テーブルのチェック
	 */
	public function check_matter_table()
	{
		if( $this->db->table_exists( $this->matter_table_name ) )
		{
			return;
		}
		
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->matter_table_name. '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `customer_id` INT UNSIGNED NOT NULL ,
			  `name` VARCHAR(256) NULL COMMENT \'事件名\' ,
			  `order_id` INT UNSIGNED NULL ,
			  `order_status` TINYINT NOT NULL DEFAULT 0 COMMENT \'受注場外状態\' ,
			  `tax_type` TINYINT NOT NULL DEFAULT 0 COMMENT \'消費税タイプ\n0:外税\n1:課税なし\' ,
			  `tax_rate` TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT \'税率(%)\' ,
			  `advances_received` INT UNSIGNED NULL DEFAULT 0 COMMENT \'前受金\' ,
			  `discount` INT UNSIGNED NULL DEFAULT 0 COMMENT \'値引き\' ,
			  `tax_total` INT UNSIGNED NOT NULL DEFAULT 0 ,
			  `grand_total` INT UNSIGNED NULL DEFAULT 0 COMMENT \'総合計\' ,
			  `remark_est` TEXT NULL COMMENT \'見積書備考\' ,
			  `remark_bill` TEXT NULL COMMENT \'請求書備考\n\' ,
			  `remark_rec` TEXT NULL COMMENT \'領収書備考\' ,
			  `date_est` DATE NULL COMMENT \'見積もり日付\' ,
			  `date_bill` DATE NULL COMMENT \'請求書日付\' ,
			  `date_rec` DATE NULL COMMENT \'領収書日付\' ,
			  `create_date` DATETIME NULL ,
			  `update_date` DATETIME NULL ,
			  `order_date` DATETIME NULL ,
			  PRIMARY KEY (`id`) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		$this->db->query( $sql );
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * 課税項目テーブルチェック
	 */
	public function check_matter_detail_tax_table()
	{
		if( $this->db->table_exists( $this->matter_detail_tax_table_name ) )
		{
			return;
		}
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->matter_detail_tax_table_name . '` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
				  `matter_id` INT UNSIGNED NOT NULL ,
				  `business_type_id` INT UNSIGNED NOT NULL DEFAULT 0 ,
				  `name` TEXT NULL ,
				  `remark` TEXT NULL ,
				  `price` INT UNSIGNED NOT NULL ,
				  `qty` INT UNSIGNED NOT NULL ,
				  `total` INT UNSIGNED NOT NULL ,
				  PRIMARY KEY (`id`) )
				ENGINE = MyISAM
				DEFAULT CHARACTER SET = utf8
				COLLATE = utf8_general_ci
				COMMENT = \'明細行課税項目\'';
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * 非課税項目テーブルチェック
	 */
	public function check_matter_detail_notax_table()
	{
		if( $this->db->table_exists( $this->matter_detail_notax_table_name ) )
		{
			return;
		}
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->matter_detail_notax_table_name . '` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
				  `matter_id` INT UNSIGNED NOT NULL ,
				  `name` TEXT NULL ,
				  `remark` TEXT NULL ,
				  `price` INT UNSIGNED NOT NULL ,
				  `qty` INT UNSIGNED NOT NULL ,
				  `total` INT UNSIGNED NOT NULL ,
				  PRIMARY KEY (`id`) )
				ENGINE = MyISAM
				DEFAULT CHARACTER SET = utf8
				COLLATE = utf8_general_ci
				COMMENT = \'明細行課税項目\'';
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * 職務上請求番号
	 */
	public function public_document_table_check()
	{
		if( $this->db->table_exists( $this->public_document_table_name ) )
		{
			return;
		}
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->public_document_table_name . '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `matter_id` INT UNSIGNED NOT NULL ,
			  `num1` VARCHAR(40) NOT NULL DEFAULT \'00\' ,
			  `num2` VARCHAR(120) NOT NULL DEFAULT \'0000000\' ,
			  `name` TEXT NULL ,
			  `rank` TINYINT UNSIGNED NOT NULL DEFAULT 0 ,
			  PRIMARY KEY (`id`) ,
			  INDEX `index_matter_id` (`matter_id` ASC) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * 業務種別
	 */
	public function check_business_type_table()
	{
		if( $this->db->table_exists( $this->business_type_table_name ) )
		{
			return;
		}
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->business_type_table_name . '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `name` VARCHAR(125) NOT NULL ,
			  `standard_price` INT UNSIGNED NOT NULL ,
			  `rank` INT UNSIGNED NOT NULL DEFAULT 0 ,
			  PRIMARY KEY (`id`) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		
		$this->db->query( $sql );
	}
	// ----------------------------------------------------
	
	/**
	 * 業務種別一覧取得
	 * @return array
	 */
	public function get_business_type_list()
	{
		$sql = 'SELECT * FROM ' . $this->business_type_table_name . ' ORDER BY rank';
		$res = $this->db->query( $sql )->result_array();
		$return = array();
		$return['0'] = '該当項目なし';
		foreach( $res as $row )
		{
			$return[ (string)$row['id'] ] = $row['name'];
		}
		return $return;
	}
	
	// ----------------------------------------------------
	
	/**
	 * 受注ステータス定義
	 */
	public function order_status_table_check()
	{
		if( $this->db->table_exists( $this->order_status_table_name ) )
		{
			return;
		}
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->order_status_table_name . '` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
				  `name` TEXT NOT NULL ,
				  `rank` TINYINT UNSIGNED NULL DEFAULT 0 ,
				  `on_order` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT \'1が渡ると受注済\n2が渡ると仕事終了\n3で入金\' ,
				  PRIMARY KEY (`id`) )
				ENGINE = MyISAM
				DEFAULT CHARACTER SET = utf8
				COLLATE = utf8_general_ci';
		
		
		$this->db->query( $sql );
	}
	// ----------------------------------------------------
	
	/**
	 * 受注ステータス取得
	 * @return 
	 */
	public function get_order_status_view()
	{
		$sql = 'SELECT * FROM `' . $this->order_status_table_name . '` ORDER BY rank';
		$res = $this->db->query( $sql )->result_array();
		array_unshift(
				$res,
				array(
					'id' => 0,
					'name' => '未選択',
					'rank' => 0,
				)
			);
		return $res;
	}
	
	// ----------------------------------------------------
	
	/**
	 * 事務所情報
	 */
	public function office_info_table_check()
	{
		if( $this->db->table_exists( $this->office_info_table_name ) )
		{
			return;
		}
		
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->office_info_table_name . '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `office_name` VARCHAR(256) NOT NULL ,
			  `division` TINYINT NOT NULL DEFAULT 0 COMMENT \'法人／個人区分\n0:個人\n1:法人\' ,
			  `name` VARCHAR(256) NOT NULL ,
			  `belongs` VARCHAR(125) NOT NULL COMMENT \'所属行政書士会\' ,
			  `belong_childre` VARCHAR(125) NULL COMMENT \'所属支部\' ,
			  `belong_number` VARCHAR(256) NULL COMMENT \'会員番号\' ,
			  `reg_number` VARCHAR(125) NULL COMMENT \'登録番号\' ,
			  `zip` VARCHAR(30) NULL ,
			  `pref` VARCHAR(45) NULL ,
			  `address1` TEXT NULL ,
			  `address2` TEXT NULL ,
			  `address3` TEXT NULL ,
			  `tel` TEXT NULL ,
			  `fax` TEXT NULL ,
			  `mailaddress` TEXT NULL ,
			  `bank_com` TEXT NULL COMMENT \'振込銀行名\' ,
			  `bank_shop` TEXT NULL ,
			  `bank_division` TINYINT UNSIGNED NULL COMMENT \'口座区分\n0:普通\n1:当座\' ,
			  `bank_name` TEXT NULL ,
			  `bank_id` TEXT NULL ,
			  PRIMARY KEY (`id`) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		
		$this->db->query( $sql );
		$this->db->insert(
				$this->office_info_table_name,
				array(
					'office_name' => '',
					'belongs' => '',
					'division' => 0,
					'name' => '',
					'name' => '',
				)
			);
	}
	
	// ----------------------------------------------------
	
	/**
	 * 報酬額定義テーブル
	 */
	public function remuneration_table_chack()
	{
		if( $this->db->table_exists( $this->remuneration_table_name ) )
		{
			return;
		}
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->remuneration_table_name. '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `business_type_id` INT UNSIGNED NULL ,
			  `name` VARCHAR(256) NULL ,
			  `standard_price` INT UNSIGNED NULL DEFAULT 0 ,
			  `rank` INT UNSIGNED NULL DEFAULT 0 ,
			  PRIMARY KEY (`id`) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	/**
	 * 報酬額定義を取得
	 */
	public function get_remuneration_list()
	{
		$sql = 'SELECT * FROM ' . $this->remuneration_table_name . ' ORDER BY rank';
		$res = $this->db->query( $sql )->result_array();
		array_unshift(
				$res,
				array(
					'id' => '0',
					'business_type_id' => '0',
					'name' => '該当なし',
					'standard_price' => 0
				)
			);
		return $res;
	}
	
	// ----------------------------------------------------
	
	/**
	 * 議事録テーブルチェック
	 */
	public function meeting_minutes_table_check()
	{
		if( $this->db->table_exists($this->meeting_minutes_table_name))
		{
			return;
		}
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->meeting_minutes_table_name . '` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
				`matter_id` INT UNSIGNED NOT NULL ,
				`date` DATE NULL ,
				`name` TEXT NULL ,
				`comment` LONGTEXT NULL ,
				PRIMARY KEY (`id`) )
				ENGINE = MyISAM
				DEFAULT CHARACTER SET = utf8
				COLLATE = utf8_general_ci';
		
		$this->db->query( $sql );
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * ドキュメントリスト
	 */
	public function document_list_table_check()
	{
		if( $this->db->table_exists($this->document_list_table_name))
		{
			return;
		}
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->document_list_table_name . '` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
				`matter_id` INT UNSIGNED NOT NULL ,
				`name` TEXT NULL ,
				`file_id` INT UNSIGNED NULL ,
				`rank` INT UNSIGNED NOT NULL DEFAULT 0 ,
				PRIMARY KEY (`id`) )
				ENGINE = MyISAM
				DEFAULT CHARACTER SET = utf8
				COLLATE = utf8_general_ci';
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * キャッシュレジスターテーブル
	 */
	public function cache_register_table_check()
	{
		if( $this->db->table_exists($this->cache_register_table_name))
		{
			return;
		}
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->cache_register_table_name . '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `create_date` DATETIME NOT NULL ,
			  `order_status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT \'0:入力中\n1:確定\n2:決済済み\' ,
			  `customer_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT \'1以上が入っていれば顧客情報と紐付け\' ,
			  `subject` VARCHAR(256) NULL COMMENT \'タイトル\' ,
			  `item_total` INT UNSIGNED NOT NULL DEFAULT 0 ,
			  `discount` INT UNSIGNED NOT NULL DEFAULT 0 ,
			  `grand_total` INT UNSIGNED NOT NULL DEFAULT 0 ,
			  `payment_method` TINYINT UNSIGNED NOT NULL DEFAULT 0 ,
			  `pay_amount` INT UNSIGNED NOT NULL DEFAULT 0 ,
			  `pay_change` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT \'お釣り\' ,
			  `payment_date` DATETIME NULL ,
			  `sign_file` TEXT NULL ,
			  PRIMARY KEY (`id`) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * キャッシュレジスター明細行
	 */
	public function cache_register_detail_table_check()
	{
		if( $this->db->table_exists($this->cache_register_detail_table_name))
		{
			return;
		}
		$sql = 'CREATE  TABLE IF NOT EXISTS `'. $this->cache_register_detail_table_name . '` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
				  `cache_register_id` INT UNSIGNED NOT NULL ,
				  `item_id` INT UNSIGNED NULL DEFAULT 0 COMMENT \'1以上が入っていれば、\n登録済み商品からのデータ\' ,
				  `name` VARCHAR(256) NOT NULL ,
				  `remark` VARCHAR(256) NOT NULL ,
				  `price` INT UNSIGNED NOT NULL DEFAULT 0 ,
				  `qty` INT UNSIGNED NOT NULL DEFAULT 1 ,
				  `sub_total` INT UNSIGNED NOT NULL DEFAULT 0 ,
				  `rank` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT \'表示順\' ,
				  PRIMARY KEY (`id`) )
				ENGINE = MyISAM
				DEFAULT CHARACTER SET = utf8
				COLLATE = utf8_general_ci';
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * レジスター用オーダーステータス
	 */
	public function regi_order_status_table_check()
	{
		if( $this->db->table_exists($this->cache_register_order_status_table_name))
		{
			return;
		}
		
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->cache_register_order_status_table_name . '` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			  `name` VARCHAR(256) NOT NULL ,
			  `rank` TINYINT UNSIGNED NOT NULL DEFAULT 0 ,
			  `on_order` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT \'オーダー状態\n0:未支払い\n1:支払済み\n2:掛売り\n3:キャンセル\' ,
			  PRIMARY KEY (`id`) )
			ENGINE = MyISAM
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci';
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * キャッシュレジスタ0支払い方法
	 */
	public function regi_payment_method_table_check()
	{
		
		if( $this->db->table_exists($this->cache_register_payment_method_table_name) )
		{
			return;
		}
		
		$sql = 'CREATE  TABLE IF NOT EXISTS `' . $this->cache_register_payment_method_table_name . '` (
		  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
		  `name` TEXT NOT NULL ,
		  `rank` TINYINT UNSIGNED NOT NULL DEFAULT 0 ,
		  `pay_type` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT \'現金の取り扱いタイプ\n0:現金\n1:クレジット\n2:プリペイドデポジット\n3:売掛\',
		  PRIMARY KEY (`id`) )
		ENGINE = MyISAM
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_general_ci';
		
		$this->db->query( $sql );
	}
	
	// ----------------------------------------------------
	
	/**
	 * 事務所用選択肢 法人個人区分
	 * @returrn 
	 */
	public function get_office_info_division()
	{
		$arr = array(
			'0' => '個人',
			'1' => '法人',
		);
		return $arr;
	}
	
	// ----------------------------------------------------
	
	/**
	 * 口座区分取得
	 * @return array
	 */
	public function get_bank_division()
	{
		$arr = array(
			'0' => '普通',
			'1' => '当座',
		);
		return $arr;
	}
	
	// ----------------------------------------------------
	
	/**
	 * 都道府県リスト取得
	 * @return arra
	 */
	public function get_pref_list()
	{
		$pref = array(
			'北海道' => '北海道',
			'青森県' => '青森県',
			'岩手県' => '岩手県',
			'宮城県' => '宮城県',
			'秋田県' => '秋田県',
			'山形県' => '山形県',
			'福島県' => '福島県',
			'茨城県' => '茨城県',
			'栃木県' => '栃木県',
			'群馬県' => '群馬県',
			'埼玉県' => '埼玉県',
			'千葉県' => '千葉県',
			'東京都' => '東京都',
			'神奈川県' => '神奈川県',
			'新潟県' => '新潟県',
			'富山県' => '富山県',
			'石川県' => '石川県',
			'福井県' => '福井県',
			'山梨県' => '山梨県',
			'長野県' => '長野県',
			'岐阜県' => '岐阜県',
			'静岡県' => '静岡県',
			'愛知県' => '愛知県',
			'三重県' => '三重県',
			'滋賀県' => '滋賀県',
			'京都府' => '京都府',
			'大阪府' => '大阪府',
			'兵庫県' => '兵庫県',
			'奈良県' => '奈良県',
			'和歌山県' => '和歌山県',
			'鳥取県' => '鳥取県',
			'島根県' => '島根県',
			'岡山県' => '岡山県',
			'広島県' => '広島県',
			'山口県' => '山口県',
			'徳島県' => '徳島県',
			'香川県' => '香川県',
			'愛媛県' => '愛媛県',
			'高知県' => '高知県',
			'福岡県' => '福岡県',
			'佐賀県' => '佐賀県',
			'長崎県' => '長崎県',
			'熊本県' => '熊本県',
			'大分県' => '大分県',
			'宮崎県' => '宮崎県',
			'鹿児島県' => '鹿児島県',
			'沖縄県' => '沖縄県'
		);
		return $pref;
	}
	
	// ----------------------------------------------------
}