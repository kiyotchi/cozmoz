<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ページ管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

  <h3>システムページ管理</h3>
  <p class="info" style="border-top : none">
    コントローラから生成される管理ページの一覧です。
  </p>
  
  <h3>フロントエンドページ</h3>
  <div id="system_pages_wrapper">
  <?php if ( count($frontend_pages) > 0 ):?>
    <?php foreach ( $frontend_pages as $key => $value ):?>
  <div class="systempage_section" id="systempage_<?php echo $value->page_id;?>">
    <p class="top dashboard_page">
      <?php echo anchor($value->page_path, prep_str($value->page_title));?>
    </p>
    <p class="configure">
      <a href="<?php echo page_link()?>dashboard/pages/system_page/rescan/<?php echo $value->page_id?>" class="view">更新</a>
      <a href="<?php echo page_link()?>dashboard/pages/system_page/page_config/<?php echo $value->page_id?>" class="edit">設定</a>
      <a href="<?php echo page_link()?>dashboard/pages/system_page/delete/<?php echo $value->page_id?>" class="delete">削除</a>
    </p>
  </div>
  <?php endforeach;?>
  <?php else:?>
  <p>システムページはありません。</p>
  <?php endif;?>
  </div>
  <p class="info" style="padding-left:30px;">
    フロントエンドページの表示順は<?php echo anchor('dashboard/pages/page_list', '一般ページ管理')?>で変更できます。
  </p>
  
  
  <h3>管理画面ページ</h3>
  
  
  <div id="system_pages_wrapper">
  <?php if ( count($system_pages) > 0 ):?>
  <?php foreach ( $system_pages as $key => $value ):?>
  <div class="systempage_section" id="systempage_<?php echo $value->page_id;?>">
    <p class="top<?php echo (strpos($value->page_path, 'dashboard') !== FALSE) ? ' dashboard_page"' : '';?>">
      <?php echo anchor($value->page_path, prep_str($value->page_title));?>
    </p>
    <p class="configure">
      <a href="<?php echo page_link()?>dashboard/pages/system_page/rescan/<?php echo $value->page_id?>" class="view">更新</a>
      <a href="<?php echo page_link()?>dashboard/pages/system_page/page_config/<?php echo $value->page_id?>" class="edit">設定</a>
      <a href="<?php echo page_link()?>dashboard/pages/system_page/delete/<?php echo $value->page_id?>" class="delete">削除</a>
    </p>
    <a href="#" class="arrow_u<?php echo ($key === 0) ? ' hide' : '';?>">&nbsp;</a>
    <a href="#" class="arrow_d<?php echo ($key === count($system_pages) - 1) ? ' hide' : ''?>">&nbsp;</a>
    <?php echo build_child_page($value);?>
  </div>
  <?php endforeach;?>
  <?php else:?>
  <p>システムページはありません。</p>
  <?php endif;?>
  </div>
<!-- 
  <table>
    <tbody>
      <tr class="caption">
        <th class="icon">&nbsp;</th>
        <th>ページタイトル</th>
        <th>ページパス</th>
        <th class="action">操作</th>
      </tr>
      <?php if (count($system_pages) > 0):?>
      <?php $times = 0;?>
      <?php foreach ($system_pages as $key => $value):?>
      <tr<?php if ($times % 2 === 0) echo ' class="odd"';?>>
        <td class="icon"><?php echo (strpos($value->page_path, 'dashboard') !== FALSE) ? set_image('config.png', TRUE) : '&nbsp;';?></td>
        
        <?php if (!$value->page_title):?>
        <td><?php echo $key;?></td>
        <?php else:?>
        <td class="page_titles">
        <?php echo anchor($value->page_path, $value->page_title);?>
        </td>
        <?php endif;?>
        
        <td><?php echo $value->page_path;?></td>
        <td class="action">
          <a href="<?php echo page_link()?>dashboard/pages/system_page/rescan/<?php echo $value->page_id?>" class="view">更新</a>
          <a href="<?php echo page_link()?>dashboard/pages/system_page/page_config/<?php echo $value->page_id?>" class="edit">設定</a>
          <a href="<?php echo page_link()?>dashboard/pages/system_page/delete/<?php echo $value->page_id?>" class="delete">削除</a>
        </td>
      </tr>
      <?php $times++;?>
      <?php endforeach;?>
      <?php else:?>
      <tr><td colspan="4">ページが見つかりませんでした。</td></tr>
      <?php endif;?>
    </tbody>
  </table>
 -->
  <p class="info"><a href="<?php echo page_link()?>dashboard/pages/system_page/scan_page" id="scan_page"><?php echo set_image('search.png', TRUE);?>ページをスキャン</a>&nbsp;<span class="init_hide">スキャン中...<img src="<?php echo base_url();?>images/loading_small.gif" alt="" /></span></p>
  <div id="scaned_page_list">
    &nbsp;
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
