<?php echo form_open($page_id, array('id' => 'sz-page_add_form'));?>
<h3>ページ情報を編集</h3>
<ul class="sz_tabs clearfix">
	<li><a href="#tab_content1" class="active">ページ設定</a></li>
	<li><a href="#tab_content3">アクセス権限</a></li>
	<li><a href="#tab_content2">使用テンプレート</a></li>
</ul>
<div id="tab_content1" class="tab_content">
	<?php echo $this->load->view('elements/page_base', array('page' => $page));?>
</div>
<div id="tab_content2" class="init_hide tab_content">
	<table>
		<tbody>
			<?php foreach ($templates as $key => $value):?>
			<tr>
				<td>
					<input type="radio" name="template_id" id="tpid_<?php echo $value->template_id;?>" value="<?php echo $value->template_id;?>"<?php if($page->template_id == $value->template_id) { echo ' checked="checked"';}?> />
				</td>
				<td>
					<label for="tpid_<?php echo $value->template_id;?>">
					<?php if (file_exists('templates/' . $value->template_handle . '/image.jpg')):?>
					<img src="<?php echo file_link()?>templates/<?php echo $value->template_handle;?>/image.jpg" alt="" />
					<?php else:?>
					<img src="<?php echo file_link()?>images/no_image.gif" alt=""/>
					<?php endif;?>
					</label>
				</td>
				<td>
					<p style="font-weight:bold"><?php echo $value->template_name;?></p>
					<p><?php echo nl2br($value->description);?></p>
				</td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
</div>
<div id="tab_content3" class="init_hide tab_content">
	<?php echo $this->load->view('elements/page_permissions_edit', array(
				'user_list' => $user_list,
				'page_permission' => array('allow_access_user' => $page->allow_access_user, 'allow_edit_user' => $page->allow_edit_user, 'allow_approve_user' => $page->allow_approve_user),
				'page_path'	=> $page->page_path
				));?>
</div>
<p class="sz_add_form_submit">
	<?php echo form_hidden('page_id', $page_id);?>
	<?php echo form_hidden('sz_token', $token);?>
	<?php echo form_hidden('page_path_id', $page->page_path_id);?>
	<?php echo form_hidden('version_number', $version_number);?>
	<input type="hidden" name="process" value="page_edit_config" />
	<input type="submit" value="ページ情報を保存する" class="page_submit" />
</p>
<?php form_close();?>