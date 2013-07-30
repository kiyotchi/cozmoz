<div id="login_box">
  <h2>パスワード再発行</h2>
  <p class="login_info">新しく設定するパスワードを入力してください。</p>
  
  <?php if (!empty($this->msg)):?>
  <p class="login_error"><?php echo $this->msg;?></p>
  <?php endif;?>
  
  <?php echo form_open('member_login/do_reset_password/' . $activation_code, array('onsubmit' => "return confirm('送信します。よろしいですか？');"));?>
    <fieldset>
      <table class="sz_member_login_table">
        <tbody>
          <tr>
            <th><label for="new_password">新しいパスワード</label></th>
            <td>
              <?php echo form_password(array('name' => 'new_password', 'id' => 'new_password', 'value' => set_value('new_passowrd'), 'class' => 'imedis'));?>
              <?php echo $this->form_validation->error('new_password');?>
            </td>
          </tr>
          <tr>
            <th><label for="new_password_confirm">パスワード（確認）</label></th>
            <td>
              <?php echo form_password(array('name' => 'new_password_confirm', 'id' => 'new_password_confirm'));?>
              <?php echo $this->form_validation->error('new_password_confirm');?>
            </td>
          </tr>
        </tbody>
      </table>

      <p class="submission">
        <?php echo form_hidden($this->ticket_name, $this->ticket);?>
        <?php echo form_submit(array('value' => '登録'));?>
      </p>
    </fieldset>
  <?php echo form_close();?>

</div>