<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
          <!-- h2 stays for breadcrumbs -->
          <h2>ファイル管理</h2>
          <div id="main">
            <?php if (!empty($this->msg)):?>
              <div class="message"><?php echo $this->msg;?></div>
            <?php endif;?>
          <h3>ファイルグループ管理</h3>
          <form></form>
          <p class="total_files" style="margin-top : 5px;"><?php echo $total;?></p>
          <p class="pagination"><?php echo $pagination;?></p>
          <p class="additional"><a href="javascript:void(0)" id="add_category"><?php echo set_image('plus.png', TRUE);?>&nbsp;グループの追加</a></p>
          <div class="category_wrapper">
            <ul class="sz_blog_category_list">
              <li class="ttl">グループ名<p>操作</p></li>
              <?php $times = 0;?>
              <?php foreach ($file_groups as $value):?>
              <li<?php if ($times % 2 === 1) echo ' class="odd"';?>>
                <span><?php echo form_prep($value->group_name);?></span>
                <?php echo form_open('dashboard/files/file_groups/ajax_update_file_group_edit', array('class' => 'init_hide'));?>
                <?php echo form_input(array('name' => 'group_name', 'value' => $value->group_name));?>
                <?php echo form_hidden('file_groups_id', $value->file_groups_id);?>
                <input type="button" class="category_edit_button" value="変更" />
                <?php echo form_close();?>
                <span class="state_ing">変更中...<img src="<?php echo file_link();?>images/loading_small.gif" /></span>
                <span class="state_do">変更しました。</span>

                <p>
                  <a href="javascript:void(0)" class="toggle_edit" rel="1">編集</a>&nbsp;
                  <a href="<?php echo page_link();?>dashboard/files/file_groups/ajax_delete_file_group/<?php echo $value->file_groups_id?>" class="li_del">削除</a>
                </p>
              </li>
              <?php $times++;?>
              <?php endforeach;?>
            </ul>
            <div id="additional_body">
              <span id="info">追加したいグループを登録してください。</span>
              <p><label>グループ</label>&nbsp;<?php echo form_input(array('name' => 'group_name_master'));?></p>
              <p><input type="button" id="add_cat" value="グループ追加" /></p>
            </div>
          </div>
          <p class="pagination"><?php echo $pagination;?></p>
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
