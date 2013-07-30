<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->

<!-- h2 stays for breadcrumbs -->
<h2>メンバー管理</h2>
<div id="main">
  
  <?php if (!empty($this->msg)):?>
  <div class="message"><?php echo form_prep($this->msg);?></div>
  <?php endif;?>
  
  <h3>メンバー項目の登録</h3>
    
  <div class="sz_member_att_edit">
    <?php echo form_open('dashboard/members/attributes/do_edit', array('id' => 'att_form'));?>
      <table cellspacing="0" cellpadding="0" id="att_form_table">
        <tbody>
          <tr>
            <th>ユーザー更新設定：</th>
            <td><label><?php echo form_checkbox('is_inputable', 1, TRUE);?>&nbsp;ユーザーが入力/変更できる項目にする</label></td>
          </tr>
          <tr>
            <th><label for="attribute_name">項目名：</label></th>
            <td><?php echo form_input(array('name' => 'attribute_name', 'id' => 'attribute_name', 'value' => '', 'style' => 'width:80%'));?></td>
          </tr>
          <tr>
            <th><label for="attribute_type">入力タイプ：</label></th>
            <td><?php echo form_dropdown('attribute_type', $attribute_types, FALSE);?></td>
          </tr>
          <tr class="init_hide">
            <th>&nbsp;</th>
            <td>
              <label>行数：<?php echo form_input(array('name' => 'rows', 'id' => 'rows', 'value' => '6'));?></label>
              <label>列数：<?php echo form_input(array('name' => 'cols', 'id' => 'cols', 'value' => '20'));?></label>
            </td>
          </tr>
          <tr class="init_hide">
            <th><label for="options">項目オプション：</label></th>
            <td id="option_base">
              <p>
                <a href="javascript:void(0)" id="add_options">
                  <?php echo set_image('plus.png', TRUE);?>&nbsp;オプションを追加
                </a>
              </p>
              <p class="opts">
                  オプション名：<?php echo form_input(array('name' => 'options[]', 'id' => 'options'));?>
                <a href="javascript:void(0)" class="att_option">
                  <?php echo set_image('delete.png', TRUE);?>&nbsp;削除
                </a>
              </p>
            </td>
          </tr>
          <tr>
            <th>入力値の検証ルール:</th>
            <td>
              <label><?php echo form_checkbox('required', 1);?>&nbsp;必須入力にする</label><br />
              <label><?php echo form_checkbox('integer', 1);?>&nbsp;数値のみ許可する</label><br />
              <label><?php echo form_checkbox('valid_url', 1);?>&nbsp;URL形式をチェックする</label><br />
              <label><?php echo form_input(array('name' => 'max_length', 'value' => '', 'size' => 3));?>&nbsp;文字以内に制限する</label><br />
              <label><?php echo form_input(array('name' => 'min_length', 'value' => '', 'size' => 3));?>&nbsp;文字以上に制限する</label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <br />
    <p class="submission">
      <?php echo form_hidden('ticket', $ticket);?>
      <?php echo form_hidden('att_id', $att_id);?>
      <?php echo form_submit(array('value' => '登録する'));?>
    </p>
  <?php echo form_close();?>
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
