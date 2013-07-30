 <ul>
 	<li class="ch open" id="page_1">
 		<div class="movable" pid="1">
 			<img src="<?php echo file_link();?>images/dashboard/folder.png" /><span pid="<?php echo $pages[0]['page_id']?>" class="ttl"><?php echo $pages[0]['page_title'];?></span>
 		</div>
 		<a href="javascript:void(0)" class="open_dir oc">&nbsp;</a>
 		<?php if ($pages['childs']):?>
 		<ul>
 			<?php foreach ($pages['childs'] as $value):?>
 			<li id="page_<?php echo $value['page_id'];?>" class="sz_sortable<?php if ($value['childs']) { echo ' ch close';}?>">
 				<div class="sz_sitemap_page movable<?php if ((int)$value['alias_to'] > 0) { echo ' alias';}?>" pid="<?php echo $value['page_id'];?>">
 					<?php if ($value['childs']):?>
 					<img src="<?php echo file_link()?>images/dashboard/folder.png" class="sort_page" />
 					<?php elseif ((int)$value['alias_to'] > 0):?>
 					<img src="<?php echo file_link()?>images/dashboard/alias.png" />
 					<?php else:?>
 					<img src="<?php echo file_link()?>images/dashboard/file.png" class="sort_page" />
 					<?php endif;?>
 					<span pid="<?php echo $value['page_id']?>" class="ttl">
 						<?php echo $value['page_title'];?><span><?php if ((int)$value['childs'] > 0) echo '(' . $value['childs'] . ')';?></span>
 					</span>
 				</div>
 				<?php if ($value['childs']):?>
 				<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>
 				<?php endif;?>
 			</li>
 			<?php endforeach;?>
  		</ul>
  		<?php endif;?>
  	</li>
  </ul>
