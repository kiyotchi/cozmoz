					<table class="sz_flt" id="file_table_inner">
						<tbody>
							<?php if (count($files) === 0):?>
							<tr>
								<td colspan="6" class="none_data">登録ファイルはありません。</td>
							</tr>
							<?php else:?>
							<?php foreach ($files as $key => $value):?>
							<tr<?php if ((int)$key % 2 === 0){ echo ' class="odd"';}?>>
								<td class="chbox_cell cell1"><input type="checkbox" name="file_ids[]" value="<?php echo $value->file_id;?>" class="edit_targets" /></td>
								<td class="thumb cell2" vailgn="middle">
									<div class="thumbnail_frame" filename="<?php echo $value->crypt_name . '.' . $value->extension;?>" file_id="<?php echo $value->file_id;?>">
										<?php if(has_icon_ext($value->extension)):?>
										<p style="background: transparent url(<?php echo file_link() . 'images/icons/files/' . $value->extension . '.png';?>) center center no-repeat;">&nbsp;</p>
										<?php elseif (image_ext($value->extension)):?>
										<p style="background: transparent url(<?php echo file_link() . 'files/thumbnail/' . $value->crypt_name . '.' . $value->extension;?>) center center no-repeat;">&nbsp;</p>
										<?php else:?>
										<p style="background: transparent url(<?php echo file_link() . 'images/icons/file.png';?>) center center no-repeat;">&nbsp;</p>
										<?php endif;?>
									</div>
								</td>
								<td class="cell3"><?php echo $value->extension;?></td>
								<td class="fname cell4"><?php echo form_prep($value->file_name . '.' . $value->extension);?></td>
								<td class="cell5"><?php echo $value->added_date;?></td>
								<td class="cell6"><?php echo set_file_info($value->size, $value->width, $value->height);?></td>
							</tr>
							<?php endforeach;?>
							<?php endif;?>
						</tbody>
					</table>