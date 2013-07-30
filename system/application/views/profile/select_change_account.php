<div class="regist_success">
<h2>ログイン情報の変更</h2>
<p>変更する項目を選択してください。
<a href="<?php echo page_link();?>profile/edit_email" class="select_box_link">
メールアドレスを変更する
</a>
<a href="<?php echo page_link();?>profile/edit_password" class="select_box_link">
ログインパスワードを変更する
</a>
<br />
<?php echo anchor('profile/' . $this->member_id, 'プロフィール画面へ戻る')?>
</p>
</div>