<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>下書きブロック管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

<h3>下書き保存されたブロックの一覧</h3>
<?php if ($draft_block_count > 0):?>
  <p class="additional"><?php echo $draft_block_count;?>個のブロックが保存されています。<br />
    <span style="color:red">※CSSの関連のため、実際の表示形式と異なります。</span>
  </p>
  
  <?php foreach ($draft_block_list as $block):?>
  <h4 class="draft_block_caption">
    <?php if ( ! empty($block->alias_name) ):?>
    <?php echo prep_str($block->alias_name);?>
    <?php else:?>
    <?php echo prep_str($block->collection_name);?>ブロック
    <?php endif;?>
    <a href="javascript:void(0)" class="edit" id="block_<?php echo $block->draft_blocks_id?>" rel="draft">名前変更</a>
    <a href="javascript:void(0)" class="view">詳細</a>
    <a href="<?php echo page_link();?>ajax/delete_draft_block/<?php echo $block->draft_blocks_id;?>" class="delete"><?php echo set_image('delete.png', TRUE);?>&nbsp;削除</a>
  </h4>
  <div class="draft_block_content">
    <div class="draft_block_inner">
      <?php echo $this->load->block($block->collection_name, $block->block_id);?>
    </div>
  </div>
  <?php endforeach;?>
  
  <?php else:?>
  <p>下書きブロックは登録されていません。</p>
  <?php endif;?>
  

<h3>共有ブロック一覧</h3>
<?php if ($static_blocks_count > 0):?>
  <p class="additional"><?php echo $static_blocks_count;?>個のブロックが保存されています。<br />
    <span style="color:red">※CSSの関連のため、実際の表示形式と異なります。</span>
  </p>
  
  <?php foreach ($static_blocks as $block):?>
  <h4 class="draft_block_caption">
    <?php echo ( ! empty($block->alias_name) ) ? prep_str($block->alias_name) : prep_str($block->collection_name) . 'ブロック';?>
    <?php if ( $block->add_user_id == $this->user_id ):?>
    <a href="javascript:void(0)" class="edit" id="block_<?php echo $block->static_block_id?>" rel="static">名前変更</a>
    <a href="<?php echo page_link();?>ajax/delete_static_block/<?php echo $block->static_block_id;?>" class="delete static"><?php echo set_image('delete.png', TRUE);?>&nbsp;削除</a>
    <?php endif;?>
    <a href="javascript:void(0)" class="view">詳細</a>
  </h4>
  <div class="draft_block_content">
    <div class="draft_block_inner">
      <?php echo $this->load->block($block->collection_name, $block->block_id);?>
    </div>
  </div>
  <?php endforeach;?>
  
  <?php else:?>
  <p>共有ブロックは登録されていません。</p>
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
