<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<title>Seezooのインストール</title>

	<link rel="stylesheet" type="text/css" href="<?php echo $css_uri; ?>/dashboard.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $css_uri; ?>/install.css" />
	<script type="text/javascript" src="<?php echo get_seezoo_uri(); ?>js/config/base.config.js"></script>
	<script type="text/javascript" src="<?php echo get_seezoo_uri(); ?>js/flint.dev.js"></script>
	<script type="text/javascript">
	FL.load.library('install');
	FL.ready('install', function() { FL.install.init(); });
	</script>
</head>
<body>
	<div id="wrapper">
	<!-- h1 tag stays for the logo, you can use the a tag for linking the index page -->

		<!-- You can name the links with lowercase, they will be transformed to uppercase by CSS, we prefered to name them with uppercase to have the same effect with disabled stylesheet -->
		<ul id="mainNav">
			<li><span>Installation&nbsp;Seezoo</span></li> <!-- Use the "active" class for the active menu item  -->
		</ul>
		<!-- // #end mainNav -->
		<div id="containerHolder">

			<div id="container">
				<div id="container_full">
					<h3 class="install_caption">Seezooをインストール</h3>
					<h4 class="install_caption">ファイルの書き込み権限</h4>
					<div class="install_permission_wrapper">
						<ul class="install_permissions">
						<?php $cnt = 0;?>
						<?php foreach ($file_permissions as $key => $value):?>
							<li<?php if ($cnt === 0) echo ' class="first"';?>>
								<p>
								<em class="path_icon"><?php echo get_path_icons($key);?></em>への書き込み権限
								<span>
								<?php if ($value === TRUE):?>
								<?php echo set_install_icons('check.gif');?>&nbsp;OK
								<?php else:?>
								<?php echo set_install_icons('delete.png');?>&nbsp;書き込み権限がありません
								<?php endif;?>
								</span>
								</p>
							</li>
							<?php $cnt++;?>
						<?php endforeach;?>
						</ul>
					</div>
					<h4 class="install_caption">サーバー/PHPのインストール要件チェック</h4>
					<div class="install_permission_wrapper">
						<ul class="install_permissions">
						<li class="first">
							<p>
							<em class="path_icon">&nbsp;<?php echo set_install_icons('config.png');?>&nbsp;Apache&nbsp;mod_rewrite</em>モジュールの利用可否
							<span>
								<?php if ($is_mod_rewrite === 0):?>
								<?php echo set_install_icons('delete.png');?>&nbsp;利用できません
								<?php elseif ($is_mod_rewrite === 1):?>
								<?php echo set_install_icons('check.gif');?>&nbsp;利用可能
								<?php else:?>
								<?php echo set_install_icons('warning.png');?>&nbsp;不明
								<?php endif;?>
							</span>
							</p>
						</li>
						<li>
							<p>
							<em class="path_icon">&nbsp;<?php echo set_install_icons('config.png');?>&nbsp;PHP&nbsp;version&nbsp;:&nbsp;<?php echo PHP_VERSION;?></em>&gt;&nbsp;5.1.2
							<span>
								<?php if ($php_version === FALSE):?>
								<?php echo set_install_icons('delete.png');?>&nbsp;5.0以上が必要です
								<?php else:?>
								<?php echo set_install_icons('check.gif');?>&nbsp;OK
								<?php endif;?>
							</span>
							</p>
						</li>
						<li>
							<p>
							<em class="path_icon">&nbsp;<?php echo set_install_icons('config.png');?>&nbsp;json_encode関数</em>が利用可能かどうか
							<span>
								<?php if ($is_json_encode === FALSE):?>
								<?php echo set_install_icons('delete.png');?>&nbsp;利用できません
								<?php else:?>
								<?php echo set_install_icons('check.gif');?>&nbsp;OK
								<?php endif;?>
							</span>
							</p>
						</li>
						<li>
							<p>
							<em class="path_icon">&nbsp;<?php echo set_install_icons('config.png');?>&nbsp;SimpleXML関数</em>が利用可能かどうか
							<span>
								<?php if ($is_xml === FALSE):?>
								<?php echo set_install_icons('delete.png');?>&nbsp;利用できません
								<?php else:?>
								<?php echo set_install_icons('check.gif');?>&nbsp;OK
								<?php endif;?>
							</span>
							</p>
						</li>
						<li>
							<p>
							<em class="path_icon">&nbsp;<?php echo set_install_icons('config.png');?>&nbsp;GD関数</em>が利用可能かどうか
							<span>
								<?php if ($is_gd === FALSE):?>
								<?php echo set_install_icons('delete.png');?>&nbsp;利用できません
								<?php else:?>
								<?php echo set_install_icons('check.gif');?>&nbsp;OK
								<?php endif;?>
							</span>
							</p>
						</li>
						<li>
							<p>
							<em class="path_icon">&nbsp;<?php echo set_install_icons('config.png');?>&nbsp;mbsting関数</em>が利用可能かどうか
							<span>
								<?php if ($is_mbstring === FALSE):?>
								<?php echo set_install_icons('delete.png');?>&nbsp;利用できません
								<?php else:?>
								<?php echo set_install_icons('check.gif');?>&nbsp;OK
								<?php endif;?>
							</span>
							</p>
						</li>
						<li>
							<p>
							<em class="path_icon">&nbsp;<?php echo set_install_icons('config.png');?>&nbsp;ZipArchiveクラス</em>が利用可能かどうか（推奨）
							<span>
								<?php if ($is_mbstring === FALSE):?>
								<?php echo set_install_icons('warning.png');?>&nbsp;未サポート
								<?php else:?>
								<?php echo set_install_icons('check.gif');?>&nbsp;OK
								<?php endif;?>
							</span>
							</p>
						</li>
						</ul>
					</div>
					<?php if (in_array(FALSE, $file_permissions)):?>
					<div class="permission_error">
						ディレクトリ/ファイルに書き込み権限が無いものがあります。権限を再度チェックしてください。<br />
						<a href="javascript:void(0)" id="reload"><?php echo set_install_icons('back.png', TRUE);?>&nbsp;再チェック</a>
					</div>
					<div id="install_data_wrapper" style="display:none">
					<?php elseif ($php_version === FALSE):?>
					<div class="permission_error">
						PHPのバージョンが動作対象外です。PHPのバージョン5.0以上が対象です。
						<a href="javascript:void(0)" id="reload"><?php echo set_install_icons('back.png', TRUE);?>&nbsp;再チェック</a>
					</div>
					<div id="install_data_wrapper" style="display:none">
					<?php elseif ($is_json_encode === FALSE || $is_xml === FALSE || $is_gd === FALSE):?>
					<div class="permission_error">
						Seezooの動作に必要な関数が利用できません。PHPのバージョンを確認してください。
						<a href="javascript:void(0)" id="reload"><?php echo set_install_icons('back.png', TRUE);?>&nbsp;再チェック</a>
					</div>			
					<div id="install_data_wrapper" style="display:none">
					<?php else:?>
					<div id="install_data_wrapper">
					<?php endif;?>
							<h4 class="install_caption">インストール情報の入力</h4>
							<div id="input_install_data">
								<?php $this->form_validation->set_error_delimiters('<p class="errors">', '</p>'); ?>
								<?php echo $this->form_validation->error_string();?>
								<?php echo form_open(get_seezoo_uri() . 'index.php/install/do_install', array('id' => 'sz_install_form'));?>
								<?php $cnt = 0;?>
								<table>
									<tbody>
										<?php foreach ($formdata as $field_name => $values) : ?>
										<tr<?php if ($cnt % 2 > 0) echo ' class="odd"';?>>
											<th>
												<?php echo $values['label']; ?>
												
												<?php if ( $field_name === 'admin_password'):?>
												<a href="javascript:void(0)" id="randomize">
													<img src="<?php echo get_seezoo_dir();?>/images/lock.png" alt="" />&nbsp;ランダム生成
												</a>
												<?php endif;?>
												
											</th>
											<td>
											<?php echo form_input(array(
												'name'	=> $field_name,
												'id'	=> $field_name,
												'value'	=> $values['for_form'],
												'tabindex'	=> $cnt + 1
											)); ?>
											</td>
										</tr>
										<?php $cnt++;?>
										<?php endforeach; ?>
									</tbody>
								</table>
								<p id="login_btn">
									<?php foreach ($hidden as $name => $value) : ?>
									<?php echo form_hidden($name, $value); ?>
									<?php endforeach; ?>
									<input type="submit" alt="インストール" value="Seezooをインストール!" id="btn" disabled="disabled" />
								</p>
								<?php echo form_close();?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<p id="footer"></p>
	</div>
</body>
</html>
