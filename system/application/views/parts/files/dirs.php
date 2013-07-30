<?php foreach ($dirs as $dir):?>
<?php if ($dir->access_permission == '' || strpos($dir->access_permission, ':' . $this->user_id . ':') !== FALSE):?>
<li class="sz_dir sz_sort">
	<div class="sz_dir_view sz_sort_handle" dir_id="<?php echo $dir->directories_id;?>">
		<div class="sz_data sz_dir">
			<img src="<?php echo file_link() . 'images/icons/files/dir' . PNG?>" alt="<?php echo $dir->dir_name;?>" />
<?php else:?>
<li class="sz_dir pm_denied">
	<div class="sz_dir_view" dir_id="<?php echo $dir->directories_id;?>">
		<div class="sz_data sz_dir">
			<img src="<?php echo file_link() . 'images/icons/files/lock_folder' . PNG?>" alt="<?php echo $dir->dir_name;?>" />
<?php endif;?>
			<div class="sz_name"><?php echo $dir->dir_name;?></div>
		</div>
	</div>
</li>
<?php endforeach;?>
<?php foreach ($files as $file):?>
<li>
	<div class="sz_file_view sz_sort_handle" file_id="<?php echo $file->file_id;?>" filename="<?php echo $file->file_name;?>.<?php echo $file->extension;?>" title="<?php echo $file->file_name;?>.<?php echo $file->extension;?>">
		<div class="sz_data">
			<?php if(has_icon_ext($file->extension)):?>
			<p style="background: transparent url(<?php echo file_link() . 'images/icons/files/' . $file->extension . PNG;?>) center center no-repeat;">&nbsp;</p>
			<?php elseif (image_ext($file->extension)):?>
			<p style="background: transparent url(<?php echo file_link() . 'files/thumbnail/' . $file->crypt_name . '.' . $file->extension;?>) center center no-repeat;">&nbsp;</p>
			<?php else:?>
			<p style="background: transparent url(<?php echo file_link() . 'images/icons/files/file' . PNG;?>) center center no-repeat;">&nbsp;</p>
			<?php endif;?>
			<div class="sz_name"><?php echo $file->file_name . '.' . $file->extension;?></div>
		</div>
	</div>
</li>
<?php endforeach;?>