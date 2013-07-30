<?php echo form_open('dashboard/directory_view/do_rename_file', array('id' => 'sz_file_name_form'));?>
<h3 class="center">新しいファイル名を入力してください</h3>
<p class="center">
  <?php echo form_input(array('name' => 'new_filename', 'class' => 'new_filename', 'value' => $file->file_name, 'size' => 50));?>
  <strong>.<?php echo $file->extension;?></strong>
</p>
<p class="center" style="margin-top:20px">
  <?php echo form_hidden('file_id', $file->file_id);?>
  <input type="button" id="sz_file_rename_btn" value="変更する" />
</p>
<?php echo form_close();?>