<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		INTERMediator.construct(true);
		
		INTERMediatorOnPage.expandingRecordFinish = function (name, target){//name:ターゲットノード target:行
			var id = $(target).find('.check_id').html();
			$(target).find('.check_id').css('display','none');
			var url = '<?php echo site_url('dashboard/cozmoz/customer/edit'); ?>?id=' + id ;
			$(target).find('.edit_btn').attr('href',url);
			var url = '<?php echo site_url('dashboard/cozmoz/customer/detail'); ?>?id=' + id ;
			$(target).find('.go_detail').attr('href',url);
		}
	});
</script>



<!-- h2 stays for breadcrumbs -->
<h2>業務種別管理</h2>

<div id="main">
  <h3>報酬額定義</h3>
  
  
  <div id="IM_NAVIGATOR">Navigation Controls by INTER-Mediator</div>
  <table>
	<tr>
		<th>業務種別</th>
		<th>業務内容名</th>
		<th>標準報酬額</th>
		<th>並び順</th>
	</tr>
	<tbody>
		<tr>
			<td>
				<select class="IM[<?php echo $remuneration_table; ?>@business_type_id]">
					<?php foreach( $business_type_list as $key => $val ): ?>
						<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="text" class="IM[<?php echo $remuneration_table; ?>@name]" />
			</td>
			<td>
				<input type="text" class="IM[<?php echo $remuneration_table; ?>@standard_price]" />
			</td>
			<td>
				<input type="text" class="IM[<?php echo $remuneration_table; ?>@rank]" />
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
