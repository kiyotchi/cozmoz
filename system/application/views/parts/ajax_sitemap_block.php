<p class="sitemap_caption">基点ページを選択してください。</p>
<div id="sz_sitemap_wrapper">
<div id="sitemap">
 <ul>
 	<li class="ch open" id="page_1">
 		<div class="movable" pid="1">
 			<img src="<?php echo file_link();?>images/dashboard/folder.png" />
 			<span pid="<?php echo $pages[0]['page_id']?>" class="ttl"><?php echo $pages[0]['page_title'];?>
 				<span><?php if ((int)$pages[0]['childs'] > 0) echo '&nbsp;(' . (int)$pages[0]['childs'] . ')';?></span>
 			</span>
 		</div>
 		<a href="javascript:void(0)" class="open_dir oc">&nbsp;</a>
 		<?php if ($pages['childs']):?>
 		<ul>
 			<?php foreach ($pages['childs'] as $value):?>
 			<li id="page_<?php echo $value['page_id'];?>" class="sz_sortable<?php if ($value['childs']) { echo ' ch close';}?>">
 				<div class="sz_sitemap_page movable<?php if ((int)$value['alias_to'] > 0) { echo ' alias';}?>" pid="<?php echo $value['page_id'];?>">
 					<?php if ($value['childs']):?>
 					<img src="<?php echo file_link()?>images/dashboard/folder.png" class="sort_page" />
 					<?php elseif ((int)$value['alias_to'] > 0):?>
 					<img src="<?php echo file_link()?>images/dashboard/alias.png" />
 					<?php elseif ((int)$value['is_system_page'] > 0):?>
 					<img src="<?php echo file_link();?>images/config.png" alt="" />
 					<?php elseif ( ! empty($value['external_link']) ):?>
 					<img src="<?php echo file_link()?>images/dashboard/external.png" />
 					<?php else:?>
 					<img src="<?php echo file_link()?>images/dashboard/file.png" class="sort_page" />
 					<?php endif;?>
 					<span pid="<?php echo $value['page_id']?>"<?php echo ( (int)$value['alias_to'] > 0 || ! empty($value['external_link']) ) ? '' : ' class="ttl"';?> d_o="<?php echo $value['display_order'];?>">
 						<?php echo $value['page_title'];?><span><?php if ((int)$value['childs'] > 0) echo '&nbsp;(' . $value['childs'] . ')';?></span>
 					</span>
 				</div>
 				<?php if ($value['childs']):?>
 				<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>
 				<?php endif;?>
 			</li>
 			<?php endforeach;?>
  		</ul>
  		<?php endif;?>
  	</li>
  </ul>
  <?php if (isset($system_pages)):?>
  <ul class="system_page_tree">
  	<li class="ch close" id="page_dashboard">
  		<div pid="dashboard">
  			&nbsp;<?php echo set_image('config.png', TRUE);?>
  			<span pid="dashboard">dashboard<span>&nbsp;(<?php echo $system_pages;?>)</span></span>
  		</div>
  		<a href="javascript:void(0)" class="close_dir oc">&nbsp;</a>
  	</li>
  </ul>
  <?php endif;?>
  </div>
  <div id="sitemap_search_result" style="display:none">
		<div id="sz_sitemap_search_result_box">
		
		</div>
		<p><a href="javascript:void(0)" id="toggle_search">&laquo;ツリー表示に戻る</a></p>
  </div>
</div>
  <div id="sitemap_menu">
  	<p>ページ検索</p>
  	<form id="sz_sitemap_search_menu">
  		<p><label>ページタイトル:<?php echo form_input(array('name' => 'page_title', 'value' => ''))?></label></p>
  		<p><label>ページパス:<?php echo form_input(array('name' => 'page_path', 'value' => ''))?></label></p>
  		<p><input type="button" id="sz_sitemap_search_do" value="ページ検索" /></p>
  	</form>
  </div>
  