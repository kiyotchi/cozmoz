<?php if (count($entry) > 0):?>
<?php foreach ($entry as $value):?>
<h2><?php echo anchor('blog/article/' . $value->sz_blog_id, $value->title);?></h2>
<p><?php echo (isset($category_list[$value->sz_blog_category_id])) ? prep_str($category_list[$value->sz_blog_category_id]) : '-';?></p>
<p align="right">
posted&nbsp;by&nbsp;<?php echo anchor('blog/author/' . rawurlencode($value->user_name), prep_str($value->user_name));?><br />
<?php echo $value->entry_date;?>
</p>
<div class="sz_blog_body"><?php echo truncate(preg_replace('/<br\s?\/?>/', '<br />', $value->body), 300);?></div>
<div align="right">
<?php echo anchor('blog/article/' . $value->sz_blog_id, '全部読む&raquo;');?>
</div>
<br />
<hr />
<?php endforeach;?>
<p align="center"><?php echo $pagination;?></p>
<?php else:?>
<p>投稿が見つかりませんでした。</p>
<?php endif;?>
