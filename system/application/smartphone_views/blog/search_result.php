<p class="search_display">
「<?php echo prep_str($display_query);?>」で検索した結果<br />
<?php echo $total;?>
</p>
<?php if (count($result) > 0):?>
<?php foreach ($result as $value):?>
<div class="sz_blog_entry">
<h3 class="sz_blog_title clearfix">
<?php echo anchor('blog/article/' . $value->sz_blog_id, $value->title);?>
<span class="sz_blog_entry_date"><?php echo $value->entry_date;?></span>
</h3>
<div class="sz_blog_body"><?php echo truncate(preg_replace('/<br\s?\/?>/', '<br />', $value->body), 50);?></div>
<p class="morelink">
<?php echo anchor('blog/article/' . $value->sz_blog_id, '全部読む&raquo;');?>
</div>
<?php endforeach;?>
<p class="sz_blog_pagination"><?php echo $pagination;?></p>
<?php else:?>
<p>検索にヒットしませんでした。</p>
<?php endif;?>
