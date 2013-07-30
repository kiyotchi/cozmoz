<?php echo $to_user->user_name;?> さん

<?php echo $from_user->user_name;?> さんからページ公開申請の確認結果が届いています。

================= 申請対象のページ ==================

<?php echo $page->page_title;?>　
バージョン<?php echo $data['vid'];?>　

<?php if ( $page->is_ssl_page > 0):?>
URL : <?php echo ssl_page_link() . $page->page_path;?>
<?php else:?>
URL : <?php echo page_link() . $page->page_path;?>
<?php endif;?>　

================= 申請確認結果 ======================　

<?php echo ($data['status'] == 1) ? '公開承認' : '差し戻し';?>
　

<?php echo $data['comment'];?>　

====================================================

結果は管理画面トップからも確認できます。（ログインが必要です）
<?php echo page_link() . 'dashboard/panel';?>

------------------------------------------------------------------------------
<?php echo SITE_TITLE;?>システムメール
sended at <?php echo db_datetime();?>

