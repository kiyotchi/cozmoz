<?php if (count($files) === 0):?>
<p>ヒットしませんでした。</p>
<?php else:?>
<?php foreach ($files as $file):?>
<div class="sz_file_search_section clearfix">
	<div class="sz_file_search_img" file_id="<?php echo $file->file_id;?>">
		<?php if(has_icon_ext($file->extension)):?>
		<p style="background: transparent url(<?php echo file_link() . 'images/icons/files/' . $file->extension . '.png';?>) center center no-repeat;">&nbsp;</p>
		<?php elseif (image_ext($file->extension)):?>
		<p style="background: transparent url(<?php echo file_link() . 'files/thumbnail/' . $file->crypt_name . '.' . $file->extension;?>) center center no-repeat;">&nbsp;</p>
		<?php else:?>
		<p style="background: transparent url(<?php echo file_link() . 'images/icons/files/file.png';?>) center center no-repeat;">&nbsp;</p>
		<?php endif;?>
	</div>
	<div class="sz_file_search_data">
		<p><?php echo prep_str($file->file_name . '.' . $file->extension);?></p>
		<?php if ($file->width > 0 && $file->height > 0):?>
		<p><?php echo $file->width;?>px&nbsp;x<?php echo $file->height;?>px</p>
		<?php endif;?>
		<p><?php echo $file->size;?>KB</p>
	</div>
</div>
<?php endforeach;?>
<?php endif;?>