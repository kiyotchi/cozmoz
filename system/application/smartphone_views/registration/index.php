<div class="sz_registration_block">
	<h2><?php echo SITE_TITLE;?>&nbsp;メンバー登録</h2>
	<p>新規ユーザー登録を行います。以下の項目を入力してください。</p>
	<?php echo form_open('registration/confirm', array('id' => 'sz_registration_form'));?>
		<table class="sz_registration_table">
			<tbody>
				<tr>
					<th>
						<label for="nick_name">ニックネーム<span class="r_need">*</span></label>
					</th>
					<td>
						<?php echo form_input(array('name' => 'nick_name', 'id' => 'nick_name', 'value' => $this->input->post('nick_name')));?>
						<?php echo $this->form_validation->error('nick_name');?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="email">メールアドレス<span class="r_need">*</span></label>
					</th>
					<td>
						<?php echo form_input(array('name' => 'email', 'id' => 'email', 'value' => $this->input->post('email'), 'class' => 'alnum'));?>
						<?php echo $this->form_validation->error('email');?>
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
				<tr>
					<th>
						<label for="password">パスワード<span class="r_need">*</span></label>
					</th>
					<td>
						<?php echo form_password(array('name' => 'password', 'id' => 'password', 'value' => $this->input->post('password'), 'class' => 'alnum'));?>
						<div id="sz_passowrd_checker">
							<div id="sz_ps_length"></div>
						</div>
						<?php echo $this->form_validation->error('password');?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="password_match">パスワード（確認）<span class="r_need">*</span></label>
					</th>
					<td>
						<?php echo form_password(array('name' => 'password_match', 'id' => 'password_match', 'value' => $this->input->post('password_match'), 'class' => 'alnum'));?>
						<?php echo $this->form_validation->error('password_match');?>
					</td>
				</tr>
				<!-- attributes -->
				<?php foreach ( $attributes as $att ):?>
				<tr>
					<th>
						<label for="attribute_<?php echo $att->sz_member_attributes_id?>">
							<?php echo prep_str($att->attribute_name);?>
							<?php echo set_require_mark($att->validate_rule);?>
						</label>
					</th>
					<td>
						<?php echo build_registration_form_parts($att);?>
						<?php echo $this->form_validation->error('attribute_' . $att->sz_member_attributes_id);?>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		<div class="sz_r_submission">
		<?php echo form_hidden('ticket', $ticket);?>
		<?php echo form_submit(array('value' => '確認画面へ', 'class' => 'button'));?>
		</div>
	<?php echo form_close();?>
</div>