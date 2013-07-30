<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>管理ユーザー設定</h2>
                <?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo form_prep($this->msg);?></div>
                <?php endif;?>
                <div id="main">
                  <h3>ユーザー一覧/検索</h3>
                  <?php if ( $is_master ):?>
                  <p class="total"><?php echo $total;?><?php echo anchor('dashboard/users/edit_user/', set_image('plus.png', TRUE) . 'ユーザーを追加');?></p>
                  <?php endif;?>
                  <?php if (!empty($pagination)):?>
                  <p class="pagination"><?php echo $pagination;?></p>
                  <?php endif;?>
                  <p class="search_user"><a href="javascript:void(0)" id="search_open"><?php echo set_image('search.png', TRUE);?>検索フォームを開く</a></p>
                  <div class="user_search_form">
                    <?php echo form_open('dashboard/users/user_list/search_init');?>
                    <p><label>ユーザー名:<?php echo form_input(array('name' => 'user_name', 'value' => '', 'class' => 'input_text'))?></label></p>
                    <p><label>メールアドレス:<?php echo form_input(array('name' => 'email', 'value' => '', 'class' => 'imedis input_text'))?></label></p>
                    <p>
                      <?php echo form_hidden('token', $search_token);?>
                      <?php echo form_submit(array('value' => '検索', 'name' => 'do_search'));?>
                    </p>
                    <?php echo form_close();?>
                  </div><br />
                  <p><?php echo set_image('lock.png', TRUE);?>&nbsp;アイコンのあるものはアカウントロックされています。</p>
                  <br />
                  <table cellpadding="0" cellspacing="0" class="admin_users_table">
                    <tr>
                      <td>&nbsp;</td>
                      <td>ユーザー名</td>
                      <td>メールアドレス</td>
                      <td>ログイン回数</td>
                      <td class="action">操作</td>
                    </tr>
                    <?php foreach ($users as $key => $val):?>
                    <tr<?php if ((int)$key % 2 === 0) { echo ' class="odd"';}?>>
                      <td>
                      <?php if ($val->login_miss_count >= 3):?>
                      <?php echo set_image('lock.png', TRUE);?>
                      <?php else:?>
                      &nbsp;
                      <?php endif;?>
                      </td>
                      <td><?php echo form_prep($val->user_name);?></td>
                      <td><?php echo $val->email;?></td>
                      <td class="times"><?php if ((int)$val->user_id > 1) echo $val->login_times; else echo '-'?></td>
                      <td class="action">
                        <?php if ((int)$val->user_id > 1 || $this->is_master):?>
                          <?php echo anchor('dashboard/users/user_list/detail/' . $val->user_id, '詳細', 'class="view sz_zoom"')?>
                          <?php if ($this->is_master):?>
                            <?php echo anchor('dashboard/users/edit_user/index/' . $val->user_id, '編集', 'class="edit"');?>
                            <?php if ($val->user_id > 1):?>
                              <?php echo anchor('dashboard/users/user_list/delete/' . $val->user_id, '削除', 'class="delete"');?>
                            <?php endif?>
                          <?php elseif ($this->user_id == $val->user_id):?>
                            <?php echo anchor('dashboard/users/edit_user/index/' . $val->user_id, '編集', 'class="edit"');?>
                          <?php endif;?>
                          <?php if ($this->is_master && $val->login_miss_count >= 3):?>
                            <?php echo anchor('dashboard/users/user_list/unlock_user/' . $val->user_id, 'ロック解除', 'class="unlock"');?>
                          <?php endif;?>
                        <?php else:?>
                        &nbsp;
                        <?php endif;?>
                      </td>
                    </tr>
                    <?php endforeach;?>
                  </table>
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
