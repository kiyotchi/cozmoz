<div class="regist_success">
<h2>ログイン情報の変更</h2>
<p class="caption">変更する項目を選択してください。</p>
<a href="<?php echo page_link();?>profile/edit_email" class="button sp">
メールアドレスを変更する
</a>
<a href="<?php echo page_link();?>profile/edit_password" class="button sp">
パスワードを変更する
</a>

<p class="backlink sp">
<?php echo anchor('profile/' . $this->member_id, '&laquo;プロフィール画面へ戻る')?>
</p>
</div>