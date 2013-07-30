<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>管理ユーザー設定</h2>
                <div id="main">
                 <?php if (isset($user) || $this->input->post('uid')):?>
                 <h3>ユーザー編集</h3>
                 <?php else:?>
                 <h3>ユーザー追加</h3>
                 <?php endif;?>
                 <?php echo form_open('dashboard/users/edit_user/confirm/' . $uid, array('class' => 'jNice', 'id' => 'setting_form'))?>
                   <fieldset>
                          <p>
                            <label>ユーザー名<?php if ($is_validated === TRUE) echo $this->form_validation->error('user_name');?></label>
                            <?php if ($is_validated === TRUE):?>
                            <?php echo form_input(array('name' => 'user_name', 'value' => set_value('user_name'), 'class' => 'text-long'));?>
                            <?php else:?>
                            <?php echo form_input(array('name' => 'user_name', 'value' => (isset($user)) ? $user->user_name : '', 'class' => 'text-long'));?>
                            <?php endif;?>
                          </p>
                          <p>
                            <label>メールアドレス<?php if ($is_validated === TRUE) echo $this->form_validation->error('email');?></label>
                            <?php if ($is_validated === TRUE):?>
                            <?php echo form_input(array('name' => 'email', 'value' => set_value('email'), 'class' => 'text-long imedis'));?>
                            <?php else:?>
                            <?php echo form_input(array('name' => 'email', 'value' => (isset($user)) ? $user->email : '', 'class' => 'text-long imedis'));?>
                            <?php endif;?>
                          </p>
                          <p>
                            <label>パスワード<?php if ($is_validated === TRUE) echo $this->form_validation->error('password');?></label>
                            <?php echo form_password(array('name' => 'password', 'value' => '', 'class' => 'text-long'));?>
                          </p>
                          <p style="color:#c00"><?php if(isset($user)):?>※パスワードを変更しない場合は空欄にしてください。<?php endif;?></p>
                          <?php if ($this->is_master):?>
	                          <p>
	                                管理者権限<?php if ($is_validated === TRUE) echo $this->form_validation->error('admin_flag');?>
	                            <?php if ($uid == 1 && $this->is_master):?>
	                            <span style="display:block;margin-top:10px">マスターユーザー</span>
	                            <?php echo form_hidden('admin_flag', 1);?>
	                            <?php elseif ($this->input->post('admin_flag') !== FALSE):?>
	                            <label><input type="radio" name="admin_flag" value="0"<?php if ((int)$this->input->post('admin_flag') === 0) { echo ' checked="checked"';}?> />&nbsp;付与しない</label>
	                            <label><input type="radio" name="admin_flag" value="1"<?php if ((int)$this->input->post('admin_flag') === 1) { echo ' checked="checked"';}?> />&nbsp;付与する</label>
	                            <?php else:?>
	                            <label><input type="radio" name="admin_flag" value="0"<?php if (isset($user) && $user->admin_flag == 0) { echo ' checked="checked"';}?> />&nbsp;付与しない</label>
	                            <label><input type="radio" name="admin_flag" value="1"<?php if (isset($user) && $user->admin_flag == 1) { echo ' checked="checked"';}?> />&nbsp;付与する</label>
	                            <?php endif;?>
	                          </p>
	                       <?php endif;?>
                          <?php echo form_hidden('ticket', $ticket);?>
                          <?php echo form_hidden('uid', $uid);?>
                          <?php echo form_submit(array('value' => '確認画面へ'));?>
                      </fieldset>
                        <?php echo form_close();?>
                        <br />
                        <br />
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
