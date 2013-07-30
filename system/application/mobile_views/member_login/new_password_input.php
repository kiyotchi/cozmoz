<h3>パスワード再発行</h3>
<p>新しく設定するパスワードを入力してください。</p>

<?php if (!empty($this->msg)):?>
<p><?php echo $this->msg;?></p>
<?php endif;?>

<?php echo form_open('member_login/do_reset_password/' . $activation_code, array('onsubmit' => "return confirm('送信します。よろしいですか？');"));?>
<p>
<label for="new_password">新しいパスワード<font color="#FF0000">※</font></label><br />
<?php echo form_password(array('name' => 'new_password', 'id' => 'new_password', 'value' => set_value('new_passowrd'), 'class' => 'imedis'));?>
<?php echo $this->form_validation->error('new_password');?>
</p>
<p>
<label for="new_password_confirm">パスワード（確認）<font color="#FF0000">※</font></label><br />
<?php echo form_password(array('name' => 'new_password_confirm', 'id' => 'new_password_confirm'));?>
<?php echo $this->form_validation->error('new_password_confirm');?>
</p>

<p align="center">
<?php echo form_hidden($this->ticket_name, $this->ticket);?>
<?php echo form_submit(array('value' => 'パスワードを再発行'));?>
</p>
<?php echo form_close();?>