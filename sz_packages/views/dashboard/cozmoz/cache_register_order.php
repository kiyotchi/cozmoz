<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	
	//明細行削除時のフックポイント
	var COZMOZ_DELETE_FUNTION;
	
	
	$(document).ready(function(){
		
		//明細行削除時のフックポイント
		COZMOZ_DELETE_FUNTION = calc;
		
		
		//初期化
		INTERMediator.construct(true);
		var instans_check_discount = 0;
		var instans_check_payment = 0;
		
		//全体処理
		INTERMediatorOnPage.expandingEnclosureFinish = function(name,target){
			
			//メインだけ処理
			if( name !='<?php echo $cache_register_table; ?>'){
				return;
			}
			
			//割引入力
			if(
				( instans_check_discount == 0 )&&
				( $(target).find('.discount').length > 0)
			){
				$(target).find('.discount').bind(
					'change',
					function(e){
						calc();
					}
				);
				instans_check_discount ++ ;
			}
			
			//支払い入力
			if(
				( instans_check_payment == 0 )&&
				( $(target).find('.pay_amount').length > 0)
			){
				$(target).find('.pay_amount').bind(
					'change',
					function(e){
						calc_change();
					}
				);
				instans_check_payment ++ ;
			}
			
			
		}
		
		
		//明細行変更
		INTERMediatorOnPage.expandingRecordFinish = function( name, target ){
			$(target).find('.detail_price,.detail_qty').bind(
				'change',
				function(e){
					$(target).find('.detail_total').eq(0).val(
						~~$(target).find('.detail_price').eq(0).val() *
						~~$(target).find('.detail_qty').eq(0).val()
					);
					INTERMediator.valueChange( $(target).find('.detail_total').eq(0).attr('id') );
					calc();
				}
			);
			
		}
		
		//合計値計算
		function calc(){
			
			//商品合計計算
			var i = 0;
			var item_total = 0;
			while( i < $('.detail_total').length ){
				item_total += ~~$('.detail_total').eq(i).val();
				i ++ ;
			}
			$('.item_total').eq(0).val( item_total );
			INTERMediator.valueChange(　$('.item_total').eq(0).attr('id') );
			
			
			//総合計計算
			$('.grand_total').eq(0).val(
				item_total - ~~$('.discount').eq(0).val()
			);
			INTERMediator.valueChange(　$('.grand_total').eq(0).attr('id') );
			
			
		}
		
		
		//お釣り計算
		function calc_change(){
			
			INTERMediator.valueChange(
				$('.pay_change').eq(0).val(
					~~ $('.pay_amount').eq(0).val() -
					~~ $('.grand_total').eq(0).val()
				).attr('id')
			);
		}
		
		
	});
</script>



<!-- h2 stays for breadcrumbs -->
<h2>オーダー</h2>


<div id="main">
<div class="_im_enclosure">
	  <div class="_im_repeater">
			
			<h3>オーダー</h3>
			<table>
				<tbody>
					<tr>
						<th width="15%">オーダー名</th>
						<td>
							<input type="text"  class="IM[<?php echo $cache_register_table; ?>@subject]" />
						</td>
					</tr>
					<tr>
						<th width="15%">作成日</th>
						<td>
							<input type="text"  class="IM[<?php echo $cache_register_table; ?>@create_date]" />
						</td>
					</tr>
				</tbody>
			</table>
			
			
		<h3>明細行</h3>
			<span style="display: none;" class="IM[<?php echo $cache_register_table; ?>@id]"></span>
			<table>
			<thead>
				<tr>
					<th>品名<br />備考</th>
					<th>単価</th>
					<th>数量</th>
					<th>小計</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<input type="text" class="IM[<?php echo $cache_register_detail_table; ?>@name]" /><br />
						<input type="text" class="IM[<?php echo $cache_register_detail_table; ?>@remark]" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $cache_register_detail_table; ?>@price] detail_price" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $cache_register_detail_table; ?>@qty] detail_qty" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $cache_register_detail_table; ?>@sub_total] detail_total" readonly="readonly" />
					</td>
				</tr>
			  </tbody>
		</table>
		
		
		<h3>合計</h3>
			<span style="display: none;" class="IM[<?php echo $cache_register_table; ?>@id]"></span>
			<table>
				<tbody>
					<tr>
						<th width="15%">商品合計</th>
						<td>
							<input type="text"  class="IM[<?php echo $cache_register_table; ?>@item_total] item_total" readonly="readonly" />
						</td>
					</tr>
					<tr>
						<th width="15%">割引額</th>
						<td>
							<input type="text"  class="IM[<?php echo $cache_register_table; ?>@discount] discount" />
						</td>
					</tr>
					<tr>
						<th width="15%">総合計</th>
						<td>
							<input type="text"  class="IM[<?php echo $cache_register_table; ?>@grand_total] grand_total" readonly="readonly" />
						</td>
					</tr>
					<tr>
						<th width="15%">お支払い額</th>
						<td>
							<input type="text"  class="IM[<?php echo $cache_register_table; ?>@pay_amount] pay_amount" />
						</td>
					</tr>
					<tr>
						<th width="15%">お支払い方法</th>
						<td>
							<?php foreach($payment_method_list as $row): ?>
								<label>
									<input type="radio" value="<?php echo $row['id']; ?>" class="IM[<?php echo $cache_register_table; ?>@payment_method]" />
									<?php echo $row['name']; ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th width="15%">オーダーステータス</th>
						<td>
							<?php foreach($order_status_list as $row): ?>
								<label>
									<input type="radio" value="<?php echo $row['id']; ?>" class="IM[<?php echo $cache_register_table; ?>@order_status]" />
									<?php echo $row['name']; ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th width="15%">お釣り</th>
						<td>
							<input type="text"  class="IM[<?php echo $cache_register_table; ?>@pay_change] pay_change" readonly="readonly" />
						</td>
					</tr>
				</tbody>
			</table>
		
		
	</div>
</div>
  
  
</div>
  <!-- // #main -->
  
<div class="clear"></div>
</div>
<!-- // #container -->
</div>	
<!-- // #containerHolder -->

<p id="footer"></p>
</div>
<!-- // #wrapper -->
</body>
</html>
