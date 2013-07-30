<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>ページ管理</h2>
                <div id="main">
					<h3>静的ページ管理</h3>
					<p class="additional">システムに依存しないページを管理します。</p>
					<p class="notify">
						重複しているページがある場合、静的ページのファイル名を変更するか、一般ページのページパスを変更してください。
					</p>
					<div id="sitemap_statics">
						<?php if (count($static_pages) > 0):?>
						<?php echo build_static_page_tree($static_pages);?>
						<?php else:?>
						<p>出力可能な静的ページはありません。</p>
						<?php endif;?>
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
