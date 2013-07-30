<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->

<!-- h2 stays for breadcrumbs -->
<h2>Seezoo&nbsp;管理パネル</h2>

<div id="main">
  <?php if (isset($member) || $this->input->post('mid')):?>
  <h3>メンバー情報編集</h3>
  <?php else:?>
  <h3>新規メンバー登録</h3>
  <p style="color:#c00;margin:8px;">管理画面からのメンバー登録はメールアドレスによるアクティベーションを行ないません。</p>
  <?php endif;?>
  
  <?php echo form_open('dashboard/members/edit_member/confirm', array('class' => 'jNice', 'id' => 'setting_form'))?>
    <fieldset>
      <p>
        <label>ニックネーム<?php if ($is_validated === TRUE) echo $this->form_validation->error('nick_name');?></label>
        <?php if ($is_validated === TRUE):?>
        <?php echo form_input(array('name' => 'nick_name', 'value' => set_value('nick_name'), 'class' => 'text-long'));?>
        <?php else:?>
        <?php echo form_input(array('name' => 'nick_name', 'value' => (isset($member)) ? $member->nick_name : '', 'class' => 'text-long'));?>
        <?php endif;?>
      </p>
      <p>
        <label>メールアドレス<?php if ($is_validated === TRUE) echo $this->form_validation->error('email');?></label>
        <?php if ($is_validated === TRUE):?>
        <?php echo form_input(array('name' => 'email', 'value' => set_value('email'), 'class' => 'text-long imedis'));?>
        <?php else:?>
        <?php echo form_input(array('name' => 'email', 'value' => (isset($member)) ? $member->email : '', 'class' => 'text-long imedis'));?>
        <?php endif;?>
      </p>
      <p>
        <label>パスワード<?php if ($is_validated === TRUE) echo $this->form_validation->error('password');?></label>
        <?php echo form_password(array('name' => 'password', 'value' => '', 'class' => 'text-long'));?>
      </p>
      <p style="color:#c00"><?php if(isset($member)):?>※パスワードを変更しない場合は空欄にしてください。<?php endif;?></p>
      <?php echo form_hidden('ticket', $ticket);?>
      <?php echo form_hidden('mid', $mid);?>
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
