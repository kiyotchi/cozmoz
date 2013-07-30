<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>カレンダー管理</h2>
  <div id="main">
    <h3>カレンダー詳細</h3>
    <p class="customize"><?php echo anchor('dashboard/wallpaper/cal_entries', set_image('back.png', TRUE) . '&nbsp;カレンダー一覧へ戻る')?></p>
    <table class="confirm">
      <tbody>
        <tr>
          <td>投稿ID</td>
          <td class="action"><?php echo (int)$entry->wall_cal_dat_id;?></td>
        </tr>
        <tr class="odd">
          <td>投稿日時</td>
          <td class="action"><?php echo $entry->entry_date;?></td>
        </tr>
        <tr>
          <td>タイトル</td>
          <td class="action"><?php echo $entry->title;?></td>
        </tr>
        <tr class="odd">
          <td>メーカー</td>

          <?php if ( isset($category[$entry->wall_maker_id]) ):?>
          <td class="action"><?php echo $category[form_prep($entry->wall_maker_id)];?></td>
          <?php else:?>
          <td class="action"><span style="color : #c00">未登録</span></td>
          <?php endif;?>

        </tr>
       <tr class="odd">
        <td>カレンダーイメージ</td>
        <td>
        <table border="0" style="width:400px">
        	<tr>
        		<td colspan="2">携帯電話用</td>
        		<td colspan="2">スマートフォン用</td>
        	</tr>
        	<tr>
        		<td><a href="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/tn_hp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/tn_hp_crypt_name')?>" border="0"></a></td>
        		<td><a href="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/hp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/hp_crypt_name')?>" border="0" width="100"></a></td>
        		<td><a href="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/tn_sp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/tn_sp_crypt_name')?>" border="0" width="100"></a></td>
        		<td><a href="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/sp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/calendar/'.$entry->wall_cal_dat_id.'/sp_crypt_name')?>" border="0" width="100"></a></td>
        	</tr>
        	<tr>
        		<td colspan="4">※画像をクリックすると、オリジナルサイズで表示されます。</td>
        	</tr>
        </table>
        </td>
      </tr>
        <tr>
          <td colspan="2">本文</td>
        </tr>
        <tr class="odd">
          <td class="action blog_confirm" colspan="2">
            <div>
              <?php echo nl2br($entry->body);?>
            </div>
          </td>
        </tr>
      <tr>
        <td>公開日時</td>
        <td class="action">
          <?php echo $entry->public_datetime;?>
        </td>
      </tr>
       <tr>
        <td>課金設定</td>
        <td>
         <table border="0" style="width:400px">
        	<tr>
        		<td>docomo</td>
        		<td>AU</td>
        		<td>softbank</td>
        	</tr>
        	<tr>
        		<td>
		          <?php if ((int)$entry->mobile_docomo === 1):?>
		             課金する
		          <?php else:?>
		          <span style="color:red">課金しない</span>
		          <?php endif;?>
        		</td>
        		<td>
 		          <?php if ((int)$entry->mobile_au === 1):?>
		             課金する
		          <?php else:?>
		          <span style="color:red">課金しない</span>
		          <?php endif;?>
        		</td>
        		<td>
		          <?php if ((int)$entry->mobile_softbank === 1):?>
		             課金する
		          <?php else:?>
		          <span style="color:red">課金しない</span>
		          <?php endif;?>
        		</td>
        	</tr>

        </table>

        </td>
      </tr>
      </tbody>
    </table>
    <div class="custom_area">
      <?php echo anchor('dashboard/wallpaper/cal_edit/index/' . $entry->wall_cal_dat_id, '編集する');?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?php echo anchor('dashboard/wallpaper/cal_entries/delete/' . $entry->wall_cal_dat_id, '削除する');?>
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
