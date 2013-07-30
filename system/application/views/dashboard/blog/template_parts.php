<?php if (count($templates) > 0):?>
<table class="template_table">
	<tbody>
		<?php foreach ($templates as $key => $value):?>
		<tr>
			<td>
				<input type="radio" name="template_id" id="tpid_<?php echo $value->template_id;?>" value="<?php echo $value->template_id;?>"<?php if($use_template == $value->template_id) { echo ' checked="checked"';}?> />
			</td>
			<td>
				<label for="tpid_<?php echo $value->template_id;?>">
				<?php if (file_exists('templates/' . $value->template_handle . '/image.jpg')):?>
				<img src="<?php echo file_link()?>templates/<?php echo $value->template_handle;?>/image.jpg" alt="" />
				<?php else:?>
				<img src="<?php echo file_link()?>images/no_image.gif" alt=""/>
				<?php endif;?>
				</label>
			</td>
			<td>
				<p style="font-weight:bold"><?php echo $value->template_name;?></p>
				<p><?php echo nl2br($value->description);?></p>
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<?php else:?>
<p>利用出来るテンプレートがありません。</p>
<?php endif;?>