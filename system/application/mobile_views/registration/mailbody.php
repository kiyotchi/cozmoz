<?php echo prep_str($nick_name);?>　様

<?php echo SITE_TITLE;?>にユーザー登録して頂き、ありがとうございます。

現在、メールアドレスのアクティベーション待機中です。
以下のリンクからアクティベーションを完了させてください。

<?php echo page_link();?>registration/activate/<?php echo $activation_code;?>


お待ちしております！


===============================================================
<?php echo SITE_TITLE;?>システムメール<<?php echo $from;?>>
sended at <?php echo date('Y-m-d H:i:s');?>

