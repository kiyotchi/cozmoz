<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->

<!-- h2 stays for breadcrumbs -->
<h2>メンバー管理</h2>
<div id="main">
  
  <?php if (!empty($this->msg)):?>
  <div class="message"><?php echo form_prep($this->msg);?></div>
  <?php endif;?>
  
  <h3>メンバー項目の登録/編集</h3>
    
  <div class="sz_member_att_edit">
    <?php echo form_open('dashboard/members/attributes/do_edit', array('id' => 'att_form'));?>
      <table cellspacing="0" cellpadding="0" id="att_form_table">
        <tbody>
          <tr>
            <th>ユーザー更新設定：</th>
            <td><?php echo form_checkbox('is_inputable', 1, ($att->is_inputable > 0) ? TRUE : FALSE);?>&nbsp;ユーザーが入力/変更できる項目にする</td>
          </tr>
          <tr>
            <th><label for="attribute_name">項目名：</label></th>
            <td><?php echo form_input(array('name' => 'attribute_name', 'id' => 'attribute_name', 'value' => $att->attribute_name, 'style' => 'width:80%'));?></td>
          </tr>
          <tr>
            <th><label for="attribute_type">入力タイプ：</label></th>
            <td><?php echo form_dropdown('attribute_type', $attribute_types, $att->attribute_type);?></td>
          </tr>
          <tr class="init_hide">
            <th>&nbsp;</th>
            <td>
              <label>行数：<?php echo form_input(array('name' => 'rows', 'id' => 'rows', 'value' => $att->rows));?></label>
              <label>列数：<?php echo form_input(array('name' => 'cols', 'id' => 'cols', 'value' => $att->cols));?></label>
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
              
              <?php foreach ( $options as $opt ):?>
              <?php if ( $opt !== '' ):?>
              <p>
                  オプション名：<?php echo form_input(array('name' => 'options[]', 'value' => $opt));?>
                <a href="javascript:void(0)" class="att_option">
                  <?php echo set_image('delete.png', TRUE);?>&nbsp;削除
                </a>
              </p>
              <?php endif;?>
              <?php endforeach;?>
              
              <?php if ( count($options) === 0 ):?>
              <p>
                  オプション名：<?php echo form_input(array('name' => 'options[]', 'value' => ''));?>
                <a href="javascript:void(0)" class="att_option">
                  <?php echo set_image('delete.png', TRUE);?>&nbsp;削除
                </a>
              </p>
              <?php endif;?>
              
            </td>
          </tr>
          <tr>
            <th>入力値の検証ルール:</th>
            <td>
              <label><?php echo form_checkbox('required', 1, in_array('required', $v_rules));?>&nbsp;必須入力にする</label><br />
              <label><?php echo form_checkbox('integer', 1, in_array('integer', $v_rules));?>&nbsp;数値のみ許可する</label><br />
              <label><?php echo form_checkbox('valid_url', 1, in_array('callback_is_valid_url', $v_rules));?>&nbsp;URL形式をチェックする</label><br />
              <label><?php echo form_input(array('name' => 'max_length', 'value' => $max_length, 'size' => 3));?>&nbsp;文字以内に制限する</label><br />
              <label><?php echo form_input(array('name' => 'min_length', 'value' => $min_length, 'size' => 3));?>&nbsp;文字以上に制限する</label>
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
