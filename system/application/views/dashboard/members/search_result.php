<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->

<!-- h2 stays for breadcrumbs -->
<h2>メンバー管理</h2>

<?php if (!empty($this->msg)):?>
<div class="message"><?php echo form_prep($this->msg);?></div>
<?php endif;?>

<div id="main">
  <h3>メンバー検索結果</h3>
  <p>ニックネーム:「&nbsp;<?php echo form_prep($username_q);?>&nbsp;」、メールアドレス:「&nbsp;<?php echo form_prep($email_q);?>&nbsp;」で検索した結果</p>
  <p class="pagination"><?php echo $pagination;?></p>
  <p class="total">
    <?php echo $total;?>
    <a href="<?php echo page_link();?>dashboard/members/member_list/index">
      <?php echo set_image('back.png', TRUE);?>メンバー一覧に戻る
    </a>
  </p>
  <table cellpadding="0" cellspacing="0" class="admin_users_table">
    <tr>
      <td>&nbsp;</td>
      <td>ニックネーム</td>
      <td>メールアドレス</td>
      <td>ログイン回数</td>
      <td class="action">操作</td>
    </tr>
    
    <?php foreach ($search_result as $key => $val):?>
    <tr<?php if ((int)$key % 2 === 0) { echo ' class="odd"';}?>>
      <td>
          <?php if ($val->banned > 0):?>
          <a href="javascript:void(0)" class="unlock_member" rel="<?php echo $val->sz_member_id?>">
            <?php echo set_image('lock.png', TRUE);?>
          </a>
          <?php else:?>
          &nbsp;
          <?php endif;?>
       </td>
      <td><?php echo form_prep($val->nick_name);?></td>
      <td><?php echo form_prep($val->email);?></td>
      <td class="times"><?php echo $val->login_times;?></td>
      <td class="action">
        <?php echo anchor('dashboard/members/member_list/detail/' . $val->sz_member_id, '詳細', 'class="view sz_zoom"')?>
        <?php if ($this->is_master):?>
        <?php echo anchor('dashboard/members/edit_member/index/' . $val->sz_member_id, '編集', 'class="edit"');?>
        <?php echo anchor('dashboard/members/member_list/delete/' . $val->sz_member_id, '削除', 'class="delete"')?>
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
