<?php echo form_open($page_id, array('id' => 'sz-page_add_form'));?>
<h3>ページ情報を編集</h3>
<ul class="sz_tabs clearfix">
	<li><a href="#tab_content1" class="active">ページ設定</a></li>
	<li><a href="#tab_content3">アクセス権限</a></li>
</ul>
<div id="tab_content1" class="tab_content">
	<?php echo $this->load->view('dashboard/pages/system_page_base', array('page' => $page));?>
</div>
<div id="tab_content3" class="init_hide tab_content">
	<?php echo $this->load->view('elements/page_permissions_edit', array('user_list' => $user_list, 'page_permission' => array('allow_access_user' => $page->allow_access_user, 'allow_edit_user' => $page->allow_edit_user, 'allow_approve_user' => $page->allow_approve_user, 'system' => $is_system)));?>
</div>
<br />
<p class="sz_add_form_submit">
	<?php echo form_hidden('page_id', $page_id);?>
	<?php echo form_hidden('sz_token', $token);?>
	<?php echo form_hidden('page_path_id', $page->page_path_id);?>
	<?php echo form_hidden('version_number', 1);?>
	<input type="hidden" name="process" value="page_edit_config" />
	<input type="submit" value="ページ情報を編集する" class="page_submit" />
</p>
<?php form_close();?>