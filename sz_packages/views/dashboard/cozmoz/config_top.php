<?php echo $this->load->view('dashboard/dashboard_header');?>

<!-- h2 stays for breadcrumbs -->
<h2>各種設定</h2>

<div id="main">
	<h3>設定</h3>
	<ul>
		<li>
			<a href="<?php echo site_url('dashboard/cozmoz/config/office_info'); ?>">
				事務所情報
			</a>
		</li>
		<li>
			<a href="<?php echo site_url('dashboard/cozmoz/config/order_status'); ?>">
				受注ステータス定義
			</a>
		</li>
	</ul>
	
	
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
