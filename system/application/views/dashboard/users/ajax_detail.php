<h3>ユーザー詳細</h3>
<div class="list_wrap">
<?php if ($user->login_miss_count >= 3):?>
<p style="text-align:center;margin:8px 0;color:#c00;">このユーザーアカウントはロックされています！</p>
<?php endif;?>
<table>
	<tbody>
		<tr class="odd">
			<th>ユーザー名</th>
			<td><?php echo $user->user_name;?></td>
		</tr>
		<tr>
			<th>メールアドレス</th>
			<td><?php echo $user->email;?></td>
		</tr>
		<tr class="odd">
			<th>最終ログイン日時</th>
			<td><?php echo $user->last_login;?></td>
		</tr>
		<tr>
			<th>ログイン回数</th>
			<td><?php echo $user->login_times;?></td>
		</tr>
		<tr class="odd">
			<th>管理者権限</th>
			<td><?php echo ((int)$user->admin_flag === 1) ? '有り' : '無し';?></td>
		</tr>
		<tr>
			<th>登録日時</th>
			<td><?php echo $user->regist_time;?></td>
		</tr>
	</tbody>
</table>
<h3 style="margin-left:-30px;" id="prof_caption">プロフィール画像</h3>
<div style="text-align:center;">
	<?php if ($user->image_data):?>
	<img src="<?php echo file_link();?>files/members/<?php echo $user->image_data;?>" alt="<?php echo $user->user_name;?>" width="100" height="100" style="vertical-align:bottom;" />
	<?php if ($this->user_id == $user->user_id):?>
	<a href="<?php echo page_link();?>dashboard/users/user_list/delete_profile_image/<?php echo $user->user_id;?>" class="dpi">削除</a>
	<p style="margin : 8px 0;">プロフィール画像を変更する場合はアップロードしてください。（100px&nbsp;x&nbsp;100px推奨）</p>
	<?php endif;?>
	<?php else:?>
	<img src="<?php echo file_link();?>images/no_image.gif" alt="" width="100" height="100" alt="<?php echo $user->user_name;?>" style="vertical-align:bottom;" />
	<?php if ($this->user_id == $user->user_id):?>
	<a href="<?php echo page_link();?>dashboard/users/user_list/delete_profile_image/<?php echo $user->user_id;?>" class="dpi">削除</a>
	<p style="margin : 8px 0;">プロフィール画像を登録する場合はアップロードしてください。（100px&nbsp;x&nbsp;100px推奨）</p>
	<?php endif;?>
	<?php endif;?>
	<?php if ($this->user_id == $user->user_id):?>
	<iframe src="<?php echo page_link();?>dashboard/users/user_list/profile_image_form/<?php echo $user->user_id?>" frameborder="0" scrolling="no" style="width:100%;height:25px;"></iframe>
	<?php endif;?>
</div>
<p class="conf">
	<?php if ($this->is_master || $this->user_id == $user->user_id):?>
	<?php echo anchor('dashboard/users/edit_user/index/' . $user->user_id, set_image('edit.png', TRUE) . '&nbsp;編集する');?>
	<?php endif;?>
	<?php if ($this->is_master):?>
	<?php echo anchor('dashboard/users/user_list/delete/' . $user->user_id . '/' . $ticket, set_image('delete.png', TRUE) . '&nbsp;削除する', 'onclick="return confirm(\'このユーザーを削除します。よろしいですか？\');"');?>
	<?php endif;?>
</p>
<?php if ($this->is_master):?>
<p class="conf">
	<?php echo anchor('dashboard/users/user_list/relogin_width_other_address/' . $user->user_id, set_image('config.png', TRUE) . '&nbsp;このユーザーとしてログインする', 'id="re_login"');?>
</p>
<?php endif;?>

</div>
