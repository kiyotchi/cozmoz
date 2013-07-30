<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">

<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>

<?php if ($hidden['sz_blog_id'] > 0):?>
  <h3>編集内容の確認</h3>
  <h4 class="confirm_msg">編集内容を確認してください。</h4>
<?php else:?>
  <h3>投稿内容の確認</h3>
  <h4 class="confirm_msg">投稿内容を確認してください。</h4>
<?php endif;?>

  <table class="confirm">
    <tbody>
      <tr>
        <td>タイトル</td>
        <td class="action"><?php echo form_prep($hidden['title']);?></td>
      </tr>
      <tr class="odd">
        <td>登録カテゴリ</td>
        <td class="action"><?php echo $category[form_prep($hidden['sz_blog_category_id'])];?></td>
      </tr>
      <tr>
        <td>公開日時</td>
        <td class="action">
          <?php echo form_prep(sprintf('%s %s:%s', $hidden['show_date'], $hidden['show_hour'], $hidden['show_minute']));?>
        </td>
      </tr>
      <tr class="odd">
        <td>URIセグメント</td>
        <td class="action">
          <?php echo form_prep($hidden['permalink']);?>
        </td>
      </tr>
      <tr>
        <td colspan="2">本文</td>
      </tr>
      <tr class="odd">
        <td class="action blog_confirm" colspan="2">
          <div>
            <?php echo nl2br($hidden['body']);?>
          </div>
        </td>
      </tr>
      <tr>
        <td>コメント</td>
        <td class="action">
          <?php if ((int)$hidden['is_accept_comment'] === 1):?>
             受け付ける
          <?php else:?>
          <span style="color:red">受け付けない</span>
          <?php endif;?>
        </td>
      </tr>
      <tr class="odd">
        <td>トラックバック</td>
        <td class="action">
          <?php if ((int)$hidden['is_accept_trackback'] === 1):?>
             受け付ける
          <?php else:?>
          <span style="color:red">受け付けない</span>
          <?php endif;?>
        </td>
      </tr>
    </tbody>
  </table>
  <div class="submit_area">
  <?php echo form_open('dashboard/blog/edit/index', array('class' => 'inline'))?>
  <?php foreach ($hidden as $key => $value):?>
  
  <?php if ($key === 'body'):?>
    <input type="hidden" name="<?php echo $key;?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');?>" />
  <?php else:?>
    <?php echo form_hidden($key, $value);?>
  <?php endif;?>
  <?php endforeach;?>

    <?php echo form_hidden($this->ticket_name, $ticket);?>
    <?php echo form_submit(array('name' => 'modify', 'value' => '入力画面に戻る'));?>
  <?php echo form_close();?>&nbsp;&nbsp;
  
  <?php echo form_open('dashboard/blog/edit/do_edit', array('class' => 'inline'))?>
  <?php foreach ($hidden as $key => $value):?>
  
  <?php if ($key === 'body'):?>
    <input type="hidden" name="<?php echo $key;?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');?>" />
  <?php else:?>
    <?php echo form_hidden($key, $value);?>
  <?php endif;?>
  <?php endforeach;?>
  
    <?php echo form_hidden($this->ticket_name, $ticket);?>
    <?php echo form_submit(array('name' => 'regist', 'value' => '編集を確定する'));?>
  <?php echo form_close();?>
  </div>
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
