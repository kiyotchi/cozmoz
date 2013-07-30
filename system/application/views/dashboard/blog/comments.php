<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

  <h3>コメント管理</h3>
  <p class="additional"><?php echo $total;?></p>
  
  <?php if (count($comments) > 0):?>
  <?php echo form_open('dashboard/blog/comment/delete_comment_selectables', array('id' => 'sz_blog_comment_selectable'));?>
    
  <?php foreach ($comments as $value):?>
    <div class="sz_blog_comment">
      <span class="del_check">
        <input type="checkbox" name="sz_delete_comment[]" value="<?php echo $value->sz_blog_comment_id;?>" />
      </span>
      <div class="comment_body">
        <?php echo nl2br(form_prep($value->comment_body));?>
      </div>
      <p class="comment_info">
        posted&nbsp;by&nbsp;<?php echo form_prep($value->name);?><br />
        <span>to&nbsp;<a href="<?php echo page_link();?>dashboard/blog/article/<?php echo $value->sz_blog_id;?>"><?php echo form_prep($titles[$value->sz_blog_id]);?></a></span>
        <span>at&nbsp;<?php echo $value->post_date;?></span>
      </p>
      <p class="comment_action">
        <a href="<?php echo page_link();?>dashboard/blog/comment/delete_comment/<?php echo $value->sz_blog_comment_id?>">削除</a>
      </p>
    </div>
  <?php endforeach;?>
    
    <p class="pagination">
      <?php echo $pagination;?>
    </p>
    <p>
      <a href="javascript:void(0)" id="all_checked">全てチェック／チェックを外す</a>
      &nbsp;&nbsp;
      <?php echo form_hidden('sz_ticket', $ticket);?>
      <input type="submit" value="チェックしたものを削除" />
    </p>
  <?php echo form_close();?>
  <?php else:?>
  <p>コメントは投稿されていません。</p>
  <?php endif;?>
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
