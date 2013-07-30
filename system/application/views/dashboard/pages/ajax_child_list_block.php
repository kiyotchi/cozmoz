<?php if (count($childs) > 0):?>
<ul>
	<?php foreach ($childs as $value):?>
 	<li id="page_<?php echo $value['page_id'];?>" class="sz_soratble<?php if ($value['childs']) { echo ' ch close';}?>">
 		<div class="sz_sitemap_page movable" pid="<?php echo $value['page_id'];?>">
			
			<?php if ($value['childs']):?>
			<img src="<?php echo file_link()?>images/dashboard/folder.png" class="sort_page" />
			<?php elseif ((int)$value['alias_to'] > 0):?>
 			<img src="<?php echo file_link()?>images/dashboard/alias.png" />
 			<?php elseif ((int)$value['is_system_page'] > 0):?>
 			<img src="<?php echo file_link();?>images/config.png" alt="" />
 			<?php elseif ( ! empty($value['external_link']) ):?>
 			<img src="<?php echo file_link()?>images/dashboard/external.png" />
 			<?php else:?>
	 		<img src="<?php echo file_link()?>images/dashboard/file.png" class="sort_page" />
	 		<?php endif;?>
	 		
 			<span pid="<?php echo $value['page_id']?>"<?php echo ( (int)$value['alias_to'] > 0 || (int)$value['is_system_page'] > 0 || ! empty($value['external_link']) ) ? '' : ' class="ttl"';?>>
 				<?php echo $value['page_title'];?><span><?php if ((int)$value['childs'] > 0) echo '&nbsp;(' . $value['childs'] . ')';?></span>
 			</span>
	 	</div>
	 	<?php if ( $value['childs'] ):?>
	 	<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>
	 	<?php endif;?>
 	</li>
	<?php endforeach;?>
</ul>
<?php endif;?>