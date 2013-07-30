<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">
  <?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
  <?php endif;?>
  <h3>トラックバック管理</h3>
  <p class="additional"><?php echo $total;?></p>
  <p>背景色がついているものはまだ承認されていません。</p>
  <br />
  <?php if (count($trackbacks) > 0):?>
  <?php echo form_open('dashboard/blog/trackbacks/delete_tb_selectables', array('id' => 'sz_blog_comment_selectable'));?>
  <?php foreach ($trackbacks as $tb):?>
    <div class="sz_blog_comment<?php if ($tb->is_allowed > 0) echo ' allowed';?>">
      <span class="del_check">
        <input type="checkbox" name="sz_delete_trackback[]" value="<?php echo $tb->sz_blog_trackbacks_id;?>" />
      </span>
      <div class="comment_body">
        <?php echo nl2br(form_prep($tb->excerpt));?>
      </div>
      <p class="comment_info">
        requested&nbsp;by&nbsp;<?php echo anchor($tb->url, form_prep($tb->title . ' - ' . $tb->blog_name));?><br />
        <span>to&nbsp;<a href="<?php echo page_link();?>dashboard/blog/article/<?php echo $tb->sz_blog_id;?>"><?php echo form_prep($tb->blog_title);?></a></span>
        <span>at&nbsp;<?php echo $tb->received_date;?></span>
      </p>
      
      <?php if ( $tb->is_allowed < 1 ):?>
      <p class="comment_action">
        <a href="<?php echo page_link();?>dashboard/blog/trackbacks/allow_trackback/<?php echo $tb->sz_blog_trackbacks_id?>" class="ok" title="承認する">承認する</a>
      </p>
      <?php endif;?>
      
    </div>
  <?php endforeach;?>
    <p class="pagination">
      <?php echo $pagination;?>
    </p>
    <p>
      <a href="javascript:void(0)" id="all_checked">全てチェック／チェックを外す</a>
      &nbsp;&nbsp;
      <?php echo form_hidden($this->ticket_name, $ticket);?>
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
