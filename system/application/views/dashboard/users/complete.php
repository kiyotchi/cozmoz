<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>Seezoo&nbsp;管理パネル</h2>
                
                <div id="main">
                  <h3>処理が完了しました。</h3>
                  <br />
                  <p class="center">
                    <?php echo anchor('dashboard/users/', 'ユーザー一覧へ戻る');?>
                  </p>
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
