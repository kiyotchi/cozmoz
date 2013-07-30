<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

  <h3>ping送信先管理</h3>
  <p class="additional"><a href="javascript:void(0)" id="add_category"><?php echo set_image('plus.png', TRUE);?>&nbsp;ping送信先の追加</a></p>
  <div class="category_wrapper">
    <table>
      <tbody>
        <tr class="caption">
          <th>ping名</th>
           <th>送信先URL</th>
           <th>操作</th>
        </tr>
        
        <?php foreach ($ping_list as $value):?>
        <tr<?php if (++$times % 2 === 1) echo ' class="odd"';?>>
          <td><?php echo form_prep($value->ping_name);?></td>
          <td><?php echo form_prep($value->ping_server)?></td>
          <td class="action">
            <a class="edit" href="<?php echo page_link();?>dashboard/blog/ping/edit_ping/<?php echo $value->sz_blog_ping_list_id;?>/<?php echo $js_token;?>">編集</a>
            <a class="delete" href="<?php echo page_link();?>dashboard/blog/ping/delete_ping/<?php echo $value->sz_blog_ping_list_id;?>/<?php echo $js_token;?>">削除</a>
          </td>
        </tr>
        <?php endforeach;?>
      
      </tbody>
    </table>
    <div id="additional_body">
      <span id="info">追加したいping送信先を登録してください。</span>
      <p><label>ping名</label>&nbsp;<?php echo form_input(array('name' => 'ping_name'));?></p>
      <p><label>URL</label>&nbsp;<?php echo form_input(array('name' => 'ping_server'));?></p>
      <p><input type="button" id="add_cat" value="ping送信先追加" /></p>
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
