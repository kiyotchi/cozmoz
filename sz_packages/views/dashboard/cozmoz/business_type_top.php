<?php echo $this->load->view('dashboard/dashboard_header');?>

<!-- h2 stays for breadcrumbs -->
<h2>業務内容定義</h2>

<div id="main">
	<h3>設定へ</h3>
	<ul>
		<li>
			<a href="<?php echo site_url('dashboard/cozmoz/business_type/type'); ?>">
				業務種別定義
			</a>
		</li>
		<li>
			<a href="<?php echo site_url('dashboard/cozmoz/business_type/remuneration'); ?>">
				報酬額定義
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
