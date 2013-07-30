<div class="sz_page_out">
<h4>編集モード終了操作</h4>
<?php echo form_open(get_base_link() . $pid);?>
<fieldset>
<p>
<label>編集バージョンのコメント:<br /><?php echo form_input(array('name' => 'version_comment', 'value' => 'バージョン' . $version, 'style' => 'width:40em;margin:0;padding:2px;font-size:16px;'));?></label>
</p>
<?php echo form_hidden('pid', $pid);?>
<?php echo form_hidden('ticket', $ticket);?>
<?php echo form_hidden('process', 'edit_out');?>
<?php echo form_submit(array('name' => 'destroy', 'value' => 'この編集を破棄する'));?>
<?php echo form_submit(array('name' => 'scrap', 'value' => 'この編集を下書きバージョンとして保存する'));?>
<?php if ($can_approve):?>
<?php echo form_submit(array('name' => 'approve', 'value' => 'この編集を公開する'));?>
<?php endif;?>
<?php if (isset($approval_users)):?>
<label><input type="checkbox" name="approval_regist" id="approval_regist" value="1" />このバージョンの公開承認を申請する</label>
<div id="approve_comment" style="display:none;">
	<p><span style="color:#c00">※既に申請しているデータがある場合、今回の申請データに置き換えられます。</span></p>
	<p>承認申請のコメント：</p>
	<textarea name="approve_comment" style="width:400px;height:100px;"></textarea><br />
	<p><label><?php echo form_checkbox('is_recieve_mail', 1, FALSE);?>&nbsp;確認結果をメールで受け取る</label></p>
</div>
<?php endif;?>
</fieldset>
<?php echo form_close();?>
</div>
