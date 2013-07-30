<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>管理ユーザー設定</h2>
                <div id="main">
					<h3>登録内容確認</h3>
					<h4>以下の内容で登録します。</h4>
					<table cellpadding="0" cellspacing="0" style="margin-top:10px;">
						<tr>
							<td>ユーザー名</td>
							<td class="action"><?php echo set_value('user_name');?></td>
						</tr>
						<tr class="odd">
							<td>メールアドレス</td>
							<td class="action"><?php echo set_value('email');?></td>
						</tr>
						<tr>
							<td>パスワード</td>
							<td class="action"><?php echo preg_replace('/./', '*', set_value('password'));?></td>
						</tr>
						<tr class="odd">
							<td>管理者権限</td>	
							<td class="action"><?php if ((int)set_value('admin_flag') == 1) { echo '付与する';} else { echo '付与しない';}?></td>
						</tr>
					</table>
					<div class="submit_box">
					<?php echo form_open('dashboard/users/add', array('class' => 'inline'));?>
					<?php foreach ($hidden as $key => $val):?>
					<?php echo form_hidden($key, $val);?>
					<?php endforeach;?>
                    <?php echo form_hidden('ticket', $ticket);?>
                    <?php echo form_submit(array('value' => '入力画面に戻る', 'name' => 'modify'));?>
                    <?php echo form_close();?>&nbsp;&nbsp;
					<?php echo form_open('dashboard/users/regist', array('class' => 'inline'));?>
					<?php foreach ($hidden as $key => $val):?>
					<?php echo form_hidden($key, $val);?>
					<?php endforeach;?>
                    <?php echo form_hidden('ticket', $ticket);?>
                    <?php echo form_submit(array('value' => '登録する'));?>
                    <?php echo form_close();?>
                    </div>
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
