<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>Seezoo&nbsp;管理パネル</h2>
                <div id="main">
                	<?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo $this->msg;?></div>
                	<?php endif;?>
					<h3>ファイル管理</h3>
					<div class="import_file">
						<iframe src="<?php echo page_link();?>dashboard/files/file_list/upload_init" id="async_upload" style="height:35px;overflow:hidden;margin-bottom : 10px;width:450px" frameborder="0" scrolling="no"></iframe>
						<p class="multiple"><a href="<?php echo page_link();?>dashboard/files/file_list/multiple_upload" class="sz_multi_upload">複数ファイルのアップロード</a></p>
					</div>
					<p class="total_files"><?php echo $total;?><span id="total_nums" style="display:none"><?php echo $total_num;?></span></p>
					<p class="pagination"></p>
					<form></form>
					<?php echo form_open('dashboard/files/file_list/edit', array('id' => 'file_edit_form'));?>
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
					<p><?php echo form_hidden('sz_file_token', $ticket);?></p>
					<?php echo form_close();?>
					<p class="pagination"></p>
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
