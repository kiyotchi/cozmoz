<?php if (count($scaned_page) > 0):?>
<p class="info"><?php echo count($scaned_page);?>&nbsp;ページがスキャンされました。</p>
<?php echo form_open('dashboard/pages/system_page/add_system_page');?>
<table>
	<tbody>
		<tr class="caption">
			<th>&nbsp;</th>
			<th>ページパス</th>
			<th>ページタイトル（コントローラクラス名）</th>
		</tr>
		<?php foreach ($scaned_page as $value):?>
		<tr>
			<td>
				<?php echo form_checkbox('add_page_path[]', $value['page_path']);?>
			</td>
			<td><?php echo $value['page_path'];?></td>
			<td><?php echo ($value['page_title'] === '') ? 'ディレクトリページ' : ucfirst($value['page_title'])?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<p class="info"><a href="javascript:void(0)" id="toggle_check">全てチェック／チェックを外す</a></p>
<?php echo form_hidden('sz_ticket', $ticket);?>
<?php echo form_submit(array('value' => 'チェックしたものをインストール'));?>
<?php echo form_close();?>
<?php else:?>
<p class="info">追加可能なページが見つかりませんでした。</p>
<?php endif;?>