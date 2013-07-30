<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	
	var enc_check = 0;
	var check = 0;
	
	var COZMOZ_DELETE_FUNTION;
	$(document).ready(function(){
		
		INTERMediator.construct(true);
		COZMOZ_DELETE_FUNTION = calc;
		
		
		//小計計算ハンドル
		INTERMediatorOnPage.expandingRecordFinish  = function(name,target){
			$(target).find('.calc_detail_tax_price,.calc_detail_tax_qty,.calc_detail_notax_price,.calc_detail_notax_qty').bind(
				'change',
				function(e){
					calc( this );
				}
			);
		}
		INTERMediatorOnPage.expandingEnclosureFinish = function(name,target){
			if( ( name != '<?php echo $matter_table; ?>' )||( enc_check > 0 )){
				enc_check ++ ;
				return;
			}
			$('.discount,.tax_rate').change(
				function(){
					calc( this );
				}
			);
			$('.tax_type').bind(
				'click',
				function(e){
					calc( this );
				}
			);
		}
		
		//小計計算
		function calc( that ){
			
					var id_list = [];
					var items_total = 0;
					var kazei = 0;//課税項目合計
					
					//課税項目
					var i = 0;
					var len = $('.calc_detail_tax_price').length;
					while( i < len ){
						var total =
							~~( $('.calc_detail_tax_price').eq(i).val() ) *
							~~( $('.calc_detail_tax_qty').eq(i).val() );
						$('.calc_detail_tax_total').eq(i).val(
							total
						);
						items_total += total;
						kazei += total;
						id_list.push( $('.calc_detail_tax_total').eq(i).attr('id') );
						i ++ ;
					}
					
					
					//非課税
					var i = 0;
					var len = $('.calc_detail_notax_price').length;
					while( i < len ){
						var total =
							~~( $('.calc_detail_notax_price').eq(i).val() ) *
							~~( $('.calc_detail_notax_qty').eq(i).val() );
						items_total += total;
						$('.calc_detail_notax_total').eq(i).val(
							total
						);
						id_list.push( $('.calc_detail_notax_total').eq(i).attr('id') );
						i ++ ;
					}
					
					//消費税額
					i = 0;
					var tax_type = '';
					while( i < $('.tax_type').length ){
						if( $('.tax_type').get(i).checked == true ){
							tax_type = $('.tax_type').eq(i).val();
						}
						i ++ ;
					}
					if( tax_type == 0 ){
						$('.tax_total').val(
							Math.ceil( kazei * ( ~~($('.tax_rate').eq(0).val()) / 100 ) )
						);
					}
					else{
						$('.tax_total').val(0);
					}
					
					id_list.push( $('.tax_total').eq(i).attr('id') );
					
					//総合計
					var advances_received = ~~$('.advances_received').eq(0).val();
					var discount = ~~$('.discount').eq(0).val();
					//console.log(discount);
					$('.grand_total').eq(0).val(
						items_total - discount
					);
					INTERMediator.valueChange( $('.grand_total').eq(0).attr('id') );
					
					
					//合計保存
					for( var i in id_list ){
						INTERMediator.valueChange( id_list[i] );
					}
					
					//console.log(check);
					check ++ ;
		}
		
	});
	
	
</script>



<!-- h2 stays for breadcrumbs -->
<h2>事件簿管理</h2>


<div id="main">
<div class="_im_enclosure">
	  <div class="_im_repeater">
	<h3>事件</h3>
	<table>
		<tbody>
			<tr>
				<th width="15%">事件名</th>
				<td class="IM[<?php echo $matter_table; ?>@name]">
					<span style="display: none;" class="IM[<?php echo $matter_table; ?>@name]"></span>
				</td>
			</tr>
			<tr>
				<th>作成日</th>
				<td class="IM[<?php echo $matter_table; ?>@create_date]"></td>
			</tr>
			<!--
			<tr>
				<th>更新日</th>
				<td class="IM[<?php echo $matter_table; ?>@update_date]"></td>
			</tr>
			-->
			<tr>
				<th>作業ステータス</th>
				<td>
					<select class="IM[<?php echo $matter_table; ?>@order_status]">
						<?php foreach( $order_status_list as $row ): ?>
							<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>課税</th>
				<td>
					<label><input name="tax_type" type="radio" class="IM[<?php echo $matter_table; ?>@tax_type] tax_type" value="0" />課税</label><br />
					<label><input name="tax_type" type="radio" class="IM[<?php echo $matter_table; ?>@tax_type] tax_type" value="1" />非課税</label>
				</td>
			</tr>
			<tr>
				<th>消費税率</th>
				<td>
					<input type="text" class="IM[<?php echo $matter_table; ?>@tax_rate] tax_rate" />%
				</td>
			</tr>
			<tr>
				<th>お客様情報</th>
				<td>
					<dl>
						<dt>屋号</dt>
						<dd>
							<span class="IM[<?php echo $cutomer_table; ?>@company]"></span>
							<span class="IM[<?php echo $cutomer_table; ?>@honorific]"></span>
						</dd>
						<dt>お名前</dt>
						<dd>
							<span class="IM[<?php echo $cutomer_table; ?>@name1]"></span>
							<span class="IM[<?php echo $cutomer_table; ?>@name2]"></span>
						</dd>
					</dl>
				</td>
			</tr>
		</tbody>
	</table>
	
	
		<h3>議事録</h3>
			<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
			<table>
			<thead>
				<tr>
					<th width="15%">日付</th>
					<th>タイトル／内容</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<input type="text" class="IM[<?php echo $meeting_minutes_table; ?>@date]" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $meeting_minutes_table; ?>@name]" /><br />
						<textarea style="width: 100%;" class="IM[<?php echo $meeting_minutes_table; ?>@comment]"></textarea>
					</td>
				</tr>
			  </tbody>
		</table>
		
		<!--
		<h3>書類管理</h3>
		<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
			<div id="document_select_base">
				<?php echo select_file('file_id'); ?>
			</div>
			<table>
			<tbody>
				<tr>
					<th rowspan="2" width="20%">書類名</th>
					<td>
						<input type="text" class="IM[<?php echo $document_list_table; ?>@name]" />
					</td>
				</tr>
			  </tbody>
		</table>
		-->
		
		
		<h3>課税項目</h3>
		<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
		<table>
			<thead>
				<tr>
					<th>摘要<br />備考<br />業務種別</th>
					<th>単価
					<th>数量</th>
					<th>小計</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<span style="display: none;">
							<input type="text" class="IM[<?php echo $detail_tax; ?>@id]" />
						</span>
						
						<input type="text" class="IM[<?php echo $detail_tax; ?>@name]" /><br />
						<input type="text" class="IM[<?php echo $detail_tax; ?>@remark]" /><br />
						<select class="IM[<?php echo $matter_table; ?>@business_type_id]">
							<?php foreach( $remuneration_list as $row ): ?>
								<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<input type="text" class="IM[<?php echo $detail_tax; ?>@price] calc_detail_tax_price" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $detail_tax; ?>@qty] calc_detail_tax_qty" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $detail_tax; ?>@total] calc_detail_tax_total" readonly="readonly" />
					</td>
				  </tr>
			  </tbody>
		</table>
		
		<h3>非課税項目</h3>
		<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
		<table>
			<thead>
				<tr>
					<th>摘要<br />備考</th>
					<th>単価</th>
					<th>数量</th>
					<th>小計</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<span style="display: none;">
							<input type="text" class="IM[<?php echo $detail_notax; ?>@id]" />
						</span>
						<input type="text" class="IM[<?php echo $detail_notax; ?>@name]" /><br />
						<input type="text" class="IM[<?php echo $detail_notax; ?>@remark]" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $detail_notax; ?>@price] calc_detail_notax_price" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $detail_notax; ?>@qty] calc_detail_notax_qty" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $detail_notax; ?>@total] calc_detail_notax_total" readonly="readonly" />
					</td>
				  </tr>
			  </tbody>
		</table>
		
		
		<h3>合計</h3>
		<table>
			<tr>
				<th>消費税額</th>
				<td>
					<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
					<input type="text" class="IM[<?php echo $matter_table; ?>@tax_total] tax_total" readonly="readonly" /></td>
			</tr>
			<tr>
				<th>前受金</th>
				<td>
					<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
					<input type="text" class="IM[<?php echo $matter_table; ?>@advances_received] advances_received" /></td>
			</tr>
			<tr>
				<th>値引き</th>
				<td><input type="text" class="IM[<?php echo $matter_table; ?>@discount] discount" /></td>
			</tr>
			<tr>
				<th>総合計</th>
				<td><input type="text" class="IM[<?php echo $matter_table; ?>@grand_total] grand_total" readonly="readonly" /></td>
			</tr>
		</table>
		
		
		<h3>職務上請求</h3>
			<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
			<table>
			<thead>
				<tr>
					<th width="15%">請求番号</th>
					<th>内容</th>
					<th>並び順</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<input type="text" class="IM[<?php echo $public_document_table; ?>@num1]" />
						-
						<input type="text" class="IM[<?php echo $public_document_table; ?>@num2]" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $public_document_table; ?>@name]" />
					</td>
					<td>
						<input type="text" class="IM[<?php echo $public_document_table; ?>@rank]" />
					</td>
				</tr>
			</tbody>
		</table>
		
		
		
		<h3>見積書</h3>
		<table>
			<tr>
				<th>
					日付
				</th>
				<td>
					<input type="text" class="IM[<?php echo $matter_table; ?>@date_est]" />
				</td>
			</tr>
			<tr>
				<th>
					備考
				</th>
				<td>
					<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
					<textarea style="width:100%; heght: 30px;" class="IM[<?php echo $matter_table; ?>@remark_est]"></textarea>
				</td>
			</tr>
			<tr>
				<th colspan="2">
					<input type="button" value="見積書作成" />
				</th>
			</tr>
		</table>
		
		
		<h3>請求書</h3>
		<table>
			<tr>
				<th>
					日付
				</th>
				<td>
					<input type="text" class="IM[<?php echo $matter_table; ?>@date_bill]" />
				</td>
			</tr>
			<tr>
				<th>
					備考
				</th>
				<td>
					<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
					<textarea style="width:100%; heght: 30px;" class="IM[<?php echo $matter_table; ?>@remark_bill]"></textarea>
				</td>
				<tr>
				<th colspan="2">
					<input type="button" value="請求書作成" />
				</th>
			</tr>
			</tr>
		</table>
		
		
		<h3>領収書</h3>
		<table>
			<tr>
				<th>
					日付
				</th>
				<td>
					<input type="text" class="IM[<?php echo $matter_table; ?>@date_rec]" />
				</td>
			</tr>
			<tr>
				<th>
					備考
				</th>
				<td>
					<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id]"></span>
					<textarea style="width:100%; heght: 30px;" class="IM[<?php echo $matter_table; ?>@remark_rec]"></textarea>
				</td>
				<tr>
				<th colspan="2">
					<input type="button" value="領収書作成" />
				</th>
			</tr>
			</tr>
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
