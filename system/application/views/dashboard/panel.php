<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>Seezoo&nbsp;管理パネル&nbsp;TOP</h2>
                <div id="main">
                <?php if (!empty($msg)):?>
                <div class="message"><?php echo $msg;?></div>
                <?php endif;?>
                <p class="admin_name">ようこそ、<?php echo $user_data->user_name;?>さん！</p>
                <h3>サイト情報</h3>
                  <table cellpadding="0" cellspacing="0">
                    <tr class="odd">
                        <td>Seezooのバージョン</td>
                        <td class="action"><?php echo $this->config->item('seezoo_current_version');?></td>
                    </tr>
                    <tr>
                      <td>サイト名</td>
                      <td class="action"><?php echo $site->site_title;?></td>
                    </tr>
                    <tr class="odd">
                      <td>サイトアドレス</td>
                      <td class="action"><?php echo page_link()?></td>
                    </tr>
                    <tr>
                      <td>総ログイン回数</td>
                      <td class="action"><?php echo $user_data->login_times;?></td>
                    </tr>
                    <tr class="odd">
                      <td>ログイン日時</td>
                      <td class="action"><?php echo $user_data->last_login;?></td>
                    </tr>
                    <tr>
                      <td>編集中のページ数</td>
                      <td class="action"><?php echo $edit_pages;?></td>
                    </tr>
                    <tr class="odd">
                      <td>デフォルトで使用するテンプレート</td>
                      <td class="action"><?php echo $default_template;?></td>
                    </tr>
                  </table>
                  <br />
                  <br />
                 <h3>システムインフォメーション</h3>
                 <h4>申請状況</h4>
                 <ul class="sz_system_info">
                 <?php if (count($approve_orders) > 0):?>
                 <?php foreach($approve_orders as $v):?>
                 <li>
                   <em><?php echo $v->ordered_date?></em>
                   <a href="<?php echo page_link() . $v->page_id;?>"><?php echo prep_str($v->page_title);?>のバージョン<?php echo $v->version_number;?></a>&nbsp;
                   <?php if ($v->status == 0):?>
                   を申請中です
                   <a href="javascript:void(0)" class="cancel_approve_order" rel="<?php echo $v->page_approve_orders_id;?>"><?php echo set_image('delete.png', TRUE);?>&nbsp;申請を取り消す</a>
                   <?php elseif ($v->status == 1):?>
                   が<span style="color:#c00">承認されました</span>
                   <?php if (!empty($v->comment)):?>
                   <a href="javascript:void(0)" class="open_approve_comment"><?php echo set_image('plus.png', TRUE);?>コメントを見る</a>
                   <div class="sz_approve_comment">
                   <div>
                   <p><?php echo $v->user_name;?>からのコメント:</p>
                   <?php echo nl2br(prep_str($v->comment));?>
                   </div>
                   </div>
                   <?php endif;?>
                   <?php elseif ($v->status == 2):?>
                   が<span style="color:#00c">差し戻しになっています</span>
                   <?php if (!empty($v->comment)):?>
                   <a href="javascript:void(0)" class="open_approve_comment"><?php echo set_image('plus.png', TRUE);?>コメントを見る</a>
                   <div class="sz_approve_comment">
                   <div>
                   <p><?php echo $v->user_name;?>からのコメント:</p>
                   <?php echo nl2br(prep_str($v->comment));?>
                   </div>
                   </div>
                   <?php endif;?>
                   <?php endif;?>
                  </li>
                  <?php endforeach;?>
                  <?php else:?>
                  <li>申請情報はありません。</li>
                  <?php endif;?>
                  </ul>
                 <h4>承認情報</h4>
                 <ul class="sz_system_info">
                 <?php if (count($approve_requests) > 0):?>
                  <?php foreach($approve_requests as $v):?>
                  <li class="requests">
                  <a href="<?php echo page_link() . 'page/preview/' . $v->version_number . '/' . $v->page_id;?>" class="version_preview" rel="<?php echo $v->version_number;?>:<?php echo $v->page_id?>:<?php echo $v->page_approve_orders_id;?>"><?php echo prep_str($v->page_title);?>のバージョン<?php echo $v->version_number;?></a>&nbsp;について承認の申請があります。
                  <?php if (!empty($v->comment)):?>
                  <a href="javascript:void(0)" class="open_approve_comment"><?php echo set_image('plus.png', TRUE);?>コメントを見る</a>
                  <div class="sz_approve_comment">
                  <div>
                  <p><?php echo $v->user_name;?>からのコメント:</p>
                  <?php echo nl2br(prep_str($v->comment));?>
                  </div>
                  </div>
                  <?php endif;?>
                  </li>
                  <?php endforeach;?>
                  <?php else:?>
                  <li>情報はありません。</li>
                  <?php endif;?>
                  </ul>
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
