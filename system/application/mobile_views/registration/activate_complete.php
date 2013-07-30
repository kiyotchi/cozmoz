<?php echo prep_str($nick_name);?>　様

<?php echo SITE_TITLE;?>にユーザー登録が完了いたしましたので、
お知らせ致します。

以下のアドレスからログインできます。

<?php echo page_link() . 'member_login';?>

よろしくお願い致します。


===============================================================
<?php echo SITE_TITLE;?>システムメール<<?php echo $from;?>>
sended at <?php echo date('Y-m-d H:i:s');?>