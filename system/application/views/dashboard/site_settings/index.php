<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>サイト全体の設定</h2>
<div id="main">
<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>

  <h3>サイト運用設定</h3>

  <ul class="sz_tabs clearfix" id="setting_tab">
    <li><a href="#tab_content1" class="tab">基本設定</a></li>
    <li><a href="#tab_content2" class="tab">短縮URL・favicon設定</a></li>
    <li><a href="#tab_content3" class="tab">運用・デバッグ設定</a></li>
    <li><a href="#tab_content4" class="tab">システム設定</a></li>
  </ul>

  <!-- site setting -->
  <div class="sz_tab_content">
  <h3>サイト基本情報</h3>
  <?php echo form_open('dashboard/site_settings/base/update', array('class' => 'jNice', 'id' => 'setting_form'))?>
    <fieldset>
      <p>
        <label>サイト名</label>
        <?php echo form_input(array('name' => 'site_title', 'value' => $site->site_title, 'class' => 'text-long'));?>
      </p>
      <p>
        <label>アクセス解析コード（Google&nbsp;Analyticsのコードをペーストしてください）</label>
        <textarea name="google_analytics" cols="1" rows="1" style="overflow:auto"><?php echo $site->google_analytics;?></textarea>
        <?php if ( $mobile_ga_notify ):?>
        <span class="ga_notify">
           モバイル用のアクセス解析が有効になっていません。以下の手順で設置してください。<br />
           1.&nbsp;<a href="http://www.google.com/analytics/" target="_blank">Google&nbsp;Analyticsのサイト</a>から「ga.php」をダウンロードしてください。<br />
           2.&nbsp;seezooをインストールしたディレクトリのindex.phpと同じ階層にアップロードしてください。<br />
           ※トラッキングコードの生成は自動で行われます
        </span>
        <?php endif;?>
      </p>
      <p>
        <label>システムメールアドレス（自動送信メール送信元アドレスに設定されます）</label>
        <?php echo form_input(array('name' => 'system_mail_from', 'class' => 'text-long', 'value' => $site->system_mail_from));?>
      </p>
      <p>
        <label><input type="checkbox" name="is_maintenance" value="1"<?php if ($site->is_maintenance == 1) { echo ' checked="checked"';}?> />メンテナンスモードにする</label>
      </p>
      <p class="submission">
        <?php echo form_hidden('ticket', $ticket);?>
        <?php echo form_submit(array('name' => 'update_setting', 'value' => '変更する'));?>
      </p>
    </fieldset>
  <?php echo form_close();?>
  <!-- OGP setting -->
  <h3>Open&nbsp;Graph&nbsp;Protocol基本設定</h3>
  <?php echo form_open('dashboard/site_settings/base/update_ogp');?>
  <div class="cache_setting">
    <label><input type="checkbox" name="enable_ogp" id="ogp_enabled" value="1"<?php echo ( $ogp_setting->is_enable > 0 ) ? ' checked="checked"' : ''?> />&nbsp;ページにOpen&nbsp;Graph&nbsp;Protocolタグを埋め込む</label>
    <div class="division">
      <span>title,url,site_name,description</span>はページ設定から自動出力されます。
    </div>
    <div class="division">
      <label>サイトのタイプ</label><br />
      <?php echo form_dropdown('site_type', $ogp_types, $ogp_setting->site_type, 'id="ogp_type"');?>
    </div>
    <div class="division">
      <label>OGP用の画像</label>
      <?php echo select_file('file_id', $ogp_setting->file_id);?>
    </div>
    <div class="division">
    <p>追加で表示するOGPタグ</p>
    <?php echo form_textarea(array('name' => 'extra', 'value' => $ogp_setting->extra));?>
    </div>
    <p class="center">
      <?php echo form_hidden('ticket', $ticket);?>
      <?php echo form_submit(array('name' => 'update_ogp', 'value' => '設定を更新する'));?>
    </p>
  </div>
  <?php echo form_close();?>
  </div>

  <!-- pretty URL, favicon -->
  <div class="sz_tab_content">
  <h3>短縮URL(mod_rewrite)設定</h3>
  <div class="mod_rewrite_setting">
    アクセスURIからindex.phpを取り除きます。<br />
    <?php if ($site->enable_mod_rewrite > 0):?>
     短縮URLが設定されています。
    <p class="sz_button clearfix">
      <a href="javascript:void(0)" id="remove_mod_rewrite" class="button_left">
        <span>短縮URLの解除</span>
      </a>
    </p>
    <?php else:?>
    <form id="mod_rewrite_code">
      <p>以下のコードを「.htaccess」としてルートディレクトリに配置してください。</p>
      <?php echo form_textarea(array('id' => 'mod_rewrite_text', 'name' => 'mod_rewrite_txt', 'value' => $rewrite_txt));?>
      <span style="color:#c00">この操作を行うにはmod_rewriteモジュールが使用できる必要があります。</span>
      <p class="sz_button clearfix">
        <a href="javascript:void(0)" id="use_mod_rewrite" class="button_left">
          <span>ファイルを設置しました</span>
        </a>
      </p>
    </form>
    <?php endif;?>
  </div>
  <h3>favicon管理</h3>
  <div class="favicon_setting">
    現在使用しているfavicon：<span id="favicon_area"><?php echo write_favicon();?></span><br />
    16x16pxの.icoファイルをアップロードしてください。<br />
    <iframe src="<?php echo page_link()?>dashboard/site_settings/base/favicon_upload" frameborder="0" scrolling="no" style="width:450px;height:40px;overflow:hidden;"></iframe>
  </div>
  </div>

  <div class="sz_tab_content">
  <!-- cache setting -->
  <h3>サイトキャッシュの設定</h3>
  <div class="cache_setting">
    一般アクセスユーザー向けのキャッシュを生成します。<br />
    デバッグ時にはオフにセットしてください。<br />
    現在の設定：&nbsp;<span style="color:#c00;">キャッシュを使用<?php echo ($site->enable_cache > 0) ? 'する' : 'しない';?></span>
    <p id="sz_cache_setting_message" style="display:none" class="processing">処理中..</p>
    <p class="sz_button clearfix">
      <a href="javascript:void(0)" id="change_site_cache_btn" class="button_left" rel="<?php echo $site->enable_cache;?>">
        <span>キャッシュを<?php echo ($site->enable_cache > 0) ? 'オフ' : 'オン';?>にする</span>
      </a>
    </p>
  </div>

  <!-- delete cache -->
  <h3>サイトキャッシュの削除</h3>
  <div class="cache_setting">
     一般アクセスユーザー向けのキャッシュファイルを削除します。<br />
    リロードしても画面が更新されない場合等に実行してください。
    <p id="sz_cache_delete_message" style="display:none" class="processing">処理中..</p>
    <p class="sz_button clearfix">
      <a href="javascript:void(0)" id="delete_site_cache_btn" class="button_left">
        <span>サイトキャッシュの削除</span>
      </a>
    </p>
  </div>

  <!-- system logging -->
  <h3>システムログ設定</h3>
  <div class="cache_setting">
    データベースエラーやシステムエラー、404ページの検出、メールなどシステム上のログを保存します。<br />
    システム負荷を下げる場合はオフにしてください。
    <p class="logging_level">
      ログ保存レベル：<?php echo form_dropdown('log_level', $log_lists['level'], $site->log_level, 'id="log_level"');?>&nbsp;&nbsp;
      <?php foreach ( $log_lists['message'] as $key => $msg):?>
      <span<?php echo ($key != $site->log_level) ? ' style="display:none"' : '';?>><?php echo $msg;?></span>
      <?php endforeach;?>
    </p>
    <p id="sz_log_setting_message" style="display:none" class="processing">処理中..</p>
    <p class="sz_button clearfix">
      <a href="javascript:void(0)" id="update_log_level" class="button_left">
        <span>ロギングレベル更新</span>
      </a>
    </p>
  </div>

  <!-- debug level -->
  <h3>デバッグレベル設定</h3>
  <div class="cache_setting">
    システムプロファイラとエラーメッセージの表示を制御します。
    <p class="logging_level">
      デバッグレベル：<?php echo form_dropdown('debug_lecel', $debug_level['level'], $site->debug_level, 'id="debug_level"');?>&nbsp;&nbsp;
      <?php foreach ( $debug_level['message'] as $key => $msg):?>
      <span<?php echo ($key != $site->debug_level) ? ' style="display:none"' : '';?>><?php echo $msg;?></span>
      <?php endforeach;?>
    </p>
    <p id="sz_debug_setting_message" style="display:none" class="processing">処理中..</p>
    <p class="sz_button clearfix">
      <a href="javascript:void(0)" id="update_debug_level" class="button_left">
        <span>デバッグレベル更新</span>
      </a>
    </p>
  </div>
  </div>
  
  <div class="sz_tab_content">
  <!-- accept regisration -->
  <h3>メンバー登録設定</h3>
  <div class="mod_rewrite_setting">
    サイト内でメンバー登録を受け付けるかの設定が行えます。<br />
    <?php if ( isset($site->is_accept_member_registration) && $site->is_accept_member_registration > 0 ):?>
    メンバー登録を受け付けています。<br />
    <span style="color:#1EB0FF">※登録ページ、ログインページのナビゲーションに表示させる設定を確認してください。</span>
    <p class="sz_button clearfix">
      <a href="<?php echo page_link();?>dashboard/site_settings/base/update_accept_registration/0" id="no_accept_regist" class="button_left">
        <span>メンバー登録を許可しない</span>
      </a>
    </p>
    <?php else:?>
    <span style="color:#c00">メンバー登録を受け付けていません。</span>
    <p class="sz_button clearfix">
      <a href="<?php echo page_link();?>dashboard/site_settings/base/update_accept_registration/1" id="accept_regist" class="button_left">
        <span style="margin-left:-2px;">メンバー登録を許可する</span>
      </a>
    </p>
    <?php endif;?>
  </div>
  
  <h3>モバイルキャリア設定</h3>
  <?php echo form_open('dashboard/site_settings/base/update_enable_carrier');?>
    <fieldset id="enable_carriers">
      <p style="line-height:1.6">
         有効にしているものはアクセスキャリアに応じて表示の切り替え、
         また各キャリアの対応するページの編集が行えるようになります。<br />
        <span style="color:#c00">※キャリア毎に対応したテンプレートファイルが必要になります。</span>
      </p>
      <dl<?php echo ( isset($site->enable_mobile) && $site->enable_mobile > 0) ? ' class="enable"' : '';?>>
        <dt><?php echo form_checkbox('enable_mobile', 1, ( isset($site->enable_mobile) && $site->enable_mobile > 0) ? TRUE : FALSE);?></dt>
        <dd>フィーチャーフォンを有効にする</dd>
      </dl>
      <dl<?php echo ( isset($site->enable_smartphone) && $site->enable_smartphone > 0) ? ' class="enable"' : '';?>>
        <dt><?php echo form_checkbox('enable_smartphone', 1, ( isset($site->enable_smartphone) && $site->enable_smartphone > 0) ? TRUE : FALSE);?></dt>
        <dd>スマートフォンを有効にする</dd>
      </dl>
      <p class="clear submission">
        <?php echo form_hidden('ticket', $ticket);?>
        <?php echo form_submit(array('value' => '有効設定を更新する'));?>
      </p>
    </fieldset>
  <?php echo form_close();?>
  </div>

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
