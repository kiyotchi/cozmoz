<h2><?php echo SITE_TITLE;?>&nbsp;メンバー登録</h2>
<p>新規ユーザー登録を行います。以下の項目を入力してください。</p>
<?php echo form_open('registration/confirm', array('id' => 'sz_registration_form'));?>
<p>
<label for="nick_name">ニックネーム<font color="#FF0000">*</font></label><br />
<?php echo form_input(array('name' => 'nick_name', 'id' => 'nick_name', 'value' => $this->input->post('nick_name')));?>
<?php echo $this->form_validation->error('nick_name');?>
</p>
<p>
<label for="email">メールアドレス<font color="#FF0000">*</font></label><br />
<?php echo form_input(array('name' => 'email', 'id' => 'email', 'value' => $this->input->post('email'), 'class' => 'alnum'));?>
<?php echo $this->form_validation->error('email');?>
</p>
<p>
<label for="email_match">メールアドレス（確認入力）<font color="#FF0000">*</font></label><br />
<?php echo form_input(array('name' => 'email_match', 'id' => 'email_match', 'value' => $this->input->post('email_match'), 'class' => 'alnum'));?>
<?php echo $this->form_validation->error('email_match');?>
</p>
<p>
<label for="password">パスワード<font color="#FF0000">*</font></label><br />
<?php echo form_password(array('name' => 'password', 'id' => 'password', 'value' => $this->input->post('password'), 'class' => 'alnum'));?>
<?php echo $this->form_validation->error('password');?>
</p>
<p>
<label for="password_match">パスワード（確認）<font color="#FF0000">*</font></label><br />
<?php echo form_password(array('name' => 'password_match', 'id' => 'password_match', 'value' => $this->input->post('password_match'), 'class' => 'alnum'));?>
<?php echo $this->form_validation->error('password_match');?>
</p>

<!-- attributes -->
<?php foreach ( $attributes as $att ):?>
<p>
<label for="attribute_<?php echo $att->sz_member_attributes_id?>">
<?php echo prep_str($att->attribute_name);?>
<?php echo set_require_mark($att->validate_rule);?>
</label><br />
<?php echo build_registration_form_parts($att);?>
<?php echo $this->form_validation->error('attribute_' . $att->sz_member_attributes_id);?>
</p>
<?php endforeach;?>
<div align="center">
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_submit(array('value' => '確認画面へ'));?>
</div>
<?php echo form_close();?>