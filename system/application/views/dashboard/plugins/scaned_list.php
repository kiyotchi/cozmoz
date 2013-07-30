<table>
	<tbody>
		<tr class="caption">
			<th>プラグイン名</th>
			<th>概要</th>
			<th>&nbsp;</th>
			<th class="action">操作
		</tr>
		<?php if (count($list) > 0):?>
		<?php $times = 0;?>
		<?php foreach ($list as $key => $value):?>
		<tr<?php if ($times % 2 === 0) echo ' class="odd"';?>>
			<td><?php echo prep_str($value);?></td>
			<td style="width:250px;">
				<?php echo prep_str($value);?>
				<div style="display:none"></div>
			</td>
			<td style="width:100px;">
				<a href="<?php echo page_link();?>dashboard/plugins/plugin_list/get_plugin_detail/<?php echo prep_str($value);?>" class="plugin_detail">
				<?php echo set_image('plus.png', TRUE);?>&nbsp;プラグイン詳細
				</a>
			</td>
			<td class="action">
				<a href="<?php echo page_link();?>dashboard/plugins/plugin_list/install_plugin/<?php echo prep_str($value);?>" class="add">インストール</a>
			</td>
		</tr>
		<?php endforeach;?>
		<?php else:?>
		<tr><td colspan="4">インストール可能なプラグインはありません。</td></tr>
		<?php endif;?>
	</tbody>
</table>