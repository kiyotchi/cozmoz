<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>メーカー管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

  <h3>メーカー管理</h3>
  <p class="additional"><a href="javascript:void(0)" id="add_category"><?php echo set_image('plus.png', TRUE);?>&nbsp;メーカー名の追加</a></p>
  <div class="category_wrapper">
    <ul class="sz_blog_category_list">
      <li class="ttl">メーカー名<p>操作</p></li>
      <?php $times = 0;?>

      <?php foreach ($category as $key => $value):?>
      <li<?php if ($times % 2 === 1) echo ' class="odd"';?>>
        <span><?php echo form_prep($value);?></span>
        <?php echo form_open('dashboard/wallpaper/ajax_category_edit', array('class' => 'init_hide'));?>
        <?php echo form_input(array('name' => 'maker_name', 'value' => $value));?>
        <?php echo form_hidden('wall_maker_id', $key);?>
        <input type="button" class="category_edit_button" value="変更" />
        <?php echo form_close();?>
        <span class="state_ing">変更中...<img src="<?php echo file_link();?>images/loading_small.gif" /></span>
        <span class="state_do">変更しました。</span>
        <p>
          <a href="javascript:void(0)" class="toggle_edit" rel="1">編集</a>&nbsp;
          <a href="<?php echo page_link();?>dashboard/wallpaper/makers/ajax_delete_category/<?php echo $key?>" class="li_del">削除</a>
        </p>
      </li>
      <?php $times++;?>
      <?php endforeach;?>

    </ul>
    <div id="additional_body">
      <span id="info">追加したいメーカー名を登録してください。</span>
      <p><label>メーカー名</label>&nbsp;<?php echo form_input(array('name' => 'maker_name_master'));?></p>
      <p><input type="button" id="add_cat" value="メーカー名追加" /></p>
    </div>
  </div>
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
