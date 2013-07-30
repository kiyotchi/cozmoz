<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>ユーザーツール設定</h2>
                <div id="main">
                     <?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo $this->msg;?></div>
                	<?php endif;?>
					<h3>利用中のガジェット一覧</h3>
					<p class="additional"><a href="javascript:void(0)" id="add_gadget"><?php echo set_image('plus.png', TRUE);?>&nbsp;ガジェットの追加</a></p>
					<div id="gadget"></div>
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
