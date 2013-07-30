<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>ブロックセット管理</h2>
<div id="main">

<?php if (!empty($this->msg)):?>
  <div class="message"><?php echo $this->msg;?></div>
<?php endif;?>

<h3>ブロックセット「<?php echo prep_str($set->master_name);?>」に登録されているブロックリスト</h3>
<p class="additional">
  ドラッグ&ドロップで追加順を変更できます。<br />
</p>
<p class="additional">
  <a href="<?php echo page_link('dashboard/blocks/block_set');?>">
    <?php echo set_image('back.png', TRUE);?>&nbsp;ブロックセットマスタ一覧に戻る
  </a>
</p>
<br />
<?php if ( count($set->blocks) > 0 ):?>
<?php foreach ( $set->blocks as $block ):?>
<div class="blockset sortable" data-blocksetid="<?php echo $block->block_set_data_id;?>">
  <h4 class="draft_block_caption">
    <?php echo prep_str($block->block_name);?>
    <a href="javascript:void(0)" class="view">詳細</a>
    <a href="<?php echo page_link('dashboard/blocks/block_set/delete_block_set_data/' . $block->block_set_data_id);?>" class="delete"><?php echo set_image('delete.png', TRUE);?>&nbsp;削除</a>
  </h4>
  <div class="draft_block_content">
    <div class="draft_block_inner">
      <?php echo $this->load->block($block->collection_name, $block->block_id);?>
    </div>
  </div>
</div>
<?php endforeach;?>
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
</body>
</html>
