<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->

<!-- h2 stays for breadcrumbs -->
<h2>メンバー管理</h2>
<div id="main">
  
  <?php if (!empty($this->msg)):?>
  <div class="message"><?php echo form_prep($this->msg);?></div>
  <?php endif;?>
  
  <h3>メンバー項目設定</h3>
    <p>メンバーの追加項目を設定します。</p>
    
    <br />
    <p class="info_icon">
      <?php echo set_image('edit.png', TRUE);?>&nbsp;アイコンのあるものはユーザーにて登録/変更が可能です。<br />
      <?php echo set_image('warning.png', TRUE);?>&nbsp;アイコンのあるものは現在使用していない項目です。
    </p>
    
    <p class="right">
      <a href="<?php echo page_link();?>dashboard/members/attributes/edit">
        <?php echo set_image('plus.png', TRUE);?>&nbsp;項目追加
      </a>
    </p>
    <br />

    <table cellpadding="0" cellspacing="0" class="admin_users_table" id="att_table">
      <tr class="head">
        <td>&nbsp;</td>
        <td>項目名</td>
        <td>入力タイプ</td>
        <td class="action">操作</td>
        <td>並び替え</td>
      </tr>
      
      <?php foreach ($attributes as $key => $val):?>
      <tr id="order_<?php echo $val->sz_member_attributes_id;?>">
        <td>
          <img src="<?php echo file_link();?>images/edit.png"<?php if ( $val->is_inputable == 0 ) echo ' style="visibility:hidden"';?> />
          <img src="<?php echo file_link();?>images/warning.png"<?php if ( $val->is_use > 0 ) echo ' style="visibility:hidden"';?> />
        </td>
        <td>
          <?php echo form_prep($val->attribute_name);?>
        </td>
        <td><?php echo $attribute_types[$val->attribute_type];?></td>
        <td class="action">
          <?php echo anchor('dashboard/members/attributes/edit/' . $val->sz_member_attributes_id, '編集', 'class="edit"');?>
          
          <?php if ( $val->is_use > 0):?>
          <?php echo anchor('dashboard/members/attributes/setuse/nouse/' . $val->sz_member_attributes_id, '未使用にする', 'class="view"');?>
          <?php else: ?>
          <?php echo anchor('dashboard/members/attributes/setuse/douse/' . $val->sz_member_attributes_id, '使用する', 'class="view"');?>
          <?php endif;?>
          
          <?php echo anchor('dashboard/members/attributes/delete/' . $val->sz_member_attributes_id, '削除', 'class="delete" rel="master"');?>
        </td>
        <td class="moveup">
          <p>
            <a href="javascript:void(0)" class="up" rel="<?php echo $val->sz_member_attributes_id?>">&nbsp;</a>
            <a href="javascript:void(0)" class="down" rel="<?php echo $val->sz_member_attributes_id?>">&nbsp;</a>
          </p>
        </td>
      </tr>
      <?php endforeach;?>
      
      <?php if ( count($attributes) === 0 ):?>
      <tr class="odd">
        <td colspan="5" class="center">追加項目はありません。</td>
      </tr>
      <?php endif;?>
      
    </table>
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
