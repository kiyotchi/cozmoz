<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ページ管理</h2>
<div id="main">
  <h3>ページ管理</h3>
  <div class="clearfix" id="page_wrapper">
    <div id="sitemap">
      <?php echo $this->load->view('dashboard/pages/page_structure');?>
    </div>
    <div id="sitemap_search_result" style="display: none">
      <div id="sz_sitemap_search_result_box"></div>
      <p><a href="javascript:void(0)" id="toggle_box">&laquo;ツリー表示へ戻る</a></p>
    </div>
    <div id="sitemap_menu">
    <!--  sorry , not implememnt...
      <?php echo form_open('dashboard/pages/page_list/with_system');?>
      <input type="checkbox" value="1" id="with_system" name="with_system"<?php if (isset($system_pages)) echo ' checked="checked"';?> />&nbsp;システムページを表示する
      <?php echo form_close();?>
    -->
      <form id="sz_sitemap_page_search_dashboard">
        <p><label>ページタイトル:<br /><?php echo form_input(array('name' => 'page_title', 'value' => ''));?></label></p>
        <p><label>ページパス:<br /><?php echo form_input(array('name' => 'page_path', 'value' => ''));?></label></p>
        <p>
          <input type="hidden" name="from_dh" value="1" />
          <input type="button" id="sz_sitemap_search_do" value="検索" />
        </p>
      </form>
    </div>
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
