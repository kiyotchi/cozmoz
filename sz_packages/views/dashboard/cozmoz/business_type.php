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
  <h3>業務種別一覧</h3>
  
  
  <div id="IM_NAVIGATOR">Navigation Controls by INTER-Mediator</div>
  <table>
	<tr>
		<th>種別名</th>
		<th>標準金額</th>
	</tr>
	<tbody>
		<tr>
			<td>
				<input type="text" class="IM[<?php echo $business_type_table; ?>@name]" />
			</td>
			<td>
				<input type="text" class="IM[<?php echo $business_type_table; ?>@standard_price]" />
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
