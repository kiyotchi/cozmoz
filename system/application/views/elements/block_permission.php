<p style="color:#c00;margin : 8px 0;">※チェックを入れたユーザーに対して権限が設定されます。</p>
<form action="<?php echo get_base_link() . $page_id;?>" method="post" id="sz-blockform">
<table class="block_permissions">
	<tbody>
		<tr>
			<th>&nbsp;</th>
			<th>ブロックの内容を閲覧</th>
			<th>ブロックを編集</th>
		</tr>
		<?php $cnt = 0;?>
		<?php foreach ($user_list as $key => $value):?>
		<tr<?php if (++$cnt % 2 === 0) echo ' class="odd"';?>>
		<td><?php echo prep_str($value->user_name);?></td>
		<?php if ($key == 1 || $value->admin_flag == 1):?>
		<td colspan="3" style="text-align:center">
			管理者権限により許可
		</td>
		<?php else:?>
			<td class="pp_ch">
				<input type="checkbox" name="block_permission[]" value="<?php echo $key?>"<?php if ( in_array($key, $view_permission) )  echo ' checked="checked"';?> />
			</td>
			<td class="pp_ch">
			<?php if ($key === 0 || $key === 'm'):?>
			-
			<?php else:?>
				<input type="checkbox" name="block_permission_edit[]" value="<?php echo $key?>"<?php if ( in_array($key, $edit_permission) ) echo ' checked="checked"';?> />
			<?php endif;?>
			</td>
		</tr>
		<?php endif;?>
		<?php endforeach;?>
	</tbody>
</table>

<?php if ( $enable_mobile ):?>

<h3 style="padding:10px 0;font-size:14px;">モバイルの表示権限設定</h3>
<table class="block_permissions">
	<tbody>
		<tr>
			<th>&nbsp;</th>
			<th>ブロックの内容を閲覧</th>
		</tr>
		<tr>
			<td>Docomo</td>
			<td><input type="checkbox" name="mobile_permission[]", value="D"<?php echo ( in_array('D', $mobile_permission) ) ? ' checked="checked"' : '';?> /></td>
		</tr>
		<tr>
			<td>AU</td>
			<td><input type="checkbox" name="mobile_permission[]", value="A"<?php echo ( in_array('A', $mobile_permission) ) ? ' checked="checked"' : '';?> /></td>
		</tr>
		<tr>
			<td>Softbank</td>
			<td><input type="checkbox" name="mobile_permission[]", value="S"<?php echo ( in_array('S', $mobile_permission) ) ? ' checked="checked"' : '';?> /></td>
		</tr>
		<tr>
			<td>Willcom</td>
			<td><input type="checkbox" name="mobile_permission[]", value="W"<?php echo ( in_array('W', $mobile_permission) ) ? ' checked="checked"' : '';?> /></td>
		</tr>
	</tbody>
</table>

<?php endif;?>

<div id="sz-blockform-submit" class="clearfix">
	<p class="sz_button">
		<a href="javascript:winClose()" class="sz-blockform-close">
			<span>閉じる</span>
		</a>
		<a href="javascript:doSubmit()" class="button_right">
			<span>更新</span>
		</a>
	</p>
	<?php echo form_hidden('sz_token', $token);?>
	<?php echo form_hidden('page_id', $page_id);?>
	<?php echo form_hidden('process', 'block_permission');?>
	<?php echo form_hidden('block_id', $block_id);?>
</div>
</form>