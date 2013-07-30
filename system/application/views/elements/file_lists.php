<div class="file_wrapper clearfix">
<div class="sz_api_file_search">
	<div>
		<?php echo form_open(get_base_link() . 'ajax_file_search/', array('id' => 'sz_file_search_form'));?>
		<p><label>ファイル名:<?php echo form_input(array('name' => 'file_name', 'value' => ''));?></label></p>
		<p><label>拡張子:<?php echo form_dropdown('file_ext', $ext_list, FALSE, 'id="search_file_ext"');?></label></p>
		<p>
			<label>
				グループ:<select name="file_group">
											<option value="0">---</option>
											<?php foreach ($group_list as $key => $value):?>
											<option value="<?php echo $key;?>"><?php echo form_prep($value);?></option>
											<?php endforeach;?>
											</select>
			</label>
		</p>
		<p class="do_search"><input type="button" value="検索" id="sz_file_api_search_do" /><span id="uploading">&nbsp;</span></p>
		<?php echo form_close();?>
	</div>
</div>
<div class="sz_api_file_data">
<div class="import_file_api">
		<iframe src="<?php echo get_base_link();?>dashboard/files/file_list/upload_init" id="async_upload" style="height:35px;overflow:hidden;margin-bottom : 10px;width:400px;" frameborder="0" scrolling="no"></iframe>
		<p class="multiple"><a href="<?php echo get_base_link();?>dashboard/files/file_list/multiple_upload" class="sz_multi_upload">複数ファイルのアップロード</a></p>
</div>
<div class="sz_api_files_info_wrapper">
<p class="total_files"><span id="total_nums" style="display:none"><?php echo $file_count;?></span></p>
<p id="sz_api_file_pagination">&nbsp;</p>
</div>
<?php echo form_open('dashboard/files/file_list/edit', array('id' => 'sz_file_api_form'));?>
<table class="sz_flt" id="file_table">
	<tbody>
		<tr>
		<th class="cell1"><input type="checkbox" id="check_all" /></th>
		<th class="cell2">
			<select id="edit_method" disabled="disabled" name="method">
				<option value="0">------</option>
				<option value="dl">ダウンロード</option>
				<option value="group">グループ</option>
				<!--<option value="rescan">再スキャン</option>-->
			<option value="delete">削除</option>
			</select>
		</th>
		<th class="cell3">拡張子</th>
		<th class="fname cell4">ファイル名</th>
		<th class="cell5">追加日時<a href="javascript:void(0)" id="sort_mode">▼</a></th>
		<th class="cell6">ファイルサイズ</th>
		</tr>
	</tbody>
</table>
<div id="table_wrapper">
	<?php echo $this->load->view('parts/file_table');?>
</div>
<?php echo form_hidden('sz_file_token', $ticket);?>
<?php echo form_close();?>
</div>
</div>
