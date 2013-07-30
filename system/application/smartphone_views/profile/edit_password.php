<h2>ログインパスワード変更</h2>
<p class="caption">
以下の項目を入力してください。
</p>

<?php echo form_open('profile/do_edit_password', array('id' => 'sz_registration_form'));?>
	<table class="sz_registration_table">
		<tbody>
			<tr>
				<th>
					<label for="password">新しいパスワード<br />（8文字以上）<span class="r_need">*</span></label>
				</th>
				<td>
					<?php echo form_password(array('name' => 'password', 'id' => 'password', 'value' => (string)$this->input->post('password'), 'class' => 'alnum'));?>
					<div id="sz_passowrd_checker">
						<div id="sz_ps_length"></div>
					</div>
					<?php echo $this->form_validation->error('password');?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="password_match">新しいパスワード（確認）<span class="r_need">*</span></label>
				</th>
				<td>
					<?php echo form_password(array('name' => 'password_match', 'id' => 'password_match', 'value' => '', 'class' => 'alnum'));?>
					<?php echo $this->form_validation->error('password_match');?>
				</td>
			</tr>
		</tbody>
	</table>
	
	<p class="caption sp">
		この操作には現在のパスワード入力が必要です。現在のパスワードを入力してください。
	</p>
	<table class="sz_registration_table">
		<tr>
			<th>
				<label for="cur_password">現在のパスワード<span class="r_need">*</span></label>
			</th>
			<td>
				<?php echo form_password(array('name' => 'cur_password', 'id' => 'cur_password', 'value' => '', 'class' => 'alnum'));?>
				<?php echo $this->form_validation->error('cur_password');?>
			</td>
		</tr>
	</table>
	<div class="sz_r_submission">
	<?php echo form_hidden('ticket', $ticket);?>
	<?php echo form_submit(array('value' => '確認画面へ', 'class' => 'button'));?>
	</div>
<?php echo form_close();?>

<p class="backlink sp">
<?php echo anchor('profile', '&laquo;&nbsp;プロフィールに戻る');?>
</p>