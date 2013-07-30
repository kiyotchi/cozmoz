<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

  <h3>ブログメニュー管理</h3>
  <p>ブログメニューの表示/非表示と表示順を管理します。ドラッグ＆ドロップで表示順の変更が行えます。</p><br />
  <p>設定完了後、下の保存ボタンを押してください。</p>
  <div id="state_msg">設定を保存しました。</div>
  <div id="sz_blog_menu_setting_area">
      
    <?php foreach ($menu_data as $menu):?>
    <div class="sz_blog_menu_piece<?php if ($menu->is_hidden > 0) echo ' hidden';?>" sbid="<?php echo $menu->sz_blog_menu_id;?>">
      <h4><?php echo prep_str($menu->menu_title);?></h4>
      <p class="description"><?php echo prep_str($menu->description);?></p>
      <a href="javascript:void(0)" rel="<?php echo ($menu->is_hidden > 0) ? 1 : 0;?>"><?php echo set_image('ppbox/close.png', TRUE);?></a>
    </div>
    <?php endforeach;?>
    
  </div>
  <p class="submission">
    <input type="button" value="保存する" id="save_state" />
  </p>
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
