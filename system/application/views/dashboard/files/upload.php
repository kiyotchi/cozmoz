<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{ margin : 0; padding: 0;}
#up_form { float : right;}
#up_form form { display : inline;}
form input { text-align : right;}
label { font-size : 12px;}
br.clear { clear : both;}
</style>
<script type="text/javascript">
	<?php if (isset($error)):?>
	alert('<?php echo $error;?>');
	<?php endif;?>
	window.onload = function() {
		var sub = document.getElementById('add');
		sub.disabled = true;
		var file = document.getElementsByName('upload_data')[0];
		file.onchange = function() {
				sub.disabled = (this.value == '') ? true : false;
		};
		sub.onclick = function(ev) {
			//this.disabled = true;
			// try get upload path
			try {
					var DOM = window.parent.DOM;
	
					document.getElementById('upload_path').value = DOM('ul#sz_dir_tree_path a.current').get(0).parent().readAttr('dir_id');
					document.getElementsByTagName('form')[0].submit();
					return false;
			} catch(e) {}
				
		};
		window.onunload = function() {
				window.load = null;
				window.onunload = null;
				file.onchange = null;
				sub.onclick = null;
		};
	};
</script>
</head>
<body>
<div id="up_form">
	<?php echo form_open_multipart('dashboard/files/directory_view/ajax_upload');?>
	<label>新規ファイル：<input type="file" name="upload_data" value="" /></label>
	<input type="hidden" name="upload_path" id="upload_path" value="" />
	<input type="submit" value="追加" id="add" />
	<?php echo form_close();?>
</div>
<br class="clear" />
</body>
</html>