<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->

<h2>メンバー管理</h2>
<div id="main">

  <?php if ($mid > 0):?>
  <h3>ユーザー編集内容確認</h3>
  <?php else:?>
  <h3>新規ユーザー登録確認</h3>
  <?php endif;?>
  
  <h4>以下の内容で登録します。</h4>
  <table cellpadding="0" cellspacing="0" style="margin-top:10px;">
    <tr>
      <td>ニックネーム</td>
      <td class="action"><?php echo set_value('nick_name');?></td>
    </tr>
    <tr class="odd">
      <td>メールアドレス</td>
      <td class="action"><?php echo set_value('email');?></td>
    </tr>
    <tr>
      <td>パスワード</td>
      <?php if ((int)$this->input->post('mid') > 0 && $this->input->post('password') == ''):?>
      <td class="action">変更なし</td>
      <?php else:?>
      <td class="action"><?php echo preg_replace('/./', '*', set_value('password'));?></td>
      <?php endif;?>
    </tr>
  </table>
  
  <div class="submit_box">
    <?php echo form_open('dashboard/members/edit_member/index/' . $mid, array('class' => 'inline'));?>
    
    <?php foreach ($hidden as $key => $val):?>
    <?php echo form_hidden($key, $val);?>
    <?php endforeach;?>
    
    <?php echo form_hidden('ticket', $ticket);?>
    <?php echo form_hidden('mid', $mid);?>
    <?php echo form_submit(array('value' => '入力画面に戻る', 'name' => 'modify'));?>
    <?php echo form_close();?>&nbsp;&nbsp;
    
    <?php echo form_open('dashboard/members/edit_member/regist', array('class' => 'inline'));?>
    
    <?php foreach ($hidden as $key => $val):?>
    <?php echo form_hidden($key, $val);?>
    <?php endforeach;?>
    
    <?php echo form_hidden('ticket', $ticket);?>
    <?php echo form_hidden('mid', $mid);?>
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
