<?php
require_once APPPATH . 'libraries/thirdparty/phpQuery.php';
class im_emulator
{
	//CIオブジェクト
	protected $ci;
	
	//現在登録されている親
	protected $parents = array();
	
	
	//定義保存
	protected $def;
	
	//DBのノードリスト
	protected $db_nodes;
	
	//現在のリピートノード
	protected $current_repeat = array();
	
	//現在リピートしているもの
	protected $current_repeat_target = '';
	
	//前リピートしていたもの
	protected $prev_repeat_target = '';
	
	//コンパイル前HTML保存ディレクトリ
	public $tmpl_dir;
	
	//コンパイルHTMLディレクトリ
	public $comp_dir;
	
	//最優先設定
	public $def_primary = null;
	
	// ---------------------------------------------------	
	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		$this->ci = get_instance();
		$this->tmpl_dir = FCPATH . 'sz_packages/IM_views/';
		$this->comp_dir = FCPATH . 'sz_packages/IM_compile/';
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * 繰り返しノード
	 * @param string $class これからループするモノをチェック
	 * @return array
	 */
	public function repeat( $class )
	{
		foreach( $this->db_nodes[$class] as $key => $type )
		{
			if(!array_key_exists($key, $this->def))
			{
				echo $key . 'asdfafa<br />';
				continue;
			}
			
			
			//DB状態
			if(! array_key_exists($key, $this->current_repeat) )
			{
				$this->current_repeat[$key] = array(
					'current_loop' => false,//現在ループ中か
					'current_index' => 0,//現在のインデックス
					'query' => null,
					'current_type' => $type,
					'current_row' => null,
				);
			}
			
			
			//DBクエリの構築
			if(
				( $this->current_repeat[$key]['query'] === NULL )&&
				( $type === 'PARENT' )
			){
				$this->prev_repeat_target = $this->current_repeat_target;
				
				
				$this->ci->db->select('*');
				
				//取得レコードの件数
				if(isset($this->def[$key]['records']))
				{
					$this->ci->db->limit( $this->def[$key]['records'] );
				}
				else
				{
					$this->ci->db->limit( 14440 );
				}
				
				//クエリ
				if(isset($this->def[$key]['query'] ))
				{
					foreach( $this->def[$key]['query'] as $one )
					{
						if( $one['operator'] == 'LIKE' )
						{
							$this->ci->db->like( $one['field'], $one['value'] );
						}
						else
						{
							$this->ci->db->where( $one['field'] . ' ' . $one['operator'] , $one['value'] );
						}
					}
				}
				
				//リレーション
				if( (isset($this->def[$key]['relation'] )) && ( $this->prev_repeat_target != '' ) )
				{
					foreach( $this->def[$key]['relation'] as $one )
					{
						
						$this->ci->db->where(
								$one['foreign-key'] . ' ' . $one['operator'] ,
								$this->current_repeat[$this->prev_repeat_target]['current_row'][ $one['join-field'] ]
							);
					}
					
				}
				
				//ソート
				if( isset( $this->def[$key]['sort'] ) )
				{
					foreach( $this->def[$key]['sort'] as $one )
					{
						$this->db->order_by( $one['field'], $one['direction'] );
					}
				}
				
				
				//クエリ
				if( ( isset($this->def[$key]['view']) ) && ( $this->def[$key]['view'] != '' ))
				{
					$this->current_repeat[$key]['query'] = $this->ci->db->get( $this->def[$key]['view'] );
				}
				else
				{
					$this->current_repeat[$key]['query'] = $this->ci->db->get( $this->def[$key]['name'] );
				}
				
			}
			
			if( $type == 'PARENT' )
			{
				$this->current_repeat_target = $this->def[$key]['name'];
			}
			
		}
		
		if(
				($this->current_repeat[$this->current_repeat_target]['query']->num_rows())
					>
				( $this->current_repeat[$this->current_repeat_target]['current_index'] ) )
		{
			$row = $this->current_repeat[$this->current_repeat_target]['query']->row_array( $this->current_repeat[$this->current_repeat_target]['current_index'] );
			$this->current_repeat[$this->current_repeat_target]['current_row'] = $row;
			return $row;
		}
		else
		{
			//echo "\n<!-- //" . $this->current_repeat_target . ' ' . $this->prev_repeat_target . " -->\n";
			
			$this->current_repeat[$this->current_repeat_target] = array(
					'current_loop' => false,//現在ループ中か
					'current_index' => 0,//現在のインデックス
					'query' => null,
					'current_type' => $type,
					'current_row' => null,
			);
			
			$this->current_repeat_target = $this->prev_repeat_target;
			
			if( $this->current_repeat_target  == '' )
			{
				$this->current_repeat = array();
			}
			
			return false;
		}
		
		
		
	}
	// ----------------------------------------------------
	
	/**
	 * 定義を保存
	 * @param array $def
	 */
	public function def( $def )
	{
		foreach( $def as $one )
		{
			if( $this->def_primary === NULL )
			{
				$this->def_primary = $one;
			}
			$this->def[ $one['name'] ] = $one;
		}
		return;
	}
	
	// ----------------------------------------------------
	
	/**
	 * 親ノードの登録
	 * @param array $row
	 * @return void
	 */
	public function push_current($row)
	{
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * 親ノードの削除
	 * @return void
	 */
	public function next_node()
	{
		echo "\n<!-- " . $this->current_repeat_target . " -->\n";
		$this->current_repeat[$this->current_repeat_target]['current_index'] ++;
		
		
	}
	
	// ----------------------------------------------------
	
	/**
	 * 出力
	 * @param string $table
	 * @param string $field
	 * @param string $trans
	 * @return string
	 */
	public function output( $table, $field, $trans = null )
	{
		//var_dump($this->current_repeat[$table]);
		if(! isset( $this->current_repeat[$table] ) )
		{
			return false;
		}
		
		if( is_null($this->current_repeat[$table]['current_row']) )
		{
			return false;
		}
		
		return $this->current_repeat[$table]['current_row'][$field];
	}
	
	// ----------------------------------------------------
	
	/**
	 * メインノードのリスト
	 * @param string $nodes
	 * @return void
	 */
	public function set_main_node_list( $nodes )
	{
		$this->db_nodes = unserialize($nodes);
		return;
	}
	
	// ----------------------------------------------------
	
	/**
	 * ページネーションの出力
	 * @param string $node
	 * @return string
	 */
	public function pageing()
	{
		if( $this->current_repeat_target == '' )
		{
			return '優先ノードのページネーション';
		}
		return $this->current_repeat_target . 'のページネーション';
	}
	
	// ----------------------------------------------------
	
	/**
	 * パーサーの指導
	 * @param string $path
	 * @param array $data
	 * @param boolean $return
	 * @return mix
	 */
	public function parse( $path = null, $data = array(), $return = FALSE )
	{
		$data['IM'] = $this;
		
		$t_path = $this->tmpl_dir . $path . '.php';
		$mtime = filemtime( $t_path );
		$c_path = $this->comp_dir . $path . '__' . $mtime . '.php';
		
		
		//コンパイル済データが有る場合
		if(file_exists($c_path))
		{
			return $this->ci->load->view( $path . '__' . $mtime, $data, $return, $this->comp_dir );
		}
		
		
		////コンパイル
		
		//過去のコンパイル済データを削除
		$pinfo = pathinfo( $path );
		foreach( glob( $this->comp_dir . $pinfo['dirname'] . '/' . $pinfo['basename'] . '__*.php' ) as $delete )
		{
			unlink( $delete );
		}
		
		
		
		
		$html = $this->ci->load->view( $path, $data, TRUE, $this->tmpl_dir );
		$html = preg_replace( '/\<\!\-\-.*\-\-\>/Uis', '', $html );
		
		$doc = phpQuery::newDocumentPHP( $html );
		
		
		//IMを持つtbodyか
		$check_class = 'div._im_enclosure:has(*[class^=IM]),tbody:has(*[class^=IM])';
		$count = $doc[$check_class]->length();
		
		
		
		//ループを設定
		$doc[$check_class]->addClass( 'IM_CEHCKER' );
		$i = 0;
		$REPEAT_ID = 0;
		$check_class = array();
		
		$parent_delete_list = array();//親のDB情報から消すテーブル名を取得
		$db_node = array();//含まれるテーブル名を記録
		
		
		while( $i < $count )
		{
			//親の数を取得
			$parents = $doc['.IM_CEHCKER']->eq($i)->parents('.IM_CEHCKER')->length();
			
			//リピータのID
			$doc['.IM_CEHCKER']->eq($i)->addClass( 'IM_REPEAT_' . $REPEAT_ID );
			$check_class[ '.IM_REPEAT_' . $REPEAT_ID ] = $parents;
			
			$REPEAT_ID ++ ;
			$i ++ ;
		}
		arsort($check_class);
		foreach( $check_class as $key => $val )
		{
			
			$count = $doc[ $key ]->find( '*[class^=IM]' )->length();
			$x = 0;
			$tables = array();
			while( $x < $count )
			{
				$classes = explode( ' ', $doc[ $key ]->find( '*[class^=IM]' )->eq( $x )->attr('class') );
				
				foreach( $classes as $one )
				{
					$m = array();
					if(! preg_match( '/^IM\[(.*)\@/', $one, $m ) )
					{
						continue;
					}
					if(!in_array( $m[1], $tables ))
					{
						$tables[] = $m[1];
					}
				}
				
				$x++ ;
			}
			
			//DBと消す情報を記録
			$db_node[$key] = $tables;
			$x = 0;
			$count = $doc[$key]->parents('.IM_CEHCKER')->length();
			while( $x < $count )
			{
				foreach( explode( ' ', $doc[$key]->parents('.IM_CEHCKER')->eq($x)->attr( 'class' ) ) as $one )
				{
					if(!preg_match( '/^IM_REPEAT_/', $one )){ continue; }
					if(!array_key_exists( $one, $parent_delete_list ) )
					{
						$parent_delete_list['.'.$one] = $tables;
					}
					else{
						$parent_delete_list['.'.$one] = array_merge( $tables, $parent_delete_list[$one] );
					}
				}
				$x ++ ;
			}
			
			
			
			//PHPコードの記録
			$pre_php_code = 'while( $row = $IM->repeat( \'' . $key .  '\' ) ):' . "\n" . '$IM->push_current( $row );';
			$after_php_code = '$IM->next_node();' . "\n" . 'endwhile;';
			
			
			$doc[$key]->find('div._im_repeater:not(.IM_ALREADY_SET)')->beforePHP( $pre_php_code );
			$doc[$key]->find('tr:not(.IM_ALREADY_SET)')->beforePHP( $pre_php_code );
			$doc[$key]->find('div._im_repeater:not(.IM_ALREADY_SET)')->afterPHP( $after_php_code  );
			$doc[$key]->find('tr:not(.IM_ALREADY_SET)')->afterPHP( $after_php_code );
			
			
			//$doc[$key]->find('div._im_repeater:not(.IM_ALREADY_SET)')->find('.IM_NAVIGATOR')->empty()->appendPHP( 'echo $IM->pageing(\'' . $key . '\');');
			//$doc[$key]->find('div._im_repeater:not(.IM_ALREADY_SET)')->find('.IM_NAVIGATOR')->empty()->appendPHP( 'echo $IM->pageing(\'' . $key . '\');');
			
			
			$doc[$key]->find('div._im_repeater')->addClass('IM_ALREADY_SET');
			$doc[$key]->find('tr')->addClass('IM_ALREADY_SET');
			
			
		}
		
		
		//すべての項目の出力を定義
		$count = $doc['*[class^=IM]']->length();
		$count --;
		while( $count  > -1)
		{
			$classes = explode( ' ', $doc['*[class^=IM]']->eq($count)->attr('class') );
			foreach( $classes as $one )
			{
				if(!preg_match( '/^IM\[/', $one ) )
				{
					continue;
				}
				
				$defs = explode( '@', preg_replace( '/(^IM\[)|(\]$)/', '', $one ) );
				
				
				$doc['*[class^=IM]']->eq($count)->empty()->appendPHP(
					'echo $IM->output(' .
					implode( ' ,',
							array(
								'\''.$defs[0] . '\'',
								'\''.$defs[1] . '\'' 
							)
					).
					');'
				);
				
				
				
			}
			
			$count --;
		}
		
		//優先ノードのページング
		$doc['.IM_NAVIGATOR']->empty()->appendPHP( 'echo $IM->pageing();');
		
		
		
		//テーブルノードの記録
		//var_dump($parent_delete_list);
		$final_node = array();//
		foreach( $db_node as $key => $tables )
		{
			$final_node[$key] = array();
			foreach( $tables as $table )
			{
				if(array_key_exists($key, $parent_delete_list ))
				{
					if(!in_array( $table, $parent_delete_list[$key]))
					{
						$final_node[$key][$table] = 'PARENT';
					}
					else
					{
						$final_node[$key][$table] = 'CHILD';
					}
				}
				else
				{
					$final_node[$key][$table] = 'PARENT';
				}
			}
		}
		
		
		//余計なのは消す
		$doc['.IM_ALREADY_SET']->removeClass('IM_ALREADY_SET');
		
		//コードをたす（メインノードのリストを取得）
		$php_code = 
			'<?php $IM->set_main_node_list(\'' . serialize( $final_node ) . '\');?>'.
			$doc->php();
		
		
		//サブディレクトリ
		$sub_check = $this->comp_dir;
		foreach( explode( '/', $pinfo['dirname'] ) as $sub )
		{
			$check = $sub_check . '/' . $sub;
			if(is_dir($check))
			{
				continue;
			}
			mkdir($check);
		}
		
		
		
		
		//ファイルに保存
		$fh = fopen( $c_path, 'w' );
		fputs( $fh, $php_code );
		fclose( $fh );
		
		
		return $this->ci->load->view( $path . '__' . $mtime, $data, $return, $this->comp_dir );
		
	}
	
	// ----------------------------------------------------
}