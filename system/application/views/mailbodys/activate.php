<?php echo $nick_name;?>　様

<?php echo SITE_TITLE;?>にユーザー登録が完了いたしましたので、
お知らせ致します。

以下のアドレスからログインできます。

<?php echo preg_replace('/(.+)\?.*$/', '$1', page_link('member_login'));?>　
　
ご登録頂きありがとうございます！


===============================================================
<?php echo SITE_TITLE;?>システムメール<<?php echo $from;?>>
sended at <?php echo date('Y-m-d H:i:s');?>