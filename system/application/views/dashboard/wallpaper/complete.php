<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>壁紙管理</h2>
<div id="main">
  <h3>処理完了</h3>
  <h4><?php echo $this->msg;?></h4>
  <br />

  <p class="center">
    <?php echo anchor('dashboard/wallpaper/entries', '壁紙一覧へ');?>
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
