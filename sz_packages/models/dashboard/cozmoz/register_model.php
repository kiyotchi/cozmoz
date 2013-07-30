<?php
/* =============================================================================
 * キャッシュレジスターモデル
 * ========================================================================== */
class register_model extends Model
{
	
	//キャシュレジスター
	public $cache_register_table_name = 'cozmoz_cache_register';
	
	//キャシュレジスター明細行
	public $cache_register_detail_table_name = 'cozmoz_cache_register_detail';
	
	//キャッシュレジスターオーダー状態
	public $cache_register_order_status_table_name = 'cozmoz_cache_register_order_status';
	
	//キャッシュレジスター支払い方法
	public $cache_register_payment_method_table_name = 'cozmon_cache_register_payment_method';
	
	
	
	// ----------------------------------------------------
	
	/**
	 * 新規レコードを作成してIDを返す
	 */
	public function new_order()
	{
		$data = array(
			'create_date' => date('Y-m-d H:i:s')
		);
		$this->db->insert( $this->cache_register_table_name, $data );
		return $this->db->insert_id();
	}
	
	// ----------------------------------------------------
	
	/**
	 * レジスター用ステータス取得
	 * @return array
	 */
	public function get_order_status()
	{
		$sql = 'SELECT * FROM ' . $this->cache_register_order_status_table_name . ' ORDER BY rank';
		$res = $this->db->query( $sql )->result_array();
		array_unshift(
				$res ,
				array(
					'id' => 0,
					'name' => '未定義',
					'rank' => 0,
					'on_order' => 0
				)
			);
		return $res;
	}
	
	// ----------------------------------------------------
	
	/**
	 * レジスター用お支払い方法リスト
	 * @return array
	 */
	public function get_order_paymen_list()
	{
		$sql = 'SELECT * FROM ' . $this->cache_register_payment_method_table_name . ' ORDER BY rank';
		$res = $this->db->query( $sql )->result_array();
		array_unshift(
				$res ,
				array(
					'id' => 0,
					'name' => '未定義',
					'rank' => 0,
					'on_order' => 0
				)
			);
		return $res;
	}
	
	// ----------------------------------------------------
}