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
        <div class="sz_sitemap_page movable<?php if ((int)$value['alias_to'] > 0) { echo ' alias';} elseif (! empty($value['external_link'])) { echo ' external'; } ?>" pid="<?php echo $value['page_id'];?>" sys="<?php echo $value['is_system_page'];?>">
        
        <?php if ($value['childs']):?>
          <img src="<?php echo file_link()?>images/dashboard/folder.png" class="sort_page" />
        <?php elseif ((int)$value['alias_to'] > 0):?>
          <img src="<?php echo file_link()?>images/dashboard/alias.png" />
        <?php elseif ( ! empty($value['external_link']) ):?>
          <?php echo set_image('dashboard/external.png', TRUE);?>
        <?php elseif ((int)$value['is_system_page'] > 0):?>
          <img src="<?php echo file_link()?>images/dashboard/system.png" class="sort_page" />
        <?php else:?>
          <img src="<?php echo file_link()?>images/dashboard/file.png" class="sort_page" />
        <?php endif;?>
        
          <span pid="<?php echo $value['page_id']?>" class="ttl" d_o="<?php echo $value['display_order'];?>" ssl="<?php echo $value['is_ssl_page'];?>">
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
