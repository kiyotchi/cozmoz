<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->

<!-- h2 stays for breadcrumbs -->
<h2>メンバー管理</h2>
<div id="main">
  
  <?php if (!empty($this->msg)):?>
  <div class="message"><?php echo form_prep($this->msg);?></div>
  <?php endif;?>
  
  <h3>メンバー一覧/検索</h3>
    <p>「<?php echo SITE_TITLE;?>」に登録しているメンバー一覧を表示します。</p>
    <p class="total"><?php echo $total;?><?php echo anchor('dashboard/members/edit_member/', set_image('plus.png', TRUE) . 'メンバーを追加');?></p>
    
    <?php if (!empty($pagination)):?>
    <p class="pagination"><?php echo $pagination;?></p>
    <?php endif;?>
    
    <p class="search_user">
      <a href="javascript:void(0)" id="search_open"><?php echo set_image('search.png', TRUE);?>検索フォームを開く</a>
    </p>
    <div class="user_search_form">
      <?php echo form_open('dashboard/members/member_list/search_init');?>
      <p><label>ニックネーム:<?php echo form_input(array('name' => 'user_name', 'value' => '', 'class' => 'input_text'))?></label></p>
      <p><label>メールアドレス:<?php echo form_input(array('name' => 'email', 'value' => '', 'class' => 'imedis input_text'))?></label></p>
      <p>
        <?php echo form_hidden('token', $search_token);?>
        <?php echo form_submit(array('value' => '検索', 'name' => 'do_search'));?>
      </p>
      <?php echo form_close();?>
    </div>
    
    <?php if ( ! $is_exported):?>
    <div class="sz_member_exports">
      <p>あなたの管理者アカウントをメンバーデータへエクスポートできます。</p>
     <?php echo form_open('dashboard/members/member_list/export_account', array('id' => 'exp_form'));?>
       <fieldset>
          ニックネームを決めてください。<?php echo form_input(array('name' => 'nick_name', 'id' => 'nick_name', 'value' => '' ));?>&nbsp;&nbsp;
         <?php echo form_submit(array('value' => 'エクスポートする'));?>
       </fieldset>
     <?php echo form_close();?>
    </div>
    <?php endif;?>
    
    <br />
    <p><?php echo set_image('lock.png', TRUE);?>&nbsp;アイコンのあるものはログインが禁止されています。</p>
    <br />
    <table cellpadding="0" cellspacing="0" class="admin_users_table">
      <tr>
        <td>&nbsp;</td>
        <td>ユーザー名</td>
        <td>メールアドレス</td>
        <td>登録日時</td>
        <td class="action">操作</td>
      </tr>
      
      <?php foreach ($members as $key => $val):?>
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
        <td>
          <?php echo form_prep($val->nick_name);?>
        </td>
        <td><?php echo $val->email;?></td>
        <td class="times"><?php echo $val->joined_date;?></td>
        <td class="action">
          <?php echo anchor('dashboard/members/member_list/detail/' . $val->sz_member_id, '詳細', 'class="view sz_zoom"')?>
          <?php echo anchor('dashboard/members/edit_member/index/' . $val->sz_member_id, '編集', 'class="edit"');?>
          <?php echo anchor('dashboard/members/member_list/delete/' . $val->sz_member_id, '削除', 'class="delete" rel="master"');?>
        </td>
      </tr>
      <?php endforeach;?>
      
      <?php if ( count($members) === 0 ):?>
      <tr class="odd">
        <td colspan="5" class="center">登録ユーザーはいません。</td>
      </tr>
      <?php endif;?>
      
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
