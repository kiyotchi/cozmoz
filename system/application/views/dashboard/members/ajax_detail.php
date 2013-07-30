<h3>ユーザー詳細</h3>
<div class="list_wrap">
<?php if ($member->banned >0):?>
<p style="text-align:center;margin:8px 0;color:#c00;">このユーザーアカウントはロックされています！</p>
<?php endif;?>
<table>
  <tbody>
    <tr class="odd">
      <th>ニックネーム</th>
      <td><?php echo prep_str($member->nick_name);?></td>
    </tr>
    <tr>
      <th>メールアドレス</th>
      <td><?php echo prep_str($member->email);?></td>
    </tr>
    <tr class="odd">
      <th>ログイン回数</th>
      <td><?php echo $member->login_times;?></td>
    </tr>
    <tr>
      <th>登録日時</th>
      <td><?php echo $member->joined_date;?></td>
    </tr>
    <tr>
      <th>アクティベーション状況</th>
      <td>
        <?php if ($member->relation_site_user > 0):?>
        サイト管理者アカウントによりスキップ
        <?php elseif ($member->is_activate > 0):?>
        済
        <?php else:?>
        <span class="red">未</span>
        <?php endif;?>
      </td>
    </tr>
    
    <?php foreach ( $attribute_values as $value ):?>
    <tr>
      <th><?php echo prep_str($value->name);?></th>
      <td><?php echo prep_str($value->value);?></td>
    </tr>
    <?php endforeach;?>
    
	</tbody>
</table>
<h3 style="margin-left:-30px;" id="prof_caption">プロフィール画像</h3>
<div style="text-align:center;">
	<?php if ($member->image_data):?>
	<img src="<?php echo file_link();?>files/members/<?php echo $member->image_data;?>" alt="<?php echo prep_str($member->nick_name);?>" width="100" height="100" style="vertical-align:bottom;" id="member_profile_image" />
	<?php if ($this->user_id > 0):?>
	<a href="<?php echo page_link();?>dashboard/members/member_list/delete_profile_image/<?php echo $member->sz_member_id;?>" class="dpi">削除</a>
	<p style="margin : 8px 0;">プロフィール画像を変更する場合はアップロードしてください。（100px&nbsp;x&nbsp;100px推奨）</p>
	<?php endif;?>
	<?php else:?>
	<img src="<?php echo file_link();?>images/no_image.gif" alt="" width="100" height="100" alt="<?php echo prep_str($member->nick_name);?>" style="vertical-align:bottom;" id="member_profile_image" />
	<?php if ($this->user_id > 0):?>
	<a href="<?php echo page_link();?>dashboard/members/member_list/delete_profile_image/<?php echo $member->sz_member_id;?>" class="dpi">削除</a>
	<p style="margin : 8px 0;">プロフィール画像を登録する場合はアップロードしてください。（100px&nbsp;x&nbsp;100px推奨）</p>
	<?php endif;?>
	<?php endif;?>
	<?php if ($this->user_id > 0):?>
	<iframe src="<?php echo page_link();?>dashboard/members/member_list/profile_image_form/<?php echo $member->sz_member_id?>" frameborder="0" scrolling="no" style="width:100%;height:25px;"></iframe>
	<?php endif;?>
</div>
<p class="conf">
	<?php if ($this->is_master || $this->user_id > 0):?>
	<?php echo anchor('dashboard/members/edit_member/index/' . $member->sz_member_id, set_image('edit.png', TRUE) . '&nbsp;編集する');?>
	<?php endif;?>
	<?php if ($this->is_master):?>
	<?php echo anchor('dashboard/members/member_list/delete/' . $member->sz_member_id . '/' . $ticket . '/1', set_image('delete.png', TRUE) . '&nbsp;削除する', 'onclick="return confirm(\'ユーザーを削除します。よろしいですか？\');"');?>
	<?php endif;?>
</p>

</div>