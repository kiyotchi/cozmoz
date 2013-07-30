<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>Seezoo&nbsp;管理パネル</h2>
                <?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo form_prep($this->msg);?></div>
                <?php endif;?>
                <div id="main">
                <h3>管理ユーザー検索結果</h3>
                <p>ユーザー名:「&nbsp;<?php echo form_prep($username_q);?>&nbsp;」、メールアドレス:「&nbsp;<?php echo form_prep($email_q);?>&nbsp;」で検索した結果</p>
                <p class="pagination"><?php echo $pagination;?></p>
                <p class="total"><?php echo $total;?><a href="<?php echo page_link();?>dashboard/users/user_list/index"><?php echo set_image('back.png', TRUE);?>管理ユーザー一覧に戻る</a></p>
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
                  <?php foreach ($search_result as $key => $val):?>
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
