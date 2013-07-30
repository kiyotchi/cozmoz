<p class="search_display">
「<?php echo prep_str($display_query);?>」で検索した結果<br />
<?php echo $total;?>
</p>
<?php if (count($result) > 0):?>
<?php foreach ($result as $value):?>
<div class="sz_blog_entry">
<h3 class="sz_blog_title clearfix">
<a href="<?php echo article_link($value);?>"><?php echo prep_str($value->title);?></a>
<span class="sz_blog_entry_date"><?php echo $value->entry_date;?></span>
</h3>
<div class="sz_blog_body"><?php echo truncate(preg_replace('/<br\s?\/?>/', '<br />', $value->body), 100);?></div>
<p class="morelink">
<a href="<?php echo article_link($value);?>">全部読む&raquo;</a>
</div>
<?php endforeach;?>
<?php if ( isset($pagination) ):?>
<p class="sz_blog_pagination"><?php echo $pagination;?></p>
<?php endif;?>
<?php else:?>
<p>検索にヒットしませんでした。</p>
<?php endif;?>
