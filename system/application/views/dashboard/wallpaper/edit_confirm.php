<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>壁紙管理</h2>
<div id="main">

<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>

<?php if ($org_wall_cal_dat_id > 0):?>
  <h3>壁紙内容の確認</h3>
  <h4 class="confirm_msg">編集内容を確認してください。</h4>
<?php else:?>
  <h3>壁紙内容の確認</h3>
  <h4 class="confirm_msg">登録内容を確認してください。</h4>
<?php endif;?>

  <table class="confirm">
    <tbody>
      <tr class="odd">
        <td>メーカー</td>
        <td class="action"><?php if($entry->wall_maker_id != '') echo $category[form_prep($entry->wall_maker_id)];?></td>
      </tr>
      <tr>
        <td>タイトル</td>
        <td class="action"><?php echo $entry->title;?></td>
      </tr>

       <tr class="odd">
        <td>TOPイメージ</td>
        <td>
        <table border="0" style="width:400px">
        	<tr>
        		<td colspan="2">携帯電話用</td>
        		<td colspan="2">スマートフォン用</td>
        	</tr>
        	<tr>
        		<td><a href="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/tn_hp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/tn_hp_crypt_name')?>" border="0"></a></td>
        		<td><a href="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/hp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/hp_crypt_name')?>" border="0" width="100"></a></td>
        		<td><a href="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/tn_sp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/tn_sp_crypt_name')?>" border="0" width="100"></a></td>
        		<td><a href="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/sp_crypt_name')?>" class="sz_zoom"><img src="<?php echo page_link('d_files/tmp_wallpaper/'.$entry->wall_cal_dat_id.'/sp_crypt_name')?>" border="0" width="100"></a></td>
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
  <div class="submit_area">
  <?php echo form_open('dashboard/wallpaper/edit/index/'.$org_wall_cal_dat_id, array('class' => 'inline'))?>
	<?php echo form_hidden('tmp_wall_cal_dat_id', $entry->wall_cal_dat_id);?>
    <?php echo form_hidden($this->ticket_name, $ticket);?>
    <?php echo form_submit(array('name' => 'modify', 'value' => '入力画面に戻る'));?>
  <?php echo form_close();?>&nbsp;&nbsp;

  <?php echo form_open('dashboard/wallpaper/edit/do_edit', array('class' => 'inline'))?>
	<?php echo form_hidden('tmp_wall_cal_dat_id', $entry->wall_cal_dat_id);?>
	<?php echo form_hidden('org_wall_cal_dat_id', $org_wall_cal_dat_id);?>
    <?php echo form_hidden($this->ticket_name, $ticket);?>
    <?php echo form_submit(array('name' => 'regist', 'value' => '編集を確定する'));?>
  <?php echo form_close();?>
  </div>
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
