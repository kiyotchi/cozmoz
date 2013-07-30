<h3>ping送信先の編集</h3>
<form id="sz_ping">
	<p><label>ping名:&nbsp;<input type="text" name="ping_name" value="<?php echo form_prep($ping->ping_name);?>" /></label></p>
	<p><label>URL:&nbsp;<input type="text" name="ping_server" value="<?php echo form_prep($ping->ping_server);?>" /></label></p>
	<br />
	<p>
		<input type="hidden" name="pid" value="<?php echo $ping->sz_blog_ping_list_id;?>" />
		<input type="hidden" name="ticket" value="<?php echo $ticket;?>" />
		<input type="button" id="sz_ping_submit" value="編集" />
	</p>
</form>