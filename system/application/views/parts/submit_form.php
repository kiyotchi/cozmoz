<?php echo $contents;?>
<div id="sz-blockform-submit" class="clearfix">
	<p class="sz_button">
		<a href="javascript:winClose()" class="sz-blockform-close">
			<span>閉じる</span>
		</a>
		<a href="javascript:doSubmit()" class="button_right">
			<span>追加</span>
		</a>
	</p>
	<?php echo form_hidden($token_name, $token);?>
	<?php echo form_hidden('area', $area_name);?>
	<?php echo form_hidden('page_id', $page_id);?>
	<?php echo form_hidden('process', 'block_add');?>
	<?php echo form_hidden('col_id', $collection_id);?>
	<?php echo form_hidden('block_id', $block_id);?>
	<?php echo form_hidden('version_number', $version_number);?>
</div>
</form>