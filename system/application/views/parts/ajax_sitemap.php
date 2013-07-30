<div id="sitemap_sub">
<ul id="ajax_sitemap_tree">
 	<li class="ch open" id="page_1"<?php if ($sitemap['childs'] === FALSE) { echo ' single="1"';}?>>
 		<?php if ($sitemap['childs'] === FALSE):?>
 		<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>
 		<?php else:?>
 		<a href="javascript:void(0)" class="open_dir oc">&nbsp;</a>
 		<?php endif;?>
 		<img src="<?php echo file_link();?>images/dashboard/folder.png" />
 		
 		<?php if ($sitemap[0]['page_id'] == $current):?>
 		<span class="current">
 			<?php echo prep_str($sitemap[0]['page_title']);?>
 		</span>
 		<?php else:?>
 		<a href="<?php echo page_link(($sitemap[0]['is_ssl_page'] > 0) ? TRUE : FALSE) . $sitemap[0]['page_id']?>" class="sz_page_jump">
 			<?php echo prep_str($sitemap[0]['page_title']);?>
 		</a>
 		<?php endif;?>
 		
 		<?php if ($sitemap['childs']):?>
 		<ul>
 			<?php foreach ($sitemap['childs'] as $value):?>
 			<li id="page_<?php echo $value['page_id'];?>" class="<?php if ($value['childs']) { echo 'ch close';}?>">
 				<?php if ($value['childs']):?>
 				<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>
 				<img src="<?php echo file_link()?>images/dashboard/folder.png" class="sz_sortable" />
 				<?php elseif ((int)$value['alias_to'] > 0):?>
 				<img src="<?php echo file_link()?>images/dashboard/alias.png" />
 				<?php elseif ( ! empty($value['external_link']) ):?>
 				<img src="<?php echo file_link()?>images/dashboard/external.png" />
 				<?php elseif ((int)$value['is_system_page'] > 0):?>
 				<img src="<?php echo file_link();?>images/config.png" alt="" />
 				<?php else:?>
 				<img src="<?php echo file_link()?>images/dashboard/file.png" class="sz_sortable" />
 				<?php endif;?>
 				
 				<?php if ($value['page_id'] == $current):?>
 				<span class="current"><?php echo prep_str($value['page_title']);?></span>
 				<?php elseif ( ! empty($value['external_link']) ):?>
 				<?php echo prep_str($value['page_title']);?>
 				<?php else:?>
 				<a href="<?php echo page_link(($value['is_ssl_page'] > 0) ? TRUE : FALSE)  . $value['page_id'];?>" class="sz_page_jump">
 					<?php echo prep_str($value['page_title']);?>
 				</a>
 				<?php endif;?>
 				
 			</li>
 			<?php endforeach;?>
  		</ul>
  		<?php endif;?>
  	</li>
  	<?php if (isset($system_front_page) && count($system_front_page) > 0):?>
  	<?php foreach ($system_front_page as $sfp):?>
  	<li>
  	<img src="<?php echo file_link();?>images/config.png" alt="" />
  	<?php if ($sfp->page_id == $current):?>
  	<span class="current"><?php echo prep_str($sfp->page_title);?></span>
  	<?php else:?>
  	<a href="<?php echo page_link(($sfp->is_ssl_page > 0) ? TRUE : FALSE) . $sfp->page_path;?>" class="sz_page_jump">
  		<?php echo prep_str($sfp->page_title);?>
  	</a>
  	<?php endif;?>
  	</li>
  	<?php endforeach;?>
  	<?php endif;?>
  </ul>
</div>