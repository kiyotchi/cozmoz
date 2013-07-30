<?php if (count($groups) > 0):?>
<form id="sz_file_groups_list">
	<?php foreach ($groups as $value):?>
	<p><label><?php echo form_checkbox('file_group', $value->file_groups_id, (strpos($file_group, ':'.$value->file_groups_id.':') !== FALSE) ? TRUE : FALSE);?>&nbsp;<?php echo form_prep($value->group_name);?></label></p>
	<?php endforeach;?>
	<p class="sz_button clearfix">
		<a href="javascript:void(0)" id="sz_file_groups_list_add">
			<span>グループに追加</span>
		</a>
	</p>
	<?php echo form_hidden('files', $vals);?>
	<br style="clear:both" />
</form>
<?php endif;?>

<form id="sz_file_groups_add">
	<p>
		新規グループを作成して追加：&nbsp;<?php echo form_input(array('name' => 'group_name', 'value' => ''));?>
	</p>
	<p class="sz_button clearfix">
		<a href="javascript:void(0)" id="sz_file_groups_list_make_add">
			<span>グループを作成して追加</span>
		</a>
	</p>
	<?php echo form_hidden('files', $vals);?>
</form>