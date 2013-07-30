<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブログ管理</h2>
<div id="main">

  <?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
  <?php endif;?>
  
  <h3>下書き保存された記事一覧</h3>
  <p class="customize"></p>
  <table cellpadding="0" cellspacing="0">
    <tr>
      <td>タイトル</td>
      <td>下書き保存日時</td>
      <td>カテゴリ</td>
      <td>投稿者</td>
      <td style="text-align:right">操作</td>
    </tr>
    <?php if (count($entry) > 0):?>
    <?php foreach ($entry as $key => $value):?>
    <tr<?php if($key %2 === 0){ echo ' class="odd"';}?>>
      <td class="tooltip">
        <span><?php echo prep_str(truncate($value->title, 20, FALSE));?></span>
        <div class="init_hide"><?php echo prep_str($value->title);?></div>
       </td>
      <td><?php echo ( $value->update_date === '0000-00-00 00:00:00' ) ? $value->entry_date : $value->update_date;?></td>
      
      <?php if ( isset($category[$value->sz_blog_category_id]) ):?>
      <td><?php echo $category[$value->sz_blog_category_id];?></td>
      <?php else:?>
      <td><span style="color : #c00">削除されたカテゴリ</span></td>
      <?php endif;?>
      
      <?php if ( ! empty($value->user_name) ):?>
      <td><?php echo prep_str($value->user_name);?></td>
      <?php else:?>
      <td><span style="color:#c00">削除しないユーザー</span></td>
      <?php endif;?>
      
      <td class="action">
        <a href="<?php echo page_link()?>dashboard/blog/edit/index/<?php echo $value->sz_blog_id;?>" class="edit">編集</a>
        <a href="<?php echo page_link()?>dashboard/blog/drafts/delete/<?php echo $value->sz_blog_id?>" class="delete">削除</a>
      </td>
    </tr>
    <?php endforeach;?>
    <?php else:?>
    <tr>
      <td>投稿がありません。</td>
      <td colspan="4" class="action">&nbsp;</td>
    </tr>
    <?php endif;?>
  </table>
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
