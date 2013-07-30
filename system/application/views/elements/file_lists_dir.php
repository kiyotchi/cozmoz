<div class="file_wrapper clearfix">
<div class="sz_api_file_search">
	<div>
		<?php echo form_open(get_base_link() . 'ajax_file_search/', array('id' => 'sz_file_search_form'));?>
		<p><label>ファイル名:<?php echo form_input(array('name' => 'file_name', 'value' => ''));?></label></p>
		<p><label>拡張子:<?php echo form_dropdown('file_ext', $ext_list, FALSE, 'id="search_file_ext"');?></label></p>
		<p>
			<label>
				グループ:
				<select name="file_group">
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
<div id="tree_mode">
	<div class="import_file" id="sz_dir_control">
		<iframe src="<?php echo get_base_link();?>dashboard/files/directory_view/upload_init/<?php echo $force_token;?>" id="async_upload" style="height:35px;overflow:hidden;margin-bottom : 10px;width:450px" frameborder="0" scrolling="no"></iframe>
		<p class="multiple"><a href="<?php echo get_base_link();?>dashboard/files/directory_view/multiple_upload" class="sz_multi_upload">複数ファイルのアップロード</a></p>
		<div id="sz_file_dir_trash" class="sz_sort" title="ゴミ箱">
			&nbsp;
		</div>
		<a id="sz_file_archive" href="<?php echo get_base_link();?>dashboard/files/directory_view/multiple_download/">&nbsp;</a>
		<!--  hidden form -->
		<?php echo form_open('dashboard/files/directory_view/multiple_download', array('id' => 'sz_file_multiple_download'));?>
		<input type="hidden" id="archive_files" name="archive_files" value="" />
		<input type="hidden" id="archive_directories" name="archive_directories" value="" />
		<?php echo form_hidden('sz_file_token', $ticket);?>
		<?php echo form_close();?>
		<!--  hidden form end -->
	</div>
	<div class="trees">
		<ul id="sz_dir_tree_path" class="clearfix">
			<?php foreach ($tree as $key => $v):?>
			<li>
				<div class="sort_tree" dir_id="<?php echo $key;?>">
					<a href="javascript:void(0)" class="current"><?php echo prep_str($v);?></a>
				</div>
			</li>
			<?php endforeach;?>
		</ul>
		<div id="add_dir_form" class="init_hide">
			<form>
				<p>追加するディレクトリ名を入力してください。</p>
				<?php echo form_input(array('name' => 'dir_name', 'value' => ''));?><br />
				<input type="button" id="add_dir_do" value="ディレクトリ追加" />
			</form>
		</div>
	</div>
	<p id="add_dir_wrapper">
		<a href="javascript:void(0)" id="add_dir"><?php echo set_image('plus.png', TRUE);?>&nbsp;ディレクトリを追加</a>
	</p>
	<div id="sz_file_dir_view_wrapper">
		<ul id="sz_file_dir_view" class="clearfix">
			<?php echo $this->load->view('parts/files/dirs', array('dirs' => $dirs, 'files' => $files));?>
		</ul>
	</div>
	<div id="sz_file_drop_area" draggable="on">ここにドロップするとアップロードできます</div>
	<?php echo form_hidden('sz_file_token', $ticket);?>
	<?php echo form_close();?>
</div>
<div id="search_mode" class="init_hide">
	<h4 class="sz_file_search_result_caption">検索結果</h4>
	<div id="search_result_view" class="clearfix"></div>
	<a href="javascript:void(0)" id="toggle_view">&laquo;ツリー表示に戻る</a>
</div>
</div>
</div>
