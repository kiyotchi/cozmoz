<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		
		//初期化
		INTERMediator.construct(true);
		
		
		//各行処理
		INTERMediatorOnPage.expandingRecordFinish = function( name, target ){
			
			//詳細閲覧ボタン
			if( $(target).find('.check_id').length > 0 ){
				var id = $(target).find('.check_id').html();
				$(target).find('.check_id').css( 'display', 'none' );
				var url = '<?php echo site_url('dashboard/cozmoz/cache_register/new_order_step'); ?>?id=' + id;
				$(target).find('.edit_btn').eq(0).attr('href',url);
			}
			
			
		}
		
		
		
	});
</script>



<!-- h2 stays for breadcrumbs -->
<h2>キャッシュレジスター</h2>

<div id="main">
  <h3>キャッシュレジスター</h3>
  
  
  <div id="IM_NAVIGATOR">Navigation Controls by INTER-Mediator</div>
  <div>
	  <a href="<?php echo site_url('dashboard/cozmoz/cache_register/new_order'); ?>">新規お会計</a>
  </div>
  <table>
	<tr>
		<th>　</th>
		<th>作成時間</th>
		<th>タイトル</th>
		<th>合計金額</th>
	</tr>
	<tbody>
	<tr>
		<td>
			<a href="" class="edit_btn">詳細閲覧</a>
			<span class="IM[<?php echo $cache_register_table; ?>@id] check_id"></span>
		</td>
		<td class="IM[<?php echo $cache_register_table; ?>@create_date]">
		</td>
		<td class="IM[<?php echo $cache_register_table; ?>@subject]">
		</td>
		<td class="IM[<?php echo $cache_register_table; ?>@order_status]">
		</td>
	</tr>
	</tbody>
	</table>
	
	
	<h3>キャッシュレジスター機能定義</h3>
	<table>
		<tr>
			<td>
				<a href="<?php echo site_url('dashboard/cozmoz/cache_register/def_payment_method'); ?> ">
					お支払い方法定義
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="<?php echo site_url('dashboard/cozmoz/cache_register/def_order_status'); ?> ">
					オーダーステータス定義
				</a>
			</td>
		</tr>

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
