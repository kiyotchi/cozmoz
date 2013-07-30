<?php if ($profile):?>
<h2><?php echo prep_str($profile->nick_name);?>&nbsp;さんのプロフィール</h2>
<div class="sz_member_profile">
  <div class="profile_image">
    <p>
    <?php if ($profile->image_data):?>
      <img src="<?php echo get_member_profile_image($profile->image_data);?>" alt="<?php echo prep_str($profile->nick_name);?>" width="100" height="100" />
    <?php else:?>
      <img src="<?php echo file_link();?>images/etc/no_image.gif" alt="<?php echo prep_str($profile->nick_name);?>" />
    <?php endif;?>
    </p>

    <?php if ($is_self === TRUE):?>
    <p class="image_box">
      <a href="<?php echo page_link();?>profile/image_upload" id="change_image">
      <?php echo set_image('plus.png', TRUE);?>&nbsp;画像変更
     </a>
    </p>
    <?php endif;?>

  </div>

  <div class="profile_data_box">
    <table class="profile_data">
      <tbody>
        <tr>
          <th>ニックネーム</th>
          <td><?php echo prep_str($profile->nick_name);?></td>
        </tr>
        <tr>
          <th>登録日</th>
          <td><?php echo date('Y年m月d日', strtotime($profile->joined_date));?></td>
        </tr>
        
        <?php if ( $is_self === TRUE ):?>
        <tr>
          <th>メールアドレス</th>
          <td><?php echo prep_str($profile->email);?></td>
        </tr>
        <tr>
          <th>ログイン回数</th>
          <td><?php echo (int)$profile->login_times;?></td>
        </tr>
        <?php endif;?>
       
      </tbody>
    </table>
  </div>
  
  <br class="clear" />


  <?php if ( isset($attributes) ):?>
  <table class="profile_attributes">
    <tbody>
      <?php foreach ( $attributes as $att ):?>
      <tr>
        <th><?php echo prep_str($att->name);?>:</th>
        <td><?php echo prep_str($att->value)?></td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
  <?php endif;?>

  <?php if ($is_self === TRUE):?>
  <!-- edit profile -->
  <p class="edit_profile">
    <?php if ( ! empty($profile->email) ):?>
    <?php echo anchor('profile/edit/', 'アカウント情報の変更', 'class="edit_profile"');?>
    <?php echo anchor('profile/select_change_login_account/', 'ログイン情報の変更', 'class="edit_logins"');?>
    <span>
      <?php echo anchor('logout/logout_member', 'ログアウト');?>
      <?php echo anchor('profile/secession', '退会する', 'class="edit_logins"');?>
    </span>
    <?php else:?>
    <?php echo anchor('profile/edit/', 'アカウント情報の変更', 'class="edit_profile"');?>
    <?php echo anchor('logout/logout_member', 'ログアウト', 'class="edit_logins"');?>
    <?php echo anchor('profile/secession', '退会する', 'class="edit_logins"');?>
    <?php endif;?>
  </p>
  <?php endif;?>

</div>

<?php else:?>

<p>表示するプロフィールはありません。</p>

<?php endif;?>
