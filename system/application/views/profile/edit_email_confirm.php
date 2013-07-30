<h2>編集内容の確認</h2>
<p>
以下の項目で更新します。よろしれば更新ボタンを押下してください。<br />
</p>

<table class="sz_registration_table">
	<tbody>
		<tr>
			<th>
				<label for="email">メールアドレス<span class="r_need">*</span></label>
			</th>
			<td>
				<?php echo set_value('email');?>
			</td>
		</tr>
	</tbody>
</table>
<div class="sz_r_submission">
<?php echo form_open('profile/edit_email');?>
<?php foreach ($hidden as $key => $val):?>
<?php echo form_hidden($key, $val);?>
<?php endforeach;?>
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_submit(array('value' => '入力画面へ', 'name' => 'modify'));?>
<?php echo form_close();?>
&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo form_open('profile/do_edit_email');?>
<?php foreach ($hidden as $key => $val):?>
<?php echo form_hidden($key, $val);?>
<?php endforeach;?>
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_submit(array('value' => '登録する'));?>
<?php echo form_close();?>
</div>