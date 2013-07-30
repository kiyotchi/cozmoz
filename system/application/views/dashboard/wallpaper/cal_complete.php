<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>カレンダー管理</h2>
<div id="main">
  <h3>処理完了</h3>
  <h4><?php echo $this->msg;?></h4>
  <br />

  <p class="center">
  	<?php if($is_new_entry) echo anchor('dashboard/wallpaper/cal_edit', '連続投稿');?><br /><br />
    <?php echo anchor('dashboard/wallpaper/cal_entries', 'カレンダー一覧へ');?><br />
  </p>
  <br />
  <br />
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
