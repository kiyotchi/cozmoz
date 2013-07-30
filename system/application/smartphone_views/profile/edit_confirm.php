<h2>以下の内容で更新します。よろしければ登録ボタンを押してください。</h2>
<table class="sz_registration_table">
	<tbody>
		<tr>
			<th>
				<label for="nick_name">ニックネーム<span class="r_need">*</span></label>
			</th>
			<td>
				<?php echo set_value('nick_name');?>
			</td>
		</tr>
		<?php foreach ( $attributes as $attribute ):?>
		<tr>
			<th>
				<?php echo prep_str($attribute->attribute_name);?>
				<?php echo set_require_mark($attribute->validate_rule);?>
			</th>
			<td>
				<?php echo build_registration_form_parts($attribute, TRUE);?>
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<div class="sz_r_submission">
<?php echo form_open('profile/edit');?>
<?php foreach ($hidden as $key => $val):?>
<?php echo form_hidden($key, $val);?>
<?php endforeach;?>
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_submit(array('value' => '戻る', 'name' => 'modify', 'class' => 'button'));?>
<?php echo form_close();?>
<?php echo form_open('profile/edit_process');?>
<?php foreach ($hidden as $key => $val):?>
<?php echo form_hidden($key, $val);?>
<?php endforeach;?>
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_submit(array('value' => '登録する', 'class' => 'button'));?>
<?php echo form_close();?>
</div>
