<?php echo $this->load->view('dashboard/dashboard_header', array('sidebar' => FALSE));?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>画像の編集</h2>
                <div id="main">
                  <p class="attribute">
                      画像の簡単な編集が行えます。
                   <a href="<?php echo page_link()?>dashboard/files" id="back_to_file">ファイルマネージャに戻る</a>
                  </p>
                  
                  <?php if(isset($file)):?>
                  <h3><?php echo $file->file_name . '.' . $file->extension;?>（横：<?php echo $file->width?>px&nbsp;x&nbsp;縦：<?php echo $file->height?>px）</h3>
                  <?php else:?>
                  <h3>&nbsp;</h3>
                  <?php endif;?>
                  <div>
                  <?php if (isset($file)):?>
                      <img src="<?php echo file_link() . make_file_path($file);?>" width="<?php echo $file->width;?>" height="<?php echo $file->height;?>" id="sz_image_target"/>
                  <?php else:?>
                      <p class="edit_image_select"><a href="javascript:void(0)" id="select_edit_image_target">編集対象の画像ファイルを選択してください。</a></p>
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
