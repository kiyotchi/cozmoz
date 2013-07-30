<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">
  
  <?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
  <?php endif;?>
  <h3>ブログ設定情報管理</h3>
  
  <?php if ((int)$info->is_enable === 0):?>
  <h4>
    <span>ブログを使用しない設定になっています。使用する場合は以下のリンクから設定してください。</span>
  </h4>
  <p class="customize left">
    <a href="<?php echo page_link()?>dashboard/blog/settings/edit" id="enable_blog">ブログを利用可能にする</a>
  </p>
  <?php else:?>
  
  <p class="customize">
    <a href="<?php echo page_link()?>dashboard/blog/settings/edit"><?php echo set_image('config.png', TRUE);?>&nbsp;ブログ設定を変更する</a>
  </p>
  <table cellpadding="0" cellspacing="0">
    <tbody>
      <tr>
        <td>ブログの使用</td>
        <td class="action">使用する</td>
      </tr>
      <tr class="odd">
        <td>ブログタイトル</td>
        <td class="action"><?php echo $info->page_title;?></td>
      </tr>
      <tr>
        <td>1ページに表示する記事の制限</td>
        <td class="action"><?php echo $info->entry_limit;?>&nbsp;件</td>
      </tr>
      <tr class="odd">
        <td>メニューに表示するコメントの制限</td>
        <td class="action"><?php echo $info->comment_limit;?>&nbsp;件</td>
      </tr>
      <tr>
        <td>RSS配信タイプ</td>
        <td class="action"><?php echo $rss_types[$info->rss_format];?></td>
      </tr>
      <tr class="odd">
        <td>zenback連携ステータス</td>
        <td class="action"><?php echo (empty($info->zenback_code) ? '連携していません' : '連携中');?></td>
      </tr>
      <tr>
        <td>コメントの投稿に画像認証を使用するかどうか（スパム対策）</td>
        <?php if ((int)$info->is_need_captcha > 0):?>
        <td class="action">使用する</td>
        <?php else:?>
        <td class="action">使用しない</td>
        <?php endif;?>
      </tr>
      <tr class="odd">
        <td>新規投稿時に自動的にpingを送信するかどうか</td>
        <td class="action">
        <?php if ((int)$info->is_auto_ping === 1):?>
        送信する
        <?php else:?>
        送信しない
        <?php endif;?>
        </td>
      </tr>
    </tbody>
  </table>
  <?php endif;?>
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
