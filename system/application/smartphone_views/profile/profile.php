<?php if ($profile):?>
<h2><?php echo prep_str($profile->nick_name);?>&nbsp;さんのプロフィール</h2>
<div class="sz_member_profile">
  <div class="profile_image_sp">
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

  <div class="profile_data_box_sp">
    <dl>
      <dt>ニックネーム</dt>
      <dd><?php echo prep_str($profile->nick_name);?></dd>
    </dl>
    <dl>
      <dt>登録日</dt>
      <dd><?php echo date('Y年m月d日', strtotime($profile->joined_date));?></dd>
    </dl>
    <?php if ( $is_self === TRUE ):?>
    <dl>
      <dt>メールアドレス</dt>
      <dd><?php echo prep_str($profile->email);?></dd>
    </dl>
    <dl>
      <dt>ログイン回数</dt>
      <dd><?php echo (int)$profile->login_times;?></dd>
    </dl>
    <?php endif;?>
    <?php if ( isset($attributes) ):?>
    <?php foreach ( $attributes as $att ):?>
    <dl>
      <dt><?php echo prep_str($att->name);?>:</dt>
      <dd><?php echo prep_str($att->value)?></dd>
    </dl>
    <?php endforeach;?>
    <?php endif;?>
  </div>

  <?php if ($is_self === TRUE):?>
  <!-- edit profile -->
  <ul class="edit_profile">
    <?php if ( ! empty($profile->email) ):?>
    <li><?php echo anchor('profile/edit/', 'アカウント情報の変更', 'class="button"');?></li>
    <li><?php echo anchor('profile/select_change_login_account/', 'ログイン情報の変更', 'class="button"');?></li>
    <li><?php echo anchor('logout/logout_member', 'ログアウト', 'class="button"');?></li>
    <li><?php echo anchor('profile/secession', '退会する', 'class="button"');?></li>
    <?php else:?>
    <li><?php echo anchor('profile/edit/', 'アカウント情報の変更', 'class="button"');?></li>
    <li><?php echo anchor('logout/logout_member', 'ログアウト', 'class="button"');?></li>
    <li><?php echo anchor('profile/secession', '退会する', 'class="button"');?></li>
    <?php endif;?>
  </ul>
  <?php endif;?>

</div>

<?php else:?>

<p>表示するプロフィールはありません。</p>

<?php endif;?>
