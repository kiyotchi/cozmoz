<ul class="sz_tabs block clearfix">
	<li><a href="#tab_content1">ブロックリスト</a></li>
	<li><a href="#tab_content2">下書きリスト</a></li>
	<li><a href="#tab_content3">共有ブロックリスト</a></li>
	<li><a href="#tab_content4">ブロックセットから追加</a></li>
</ul>
<div class="tab_content_draft" id="tab_content1">
	<ul class="sz_block_list add">
		<?php foreach ($block_list as $cid => $block):?>
		<li class="col_<?php echo $cid;?>" ifw="<?php echo $block->interface_width;?>" ifh="<?php echo $block->interface_height;?>" cname="<?php echo $block->collection_name;?>">
			<?php echo $block->block_name;?>
			<div class="sz_block_description">
				<?php echo $block->description;?>
			</div>
		</li>
		<?php endforeach;?>
	</ul>
</div>
<div class="tab_content_draft init_hide" id="tab_content2">
	<p>下書き保存したブロックから追加できます。</p>
	<ul class="sz_block_list draft">
		<?php foreach ($draft as $value):?>
		<li did="<?php echo $value->draft_blocks_id?>">
			<?php echo ( !empty($value->alias_name) ) ? prep_str($value->alias_name) : prep_str($value->collection_name . 'ブロック');?>
			<div class="sz_draft_preview" style="display:none">
				<?php echo $this->load->block($value->collection_name, $value->block_id);?>
			</div>
			<a href="javascript:void(0)" class="toggle">&nbsp;</a>
		</li>
		<?php endforeach;?>
	</ul>
</div>
<div class="tab_content_draft init_hide" id="tab_content3">
	<p>共有ブロックから追加できます。</p>
	<ul class="sz_block_list static">
	<?php foreach ($static_blocks as $sb):?>
	<li bid="<?php echo $sb->block_id?>">
		<?php echo ( !empty($sb->alias_name) ) ? prep_str($sb->alias_name) : prep_str($sb->collection_name . 'ブロック');?>
		<div class="sz_draft_preview" style="display:none">
			<?php echo $this->load->block($sb->collection_name, $sb->block_id);?>
		</div>
		<a href="javascript:void(0)" class="toggle">&nbsp;</a>
	</li>
	<?php endforeach;?>
	</ul>
</div>
<div class="tab_content_draft init_hide" id="tab_content4">
	<p>登録したブロックセットから追加できます。</p>
	<ul class="sz_block_list blockset">
	<?php foreach ( $block_set as $bs ):?>
	<li bid="<?php echo $bs->block_set_master_id?>">
		<?php echo prep_str($bs->master_name);?>（<?php echo $bs->total;?>個のブロックが登録されています）
	</li>
	<?php endforeach;?>
	</ul>
</div>