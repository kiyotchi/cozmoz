<?php echo form_open('dashboard/files/directory_view/file_download_from_view');?>
<p style="text-align:right;background:#ddecf0;padding : 15px 0;">
	<input type="hidden" name="file_id" value="<?php echo $fid;?>" />
	<input type="hidden" name="ticket" value="<?php echo $ticket;?>" />
	<input type="submit" value="ダウンロード" />
</p>
<?php echo form_close();?>
<p style="text-align:center;margin-top : 10px;">
	<?php if (has_icon_ext($file->extension)):?>
	<img src="<?php echo file_link();?>images/icons/files/<?php echo $file->extension . '.png';?>" alt="<?php echo $file->file_name;?>" />
	<span>表示できないファイルです。</span>
	<?php elseif(image_ext($file->extension)):?>
	<img src="<?php echo file_link();?>files/<?php echo $file->crypt_name . '.' . $file->extension;?>" alt="<?php echo $file->file_name;?>" />
	<?php else:?>
	<img src="<?php echo file_link();?>images/icons/files/file.png" alt="<?php echo $file->file_name;?>" />
	<span>表示できないファイルです。</span>
	<?php endif;?>
</p>
<h3>ファイル情報</h3>
<table class="ajax_file_view_info">
	<tbody>
	<tr class="odd">
		<td>ファイル名：</td><td class="action"><?php echo $file->file_name . '.' . $file->extension;?></td>
	</tr>
	<tr>
		<td>ファイル形式：</td><td class="action"><?php echo $file->extension;?></td>
	</tr>
	<tr class="odd">
		<tr>
		<td>ファイルサイズ：</td><td class="action"><?php echo $file->size;?>&nbsp;（KB）</td>
	</tr>
	<?php if ($file->width > 0 && $file->height > 0):?>
		<tr>
		<td>横幅×高さ：</td><td class="action"><?php echo $file->width;?>px&nbsp;×&nbsp;<?php echo $file->height;?>px</td>
	</tr>
	<tr class="odd">
	<?php else:?>
	<tr>
	<?php endif;?>
		<td>登録日時：</td><td class="action"><?php echo $file->added_date;?></td>
	</tr>
	</tbody>
</table>