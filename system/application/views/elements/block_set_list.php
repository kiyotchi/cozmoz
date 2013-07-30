<?php if (count($block_set) > 0):?>
<h4 class="pp_caption">現在登録されているブロックセット</h4>
<form id="sz_blockset_list">
	<ul>
		<?php foreach ($block_set as $value):?>
		<li><label><?php echo form_checkbox('block_set_master_id[]', $value->block_set_master_id, FALSE);?>&nbsp;<?php echo prep_str($value->master_name);?>（<?php echo $value->total;?>）</label></li>
		<?php endforeach;?>
	</ul>
	<p class="sz_button clearfix">
		<a href="javascript:void(0)" id="sz_blockset_add">
			<span>ブロックセットに追加</span>
		</a>
	</p>
	<?php echo form_hidden('block_id', $block_id);?>
	<br style="clear:both" />
</form>
<?php endif;?>

<form id="sz_blockset_new_add">
<h4 class="pp_caption">新規ブロックセットを作成して追加</h4>
	<p style="text-align:center;">
		<?php echo form_input(array('name' => 'master_name', 'value' => ''));?>
	</p>
	<p class="sz_button clearfix">
		<a href="javascript:void(0)" id="sz_blockset_make_add">
			<span>セットを作成して追加</span>
		</a>
	</p>
	<?php echo form_hidden('block_id', $block_id);?>
</form>
