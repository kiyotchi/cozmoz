<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$current_mode = $this->config->item('final_output_mode');
$enable_mb    = $this->site_data->enable_mobile;
$enable_sp    = $this->site_data->enable_smartphone;
$mode         = 'PC';
if ( $current_mode === 'sp' && $enable_sp > 0 )
{
	$mode = 'スマートフォン';
}
else if ( $current_mode === 'mb' && $enable_mb > 0 )
{
	$mode = 'フィーチャーフォン';
}
?>
<!-- Start CMS edit menu -->
<div class="cmsi_menu" style="top:<?php echo $menu_y;?>px;left:<?php echo $menu_x;?>px;">
  <div class="sz_menu_rad_top">
    <div class="sz_rad_top_right">&nbsp;</div>
    <p>
      <a href="javascript:void(0)" id="sz_edit_menu_close">&nbsp;</a>
    </p>
  </div>
  <div id="sz_menu_wrapper">
    <div class="sz_menu_inner">
      <p class="mode_state <?php echo $current_mode;?>">
        <?php echo ($this->edit_mode == 'EDIT_SELF') ? '編集' : 'ビュー';?><br />
      </p>
      <ul id="sz_menu_tools" class="clearfix">
        <li class="sz_menu_tools_edit">
        
          <?php if ($this->edit_mode == 'EDIT_SELF'):?>
          <a href="javascript:void(0)" id="sz_edit_out">
            <span>編集モード終了</span>
          </a>
          <div class="sz_tooltip_right">編集モードを終了します。</div>
          <?php elseif ($this->edit_mode === 'EDIT_OTHER'):?>
          <a href="javascript:void(0)" id="other_editting">
            <span>他ユーザーが編集中</span>
          </a>
          <div class="sz_tooltip_right">このページは他のユーザーが編集中です。</div>
          <?php elseif($this->can_edit === TRUE):?>
          <?php echo form_open(get_base_link() . 'page/set_edit/' . $this->page_id, array('style' => 'display:none'));?>
          <?php echo form_hidden('redirect_path', current_url());?>
          <?php echo form_close();?>
          <a href="<?php echo get_base_link();?>page/set_edit/<?php echo $this->page_id;?>" id="to_edit">
            <span>編集モードへ</span>
          </a>
          <div class="sz_tooltip_right">編集モードへ移動します。</div>
          <?php else:?>
          <a href="javascript:void(0)" id="edit_forbidden">
            <span>編集不可</span>
          </a>
          <div class="sz_tooltip_right">このページには編集権限がありません。</div>
          <?php endif;?>
        
        </li>

        <?php if ($this->edit_mode === 'EDIT_SELF'):?>
        <li>
          <a href="<?php echo get_base_link();?>ajax/page_config/<?php echo $this->page_id;?>" id="sz_pageconfig" class="sz_zoom sz_pc">
            <span>ページ設定</span>
          </a>
          <div class="sz_tooltip_left">ページ情報や権限、使用テンプレートを変更します。</div>
        </li>
        <li>
          <a href="<?php echo get_base_link();?>ajax/get_versions/<?php echo $this->page_id?>" class="sz_zoom sz_pv">
            <span>バージョン</span>
          </a>
          <div class="sz_tooltip_right">ページのバージョン情報を確認します。</div>
        </li>
        <li>
          <a href="javascript:void(0)" id="edit_preview">
            <span>プレビュー</span>
          </a>
          <div class="sz_tooltip_left">編集領域を消してプレビューを行います。</div>
        </li>
        <li>
          <a href="javascript:void(0)" id="custom_css">
            <span>カスタムCSS</span>
          </a>
          <div class="sz_tooltip_right">テンプレート独自のCSSを作成します。</div>
        </li>
        <li>
          <a href="javascript:void(0)" id="arrange_mode">
            <span>移動モード</span>
          </a>
          <div class="sz_tooltip_left">移動モードに移行します。</div>
        </li>
        <li>
          <a href="javascript:void(0)" id="sz_block_delete_mode">
            <span>削除モード</span>
          </a>
          <div class="sz_tooltip_right">ブロック削除モードに移行します。</div>
        </li>
        <?php if ( $enable_mb > 0 || $enable_sp > 0 ):?>
        <li>
          <a href="javascript:void(0)" id="sz_change_viewmode">
            <span>表示モード</span>
          </a>
          <div class="sz_tooltip_left">表示モードを切り替えます。</div>
        </li>
        <?php endif;?>

      <?php elseif($this->can_edit === TRUE):?>

        <li>
          <a href="<?php echo get_base_link();?>ajax/get_versions/<?php echo $this->page_id?>" class="sz_zoom sz_pv">
            <span>バージョン</span>
          </a>
          <div class="sz_tooltip_left">ページのバージョン情報を確認します。</div>
        </li>
        <li>
          <a href="javascript:void(0)" id="sz_sitemap">
            <span>移動</span>
          </a>
          <div class="sz_tooltip_right">サイトマップからページを移動します。</div>
        </li>
        <?php if ( $enable_mb > 0 || $enable_sp > 0 ):?>
        <li>
          <a href="javascript:void(0)" id="sz_change_viewmode">
            <span>表示モード</span>
          </a>
          <div class="sz_tooltip_left">表示モードを切り替えます。</div>
        </li>
        <?php endif;?>

        <?php if ($this->router->fetch_class() === 'page'):?>
        
        <li>
          <a href="<?php echo get_base_link();?>ajax/add_page/<?php echo $this->page_id;?>" id="sz_addpage" class="sz_zoom">
            <span>ページ作成</span>
          </a>
          <div class="sz_tooltip_<?php echo ( $enable_mb > 0 || $enable_sp > 0 ) ? 'right' : 'left';?>">このページを親とする子ページを作成します。</div>
        </li>
        
        <?php endif;?>
        
      <?php else:?>
      
        <li>
          <a href="javascript:void(0)" id="sz_sitemap">
            <span>移動</span>
          </a>
          <div class="sz_tooltip_left">サイトマップからページを移動します。</div>
        </li>
        <?php if ( $enable_mb > 0 || $enable_sp > 0 ):?>
        <li>
          <a href="javascript:void(0)" id="sz_change_viewmode">
            <span>表示モード</span>
          </a>
          <div class="sz_tooltip_right">表示モードを切り替えます。</div>
        </li>
        <?php endif;?>
        
      <?php endif;?>
      </ul>

      <ul class="manage clearfix">
        <li>
          <a href="<?php echo page_link();?>dashboard/panel" class="sz_menu_admin">
            <span>管理画面へ</span>
          </a>
        </li>
        <li>
          <a href="<?php echo page_link();?>logout" class="sz_menu_logout">
            <span>ログアウト</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <div class="sz_menu_rad_bottom">&nbsp;</div>
</div>

<!--  advance css editor -->
<div id="sz_advance_css">
  <div class="ac_wrapper">
    <p>カスタムCSSを定義して更新ボタンを押してください。</p>
    <?php echo form_open(get_base_link() . 'page/update_advance_css', array('id' => 'advance_css_form'));?>
    <?php echo form_textarea(array('name' => 'advance_css', 'value' => $advance_css));?>
    <p class="sz_adcss_update_btn">
    <?php echo form_hidden('template_id', $template_id);?>
    <input type="button" id="sz_ac_submit" value="更新" />
    </p>
    <?php echo form_close();?>
    <a href="javascript:void(0)" id="sz_ac_close"><?php echo set_image('ppbox/close.png', TRUE);?></a>
  </div>
</div>

<!-- move sitemap sub contents -->
<div id="sz_sub_pp">
  <div class="sub_lt">&nbsp;</div>
  <div class="sub_tc">&nbsp;</div>
  <div class="sub_rt">&nbsp;</div>
  <div class="sub_lc">&nbsp;</div>
  <div class="sub_rc">&nbsp;</div>
  <div class="sub_lb">&nbsp;</div>
  <div class="sub_bc">&nbsp;</div>
  <div class="sub_rb">&nbsp;</div>
  <div id="sz_sub_pp_content"></div>
  <a id="sz_sub_pp_close" href="javascript:void(0)"><?php echo set_image('ppbox/close.png', TRUE);?></a>
</div>

<!--  destroy or scrap or publish form -->
<div id="sz_dsp_form">
  <div class="sz_dsp_form_content"></div>
  <a href="javascript:void(0)" id="sz_dsp_close"><?php echo set_image('ppbox/dsp_tab.png', TRUE);?></a>
</div>

<?php if ($this->edit_mode === 'EDIT_SELF'):?>

<!-- block edit menus-->
<div id="sz_block_edit_menu">
  <ul class="block_config">
    <li><a href="javascript:void(0)" class="sz_block_to_draft">下書きに保存</a></li>
    <li><a href="javascript:void(0)" class="sz_block_custom_template">表示変更</a></li>
    <li><a href="javascript:void(0)" class="sz_block_permission">権限設定</a></li>
    <li><a href="javascript:void(0)" class="sz_block_to_static">共有ブロックに登録</a>
    <li><a href="javascript:void(0)" class="sz_block_to_blockset">ブロックセットに追加</a></li>
  </ul>
</div>
<div id="sz_block_draft_static_namespace">
	<h3>&nbsp;</h3>
	<p>登録名を入力してください。</p>
	<form>
		<fieldset>
		 <?php echo form_input(array('name' => 'recognize_name', 'id' => 'recognize_name'));?>
		 <p><button>登録する</button></p>
		</fieldset>
	</form>
	<a href="javascript:void(0);"></a>
</div>
<!-- END OF edit menu area -->

<?php endif;?>

<?php if ($this->is_edit_timeout !== FALSE):?>

<!--  Start unlock message -->
<div id="sz_edit_timeout_link">
  <p>
    このページは編集モードのまま1時間が経過しています。
    <?php echo anchor(get_base_link() . 'page/unlock_edit_page/' . $this->page_id . '/' . $this->is_edit_timeout, '編集ロックを解除する');?>
  </p>
</div>
<!--  END unlock message -->

<?php elseif ($this->edit_mode === 'EDIT_OTHER' && $this->is_master === TRUE):?>

<!--  Start unlock message -->
<div id="sz_edit_timeout_link">
  <p>
    管理者権限により編集モードを強制開放できます。
    <?php echo anchor(get_base_link() . 'page/unlock_edit_page/' . $this->page_id, '編集ロックを解除する');?>
  </p>
</div>
<!--  END unlock message -->

<?php endif;?>

<?php if ( $enable_mb > 0 || $enable_sp > 0 ):?>

<!--  Start view mode window -->
<div id="sz_view_mode_window">
  <h4>表示モードを切り替えます。</h4>
  <p class="current_viewmode center">現在のモード：<span><?php echo $mode;?></span></p>
  <ul class="sz_view_mode_list">
    <li class="sz_view_pc"><a href="<?php echo page_link('action/change_view_mode/' . $this->page_id . '/pc')?>">PCモード</a></li>
    
    <?php if ( $enable_sp > 0 ):?>
    <li class="sz_view_sp"><a href="<?php echo page_link('action/change_view_mode/' . $this->page_id . '/sp')?>">スマートフォンモード</a></li>
    <?php endif;?>
    
    <?php if ( $enable_mb > 0 ):?>
    <li class="sz_view_mb"><a href="<?php echo page_link('action/change_view_mode/' . $this->page_id . '/mb')?>">フィーチャーフォンモード</a></li>
    <?php endif;?>
    
  </ul>
  <a href="javascript:void(0)" class="close">&nbsp;</a>
</div>
<!-- END view mode window -->

<?php endif;?>

