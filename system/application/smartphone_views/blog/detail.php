<p class="backlink">
<a href="<?php echo page_link('blog/entries');?>">&laquo;一覧へ戻る</a>
</p>

<?php if ($detail):?>

<!-- entry space -->
<div class="sz_blog_entry">

<!-- entry title -->
<h3 class="sz_blog_title clearfix">
<strong><?php echo $detail->title;?></strong>
</h3>
<!-- /entry title -->

<!-- category -->
<p class="sz_blog_category">
<?php if (isset($category[$detail->sz_blog_category_id])):?>
<?php echo prep_str($category[$detail->sz_blog_category_id]);?>
<?php endif;?>
</p>
<!-- /category -->

<!-- author -->
<p class="sz_blog_author">
posted&nbsp;by&nbsp;<?php echo anchor('blog/author/' . rawurlencode($detail->user_name), prep_str($detail->user_name));?><br />
<span class="sz_blog_entry_date"><?php echo $detail->entry_date;?></span>
</p>

<!-- body -->
<div class="sz_blog_body">
	<?php echo preg_replace('/<br\s?\/?>/', '<br />', $detail->body);?>
</div>
<!-- /body -->

<!--  next-prev article -->
<?php if ( $next_article || $prev_article ):?>
<p class="sz_blog_next_prev">

<?php if ( $prev_article ):?>
<?php echo anchor('blog/article/' . $prev_article, '&laquo;&nbsp;前の記事', 'class="sz_blog_prev"');?>
<?php endif;?>

<?php if ( $next_article ):?>
<?php echo anchor('blog/article/' . $next_article, '次の記事&nbsp;&raquo;', 'class="sz_blog_next"');?>
<?php endif;?>
</p>
<?php endif;?>
<!--  /next-prev article -->

</div>
<!-- /entry space -->

<?php if ( strlen($zenback_code) > 0 ):?>
<!-- zenback code -->
<?php echo $zenback_code;?>
<!-- /zenback code -->
<?php endif;?>

<!--  trackback space -->
<?php if ( $detail->is_accept_trackback > 0 ):?>
<div class="sz_blog_comment_list">
<h4 class="sz_blog_comment_section">この投稿へのトラックバック<span>（<?php echo count($trackbacks);?>件）</span></h4>

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

<h5 class="sz_blog_trackbacks_uri">この記事へのトラックバックURL：</h5>
<p class="trackback_uri">
<input type="text" name="tb_uri" value="<?php echo page_link();?>blog/trackback/<?php echo $detail->sz_blog_id;?>" readonly="readonly" />
</p>

</div>
<?php endif;?>

<!-- comment history -->
<div class="sz_blog_comment_list">
<h4 class="sz_blog_comment_section">この投稿に付けられたコメント<span>（<?php echo count($comment);?>件）</span></h4>

<?php if (count($comment) > 0):?>
<p id="toggle_comment">
<a href="javascript:void(0)" rel="open">コメントを表示する</a>
</p>
<div class="sz_comment_box">
<?php foreach ($comment as $key => $value):?>
<div class="sz_blog_comment<?php if ($key == 0) echo ' first'?>">
	<p><?php echo form_prep($value->name);?>&nbsp;さん<span class="post_date"><?php echo $value->post_date;?></span></p>
	<div>
		<?php echo nl2br(form_prep($value->comment_body));?>
	</div>
</div>
<?php endforeach;?>
</div>
<?php endif;?>
<!-- /comment history -->

<!-- comment form -->
<?php if ((int)$detail->is_accept_comment > 0):?>
<h4 class="sz_blog_comment_section post_comment">コメントを投稿する</h4>
	<?php echo form_open('blog/regist_comment', array('class' => 'blog_comment_form'));?>
	<fieldset<?php if (isset($comment_msg)) echo ' id="comment_posted"';?>>
	<dl class="comment_submission">
		<dt><label for="sz_comment_name">お名前:</label></dt>
		<dd>
			<?php echo form_input(array('name' => 'name', 'id' => 'sz_comemnt_name', 'class' => 'tt_input', 'value' => set_value('name')));?>
			<?php echo $this->form_validation->error('name');?>
		</dd>
		<dt><label for="sz_comment_body">コメント:</label></dt>
		<dd>
			<?php echo form_textarea(array('name' => 'comment_body', 'id' => 'sz_comment_body', 'class' => 'tt_area', 'value' => set_value('comment_body'), 'cols' => 30, 'rows' => 4));?>
			<?php echo $this->form_validation->error('comment_body')?>
		</dd>
		<?php if ($is_captcha === 1):?>
		<dt>&nbsp;</dt>
		<dd>
			<label><?php echo $captcha;?>
			<?php echo form_input(array('name' => 'captcha', 'id' => 'captcha'))?>
			</label>
			<p class="capcha_caption">画像に表示されている文字を入力してください。</p>
			<?php echo $this->form_validation->error('captcha');?>
		</dd>
		<?php endif;?>
		<dt>&nbsp;</dt>
		<dd class="center">
			<?php echo form_hidden('ticket', $ticket);?>
			<?php echo form_hidden('blog_id', $blog_id);?>
			<input type="submit" value="コメントを投稿する" id="comment_submit" />
		</dd>
	</dl>
	</fieldset>
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