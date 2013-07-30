<?php echo $this->load->view('dashboard/dashboard_header');?>
<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		INTERMediator.construct(true);
	});
</script>


<!-- h2 stays for breadcrumbs -->
<h2>レジスターオーダーステータス</h2>

<div id="main">
  <h3>ステータス管理</h3>
  
  
  <div id="IM_NAVIGATOR">Navigation Controls by INTER-Mediator</div>
  <table>
	<tr>
		<th>ステータス名</th>
		<th>並び順</th>
	</tr>
	<tbody>
	<tr>
		<td>
			<input type="text" class="IM[<?php echo $cache_register_order_status_table; ?>@name]" />
		</td>
		<td>
			<input type="text" class="IM[<?php echo $cache_register_order_status_table; ?>@rank]" />
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
