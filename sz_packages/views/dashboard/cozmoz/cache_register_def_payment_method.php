<?php echo $this->load->view('dashboard/dashboard_header');?>
<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		INTERMediator.construct(true);
	});
</script>


<!-- h2 stays for breadcrumbs -->
<h2>レジスター支払い方法</h2>

<div id="main">
  <h3>支払い方法管理</h3>
  
  
  <div id="IM_NAVIGATOR">Navigation Controls by INTER-Mediator</div>
  <table>
	<tr>
		<th>ステータス名</th>
		<th>並び順</th>
	</tr>
	<tbody>
	<tr>
		<td>
			<input type="text" class="IM[<?php echo $cache_register_payment_method; ?>@name]" />
		</td>
		<td>
			<input type="text" class="IM[<?php echo $cache_register_payment_method; ?>@rank]" />
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
