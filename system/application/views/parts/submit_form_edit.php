<?php echo $contents;?>
<div id="sz-blockform-submit" class="clearfix">
	<p class="sz_button">
		<a href="javascript:winClose()" class="sz-blockform-close">
			<span>閉じる</span>
		</a>
		<a href="javascript:doSubmit()" class="button_right">
			<span>更新</span>
		</a>
	</p>
	<?php echo form_hidden('sz_token', $token);?>
	<?php echo form_hidden('page_id', $page_id);?>
	<?php echo form_hidden('process', 'block_edit');?>
	<?php echo form_hidden('block_id', $block_id);?>
	<?php echo form_hidden('slave_block_id', $slave_id);?>
	<?php echo form_hidden('collection_name', $collection_name);?>
</div>