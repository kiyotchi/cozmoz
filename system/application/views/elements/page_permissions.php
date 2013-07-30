<p style="color:#c00;margin : 8px 0;">※チェックを入れたユーザーに対して権限が設定されます。</p>
<table class="page_permissions">
	<tbody>
		<tr>
			<th>&nbsp;</th>
			<th>ページへのアクセス</th>
			<th>ページの編集</th>
			<th>ページの公開</th>
		</tr>
		<?php $cnt = 0;?>
		<?php foreach ($permission_list as $key => $value):?>
		<tr<?php if (++$cnt % 2 === 0) echo ' class="odd"';?>>
			<td><?php echo $value->user_name;?></td>
			<?php if ($key == 1 || $value->admin_flag > 0):?>
			<td colspan="3" style="text-align:center">
				管理者権限により許可
			</td>
			<?php else:?>
			<td class="pp_ch">
				<input type="checkbox" name="permission[]" value="<?php echo $key?>"<?php if ($key === 0 || $key === 'm' || $value->admin_flag < 1) { echo ' checked="checked"';}?> />
			</td>
			<td class="pp_ch">
				<?php if ( $key === 0 || $key === 'm'):?>
				-
				<?php else:?>
				<input type="checkbox" name="permission_edit[]" value="<?php echo $key?>" />
				<?php endif;?>
			</td>
			<td class="pp_ch">
				<?php if ( $key === 0 || $key === 'm'):?>
				-
				<?php else:?>
				<input type="checkbox" name="permissions_approve[]" value="<?php echo $key?>" />
				<?php endif;?>
			</td>
			<?php endif;?>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
