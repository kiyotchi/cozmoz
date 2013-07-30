<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{ margin : 0; padding: 0;}
#up_form {
	text-align : center;
	display : block;
}
label { font-size : 12px;}
br.clear { clear : both;}
</style>
<script type="text/javascript">
	<?php if (isset($error)):?>
	alert('<?php echo $error;?>');
	<?php endif;?>
	<?php if (isset($image)):?>
	window.parent.updateProfileImage('<?php echo $image;?>');
	<?php endif;?>
	window.onload = function() {
		var sub = document.getElementById('add');
		sub.disabled = true;
		var file = document.getElementsByName('image_data')[0];
		file.onchange = function() {
				sub.disabled = (this.value == '') ? true : false;
		};
		sub.onclick = function(ev) {
			//this.disabled = true;
					document.getElementsByTagName('form')[0].submit();
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
	<?php echo form_open_multipart('dashboard/members/member_list/profile_upload');?>
	<label><input type="file" name="image_data" value="" /></label>
	<?php echo form_hidden('member_id', $member_id);?>
	<input type="submit" value="アップロード" id="add" />
	<?php echo form_close();?>
</div>
<br class="clear" />
</body>
</html>