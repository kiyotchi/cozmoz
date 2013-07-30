<h3><?php echo SITE_TITLE;?>にログイン</h3>
<p>
登録時のアカウント情報を入力してください。<br />
<?php echo anchor('registration', '新規会員登録はこちらから');?>
</p>
<?php echo spacer_gif(10, 10);?>  
<?php if (!empty($this->msg)):?>
<p><font color="#FF0000"><?php echo $this->msg;?></font></p>
<?php endif;?>

<?php echo form_open('member_login/do_member_login', array('id' => 'member_login_form'));?>
<p>
<label for="member_name">ニックネームまたはメールアドレス</label><br />
<?php echo form_input(array('name' => 'member_name', 'id' => 'member_name', 'value' => set_value('member_name'), 'class' => 'imedis'));?>
<?php echo $this->form_validation->error('member_name');?>
</p>
<p>
<label for="password">パスワード</label><br />
<?php echo form_password(array('name' => 'password', 'id' => 'password'));?>
<?php echo $this->form_validation->error('password');?>
</p>

<p align="center">
<?php echo form_hidden($this->ticket_name, $this->ticket);?>
<?php echo form_submit(array('value' => 'ログイン'));?>
</p>
<?php echo form_close();?>
  
<?php if ( isset($complete_msg) && $complete_msg !== FALSE ):?>
<p><?php echo $complete_msg;?></p>
<?php endif;?>

<hr />
<h4>パスワードを忘れた方は以下に登録時のメールアドレスを入力して送信して下さい。</h4>
<br />
<?php echo form_open('member_login/forgotten_password');?>
<p align="center">
<label><?php echo form_input(array('name' => 'forgotten_email', 'class' => 'imedis'));?></label>
<?php echo $this->form_validation->error('forgotten_email');?>
</p>
<p align="center">
<?php echo form_hidden($this->ticket_name, $this->ticket);?>
<?php echo form_submit(array('value' => 'パスワードの再発行'));?>
</p>
<?php echo form_close();?>