<h2>ログインメールアドレスの変更</h2>
<p>
以下の項目を入力して更新ボタンを押下してください。
</p>

<?php echo form_open('profile/edit_email_confirm', array('id' => 'sz_registration_form'));?>
	<table class="sz_registration_table">
		<tbody>
			<tr>
				<th>
					<label for="email">メールアドレス<span class="r_need">*</span></label>
				</th>
				<td>
					<?php if ( $is_validated === TRUE ):?>
					<?php echo form_input(array('name' => 'email', 'id' => 'email', 'value' => $this->input->post('email'), 'class' => 'alnum'));?>
					<?php echo $this->form_validation->error('email');?>
					<?php else:?>
					<?php echo form_input(array('name' => 'email', 'id' => 'email', 'value' => $profile->email, 'class' => 'alnum'));?>
					<?php endif;?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="email_match">メールアドレス（確認入力）<span class="r_need">*</span></label>
				</th>
				<td>
					<?php echo form_input(array('name' => 'email_match', 'id' => 'email_match', 'value' => $this->input->post('email_match'), 'class' => 'alnum'));?>
					<?php echo $this->form_validation->error('email_match');?>
				</td>
			</tr>
		</tbody>
	</table>
	
	<p style="margin-top:20px;">
		この操作には現在のパスワード入力が必要です。現在のパスワードを入力してください。
	</p>
	<table class="sz_registration_table">
		<tr>
			<th>
				<label for="cur_password">現在のパスワード<span class="r_need">*</span></label>
			</th>
			<td>
				<?php if ( $is_validated === TRUE ):?>
				<?php echo form_password(array('name' => 'cur_password', 'id' => 'cur_password', 'value' => '', 'class' => 'alnum'));?>
				<?php echo $this->form_validation->error('cur_password');?>
				<?php else:?>
				<?php echo form_password(array('name' => 'cur_password', 'id' => 'cur_password', 'value' => '', 'class' => 'alnum'));?>
				<?php endif;?>
			</td>
		</tr>
	</table>
	<div class="sz_r_submission">
	<?php echo form_hidden('ticket', $ticket);?>
	<?php echo form_submit(array('value' => '確認画面へ'));?>
	</div>
<?php echo form_close();?>
<p><?php echo anchor('profile', '&laquo;&nbsp;プロフィールに戻る');?></p>