<p class="backlink">
<a href="<?php echo page_link('blog/entries');?>">&laquo;一覧へ戻る</a>
</p>

<?php if ($detail):?>

<!-- entry title -->
<h3><?php echo prep_str($detail->title);?></h3>
<!-- /entry title -->

<!-- category -->
<p>
<?php if (isset($category[$detail->sz_blog_category_id])):?>
<?php echo prep_str($category[$detail->sz_blog_category_id]);?>
<?php endif;?>
</p>
<!-- /category -->

<!-- author -->
<p align="right">
posted&nbsp;by&nbsp;<?php echo anchor('blog/author/' . rawurlencode($detail->user_name), prep_str($detail->user_name));?><br />
<?php echo $detail->entry_date;?>
</p>

<!-- body -->
<div>
<?php echo preg_replace('/<br\s?\/?>/', '<br />', $detail->body);?>
</div>
<!-- /body -->
<?php echo spacer_gif(5, 40);?><br />
<!--  next-prev article -->
<?php if ( $next_article || $prev_article ):?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
<td width="50%">
<?php if ( $prev_article ):?>
<?php echo anchor('blog/article/' . $prev_article, '&laquo;&nbsp;前の記事', 'class="sz_blog_prev"');?>
<?php endif;?>
</td>
<td width="50%" align="right">
<?php if ( $next_article ):?>
<?php echo anchor('blog/article/' . $next_article, '次の記事&nbsp;&raquo;', 'class="sz_blog_next"');?>
<?php endif;?>
</td>
</tr>
</table>
<?php endif;?>
<!--  /next-prev article -->

<!-- /entry space -->

<!--  trackback space -->
<?php if ( $detail->is_accept_trackback > 0 ):?>
<hr />
<h4>この投稿へのトラックバック&nbsp;&nbsp;（<?php echo count($trackbacks);?>件）</h4>

<?php if ( count($trackbacks) > 0 ):?>
<dl class="sz_blog_trackback_section">
<?php foreach ( $trackbacks as $tb ):?>
	<dd><?php echo prep_str($tb->excerpt);?></dd>
	<dt><?php echo anchor($tb->url, prep_str($tb->title . ' - ' . $tb->blog_name));?><br /><?php echo $tb->received_date;?></dt>
	<?php endforeach;?>
</dl>
<?php else:?>
<p>トラックバックはありません。</p>
<?php endif;?>

<h5>この記事へのトラックバックURL：</h5>
<p class="trackback_uri">
<?php echo preg_replace('/(.+)\?.*$/', '$1', page_link('blog/trackback/' . $detail->sz_blog_id));?>
</p>
<?php endif;?>

<!-- comment history -->
<hr />
<div class="sz_blog_comment_list">
<h4 class="sz_blog_comment_section">この投稿に付けられたコメント（<?php echo count($comment);?>件）</h4>

<?php if (count($comment) > 0):?>
<?php foreach ($comment as $key => $value):?>
<div><?php echo nl2br(form_prep($value->comment_body));?></div>
<p align="right"><?php echo form_prep($value->name);?>&nbsp;さん<br /><?php echo $value->post_date;?></p>
<?php endforeach;?>
<?php endif;?>
<!-- /comment history -->

<!-- comment form -->
<?php if ((int)$detail->is_accept_comment > 0):?>
<hr />
<h4>コメントを投稿する</h4>
<?php echo form_open('blog/regist_comment', array('class' => 'blog_comment_form'));?>
<p>
<label for="sz_comment_name">お名前:</label><br />
<?php echo form_input(array('name' => 'name', 'id' => 'sz_comemnt_name', 'class' => 'tt_input', 'value' => set_value('name')));?>
<?php echo $this->form_validation->error('name');?>
</p>
<p>
<label for="sz_comment_body">コメント:</label><br />
<?php echo form_textarea(array('name' => 'comment_body', 'id' => 'sz_comment_body', 'class' => 'tt_area', 'value' => set_value('comment_body'), 'cols' => 30, 'rows' => 4));?>
<?php echo $this->form_validation->error('comment_body')?>
</p>
<?php if ($is_captcha === 1):?>
<p>
<label><?php echo $captcha;?>
<?php echo form_input(array('name' => 'captcha', 'id' => 'captcha'))?>
</label><br />
画像に表示されている文字を入力してください。<br />
<?php echo $this->form_validation->error('captcha');?>
</p>
<?php endif;?>
<p align="center">
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_hidden('blog_id', $blog_id);?>
<input type="submit" value="コメントを投稿する" id="comment_submit" />
</p>
<?php echo form_close();?>
<?php endif;?>
<!-- comment form -->

<?php else:?>
<p>投稿が見つかりませんでした。</p>
<?php endif;?>
</div>

<p class="backlink">
<a href="<?php echo page_link('blog/entries');?>">&laquo;一覧へ戻る</a>
</p>