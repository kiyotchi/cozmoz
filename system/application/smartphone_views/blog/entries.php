<?php if (count($entry) > 0):?>
<?php foreach ($entry as $value):?>
<div class="sz_blog_entry">
  <h3 class="sz_blog_title clearfix">
    <?php echo anchor('blog/article/' . $value->sz_blog_id, $value->title);?>
  </h3>
  <p class="sz_blog_category">
    <?php echo (isset($category_list[$value->sz_blog_category_id])) ? prep_str($category_list[$value->sz_blog_category_id]) : '-';?>
  </p>
  <p class="sz_blog_author">
    posted&nbsp;by&nbsp;<?php echo anchor('blog/author/' . rawurlencode($value->user_name), prep_str($value->user_name));?><br />
    <span class="sz_blog_entry_date"><?php echo $value->entry_date;?></span>
  </p>
  <div class="sz_blog_body"><?php echo truncate(preg_replace('/<br\s?\/?>/', '<br />', $value->body), 300);?></div>
  <p class="morelink">
    <?php echo anchor('blog/article/' . $value->sz_blog_id, '全部読む&raquo;');?>
  </p>
</div>
<?php endforeach;?>
<p class="sz_blog_pagination"><?php echo $pagination;?></p>
<?php else:?>
<p>投稿が見つかりませんでした。</p>
<?php endif;?>
