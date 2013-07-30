<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>レポート</h2>

<?php if (!empty($this->msg)):?>
<div class="message"><?php echo form_prep($this->msg);?></div>
<?php endif;?>

<div id="main">
  <h3>「<?php echo prep_str($form_data->form_title);?>」&nbsp;に投稿されたお問い合わせ一覧</h3>
  <p class="additional"><?php echo anchor('dashboard/reports', set_image('back.png', TRUE) . '&nbsp;レポート一覧に戻る');?></p>
 
  <?php echo form_open('dashboard/reports/data_dl', array('class' => 'reports_dl'));?>
  ダウンロードフォーマット：<?php echo form_dropdown('dl_format', $dl_format);?>&nbsp;で&nbsp;<?php echo form_submit(array('value' => 'ダウンロード'));?>
  <?php echo form_hidden('key', $question_key);?>
  <?php echo form_close();?>
  
  <?php foreach ($reports as $key => $value):?>
  <div class="reports_wrapper">
    <h4>投稿日時：<?php echo $key;?></h4>
    <p style="margin : 5px 0;padding-left : 5px;">
      <a href="<?php echo page_link();?>dashboard/reports/delete_answer/<?php echo strtotime($key);?>/<?php echo $question_key;?>/<?php echo $token;?>" class="delete_report">
        <?php echo set_image('delete.png', TRUE);?>&nbsp;この回答を削除する
      </a>
    </p>
    <table cellpadding="0" cellspacing="0">
      <tbody>
        <tr>
          <td>質問</td>
          <td class="action">回答</td>
        </tr>
        <?php foreach ($value as $key => $v):?>
        <tr<?php if ((int)$key % 2 === 0) { echo ' class="odd"';}?><?php if ($key > 2) { echo ' style="display:none"';}?>>
          <td><?php echo prep_str($v['question_name']);?></td>
          <td class="action">
            <?php echo format_question_answer($v);?>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
    
    <?php if (count($value) > 2):?>
    <p class="answer_more">
      <a href="javascript:void(0)" class="detail_show" rel="hide"><?php echo set_image('plus.png', TRUE);?>&nbsp;回答を全て表示</a>
    </p>
    <?php endif;?>
    
  </div>
  <?php endforeach;?>
  
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
