<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>Open&nbsp;Graph&nbsp;Protocol設定</h2>
<div id="main">
<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>

  <p class="edit_image_select"><a href="javascript:void(0)" id="select_edit_image_target">OGPに設定する画像ファイルを選択してください。</a></p>
  
  <h3>その他の属性</h3>
  <p class="additional">追加</p>
  <table>
    <tr>
      <th>プロトコル</th>
      <th>プロパティ名</th>
      <th>値</th>
    </tr>
    <tr>
      <td><?php echo form_dropdown('protocol[]', array('og', 'fb'));?></td>
      <td><?php echo form_input(array('optional_name[]', 'class' => 'tiny-text'));?></td>
      <td><?php echo form_input(array('optional_name[]', 'class' => 'tiny-text'));?></td>
    </tr>
  </table>
  
  <p class="center"><?php echo form_submit(array('value' => '設定を更新する'))?></p>

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

