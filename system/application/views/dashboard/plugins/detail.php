<h3>プラグイン詳細</h3>
<div class="list_wrap">
<table>
<tbody>
<tr class="odd">
<td>プラグイン名</td>
</tr>
<tr>
<td style="padding-left:30px"><?php echo prep_str($name);?></td>
</tr>
<tr class="odd">
<td>プラグイン概要</td>
</tr>
<tr>
<td style="padding-left:30px"><?php echo nl2br(prep_str($description));?></td>
</tr>

<?php if (count($pages) > 0):?>
<tr class="odd">
<td>このパッケージに含まれるページ</td>
</tr>
<?php foreach ($pages as $page):?>
<tr>
<td style="padding-left:30px">
<?php echo prep_str($page->page_title);?>&nbsp;<span>-<?php echo prep_str($page->description);?></span>
</td>
</tr>
<?php endforeach;?>
<?php endif;?>

<?php if (count($blocks) > 0):?>
<tr class="odd">
<td>このパッケージに含まれるブロック</td>
</tr>
<?php foreach ($blocks as $block):?>
<tr>
<td style="padding-left:30px"><?php echo prep_str($block->block_name);?>&nbsp;<span>-<?php echo prep_str($block->description);?></span></td>
</tr>
<?php endforeach;?>
<?php endif;?>

</tbody>
</table>

<p class="conf">
	<?php if ($is_installed === TRUE):?>
	<?php echo anchor('dashboard/plugins/plugin_list/delete/' . $handle, set_image('delete.png', TRUE) . '&nbspこのプラグインを無効にする', 'id="remove_plugin"');?>
	<?php else:?>
	<?php echo anchor('dashboard/plugins/plugin_list/install_plugin/' . $handle, set_image('plus.png', TRUE) . '&nbsp;このプラグインをインストール', 'id="plugin"');?>
	<?php endif;?>
</p>
</div>
