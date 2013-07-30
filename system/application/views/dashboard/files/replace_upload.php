<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{ margin : 0; padding: 0;}
label { font-size : 12px;}
br.clear { clear : both;}
#up_form { text-align : center;line-height : 1.8;}
#add { padding : 0 10px;}
</style>
<script type="text/javascript">
	<?php if (isset($file)):?>
	window.parent.replaceStacks(<?php echo json_encode($file);?>);
	<?php endif;?>
	window.onload = function() {
		var sub = document.getElementById('add');
		var file = document.getElementById('upload_data');

		if (file) {
			file.onchange = function() {
				sub.disabled = (this.value == '') ? true : false;
			};
		}
		if (sub) {
			sub.disabled = true;
		}
		window.onunload = function() {
				window.load = null;
				window.onunload = null;
				//if (file) {file.onchange = null;}
		};
	};
</script>
</head>
<body>
<div id="up_form">
	<?php if (isset($file)):?>
	<p>
		<?php if(has_icon_ext($file['extension'])):?>
			<img src="<?php echo file_link() . 'images/icons/files/' . $file['extension'] . '.png';?>" alt="" />
			<?php elseif (image_ext($file['extension'])):?>
			<img src="<?php echo file_link() . 'files/temporary/thumbnail/' . $file['crypt_name'] . '.' . $file['extension'];?>" alt="" />
			<?php else:?>
			<img src="<?php echo file_link() . 'images/icons/files/file.png';?>" alt="" />
			<?php endif;?>
		<span><?php echo $file['file_name'] . '.' . $file['extension'];?></span>
	</p>
	<p>変更する場合は再度アップロードしてください。</p>
	<?php endif;?>
	<?php echo form_open_multipart('dashboard/files/directory_view/ajax_replace_upload');?>
	<label>差し替えるファイル：<input type="file" name="upload_data" id="upload_data" /></label>
	<?php echo form_hidden('old_file_id', $file_id);?><br /><br />
	<?php if (isset($file)):?>
	<?php echo form_hidden('temp_file_name', $file['crypt_name'] . '.' . $file['extension']);?>
	<?php endif;?>
	<input type="submit" value="アップロード" id="add" />
	<?php echo form_close();?>
</div>
<br class="clear" />
</body>
</html>