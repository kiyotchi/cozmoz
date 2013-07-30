<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>ファイル管理</h2>
                <div id="main">
                	<?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo $this->msg;?></div>
                	<?php endif;?>
					<h3>ファイル管理（ディレクトリビュー）</h3>
					<div class="import_file" id="sz_dir_control">
						<iframe src="<?php echo page_link();?>dashboard/files/directory_view/upload_init" id="async_upload" style="height:35px;overflow:hidden;margin-bottom : 10px;width:450px" frameborder="0" scrolling="no"></iframe>
						<p class="multiple"><a href="<?php echo page_link();?>dashboard/files/directory_view/multiple_upload" class="sz_multi_upload">複数ファイルのアップロード</a></p>
						<div id="sz_file_dir_trash" class="sz_sort">
							&nbsp;
						</div>
						<a id="sz_file_archive" href="<?php echo page_link();?>dashboard/files/directory_view/multiple_download/">&nbsp;</a>
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
				</div>

                <!-- // #main -->

                <div class="clear"></div>
            </div>
            <!-- // #container -->
        </div>
        <!-- // #containerHolder -->

        <p id="footer"></p>
    </div>
    <!-- // #wrapper -->
</body>
</html>
