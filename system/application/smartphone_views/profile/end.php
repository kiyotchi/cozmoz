<div class="regist_success">
<h2><?php echo prep_str($head);?></h2>

<div class="sz-content">
<?php echo nl2br(prep_str($msg));?>
</div>
</div>
<br />
<p>
<?php echo anchor('profile/' . $this->member_id, 'プロフィール画面へ戻る', 'class="button"')?>
</p>