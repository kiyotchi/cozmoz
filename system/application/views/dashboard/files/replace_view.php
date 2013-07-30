<div class="sz_file_repalce_view">
	<p>ファイルを差し替えます。</p>
	<p style="color:#c00;">ブロック等で使用していたファイルは、<br />全て差し替え後のファイルに置換されます。</p>
	<div class="sz_file_replace_current">
		<h4>現在のファイル</h4>
		<div class="sz_file_current">
			<?php if(has_icon_ext($file->extension)):?>
			<img src="<?php echo file_link() . 'images/icons/files/' . $file->extension . '.png';?>" alt="" />
			<?php elseif (image_ext($file->extension)):?>
			<img src="<?php echo file_link() . 'files/thumbnail/' . $file->crypt_name . '.' . $file->extension;?>" alt="" />
			<?php else:?>
			<img src="<?php echo file_link() . 'images/icons/files/file.png';?>" alt="" />
			<?php endif;?>
			<span>
				<?php echo $file->file_name . '.' . $file->extension;?>
			</span>
		</div>
	</div>
	<div class="arrow_b">&nbsp;</div>
	<div class="sz_file_new">
		<iframe src="<?php echo page_link() . 'dashboard/files/directory_view/replace_upload_init/' . $file->file_id;?>" frameborder="0" scroll="no" style="width:100%;height:200px;"></iframe>
	</div>
	<p class="sz_button">
		<a href="javascript:void(0)" class="sz-blockform-close" id="sz_close">
			<span>キャンセル</span>
		</a>
		<a href="javascript:void(0)" class="button_right" id="sz_submit">
			<span>差し替え実行</span>
		</a>
	</p>
</div>