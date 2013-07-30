<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
					<!-- h2 stays for breadcrumbs -->
					<h2>Seezoo&nbsp;管理パネル</h2>
					<div id="main">
					<?php if (!empty($this->msg)):?>
						<div class="message"><?php echo $this->msg;?></div>
					<?php endif;?>
					<h3>プラグイン一覧</h3>
					<p class="info">
						システムにインストールされているプラグインの一覧です。
					</p>
					<table>
						<tbody>
							<tr class="caption">
								<th>プラグイン名</th>
								<th>概要</th>
								<th>&nbsp;</th>
								<th class="action">操作</th>
							</tr>
							<?php if (count($installed_list) > 0):?>
							<?php $times = 0;?>
							<?php foreach ($installed_list as $key => $value):?>
							<tr<?php if ($times % 2 === 0) echo ' class="odd"';?>>
								<td><?php echo prep_str($value->plugin_name);?></td>
								<td style="width:250px;">
									<?php echo nl2br(prep_str($value->description));?>
									<div style="display:none"></div>
								</td>
								<td style="width:100px;">
									<a href="<?php echo page_link();?>dashboard/plugins/plugin_list/get_plugin_detail/<?php echo prep_str($value->plugin_handle);?>" class="plugin_detail">
									<?php echo set_image('plus.png', TRUE);?>&nbsp;プラグイン詳細
									</a>
								</td>
								<td class="action">
									<a href="<?php echo page_link();?>dashboard/plugins/plugin_list/delete/<?php echo $value->plugin_handle;?>" class="delete">アンインストール</a>
								</td>
							</tr>
							<?php $times++;?>
							<?php endforeach;?>
							<?php else:?>
							<tr><td colspan="4">インストールされているプラグインはありません。</td></tr>
							<?php endif;?>
						</tbody>
					</table>
					<h3>インストール可能なプラグイン</h3>
					<p class="info">インストール可能なプラグインのリストです。</p>
					<div id="scaned_plugin_list">
						<?php echo $no_list;?>
					</div>
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
