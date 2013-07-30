<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="robots" content="noindex,nofollow" />
<title>Seezoo admin panel</title>

<!-- CSS -->
<link href="<?php echo file_link();?>css/dashboard.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo file_link();?>css/ajax_styles.css" rel="stylesheet" type="text/css" media="screen" />
<?php if (ADVANCE_UA === 'ie6'):?>
<link rel="stylesheet" type="text/css" href="<?php echo file_link();?>css/edit_base_advance_ie6.css" />
<?php elseif (ADVANCE_UA === 'ie7'):?>
<link rel="stylesheet" type="text/css" href="<?php echo file_link();?>css/edit_base_advance_ie7.css" />
<?php endif;?>
<?php echo write_favicon(TRUE);?>
<?php echo output_css('header');?>
<?php echo flint_execute(($this->router->is_packaged_directory) ? 'view' : 'segment');?>
<?php echo output_javascript('header');?>
</head>

<body>
	<div id="wrapper">
	<!-- h1 tag stays for the logo, you can use the a tag for linking the index page -->

        <!-- You can name the links with lowercase, they will be transformed to uppercase by CSS, we prefered to name them with uppercase to have the same effect with disabled stylesheet -->
        <ul id="mainNav">
          <li><a href="<?php echo page_link();?>dashboard/panel" class="active">Seezoo&nbsp;DASHBOARD</a></li> <!-- Use the "active" class for the active menu item  -->
          
          <?php if ($this->is_maintenance_mode === TRUE):?>
          <li><a class="maintenance" href="<?php echo page_link()?>dashboard/site_settings">現在、メンテナンスモードに設定されています！</a></li>
          <?php endif;?>
          
          <li class="logout"><a href="<?php echo page_link('logout')?>">ログアウト</a></li>
          <li class="logout"><a href="<?php echo page_link()?>">サイトに戻る</a></li>
          <?php if ( $this->session->userdata('rollback_user') ):?>
          <li class="logout"><a href="<?php echo page_link('dashboard/panel/rollback_user')?>">元のユーザに戻る</a></li>
          <?php endif;?>
        </ul>
        <!-- // #end mainNav -->
        <div id="containerHolder">
          <?php if (!isset($sidebar) || $sidebar !== FALSE):?>
          <div id="container">
            <div id="sidebar">
              <?php echo build_dashboard_menu();?>
              <!-- // .sideNav -->
              <!--  // additional side menu-->
              <?php if (isset($ext_list)):?>
              <div id ="file_wrapper">
                <p class="search_file">
                  <a href="javascript:void(0)" id="search_file_toggle"><?php echo set_image('search.png', TRUE);?>ファイル検索</a>
                </p>
                <div class="sz_file_search_box">
                  <?php echo form_open('dashboard/files/file_list/search');?>
                    <dl>
                      <dt><label for="search_file_name">ファイル名</label></dt>
                      <dd><?php echo form_input(array('name' => 'file_name', 'id' => 'search_file_name'));?></dd>
                      <dt><label for="search_file_ext">拡張子</label></dt>
                      <dd><?php echo form_dropdown('file_ext', $ext_list, FALSE, 'id="search_file_ext"');?></dd>
                      <dt><label for="search_file_group">ファイルグループ</label></dt>
                      <dd>
                        <select name="file_group">
                          <option value="0">---</option>
                          <?php foreach ($group_list as $key => $value):?>
                          <option value="<?php echo $key;?>"><?php echo form_prep($value);?></option>
                          <?php endforeach;?>
                        </select>
                      </dd>
                    </dl>
                    <p class="submit">
                      <?php echo form_submit(array('value' => '検索'));?>
                    </p>
                  <?php echo form_close();?>
                </div>
              </div>
              <?php endif;?>
              <!--  // additional side menu-->
            </div>
            <!-- // #sidebar -->
            <?php else:?>
            <div id="container_full">
            <?php endif;?>
