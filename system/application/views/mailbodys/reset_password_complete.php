<?php echo $member->nick_name;?> さん、こんにちは。

ログイン用の新規パスワードを発行しました。　
以降は、新しく入力されたパスワードにてログインしてください。

※なお、パスワードはセキュリティ上表示しておりません。

ログインURL：
<?php echo preg_replace('/(.+)\?.*$/', '$1', page_link('member_login'));?>


-----------------------------------------------
<?php echo SITE_TITLE;?>システムメール
sended at <?php echo db_datetime();?>