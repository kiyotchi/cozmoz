<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>バックエンド処理</h2>
<div id="main">

<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>

  <h3>実行可能なプロセス</h3>
  <table cellpadding="0" cellspacing="0" class="backend_list">
    <tbody>
      <tr>
        <th>実行チェック</th>
        <th>プロセス名</th>
        <th>実行内容</th>
        <th>最終実行日時</th>
        <th>結果</th>
        <th>操作</th>
      </tr>
      
      <?php if (count($installed_backend) > 0):?>
      <?php foreach ($installed_backend as $key => $v):?>
      <tr<?php if ($key % 2 >0) echo ' class="odd"';?>>
        <td class="pr">
          <?php echo form_checkbox('sz_backend_id', $v->sz_backend_id, TRUE);?>
          <img src="<?php echo file_link()?>images/loading_small.gif" alt="" />
        </td>
        <td><?php echo prep_str($v->backend_name);?></td>
        <td><?php echo prep_str($v->description);?></td>
        <td><?php echo $v->last_run;?></td>
        <td><?php echo $v->result;?></td>
        <td>
          <?php echo form_open('dashboard/backend_process/uninstall');?>
          <?php echo form_hidden('sz_backend_id', $v->sz_backend_id);?>
          <?php echo form_submit(array('value' => '削除する'));?>
          <?php echo form_close();?>
        </td>
      </tr>
      <?php endforeach;?>
      <?php else:?>
      <tr>
        <td colspan="6">実行可能なプロセスはありません。</td>
      </tr>
      <?php endif;?>
    </tbody>
  </table>
  
  <?php if (count($installed_backend) > 0):?>
  <div class="process_execute">
    <form>
      <p><input type="button" id="exe_process" value="チェックを入れたプロセスを実行する" /></p>
    </form>
  </div>
  <?php endif;?>
  
  <p class="caption">cron処理から実行する場合は、<br />
    <input type="text" name="" id="cron_address" readonly="readonly" value="php <?php echo FCPATH;?>index.php dashboard/backend_process/cron_run" />
     を実行してください。
  </p>
  
  <h3>インストール可能なプロセス</h3>
  <table cellpadding="0" cellspacing="0" class="backend_list">
    <tbody>
      <tr>
        <th>プロセス名</th>
        <th>実行内容</th>
        <th>操作</th>
      </tr>
      
      <?php if (count($enable_install_list) > 0):?>
      <?php foreach ($enable_install_list as $key => $b):?>
      <tr<?php if ($key % 2 >0) echo ' class="odd"';?>>
        <td><?php echo prep_str($b['backend_name']);?></td>
        <td><?php echo prep_Str($b['description']);?></td>
        <td>
          <?php echo form_open('dashboard/backend_process/install_process');?>
            <?php echo form_hidden('handle', $b['backend_handle']);?>
            <?php echo form_hidden('install_token', $install_token);?>
            <?php echo form_submit(array('value' => 'インストール'));?>
          <?php echo form_close()?>
        </td>
      </tr>
      <?php endforeach;?>
      <?php else:?>
      <tr>
        <td colspan="3">インストール可能なプロセスはありません。</td>
      </tr>
      <?php endif;?>

    </tbody>
  </table>
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
