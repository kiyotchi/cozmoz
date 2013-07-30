<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブロックセット管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

<h3>登録されているブロックセット一覧</h3>
<?php if ($block_set_count > 0):?>
  <p class="additional"><?php echo $block_set_count;?>個のブロックセットが登録されています。</p>
  
  </p>
  
  <table cellpadding="0" cellspacing="0">
    <tbody>
      <tr>
        <td>ブロックセット名</td>
        <td>登録ブロック数</td>
        <td class="action">操作</td>
      </tr>
      <?php foreach ( $block_set as $key => $set ):?>
      <tr<?php echo ( $key % 2 === 0 ) ? ' class="odd"' : ''?>>
        <td><?php echo prep_str($set->master_name);?></td>
        <td><?php echo $set->block_count;?>個</td>
        <td class="action">
           <a href="javascript:void(0)" class="edit" id="masterid_<?php echo $set->block_set_master_id;?>">名前変更</a>
           <a href="<?php echo page_link('dashboard/blocks/block_set/detail_edit/' . $set->block_set_master_id);?>" class="view">詳細</a>
           <a href="<?php echo page_link('dashboard/blocks/block_set/delete_block_set_master/' . $set->block_set_master_id);?>" class="delete">削除</a>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
<?php else:?>
<p>ブロックセットは登録されていません。</p>
<?php endif;?>

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
<div id="sz_block_draft_static_namespace">
	<h3>登録名の変更</h3>
	<p>登録名を入力してください。</p>
	<form action="" method="post">
		<fieldset>
		 <?php echo form_input(array('name' => 'rec_name', 'id' => 'rec_name'));?>
		 <input type="hidden" name="id" id="block_id" />
		 <p><?php echo form_submit(array('value' => '変更する'))?></p>
		</fieldset>
	</form>
	<a href="javascript:void(0);"></a>
</div>
</body>
</html>
