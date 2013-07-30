<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>ブロック管理</h2>
                <div id="main">
                	<?php if (!empty($this->msg)):?>
                	<div class="message"><?php echo form_prep($this->msg);?></div>
                	<?php endif;?>
					<p class="r_case mt10"><?php echo anchor('dashboard/blocks/block_list', '&laquo;一覧へ戻る');?></p>
					<table cellspacing="0", cellpadding="0">
						<tbody>
							<tr>
								<td>ブロック名</td>
								<td class="action"><?php echo prep_str($block->block_name);?></td>
							</tr>
							<tr class="odd">
								<td>ブロック概要</td>
								<td class="action"><?php echo prep_str($block->description);?></td>
							</tr>
							<tr>
								<td>ハンドルクラス</td>
								<td class="action"><?php echo ucfirst($block->collection_name);?></td>
							</tr>
							<tr class="odd">
								<td>編集時のウインドウ領域</td>
								<td class="action"><?php echo $block->interface_width;?>&nbsp;×&nbsp;<?php echo $block->interface_height;?></td>
							</tr>
							<tr>
								<td>追加日時</td>
								<td class="action"><?php echo $block->added_date;?></td>
							</tr>
							<tr class="odd">
								<td>PC画面での表示/追加</td>
								<td class="action"><?php echo ( $block->pc_enabled > 0 ) ? '有効' : '<span style="color:#c00">無効</span>';?></td>
							</tr>
							<tr>
								<td>スマートフォンでの表示/追加</td>
								<td class="action"><?php echo ( $block->sp_enabled > 0 ) ? '有効' : '<span style="color:#c00">無効</span>';?></td>
							</tr>
							<tr class="odd">
								<td>フィーチャーフォンでの表示/追加</td>
								<td class="action"><?php echo ( $block->mb_enabled > 0 ) ? '有効' : '<span style="color:#c00">無効</span>';?></td>
							</tr>
							<tr>
								<td>使用している数（過去バージョン含む）</td>
								<td class="action"><?php echo $use_count;?></td>
							</tr>
							<?php if ( $block->plugin_id > 0 ):?>
							<tr class="odd">
								<td>このブロックが含まれるプラグイン</td>
								<td class="action"><?php echo prep_str($block->plugin_name);?></td>
							</tr>
							<?php endif;?>
						</tbody>
					</table>
					<div class="multi_form">
						<?php echo form_open('dashboard/blocks/block_list/update');?>
						<?php echo form_hidden('col_id', $block->collection_id);?>
						<?php echo form_hidden('col_name', $block->collection_name);?>
						<?php echo form_hidden('ticket', $ticket);?>
						<?php echo form_submit(array('value' => '更新する'));?>
						<?php echo form_close();?>&nbsp;&nbsp;
						<?php echo form_open('dashboard/blocks/block_list/delete', array('id' => 'block_delete'));?>
						<?php echo form_hidden('col_id', $block->collection_id);?>
						<?php echo form_hidden('col_name', $block->collection_name);?>
						<?php echo form_hidden('ticket', $ticket);?>
						<?php echo form_submit(array('value' => '削除する'));?>
						<?php echo form_close();?>
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
