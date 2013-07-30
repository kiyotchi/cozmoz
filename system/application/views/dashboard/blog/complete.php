<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">
  <h3>処理完了</h3>
  <h4><?php echo $this->msg;?></h4>
  <br />
  
  <?php if (isset($is_ping)):?>
  <?php if ($is_ping === TRUE):?>
  <p>pingは自動的に送信されました。</p>
  <div class="sz_blog_ping_list">
    <h5>ping送信結果</h5>
    <ul>
    <?php foreach($ping as $value):?>
      <li class="result <?php echo ($value[2]) ? 'success' : 'send_error';?>"><?php echo $value[0];?><span class="state"><?php echo $value[1];?></span></li>
    <?php endforeach;?>
    </ul>
  </div>
  <?php elseif(isset($ping)):?>
  <p>更新情報をping送信できます。</p>
  <form>
    <div class="sz_blog_ping_list">
      <h5>ping送信先</h5>
      <ul>
      <?php foreach($ping as $value):?>
        <li><?php echo form_checkbox('send_ping', $value->sz_blog_ping_list_id);?>&nbsp;<?php echo $value->ping_name;?><span class="state">送信中...</span></li>
      <?php endforeach;?>
      </ul>
      <p class="center">
        <?php echo form_hidden('title', $title);?>
        <?php echo form_hidden('sz_blog_id', $sz_blog_id);?>
        <input type="button" class="send_ping" value="pingを送信する" />
      </p>
    </div>
  </form>
  <?php endif;?>
  <?php endif;?>
  <p class="center">
    <?php echo anchor('dashboard/blog/entries', '投稿記事一覧へ');?>
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
