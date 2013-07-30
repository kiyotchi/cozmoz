<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
                <!-- h2 stays for breadcrumbs -->
                <h2>ページ管理</h2>
                <div id="main">
					<h3>静的変数一覧</h3>
					<p class="additional">テンプレート（静的ページ含む）で使用できる変数の一覧です。</p>
					<table>
						<tbody>
						<tr class="caption">
							<th>変数名</th>
							<th class="wide">概要</th>
							<th>出力される値</th>
							<th>操作</th>
						</tr>
						<?php foreach ($variables as $value):?>
						<tr>
							<td>$<?php echo prep_str($value['name']);?></td>
							<td class="wide"><?php echo prep_str($value['description']);?></td>
							<?php if (isset($value['value'])):?>
							<td><?php echo prep_str($value['value']);?></td>
							<?php elseif(isset($value['method'])):?>
							<td>StaticV::<?php echo prep_str($value['name']);?>()</td>
							<?php else:?>
							<td>-</td>
							<?php endif;?>
							<td class="action">
								<?php if (isset($value['editable'])):?>
								<a href="javascript:void(0)" class="edit">編集</a>
								<a href="javascript:void(0)" class="delete">削除</a>
								<?php else:?>-<?php endif;?>
							</td>
						</tr>
						<?php endforeach;?>
						</tbody>
					</table>
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
