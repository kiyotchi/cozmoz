<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>ブロック管理</h2>
                <div id="main">
                	<?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo $this->msg;?></div>
                	<?php endif;?>
					<div class="clearfix mt10">
						<div class="installed_list b_list">
							<h4>使用可能なブロックのリスト</h4>
							<?php if (count($installed_list) > 0):?>
							<ul>
								<?php foreach ($installed_list as $value):?>
								<li>
									<a href="<?php echo page_link();?>dashboard/blocks/block_list/detail/<?php echo $value->collection_id;?>">
										<?php echo $value->block_name;?>
									</a>
<!--									<a href="<?php echo page_link();?>dashboard/blocks/delete/<?php echo $value->collection_id;?>/<?php echo $ticket;?>" class="block_delete">-->
<!--										削除-->
<!--									</a>-->
									<p class="alw_hide"><?php echo nl2br(form_prep($value->description));?></p>
								</li>
								<?php endforeach;?>
							</ul>
							<?php else:?>
							<p class="none">使用可能なブロックはありません。</p>
							<?php endif;?>
						</div>
						<div class="enable_modules b_list">
							<h4>インストール可能なブロックのリスト</h4>
							<?php if (count($list) > 0):?>
							<ul>
								<?php foreach ($list as $v):?>
								<li><a href="<?php echo page_link();?>dashboard/blocks/block_list/install/<?php echo $v['collection_name']?>/<?php echo $ticket;?>"><?php echo $v['block_name'];?></a></li>
								<?php endforeach;?>
							</ul>
							<?php else:?>
							<p class="none">インストール可能なブロックはありません。</p>
							<?php endif;?>
						</div>
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
