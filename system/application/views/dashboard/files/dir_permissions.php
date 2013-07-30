<form id="sz_dir_permissions">
<p style="color:#c00;margin : 8px 0;">※チェックを入れたユーザーのみアクセス可能になります。</p>
<ul class="pp_content_list">
	<?php foreach ($users as $key => $u):?>
	<?php if ($key > 1):?>
	<li><p><label><?php echo form_checkbox('dir_permission[]', $key, ($permission->access_permission == '' ||  $permission->access_permission == ':1:' || strpos($permission->access_permission, ':' . $key . ':') !== FALSE) ? TRUE : FALSE);?>&nbsp;<?php echo $u->user_name;?></label></p></li>
	<?php endif;?>
	<?php endforeach;?>
</ul>
<p style="margin:8px 0;text-align:center;">
	<?php echo form_checkbox('recursive', 1, TRUE);?>&nbsp;中のディレクトリにも権限を適用する<br />
	<?php echo form_hidden('did', $did);?>
	<input type="button" value="権限を設定する" id="do_permission" />
</p>
</form>
