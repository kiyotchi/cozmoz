<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>カレンダー管理</h2>
<div id="main">

  <?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
  <?php endif;?>

  <?php if ($org_wall_cal_dat_id > 0):?>
  <h3>カレンダー編集</h3>
  <?php else:?>
  <h3>新規登録</h3>
  <?php endif;?>

  <p class="additional"><a href="javascript:void(0)" id="add_category"><?php echo set_image('plus.png', TRUE);?>メーカー追加</a></p>
  <?php echo form_open_multipart('dashboard/wallpaper/cal_edit/confirm', array('class' => 'jNice', 'id' => 'entry_form'))?>
  <fieldset>
    <p>
      <label>メーカー</label>
      <select name="wall_maker_id" id="category_list">
        <option value=""<?php if (!$this->input->post('wall_maker_id')) { echo ' selected="selected"';}?>>---- メーカー選択 ----</option>
        <?php foreach ($category as $key => $value):?>
        <option value="<?php echo $key;?>"<?php if (isset($entry) && (int)$key === (int)$entry->wall_maker_id) { echo ' selected="selected"';}?>><?php echo $value;?></option>
        <?php endforeach;?>
      </select>
      <span id="add_msg">メーカーを追加しました。</span>
      <?php if ($is_validated === TRUE) echo $this->form_validation->error('wall_maker_id');?>
    </p>
    <p>
      <label>タイトル</label>
      <?php echo form_input(array('name' => 'title', 'value' => (isset($entry)) ? $entry->title : '', 'class' => 'text-long'));?>
      <?php if ($is_validated === TRUE)echo $this->form_validation->error('title');?>
    </p>
    <p>
      <label>カレンダーイメージ（自動変換され、サムネール画像でも指定します。）</label>
      <?php if(isset($entry) && $entry->tn_hp_crypt_name != '') :?>
      <?php echo '<label><img src="'.page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/tn_hp_crypt_name').'" border="0">イメージ修正しない<input type="checkbox" name="image_check" id="image_check" value="1" checked></label>';?>
      <?php endif;?>
      <?php echo form_upload(array('name' => 'thumbnail', 'value' => (isset($entry)) ? form_prep(@$entry->thumbnail) : '', 'class' => 'text-long','id' => 'thumbnail'));?>
      <?php if ($is_validated === TRUE)echo $this->form_validation->error('thumbnail');?>
    </p>
    <p>
      <label>コメント&nbsp;<?php if ($is_validated === TRUE) echo $this->form_validation->error('body');?></label>
      <textarea name="body" cols="1" rows="1" class="long-body"><?php if (isset($entry)) echo $entry->body;?></textarea>
    </p>
    <p>
    <label>公開日時</label>
		<?php if (isset($entry)):?>
		<?php echo form_input(array('name' => 'public_ymd', 'value' => set_public_datetime('Y-m-d', $entry->public_datetime), 'class' => 'imedis', 'size' => 12));?>
		<?php echo form_dropdown('public_time', hour_list(), set_public_datetime('H', $entry->public_datetime));?>:
		<?php echo form_dropdown('public_minute', minute_list(), set_public_datetime('i', $entry->public_datetime));?>
		<?php else:?>
		<?php echo form_input(array('name' => 'public_ymd','value' => date('Y-m-d', time()),'class' => 'imedis', 'size' => 12));?>
		<?php echo form_dropdown('public_time', hour_list(), date('H', time()));?>:
		<?php echo form_dropdown('public_minute', minute_list(), date('i', time()));?>
		<?php endif;?>
    </p>
    <p><label>課金設定</label>
     <label><input type="checkbox" name="mobile_docomo" value="1"<?php if (isset($entry) && (int)$entry->mobile_docomo !== 0) { echo ' checked="checked"';}?> />docomo</label>
     <label><input type="checkbox" name="mobile_au" value="1"<?php if (isset($entry) && (int)$entry->mobile_au !== 0) { echo ' checked="checked"';}?> />au</label>
     <label><input type="checkbox" name="mobile_softbank" value="1"<?php if (isset($entry) && (int)$entry->mobile_softbank !== 0) { echo ' checked="checked"';}?> />softbank</label>
    </p>
    <?php echo form_hidden($this->ticket_name, $ticket);?>
    <input type="hidden" name="org_wall_cal_dat_id" value="<?php echo (int)$org_wall_cal_dat_id?>" id="eid" />
    <?php echo form_hidden('tmp_wall_cal_dat_id', isset($entry) ? $entry->wall_cal_dat_id : '');?>
    <?php echo form_hidden('entry_date', isset($entry) ? $entry->entry_date : '');?>
    <?php echo form_submit(array('value' => '入力内容を確認する', 'name' => 'do_confirm'));?>
  </fieldset>
  <div id="additional_body">
    <span id="info">追加したいメーカーを登録してください。</span>
    <p><label>メーカー名</label>&nbsp;<?php echo form_input(array('name' => 'maker_name'));?></p>
    <p><input type="button" id="add_cat" value="メーカー追加" /></p>
  </div>
  <?php echo form_close();?>
  <br />
  <br />
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
