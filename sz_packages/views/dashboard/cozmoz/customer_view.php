<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		
		//INTERMediator.startFrom = 1;
		
		INTERMediator.construct(true);
		
		
		//レコード終了処理
		INTERMediatorOnPage.expandingRecordFinish = function (name, target){//name:ターゲットノード target:行
			var id = $(target).find('.check_id').html();
			$(target).find('.check_id').css('display','none');
			var url = '<?php echo site_url('dashboard/cozmoz/customer/edit'); ?>?id=' + id ;
			$(target).find('.edit_btn').attr('href',url);
			var url = '<?php echo site_url('dashboard/cozmoz/customer/detail'); ?>?id=' + id ;
			$(target).find('.go_detail').attr('href',url);
		}
		
		
		//全体終了処理
		INTERMediatorOnPage.expandingEnclosureFinish = function( name, target ){
			$('select[name=pref]').val('<?php echo $this->input->get('pref'); ?>');
		}
		
		//検索状態解除
		$('.clear_search').bind(
			'click',
			function(e){
				location.href = '<?php echo site_url('dashboard/cozmoz/customer'); ?>';
			}
		);
		
		
		
	});
</script>



<!-- h2 stays for breadcrumbs -->
<h2>顧客管理</h2>

<div id="main">
	
	<h3>顧客検索</h3>
	<div>
		<form action="<?php echo site_url('dashboard/cozmoz/customer'); ?>" method="get">
		<table>
			<tr>
				<th>社名／屋号</th>
				<td>
					<?php echo form_input( array( 'name' => 'company', 'value' => $this->input->get('company') ) ); ?>
				</td>
			</tr>
			<tr>
				<th>名前</th>
				<td>
					<?php echo form_input( array( 'name' => 'name1', 'value' => $this->input->get('name1') ) ); ?>　
					<?php echo form_input( array( 'name' => 'name2', 'value' => $this->input->get('name2') ) ); ?>
				</td>
			</tr>
			<tr>
				<th>電話番号</th>
				<td>
					<?php echo form_input( array( 'name' => 'tel', 'value' => $this->input->get('tel') ) ); ?>　
				</td>
			</tr>
			<tr>
				<th>居住地</th>
				<td>
					<select name="pref">
						<?php foreach( $pref as $key => $val ): ?>
							<option value="<?php echo $key; ?>" <?php if( $key == $this->input->get('pref') ): ?>selected="selected"<?php endif; ?>><?php echo $val; ?></option>
						<?php endforeach; ?>
					</select>
					<?php echo form_input( array( 'name' => 'address1', 'value' => $this->input->get('address1') ) ); ?><br />
					<?php echo form_input( array( 'name' => 'address2', 'value' => $this->input->get('address2') ) ); ?><br />
					<?php echo form_input( array( 'name' => 'address3', 'value' => $this->input->get('address3') ) ); ?>
				</td>
			</tr>
			<tr>
				<th colspan="2">
					<input type="submit" value="送信" />
					<input type="button" value="検索状態解除" class="clear_search" />
				</th>
			</tr>
		</table>
		</form>
	</div>
	
	
	
	<h3>顧客一覧</h3>
	<div id="IM_NAVIGATOR">Navigation Controls by INTER-Mediator</div>
	<div>
	  <a href="<?php echo site_url('dashboard/cozmoz/customer/add'); ?>">顧客追加</a>
	</div>
  <table>
	<tr>
		<th>編集</th>
		<th>屋号/名前</th>
		<th>かな</th>
		<th>詳細へ</th>
	</tr>
	<tbody>
	<tr>
		<td>
			<a href="" class="edit_btn">編集</a>
			<span class="IM[<?php echo $cutomer_table; ?>@id] check_id"></span>
		</td>
		<td>
			<span class="IM[<?php echo $cutomer_table; ?>@company]"></span><br />
			<span class="IM[<?php echo $cutomer_table; ?>@name1]"></span>
			<span class="IM[<?php echo $cutomer_table; ?>@name2]"></span>
		</td>
		<td>
			<span class="IM[<?php echo $cutomer_table; ?>@kana1]"></span>
			<span class="IM[<?php echo $cutomer_table; ?>@kana2]"></span>
		</td>
		<td>
			<a href="" class="go_detail">詳細へ</a>
		</td>
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
