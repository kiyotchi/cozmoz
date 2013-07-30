<div id="edit_block-<?php echo $value['block_id'];?>" class="cmsi_edit_block sz_content_blocks<?php echo ($can_move) ? ' sz_sortable' : '';?><?php echo (! $can_delete ) ? ' sz_disable_delete' : '';?><?php echo ( ! $can_edit ) ? ' sz_disable_edit' : '';?>" block_id="<?php echo $bid;?>" block_type="<?php echo $value['collection_name'];?>" ifw="<?php echo $block->get_if_width();?>" ifh="<?php echo $block->get_if_height();?>" slave_id="<?php echo $value['slave_block_id'];?>" style="position:relative">
<div class="sz_block_overlay<?php if ($block->is_multi_column() === TRUE) echo ' overlay-short';?>" block_id="<?php echo $bid;?>" collection_name="<?php echo $value['collection_name'];?>">
</div>

<?php if ( ! $can_edit ):?>
<div class="block_permission_denied">編集権限がありません。</div>
<?php endif;?>
<?php if ($can_edit):?>
<a class="sz_block_etc_menu" href="javascript:void(0)" rel="<?php echo $bid;?>">&nbsp;</a>
<?php endif;?>
<div>