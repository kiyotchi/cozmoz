<div id="login_box">
  <h2><?php echo SITE_TITLE;?>にログイン</h2>
  <p class="login_info">登録時のアカウント情報を入力してください。<br />
    <span><?php echo anchor('registration', '新規会員登録はこちらから');?></span>
  </p>
  
  <?php if (!empty($this->msg)):?>
  <p class="login_error"><?php echo $this->msg;?></p>
  <?php endif;?>
  
  <?php echo form_open('member_login/do_member_login', array('id' => 'member_login_form'));?>
    <fieldset>
      <table class="sz_member_login_table">
        <tbody>
          <tr>
            <th><label for="member_name">ニックネーム<br />または<br />メールアドレス</label></th>
            <td>
              <?php echo form_input(array('name' => 'member_name', 'id' => 'member_name', 'value' => set_value('member_name'), 'class' => 'imedis'));?>
              <?php echo $this->form_validation->error('member_name');?>
            </td>
          </tr>
          <tr>
            <th><label for="password">パスワード</label></th>
            <td>
              <?php echo form_password(array('name' => 'password', 'id' => 'password'));?>
              <?php echo $this->form_validation->error('password');?>
            </td>
          </tr>
        </tbody>
      </table>

      <p class="submission">
        <?php echo form_hidden($this->ticket_name, $this->ticket);?>
        <?php echo form_submit(array('value' => 'ログイン'));?>
      </p>
      <?php if ( $enable_twitter_login === TRUE ):?>
      <p class="right">
        <?php echo anchor('member_login/google_login', set_image('gadget/icons/twitter.png', TRUE) .'&nbsp;twitterアカウントでログイン', 'class="popup_link"');?>
      </p>
      <?php endif;?>
      <?php if ( $enable_facebook_login === TRUE ):?>
      <p class="right">
        <?php echo anchor('member_login/facebook_login', set_image('gadget/icons/facebook.png', TRUE) .'&nbsp;facebookアカウントでログイン', 'class="popup_link"');?>
      </p>
      <?php endif;?>
      <?php if ( $enable_google_login === TRUE ):?>
      <p class="right">
        <?php echo anchor('member_login/google_login', set_image('gadget/icons/google.png', TRUE) .'&nbsp;googleアカウントでログイン', 'class="popup_link"');?>
      </p>
      <?php endif;?>
    </fieldset>
  <?php echo form_close();?>
  
  <?php if ( isset($complete_msg) && $complete_msg !== FALSE ):?>
  <div class="msg" id="msg_notify"><?php echo $complete_msg;?></div>
  <?php endif;?>
  
  <p>
    <a href="#forgotten" id="pass_forgot">
      <?php echo set_image('plus.png', TRUE);?>&nbsp;パスワードを忘れた方はこちら
    </a>
  </p>
  
  <div id="forgotten"<?php if (!isset($forgotten_error)) echo 'style="display:none"';?>>
    <?php echo form_open('member_login/forgotten_password');?>
    <p>
      パスワードを忘れた方は登録時のメールアドレスを入力してください。<br />
      パスワードを再発行致します。
    </p>
    <label>
      <?php echo form_input(array('name' => 'forgotten_email', 'class' => 'imedis'));?>
    </label>
    <?php echo $this->form_validation->error('forgotten_email');?>
    <p class="submission">
      <?php echo form_hidden($this->ticket_name, $this->ticket);?>
      <?php echo form_submit(array('value' => 'パスワードの再発行'));?>
    </p>
  </div>

</div>