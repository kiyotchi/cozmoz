<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>Seezoo&nbsp;管理パネル&nbsp;</h2>
<div id="main">
  
  <?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
  <?php endif;?>
  
  <h3>ログ一覧</h3>
  
  <form class="sz_log_filters">
  <?php echo $total;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  絞り込み条件：<?php echo form_dropdown('filter', $filters, $filter, 'id="filter_dd"');?>
  </form>
  
  <?php if ( count($logs) > 0 ):?>
  <p class="right">
    <a href="<?php echo page_link();?>dashboard/site_settings/log_history/clear_log" id="clear_log">
      <?php echo set_image('delete.png', TRUE);?>&nbsp;ログをすべて削除する
    </a>
  </p>
  <?php endif;?>
  
  <p class="pagination links">
  <?php echo ($pagination) ? $pagination : '';?>
  </p>
  
  <?php echo form_open('dashboard/site_settings/log_history/delete_log', array('id' => 'log_form'));?>
  <table cellspacing="0" cellpadding="0">
    <tbody>
       <tr>
         <th class="ch">&nbsp;</th>
         <th>ログタイプ</th>
         <th class="log_txt">メッセージ</th>
         <th>作成日時</th>
       </tr>
       
       <?php if ( count($logs) > 0 ):?>
       <?php foreach ( $logs as $key => $log ):?>
       <tr<?php echo ($key % 2 > 0) ? ' class="odd"' : '';?>>
         <td class="ch"><?php echo form_checkbox('log_ids[]', $log->sz_system_logs_id);?></td>
         <td><?php echo prep_str($log->log_type);?></td>
         <td class="log_txt"><?php echo nl2br(prep_str($log->log_text));?></td>
         <td><?php echo $log->logged_date;?></td>
       </tr>
       <?php endforeach;?>
       <?php else:?>
        <tr><td colspan="4" class="center">ログはありません。</td></tr>
       <?php endif;?>
       
    </tbody>
  </table>
  
  <p class="pagination links">
  <?php echo ($pagination) ? $pagination : '';?>
  </p>
  
  <?php if ( count($logs) > 0 ):?>
  <p><a href="javascript:void(0)" id="ch_all">すべてチェック&nbsp;/&nbsp;解除</a></p>
  <p class="center">
  	<?php echo form_hidden('filter', $filter);?>
    <?php echo form_submit(array('value' => 'チェックしたものを削除する'));?>
  </p>
  <?php endif;?>
  
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
