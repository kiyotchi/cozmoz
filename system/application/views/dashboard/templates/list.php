<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>テンプレート管理</h2>
                <div id="main">
                	<?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo $this->msg;?></div>
                	<?php endif;?>
					<div class="section mt10">
						<h4>インストール済みのテンプレート</h4>
						<?php if (count($installed_list) > 0):?>
						<?php foreach ($installed_list as $val):?>
						<div class="templates clearfix">
							<?php if ($val['image']):?>
							<img src="<?php echo file_link() . $this->template_dir . $val['template_handle'] . '/image.jpg';?>" width="96" height="96" alt="" class="img_frame" />
							<?php else:?>
							<img src="<?php echo file_link() . 'images/no_image.gif';?>" width="100" height="100" alt="" />
							<?php endif;?>
							<dl>
								<dt><?php echo form_prep($val['template_name']);?></dt>
								<dd>
									<?php echo nl2br(form_prep($val['description']));?>
								</dd>
							</dl>
							<p class="template_config">
								<a href="<?php echo page_link()?>dashboard/templates/preview/<?php echo $val['template_handle'] . '/' . $ticket?>" class="preview sz_zoom">プレビュー</a>
								<a href="<?php echo page_link()?>dashboard/templates/reload/<?php echo $val['template_id'] . '/' . $ticket?>">再読み込み</a>
								<a href="<?php echo page_link()?>dashboard/templates/set_default/<?php echo $val['template_id'] . '/' . $ticket?>" class="set_default">デフォルトテンプレートに設定</a>
								<a href="<?php echo page_link()?>dashboard/templates/custom_css/<?php echo $val['template_id'] . '/' . $ticket?>" class="custom_css sz_zoom_css">CSSのカスタマイズ</a>
								<a href="<?php echo page_link()?>dashboard/templates/uninstall/<?php echo $val['template_id'] . '/' . $ticket?>" class="uninstall">アンインストール</a>
							</p>
							<?php if ($val['template_id'] == $dtid):?>
							<p class="use_default">デフォルトで使用中</p>
							<?php endif;?>
						</div>
						<?php endforeach;?>
						<?php else:?>
						<p class="none">インストール済みのテンプレートはありません。</p>
						<?php endif;?>
					</div>
					<div class="enable_templates section">
						<h4>インストール可能なテンプレート</h4>
						<?php if (count($list) > 0):?>
						<?php foreach ($list as $v):?>
						<div class="templates clearfix">
							<?php if ($v['image']):?>
							<img src="<?php echo file_link() . 'templates/' . $v['handle'] . '/image.jpg';?>" width="100" height="100" alt="" />
							<?php else:?>
							<img src="<?php echo file_link() . 'images/no_image.gif';?>" width="100" height="100" alt="" />
							<?php endif;?>
							<dl>
								<dt><?php echo form_prep($v['name']);?></dt>
								<dd><?php echo nl2br(form_prep($v['description']))?></dd>
							</dl>
							<p class="template_config">
								<?php echo anchor('dashboard/templates/install/' . $v['handle'] . '/' . $ticket, 'インストール');?>
							</p>
						</div>
						<?php endforeach;?>
						<?php else:?>
						<p class="none">インストール可能なテンプレートはありません。</p>
						<?php endif;?>
						<!-- <p class="right"><?php echo anchor('dashboard/templates/get_template', '配布テンプレートから探す', 'id="get_template"');?></p> -->
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
