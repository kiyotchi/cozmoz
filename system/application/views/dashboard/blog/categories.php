<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

  <h3>カテゴリ管理</h3>
  <p class="additional"><a href="javascript:void(0)" id="add_category"><?php echo set_image('plus.png', TRUE);?>&nbsp;カテゴリの追加</a></p>
  <div class="category_wrapper">
    <ul class="sz_blog_category_list">
      <li class="ttl">カテゴリ名<p>操作</p></li>
      <?php $times = 0;?>
      
      <?php foreach ($category as $key => $value):?>
      <li class="sz_cat_sortable<?php if ($times % 2 === 1) echo ' odd';?>" data-catid="<?php echo $key;?>">
        <span><?php echo form_prep($value);?></span>
        <?php echo form_open('dashboard/blog/ajax_category_edit', array('class' => 'init_hide'));?>
        <?php echo form_input(array('name' => 'category_name', 'value' => $value));?>
        <?php echo form_hidden('sz_blog_category_id', $key);?>
        <input type="button" class="category_edit_button" value="変更" />
        <?php echo form_close();?>
        <span class="state_ing">変更中...<img src="<?php echo file_link();?>images/loading_small.gif" /></span>
        <span class="state_do">変更しました。</span>
        <p>
          <a href="javascript:void(0)" class="toggle_edit" rel="1">編集</a>&nbsp;
          <a href="<?php echo page_link();?>dashboard/blog/categories/ajax_delete_category/<?php echo $key?>" class="li_del">削除</a>
        </p>
      </li>
      <?php $times++;?>
      <?php endforeach;?>
      
    </ul>
    <div id="additional_body">
      <span id="info">追加したいカテゴリを登録してください。</span>
      <p><label>カテゴリ名</label>&nbsp;<?php echo form_input(array('name' => 'category_name_master'));?></p>
      <p><input type="button" id="add_cat" value="カテゴリ追加" /></p>
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
