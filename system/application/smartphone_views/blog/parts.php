<div class="sz_blog_menu_parts">

<?php if ($type === 'calendar'):?>

<h4 class="blog_menu_caption"><span>カレンダー</span></h4>
<div id="sz_blog_calendar">
<?php echo $calendar_string;?>
</div>

<?php elseif ($type === 'category'):?>

<h4 class="blog_menu_caption"><span>カテゴリ一覧</span></h4>
<ul class="sz_blog_menu_list">
<?php if (count($category) > 0):?>
<?php foreach ($category as $cat):?>
<li>
<a href="<?php echo page_link();?>blog/category/<?php echo $cat->sz_blog_category_id;?>"<?php if ($cat->sz_blog_category_id == $category_id) echo ' class="active"';?>>
<?php echo prep_str($cat->category_name);?>
</a>
</li>
<?php endforeach;?>
<?php else:?>
<li class="none">カテゴリはありません。</li>
<?php endif;?>
</ul>

<?php elseif ($type === 'comment'):?>

<h4 class="blog_menu_caption"><span>最近付けられたコメント</span></h4>
<ul class="sz_blog_menu_list">
<?php if (count($comment) > 0):?>
<?php foreach ($comment as $c):?>
<li>
<a href="<?php echo page_link();?>blog/article/<?php echo $c->sz_blog_id;?>">
<?php echo truncate(prep_str($c->comment_body, 50));?>
</a>
</li>
<?php endforeach;?>
<?php else:?>
<li class="none">コメントはありません。</li>
<?php endif;?>
</ul>

<?php elseif ($type === 'article'):?>

<h4 class="blog_menu_caption"><span>最近投稿された記事</span></h4>
<ul class="sz_blog_menu_list">
<?php if (count($articles) > 0):?>
<?php foreach ($articles as $article):?>
<li>
<a href="<?php echo page_link();?>blog/article/<?php echo $article->sz_blog_id;?>">
<?php echo truncate(prep_str($article->title, 50));?>
</a>
</li>
<?php endforeach;?>
<?php else:?>
<li class="none">最近の記事はありません。</li>
<?php endif;?>
</ul>

<?php elseif ($type === 'search'):?>

<h4 class="blog_menu_caption"><span>ブログ検索</span></h4>
<?php echo form_open('blog/search', array('class' => 'sz_blog_search'));?>
<fieldset>
<?php echo form_input(array('name' => 'search_query', 'value' => '', 'class' => 'sz_blog_search_input'));?><?php echo form_submit(array('value' => '検索'));?>
</fieldset>
<?php echo form_close();?>

<?php endif;?>
</div>