<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">
<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>
  <h3>ブログ設定情報の変更</h3>
  <ul class="sz_dashboard_tabs clearfix">
    <li><a href="#content1" class="active">基本設定</a></li>
    <!-- <li><a href="#content2">テンプレート設定</a></li>-->
  </ul>
  <?php echo form_open('dashboard/blog/settings/do_settings', array('class' => 'jNice', 'id' => 'setting_form'))?>
    <div id="content1" class="sz_dashboard_tab_content">
     　<fieldset>
     
        <?php if ($this->form_validation->error('page_title') || isset($again)):?>
        <p><label>ブログタイトル</label><?php echo form_input(array('name' => 'page_title', 'value' => set_value('page_title'), 'class' => 'text-long'));?></p>
        <?php echo $this->form_validation->error('page_title');?>
        <?php else:?>
        <p><label>ブログタイトル</label><?php echo form_input(array('name' => 'page_title', 'value' => $info->page_title, 'class' => 'text-long'));?></p>
        <?php endif;?>
        
        <?php if ($this->form_validation->error('entry_limit') || isset($again)):?>
        <p><label>1ページに表示する記事数</label><?php echo form_input(array('name' => 'entry_limit', 'value' => set_value('entry_limit'), 'class' => 'text-small imedis'));?></p>
        <?php echo $this->form_validation->error('entry_limit');?>
        <?php else:?>
        <p><label>1ページに表示する記事数</label><?php echo form_input(array('name' => 'entry_limit', 'value' => $info->entry_limit, 'class' => 'text-small imedis'));?></p>
        <?php endif?>
        
        <?php if ($this->form_validation->error('comment_limit') || isset($again)):?>
        <p><label>メニューに表示するコメント数</label><?php echo form_input(array('name' => 'comment_limit', 'value' => set_value('comment_limit'), 'class' => 'text-small imedis'));?></p>
        <?php echo $this->form_validation->error('comment_limit');?>
        <?php else:?>
        <p><label>メニューに表示するコメント数</label><?php echo form_input(array('name' => 'comment_limit', 'value' => $info->comment_limit, 'class' => 'text-small imedis'));?></p>
        <?php endif?>
        
        <?php if ($this->input->post('rss_format')):?>
        <p><label>RSS配信タイプ</label><?php echo form_dropdown('rss_type', $rss_types, (int)$this->input->post('rss_format'));?></p>
        <?php else:?>
        <p><label>RSS配信タイプ</label><?php echo form_dropdown('rss_type', $rss_types, $info->rss_format);?></p>
        <?php endif;?>
        
        <?php if ($this->input->post('zenback_code')):?>
        <p>
          <label>zenback連携コード(発行されたコードをペーストしてください)<br /><span style="color:#09f;">※ソーシャルブックマークやtwitterとの連携が行えます。</span>&nbsp;&nbsp;<?php echo anchor_popup('http://zenback.jp/', 'コードを取得する');?></label>
          <?php echo form_textarea(array('name' => 'zenback_code', 'cols' => 1, 'rows' => 1));?>
          <?php echo anchor_popup('http://zenback.jp/', 'コードを取得する');?>
        </p>
        <?php else:?>
        <p>
          <label>zenback連携コード(発行されたコードをペーストしてください)<br /><span style="color:#09f;">※ソーシャルブックマークやtwitterとの連携が行えます。</span>&nbsp;&nbsp;<?php echo anchor_popup('http://zenback.jp/', 'コードを取得する');?></label>
          <?php echo form_textarea(array('name' => 'zenback_code', 'cols' => 1, 'rows' => 1, 'value' => $info->zenback_code));?>
          
        </p>
        <?php endif;?>
        
        <?php if ($this->input->post('is_need_captcha')):?>
        <p><label><input type="checkbox" name="is_need_captcha" value="1"<?php if ((int)$this->input->post('is_need_captcha') > 0) {echo ' checked="checked"';}?> />コメントの投稿に画像認証を使用する</label></p>
        <?php else:?>
        <p><label><input type="checkbox" name="is_need_captcha" value="1"<?php if ((int)$info->is_need_captcha > 0) {echo ' checked="checked"';}?> />コメントの投稿に画像認証を使用する</label></p>
        <?php endif;?>
        
        <?php if ($this->input->post('is_auto_ping')):?>
        <p><label><input type="checkbox" name="is_auto_ping" value="1"<?php if ((int)$this->input->post('is_auto_ping') > 0) {echo ' checked="checked"';}?> />新規記事投稿の際には自動的にpingを送信する</label>&nbsp;&nbsp;※記事を投稿した際、更新情報を自動的に送信します。</p>
        <?php else:?>
        <p><label><input type="checkbox" name="is_auto_ping" value="1"<?php if ((int)$info->is_auto_ping > 0) {echo ' checked="checked"';}?> />新規記事投稿の際には自動的にpingを送信する</label>&nbsp;&nbsp;※記事を投稿した際、更新情報を自動的に送信します。</p>
        <?php endif;?>
        
        <p><label><input type="checkbox" name="is_enable" value="1" checked="checked" />ブログを利用可能にする</label></p>
      </fieldset>
    </div><!-- 
    <div id="content2" class="sz_dashboard_tab_content init_hide">
      <?php echo $this->load->view('dashboard/blog/template_parts', array('templates' => $templates, 'use_template' => $info->template_id));?>
    </div> -->
    <?php echo $this->form_validation->error('is_enable');?>
    <?php echo $this->form_validation->error('is_need_captcha');?>
    <p class="submit_area">
      <?php echo form_hidden($this->ticket_name, $ticket);?>
      <?php echo form_submit(array('value' => '更新する', 'name' => 'do_update'))?>
    </p>
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
