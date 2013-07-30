<p>以下の内容でよろしければ、<br />登録ボタンを押してください。</p>
<p>
<label for="nick_name">ニックネーム</label><br />
<?php echo prep_str($hidden['nick_name']);?>
</p>
<p>
<label for="email">メールアドレス</label><br />
<?php echo prep_str($hidden['email']);?>
</p>
<p><label for="password">パスワード</label><br />
<?php echo preg_replace('/./', '*', set_value('password'));?>
</p>
<?php foreach ( $attributes as $attribute ):?>
<p>
<?php echo prep_str($attribute->attribute_name);?><br />
<?php echo build_registration_form_parts($attribute, TRUE);?>
</p>
<?php endforeach;?>
<?php echo spacer_gif(10, 30);?><br />
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
<tr>
<td width="50%">
<?php echo form_open('registration/index');?>
<?php foreach ($hidden as $key => $val):?>
<?php echo form_hidden($key, $val);?>
<?php endforeach;?>
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_submit(array('value' => '入力画面へ'));?>
<?php echo form_close();?>
</td>
<td width="50%">
<?php echo form_open('registration/do_regist');?>
<?php foreach ($hidden as $key => $val):?>
<?php echo form_hidden($key, $val);?>
<?php endforeach;?>
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_submit(array('value' => '登録する'));?>
<?php echo form_close();?>
</td>
</table>