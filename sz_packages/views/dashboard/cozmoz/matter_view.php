<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		
		INTERMediator.construct(true);
		
		
		//レコード終了処理
		INTERMediatorOnPage.expandingRecordFinish = function (name, target){//name:ターゲットノード target:行
			
			if( name != '<?php echo $matter_table; ?>' ){
				return;
			}
			var id = $(target).find('.check_id').eq(0).html();
			$(target).find('.check_id').eq(0).css( 'display', 'none' );
			var url = '<?php echo site_url('dashboard/cozmoz/matter/detail'); ?>?id=' + id;
			$(target).find('.edit_btn').eq(0).bind(
				'click',
				function(e){
					location.href = url;
				}
			);
			
		}
		
		
		//全体終了処理
		INTERMediatorOnPage.expandingEnclosureFinish = function( name, target ){
		}
		
		//検索状態解除
		$('.clear_search').bind(
			'click',
			function(e){
				location.href = '<?php echo site_url('dashboard/cozmoz/matter'); ?>';
			}
		);
		
		
	});
</script>



<!-- h2 stays for breadcrumbs -->
<h2>事件簿</h2>

<div id="main">
	
	
	
	
	<h3>事件簿一覧</h3>
	<div id="IM_NAVIGATOR">Navigation Controls by INTER-Mediator</div>
	<table>
		<tr>
			<th>詳細</th>
			<th>事件名</th>
			<th>屋号／依頼者</th>
			<th>作成日</th>
			<th>進捗ステータス</th>
		</tr>
		<tbody>
			<tr>
				<td>
					<a href="javascript:void(0);" class="edit_btn">編集</a>
					<span class="IM[<?php echo $matter_table; ?>@id] check_id"></span>
				</td>
				<td><span class="IM[<?php echo $matter_table; ?>@name]"></span></td>
				<td>
					<div class="_im_enclosure">
						<div class="_im_repeater">
							<span class="IM[<?php echo $cutomer_table; ?>@company]"></span><br />
							<span class="IM[<?php echo $cutomer_table; ?>@nam1]"></span> <span class="IM[<?php echo $cutomer_table; ?>@name2]"></span>
						</div>
					</div>
				</td>
				<td><span class="IM[<?php echo $matter_table; ?>@create_date]"></span></td>
				<td><span class="IM[<?php echo $matter_table; ?>@order_status]"></span></td>
			</tr>
		</tbody>
	</table>

  
  
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
