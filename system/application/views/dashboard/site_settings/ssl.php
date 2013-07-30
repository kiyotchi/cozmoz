<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>サイト全体の設定</h2>
<div id="main">
<?php if ($msg !== FALSE):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>

  <h3>SSL設定</h3>
  <p>
    SSLの設定を行います。各ページの設定で「SSLページに設定」をチェックを入れると、ここで設定されたSSL用のURLが使用されます。<br />
    なお、運用サイトにSSLを導入する際には以下の項目を確認してください：
  </p>
  <div class="notify_up">
  <ol>
    <li>運用ドメインがSSLをサポートしているかどうかを確認してください。</li>
    <li>共有SSLでの使用は非推奨です。VPSや専用サーバなど、設定が変更できるものに対して使用してください。</li>
    <li>SSL通信の際に、ソースを設置する先が違う場所になるような設定のサーバーでは利用できません。</li>
    <li>その他、詳細は各サーバー会社にお問い合わせください。</li>
  </ol>
  </div>
  
  <p>
    SSLの導入が可能な場合、SSL用のURLを入力し、更新ボタンを押してください。<br />
    解除する場合は空欄にして更新してください。
  </p>
  <br />
  
  <?php echo form_open('dashboard/site_settings/ssl/update');?>
  <fieldset>
    <p>
      <label>
        <strong>https://</strong>&nbsp;
        <?php echo form_input(array('name' => 'ssl_base_url', 'id' => 'ssl_base_url', 'value' => $ssl_base_url, 'style' => 'width:70%'))?>
        <a href="javascript:void(0)" id="enc">
          <?php echo set_image('edit.png', TRUE);?>&nbsp;URLエンコード</a>
      </label>
    </p>
    <p class="center">
      <?php echo form_hidden('ticket', $ticket);?>
      <?php echo form_submit(array('value' => '解除する', 'name' => 'delete', 'id' => 'delete'));?>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?php echo form_submit(array('value' => '更新する', 'name' => 'update'));?>
      
    </p>
  </fieldset>
  <?php echo form_close();?>

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
