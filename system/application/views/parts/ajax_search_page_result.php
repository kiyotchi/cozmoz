<h3>検索結果</h3>
<?php if (count($pages) > 0):?>
<?php foreach ($pages as $page):?>
<div class="sz_section clearfix" pid="<?php echo $page->page_id;?>">
	<img src="<?php echo file_link()?>images/dashboard/file.png" />
	<span pid="<?php echo $page->page_id;?>"><?php echo prep_str($page->page_title);?></span>
</div>
<?php endforeach;?>
<?php else:?>
<p>ヒットしませんでした。</p>
<?php endif;?>
