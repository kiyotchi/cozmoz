<table>
	<tbody>
		<tr class="odd">
			<td valign="top">
				ページタイトル<br />
				<?php if (isset($page) && !empty($page->page_title)):?>
				<?php echo form_input(array('name' => 'page_title', 'value' => $page->page_title, 'id' => 'sz_input_page_title'))?>
				<?php else:?>
				<?php echo form_input(array('name' => 'page_title', 'value' => '', 'id' => 'sz_input_page_title'))?>
				<?php endif;?>
			</td>
			<td>
				<?php if (isset($page) && (int)$page->is_system_page > 0):?>
					ページパス(システムページのため変更不可)<br />
					<p class="no_editable" style="margin:0px">
					<?php echo trim(rawurldecode(parse_path_segment($page->page_path, TRUE)) . '/' .rawurldecode(parse_path_segment($page->page_path)));?>
					<?php echo form_hidden('page_path', $page->page_path);?>
					</p>
				<?php else:?>
					ページパス(バージョン管理対象外)<br />
					<?php if (isset($page) && !empty($page->page_path)):?>
					<span>
						<?php if ($page->page_id > 1):?>
							<?php echo rawurldecode(parse_path_segment($page->page_path, TRUE)) . '/';?>
							<?php echo form_hidden('parent_page_path', rawurldecode(parse_path_segment($page->page_path, TRUE)) . '/');?>
						<?php else:?>
							<?php echo '/'?>
							<?php echo form_hidden('parent_page_path', '');?>
						<?php endif;?>
					</span>
						<?php echo form_input(array('name' => 'page_path', 'value' => rawurldecode(parse_path_segment($page->page_path)), 'id' => 'sz_input_page_path'))?>
					<?php else:?>
						<?php echo rawurldecode($parent_path) . '/';?>
						<?php echo form_hidden('parent_page_path', rawurldecode($parent_path) . '/');?>
						<?php echo form_input(array('name' => 'page_path', 'value' => '', 'id' => 'sz_input_page_path'))?>
					<?php endif;?>
					<p style="margin:0">
					<a href="javascript:void(0)" id="check_exists" rel="<?php echo (isset($page)) ? $page->page_id : 0;?>">ページパスが存在するかチェック</a>
					</p>
				<?php endif;?>
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
		<tr class="odd">
			<td>
				<label><input type="checkbox" name="navigation_show" value="1"<?php if (!isset($page) || (isset($page) && (int)$page->navigation_show === 1)){ echo ' checked="checked"';} ?> />&nbsp;ナビゲーションに表示させる</label><br />
			</td>
			<td>
				<label>
					<input type="checkbox" name="target_blank" value="1"<?php if (isset($page) && (int)$page->target_blank === 1){ echo ' checked="checked"';} ?> />&nbsp;別ウインドウ（タブ）でリンクを開く
				</label>
			</td>
		</tr>
		<?php if ( !empty($this->site_data->ssl_base_url) ):?>
		<tr>
			<td colspan="2">
				<label>
					<input type="checkbox" name="is_ssl_page" value="1"<?php if (isset($page) && (int)$page->is_ssl_page === 1){ echo ' checked="checked"';} ?> />&nbsp;このページをSSL通信にする
				</label>
			</td>
		</tr>
		<?php endif;?>
	</tbody>
</table>