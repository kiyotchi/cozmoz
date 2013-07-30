<table>
	<tbody>
		<tr class="odd">
			<td>
				ページタイトル<br />
				<?php if (isset($page) && !empty($page->page_title)):?>
				<?php echo form_input(array('name' => 'page_title', 'value' => $page->page_title))?>
				<?php else:?>
				<?php echo form_input(array('name' => 'page_title', 'value' => ''))?>
				<?php endif;?>
			</td>
			<td>
				ページパス(システムページのため変更不可)<br />
				<p class="no_editable">
				<?php echo trim(rawurldecode(parse_path_segment($page->page_path, TRUE)) . '/' .rawurldecode(parse_path_segment($page->page_path)));?>
				</p>
			</td>
		</tr>
		<tr>
			<td>
				メタタグタイトル<br />
				<?php if (isset($page) && !empty($page->meta_title)):?>
				<?php echo form_input(array('name' => 'meta_title', 'value' => $page->meta_title));?>
				<?php else:?>
				<?php echo form_input(array('name' => 'meta_title', 'value' => ''));?>
				<?php endif;?>
			</td>
			<td>
				公開日時<br />
				<?php if (isset($page)):?>
				<?php echo form_input(array('name' => 'public_ymd', 'value' => set_public_datetime('Y-m-d', $page->public_datetime), 'class' => 'imedis', 'size' => 12));?>
				<?php echo form_dropdown('public_time', hour_list(), set_public_datetime('H', $page->public_datetime));?>:
				<?php echo form_dropdown('public_minute', minute_list(), set_public_datetime('i', $page->public_datetime));?>
				<?php else:?>
				<?php echo form_input(array('name' => 'public_ymd','value' => date('Y-m-d', time()),'class' => 'imedis', 'size' => 12));?>
				<?php echo form_dropdown('public_time', hour_list(), date('H', time()));?>:
				<?php echo form_dropdown('public_minute', minute_list(), date('i', time()));?>
				<?php endif;?>
			</td>
				
		</tr>
		<tr class="odd">
			<td colspan="2">
				メタキーワード(複数設定する場合はカンマで区切ってください)<br />
				<?php if (isset($page) && !empty($page->meta_keyword)):?>
				<?php echo form_textarea(array('name' => 'meta_keyword', 'value' => $page->meta_keyword, 'cols' => 40, 'rows' => 2));?>
				<?php else:?>
				<?php echo form_textarea(array('name' => 'meta_keyword', 'value' => '', 'cols' => 40, 'rows' => 2));?>
				<?php endif;?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				概要ワード<br />
				<?php if (isset($page) && !empty($page->meta_description)):?>
				<?php echo form_textarea(array('name' => 'meta_description', 'value' => $page->meta_description, 'cols' => 40, 'rows' => 2));?>
				<?php else:?>
				<?php echo form_textarea(array('name' => 'meta_description', 'value' => '', 'cols' => 40, 'rows' => 2));?>
				<?php endif;?>
			</td>
		</tr>
	</tbody>
</table>