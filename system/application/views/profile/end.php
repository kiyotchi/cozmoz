<div class="regist_success">
<h2><?php echo prep_str($head);?></h2>
<p><?php echo nl2br(prep_str($msg));?>
<br />
<?php echo anchor('profile/' . $this->member_id, 'プロフィール画面へ戻る')?>
</p>
</div>