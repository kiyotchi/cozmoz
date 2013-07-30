<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>レポート</h2>

<?php if (!empty($this->msg)):?>
<div class="message"><?php echo form_prep($this->msg);?></div>
<?php endif;?>

<div id="main">
  <h3>設置されたお問い合わせフォーム一覧</h3>
  <p class="caption">
    フォーム名にリンクの無いものは既に削除されたフォームです。
  </p><br />
  <table cellpadding="0" cellspacing="0">
    <tr>
      <td>フォーム名</td>
      <td class="action">操作</td>
    </tr>
    
    <?php foreach ($forms as $key => $val):?>
    <tr<?php if ((int)$key % 2 === 0) { echo ' class="odd"';}?>>
    <?php if (array_key_exists('page_id', $val)):?>
      <td><a href="<?php echo page_link() . $val['page_id'];?>" class="reports_page"><?php echo form_prep($val['form_title']);?>（<?php echo $val['count'];?>）</a></td>
    <?php else:?>
      <td><?php echo form_prep($val['form_title']);?>（<?php echo $val['count'];?>）</td>
    <?php endif;?>
      <td class="action">
        <?php if ($val['count'] > 0):?>
        <?php echo anchor('dashboard/reports/detail/' . $val['question_key'], '問い合わせを見る', 'class="view"')?>
        <?php else:?>
         <span>問い合わせはありません。</span>
        <?php endif;?>
        &nbsp;<?php echo anchor('dashboard/reports/delete_report/' . $val['question_key'] . '/' .$token, '削除', 'class="delete"');?>
      </td>
    </tr>
    <?php endforeach;?>
    
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
