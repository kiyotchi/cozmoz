<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>サイト全体の設定</h2>
<div id="main">

<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>

  <h3>システムのアップグレード</h3>
  <div class="sz_system_upgrade">
  
    <?php if ($upgrade > 0):?>
    <p>
       現在<strong class="upv"><?php echo $this->version;?></strong><strong class="upv">→</strong><strong class="upv up_to"><?php echo SEEZOO_VERSION;?></strong>アップグレード後<br /><br />
       へのアップグレードが可能です。実行する場合は下の実行ボタンを押してください。
    </p>
    <div class="notify_up">
      <h4>アップグレード前の注意</h4>
      <p>アップグレード前に必ず以下の操作を行ってから実行してください。</p>
      <ol>
        <li>現在動作中のファイルとデータベースのバックアップを<strong class="szv">必ず</strong>取ってください。</li>
        <li><dfn>system/application/config/config.php</dfn>に書き込み権限を与えてください。</li>
      </ol>
      <p>上記の手順を行っていない場合、システムの復旧が難しくなります。必ず行ってください。</p>
    </div>
    <p><label><input type="checkbox" id="up_checked" value="1" />&nbsp;上記操作を行いました。</label></p><br />
    <p class="up_link" id="open_close">
    
      <?php if ($upgrade === 2):?>
      <a href="<?php echo page_link();?>dashboard/site_settings/upgrade/execute/<?php echo str_replace('.', '-', SEEZOO_VERSION);?>/<?php echo $ticket;?>">
      <?php else:?>
      <a href="<?php echo page_link();?>dashboard/site_settings/upgrade/execute/noscript/<?php echo $ticket;?>">
      <?php endif;?>
      
      <img src="<?php echo file_link();?>images/dashboard/upgrade.gif" alt="アップグレードを実行" />&nbsp;アップグレードを実行する
      </a>
    </p>
  </div>
  <?php else:?>
  
  <p>
    現在のバージョン：<strong class="upv"><?php echo $this->version;?></strong><br /><br />
    <span>システムは最新の状態です。</span>
  </p>
  <?php endif;?>
  
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
