<?php if (count($childs) > 0):?>
<ul>
	<?php foreach ($childs as $value):?>
 	<li id="page_<?php echo $value['page_id'];?>"<?php if ($value['childs']) { echo ' class="ch close"';}?>>
 		<?php if ($value['childs']):?>
 		<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>
 		<img src="<?php echo file_link()?>images/dashboard/folder.png" />
 		<?php elseif ((int)$value['alias_to'] > 0):?>
 		<img src="<?php echo file_link()?>images/dashboard/alias.png" />
 		<?php elseif ( ! empty($value['external_link']) ):?>
 		<img src="<?php echo file_link()?>images/dashboard/external.png" />
 		<?php else:?>
 		<img src="<?php echo file_link()?>images/dashboard/file.png" />
 		<?php endif;?>
 		
 		<?php if ($value['page_id'] == $current):?>
 		<span class="current"><?php echo prep_str($value['page_title']);?></span>
 		<?php elseif ( ! empty($value['external_link']) ):?>
 		<?php echo prep_str($value['page_title']);?>
 		<?php else:?>
 		<a href="<?php echo (($value['is_ssl_page'] > 0) ? ssl_page_link() : page_link()) . $value['page_id'];?>" class="sz_page_jump">
 			<?php echo prep_str($value['page_title']);?>
 		</a>
 		<?php endif;?>
 		
  	</li>
	<?php endforeach;?>
</ul>
<?php endif;?>