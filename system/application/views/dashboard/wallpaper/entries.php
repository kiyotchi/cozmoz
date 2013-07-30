<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>壁紙管理</h2>
<div id="main">
<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>
  <h3>壁紙一覧</h3>
  <p class="customize"><a href="<?php echo page_link()?>dashboard/wallpaper/edit/"><?php echo set_image('plus.png', TRUE)?>&nbsp;新規登録</a></p>
  <table cellpadding="0" cellspacing="0">
    <tr>
      <td>タイトル</td>
      <td>サムネール</td>
      <td>メーカー</td>
      <td>投稿者</td>
      <td style="text-align:right">操作</td>
    </tr>

    <?php if (count($entry) > 0):?>
    <?php foreach ($entry as $key => $value):?>
    <tr<?php if($key %2 === 0){ echo ' class="odd"';}?>>
      <td class="tooltip">
        <?php echo anchor('dashboard/wallpaper/entries/detail/' . $value->wall_cal_dat_id, $value->title);?>
        <div class="init_hide">
          <?php echo $value->title;?>
        </div>
      </td>
      <td><?php echo '<img src="'.page_link('d_files/wallpaper/'.$value->wall_cal_dat_id.'/tn_hp_crypt_name').'" border="0">';?></td>

      <?php if (array_key_exists($value->wall_maker_id, $category)):?>
      <td><?php echo $category[$value->wall_maker_id];?></td>
      <?php else:?>
      <td><span style="color : #c00">未登録</span></td>
      <?php endif;?>

      <?php if ( ! empty($value->user_name) ):?>
      <td><?php echo prep_str($value->user_name);?></td>
      <?php else:?>
      <td><span style="color:#c00">削除しないユーザー</span></td>
      <?php endif;?>

      <td class="action">
        <a href="<?php echo page_link()?>dashboard/wallpaper/edit/index/<?php echo $value->wall_cal_dat_id;?>" class="edit">編集</a>
        <a href="<?php echo page_link()?>dashboard/wallpaper/entries/delete_confirm/<?php echo $value->wall_cal_dat_id?>" class="delete">削除</a>
      </td>
    </tr>
    <?php endforeach;?>
    <?php else:?>
    <tr>
      <td>登録データがありません。</td>
      <td colspan="4" class="action"><a href="<?php echo page_link()?>dashboard/wallpaper/edit" class="edit"><?php echo set_image('plus.png', TRUE);?>&nbsp;新規登録</a></td>
    </tr>
    <?php endif;?>
  </table>
  <p class="pagination"><?php echo $pagination;?></p>
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
