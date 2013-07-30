<div class="sz_page_versions">
	<h3>バージョン情報</h3>
	<div class="sz_pv_infos">
		<p>公開・非公開の切り替えを行います。<img src="<?php echo file_link();?>images/check.gif" style="display:inline" />のついたバージョンが公開バージョンです。</p>
	</div>
	<div class="sz_pv_operate">
		<p>
			<!-- 
			<input type="button" value="比較する" id="make_diff" disabled="disabled" />
			 -->
			<?php if ($is_approve === TRUE):?>
			<input type="button" value="公開バージョンとして承認する" id="do_public" disabled="disabled" />
			<?php endif;?>
			<input type="button" value="バージョンを削除" id="delete_version" disabled="disabled" />
		</p>
		<div class="sz_page_version_list_wrapper">
		<table class="sz_page_version_list">
			<tbody>
				<tr class="pv_head">
					<th>バージョン番号</th>
					<th>バージョンのコメント</th>
					<th>作成者</th>
					<th>承認者</th>
					<th>作成日時</th>
				</tr>
				<?php foreach ($versions as $key => $value):?>
				<?php if ($key % 2 === 0):?>
				<tr class="odd">
				<?php else:?>
				<tr>
				<?php endif;?>
					<td<?php if ((int)$value->is_public === 1) { echo ' class="approved"';}?>><?php echo $value->version_number;?></td>
					<td><a href="<?php echo get_base_link();?>page/preview/<?php echo $value->version_number;?>" class="sz_version_preview" rel="<?php echo $value->version_number;?>"><?php echo $value->version_comment;?></a></td>
					<td><?php if ((int)$value->created_user_id > 0) { echo $users[$value->created_user_id];}?></td>
					<td><?php if ((int)$value->approved_user_id > 0) { echo $users[$value->approved_user_id];}?></td>
					<td><?php echo $value->version_date;?></td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		</div>
		<br />
	</div>
</div>