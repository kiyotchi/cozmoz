<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
					<!-- h2 stays for breadcrumbs -->
					<h2>Seezoo&nbsp;ブログ管理</h2>
					<div id="main">
					<h3>エントリー削除の確認</h3>
					<p class="customize"><?php echo anchor('dashboard/blog/entries', set_image('back.png', TRUE) . '&nbsp;投稿一覧へ戻る')?></p>
					<h4>以下の投稿を削除します。よろしいですか？</h4><br />
					<table class="confirm">
						<tbody>
							<tr>
								<td>投稿ID</td>
								<td class="action"><?php echo (int)$entry->sz_blog_id;?></td>
							</tr>
							<tr class="odd">
								<td>投稿日時</td>
								<td class="action"><?php echo $entry->entry_date;?></td>
							<tr>
								<td>タイトル</td>
								<td class="action"><?php echo form_prep($entry->title);?></td>
							</tr>
							<tr class="odd">
								<td>登録カテゴリ</td>
								<?php if (array_key_exists($entry->sz_blog_category_id, $category)):?>
								<td class="action"><?php echo $category[form_prep($entry->sz_blog_category_id)];?></td>
								<?php else:?>
								<td class="action"><span style="color : #c00">削除されたカテゴリ</span></td>
								<?php endif;?>
							</tr>
							<tr>
								<td colspan="2">本文</td>
							</tr>
							<tr class="odd">
								<td class="action" colspan="2">
									<div>
										<?php echo nl2br($entry->body);?>
									</div>
								</td>
							</tr>
							<tr>
								<td>コメント</td>
								<td class="action">
									<?php if ((int)$entry->is_accept_comment === 1):?>
						 			受け付ける
									<?php else:?>
									<span style="color:red">受け付けない</span>
									<?php endif;?>
								</td>
							</tr>
						</tbody>
						</table>
						<div class="custom_area">
							<?php echo form_open('dashboard/blog/entries/do_delete')?>
							<?php echo form_hidden('referer', $ref);?>
							<?php echo form_hidden('sz_blog_id', $id);?>
							<?php echo form_hidden('sz_blog_delete_ticket', $ticket);?>
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
