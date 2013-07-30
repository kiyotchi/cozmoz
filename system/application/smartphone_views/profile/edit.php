<h2>プロフィール編集</h2>
<p class="caption">以下の項目を入力してください。</p>

<?php echo form_open('profile/edit_confirm', array('id' => 'sz_registration_form'));?>
	<table class="sz_registration_table">
		<tbody>
			<tr>
				<th>
					<label for="nick_name">ニックネーム<span class="r_need">*</span></label>
				</th>
				<td>
					<?php if ( $is_validated === FALSE ):?>
					<?php echo form_input(array('name' => 'nick_name', 'id' => 'nick_name', 'value' => $profile->nick_name));?>
					<?php else:?>
					<?php echo form_input(array('name' => 'nick_name', 'id' => 'nick_name', 'value' => $this->input->post('nick_name')));?>
					<?php echo $this->form_validation->error('nick_name');?>
					<?php endif;?>
				</td>
			</tr>
			<!-- attributes -->
			<?php foreach ( $attributes as $att ):?>
			<tr>
				<th>
					<label for="attribute_<?php echo $att->sz_member_attributes_id?>">
						<?php echo prep_str($att->attribute_name);?>
						<?php echo set_require_mark($att->validate_rule);?>
					</label>
				</th>
				<td>
					<?php if ( $is_validated === FALSE ):?>
					<?php echo build_registration_form_parts($att, FALSE, set_attribute_value($att->attribute_name, $attributes_values));?>
					<?php else:?>
					<?php echo build_registration_form_parts($att, FALSE);?>
					<?php endif;?>
					<?php echo $this->form_validation->error('attribute_' . $att->sz_member_attributes_id);?>
				</td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<div class="sz_r_submission">
	<?php echo form_hidden('ticket', $ticket);?>
	<?php echo form_submit(array('value' => '確認画面へ', 'class' => 'button'));?>
	</div>
<?php echo form_close();?>
<p class="backlink">
<?php echo anchor('profile', '&laquo;&nbsp;プロフィールに戻る');?>
</p>