<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">
  
  <?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
  <?php endif;?>
  
  <?php if ($entry_id > 0):?>
  <h3>記事の編集</h3>
  <?php else:?>
  <h3>新規投稿</h3>
  <?php endif;?>
  
  <p class="additional"><a href="javascript:void(0)" id="add_category"><?php echo set_image('plus.png', TRUE);?>カテゴリの追加</a></p>
  <?php echo form_open('dashboard/blog/edit/confirm', array('class' => 'jNice', 'id' => 'entry_form'))?>
  <fieldset>
    <p>
      <label>タイトル</label>
      <?php echo form_input(array('name' => 'title', 'value' => (isset($entry)) ? $entry->title : '', 'class' => 'text-long'));?>
      <?php if ($is_validated === TRUE)echo $this->form_validation->error('title');?>
    </p>
    <p>
      <label>登録カテゴリ</label>
      <select name="sz_blog_category_id" id="category_list">
        <option value=""<?php if (!$this->input->post('sz_blog_category_id')) { echo ' selected="selected"';}?>>---- カテゴリを選択 ----</option>
        <?php foreach ($category as $key => $value):?>
        <option value="<?php echo $key;?>"<?php if (isset($entry) && (int)$key === (int)$entry->sz_blog_category_id) { echo ' selected="selected"';}?>><?php echo $value;?></option>
        <?php endforeach;?>
      </select>
      <span id="add_msg">カテゴリを追加しました。</span>
      <?php if ($is_validated === TRUE) echo $this->form_validation->error('sz_blog_category_id');?>
    </p>
    <p>
      <label>URIセグメント</label>
      <?php echo form_input(array('name' => 'permalink', 'value' => (isset($entry)) ? $entry->permalink : '', 'class' => 'text-long'));?>
      <?php if ($is_validated === TRUE)echo $this->form_validation->error('permalink');?>
    </p>
    <p>
      <label>公開日時</label>
      <?php echo form_input(array('name' => 'show_date', 'id' => 'cal_target', 'value' => ( ! isset($entry) || $entry->show_date === '0000-00-00') ? date('Y-m-d') : $entry->show_date, 'class' => 'text-medium'));?>
      <?php echo form_dropdown('show_hour', hour_list(), ( isset($entry) ) ? $entry->show_hour : date('H'));?>：
      <?php echo form_dropdown('show_minute', minute_list(), ( isset($entry) ) ? $entry->show_minute : date('i'));?>
      <?php if ($is_validated === TRUE):?>
      <?php echo $this->form_validation->error('show_date');?>
      <?php echo $this->form_validation->error('show_hour');?>
      <?php echo $this->form_validation->error('show_minute');?>
      <?php endif;?>
    </p>
    <div>
      <label>本文&nbsp;<?php if ($is_validated === TRUE) echo $this->form_validation->error('body');?></label><textarea name="body" cols="1" rows="1" class="long-body"><?php if (isset($entry)) echo $entry->body;?></textarea>
      <p class="save_button">
        <span>&nbsp;</span>
        <input type="button" value="下書き保存" id="save_to_draft_btn" />&nbsp;
      </p>
    </div>
    <p><label><input type="checkbox" name="is_accept_comment" value="1"<?php if (isset($entry) && (int)$entry->is_accept_comment !== 0) { echo ' checked="checked"';}?> />コメントを受け付ける</label></p>
    <p><label><input type="checkbox" name="is_accept_trackback" value="1"<?php if (isset($entry) && (int)$entry->is_accept_trackback !== 0) { echo ' checked="checked"';}?> />トラックバックを受け付ける</label></p>
    <?php echo form_hidden($this->ticket_name, $ticket);?>
    <input type="hidden" name="sz_blog_id" value="<?php echo (int)$entry_id?>" id="eid" />
    <?php echo form_submit(array('value' => '入力内容を確認する', 'name' => 'do_confirm'));?>
  </fieldset>
  <div id="additional_body">
    <span id="info">追加したいカテゴリを登録してください。</span>
    <p><label>カテゴリ名</label>&nbsp;<?php echo form_input(array('name' => 'category_name'));?></p>
    <p><input type="button" id="add_cat" value="カテゴリ追加" /></p>
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
