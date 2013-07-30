<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		INTERMediator.construct(true);
		INTERMediatorOnPage.expandingRecordFinish = function (name, target){//name:ターゲットノード target:行
			if( name != '<?php echo $matter_table; ?>' ){
				return;
			}
			var id = $(target).find('.detail_link_id').eq(0).html();
			var url = '<?php echo site_url('dashboard/cozmoz/matter/detail'); ?>?id=' + id;
			$(target).find('.matter_detail_btn').attr('href', url);
			
			if( $(target).find('.create_date').val() == '' ){
				var arg = [
					{ 'field': 'create_date', 'value': '<?php echo date('Y-m-d H:i:s'); ?>' },
					{ 'field': 'update_date', 'value': '<?php echo date('Y-m-d H:i:s'); ?>' }
				];
				INTERMediator_DBAdapter.db_update({
					'name':'<?php echo $matter_table; ?>',
					'conditions':[
						{'field':'id','operator':'=','value':id}
					],
					'dataset': arg
				});
			}
			$(target).find('.hidden_field').css('display','none');
		}
		
		
	});
	
	
	
</script>



<!-- h2 stays for breadcrumbs -->
<h2>顧客管理</h2>

<div id="main">
	<h3>顧客詳細</h3>
		<table>
			<tr>
				<th width="20%">会社名/屋号</th>
				<td>
					<span class="IM[<?php echo $cutomer_table; ?>@company]"></span>
					<span class="IM[<?php echo $cutomer_table; ?>@honorific]"></span>
				</td>
			</tr>
			<tr>
				<th>お名前</th>
				<td>
					<span class="IM[<?php echo $cutomer_table; ?>@name1]"></span>
					<span class="IM[<?php echo $cutomer_table; ?>@name2]"></span>
				</td>
			</tr>
			<tr>
				<th>ふりがな</th>
				<td>
					<span class="IM[<?php echo $cutomer_table; ?>@kana1]"></span>
					<span class="IM[<?php echo $cutomer_table; ?>@kana2]"></span>
				</td>
			</tr>
			<tr>
				<th>TEL</th>
				<td>
					<span class="IM[<?php echo $cutomer_table; ?>@tel]"></span>
				</td>
			</tr>
			<tr>
				<th>FAX</th>
				<td>
					<span class="IM[<?php echo $cutomer_table; ?>@fax]"></span>
				</td>
			</tr>
			<tr>
				<th>メールアドレス</th>
				<td>
					<span class="IM[<?php echo $cutomer_table; ?>@mailaddress]"></span>
				</td>
			</tr>
			<tr>
				<th>住所</th>
				<td>
					<span class="IM[<?php echo $cutomer_table; ?>@zip]"></span><br />
					<span class="IM[<?php echo $cutomer_table; ?>@pref]"></span><br />
					<span class="IM[<?php echo $cutomer_table; ?>@address1]"></span><br />
					<span class="IM[<?php echo $cutomer_table; ?>@address2]"></span><br />
					<span class="IM[<?php echo $cutomer_table; ?>@address3]"></span>
				</td>
			</tr>
		</table>
		
		
		<h3>事件簿</h3>
		<div class="_im_enclosure">
			<div class="_im_repeater">
				<span style="display: none;" class="IM[<?php echo $cutomer_table; ?>@id]"></span>
				<table>
						<tr>
							<td>
								事件名:<input style="width:300px" type="text" class="IM[<?php echo $matter_table; ?>@name]" value="" />
							</td>
							<td>
								<div class="hidden_field">
									<input type="text" class="IM[<?php echo $matter_table; ?>@create_date] create_date" value="" />
									<input type="text" class="IM[<?php echo $matter_table; ?>@update_date] update_date" value="" />
								</div>
								<span style="display: none;" class="IM[<?php echo $matter_table; ?>@id] detail_link_id"></span>
								<a href="" class="matter_detail_btn">詳細へ</a>
							</td>
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
