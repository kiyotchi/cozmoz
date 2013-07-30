<p>
「<?php echo prep_str($display_query);?>」で検索した結果<br />
<?php echo $total;?>
</p>
<?php if (count($result) > 0):?>
<?php foreach ($result as $value):?>
<h2><?php echo anchor('blog/article/' . $value->sz_blog_id, $value->title);?></h2>
<p align="right"><?php echo $value->entry_date;?></p>
<div class="sz_blog_body">
<?php echo truncate(preg_replace('/<br\s?\/?>/', '<br />', $value->body), 50);?>
</div>
<div align="right">
<?php echo anchor('blog/article/' . $value->sz_blog_id, '全部読む&raquo;');?>
</div>
<?php endforeach;?>
<p align="center"><?php echo $pagination;?></p>
<?php else:?>
<p>検索にヒットしませんでした。</p>
<?php endif;?>
