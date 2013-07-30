<?php echo $nick_name;?>　様

<?php echo SITE_TITLE;?>にユーザー登録して頂き、ありがとうございます。

現在、メールアドレスのアクティベーション待機中です。
以下のリンクからアクティベーションを完了させてください。

<?php echo preg_replace('/(.+)\?.*$/', '$1', page_link('registration/activate/' . $activation_code));?>


お待ちしております！


===============================================================
<?php echo SITE_TITLE;?>システムメール<<?php echo $from;?>>
sended at <?php echo date('Y-m-d H:i:s');?>

