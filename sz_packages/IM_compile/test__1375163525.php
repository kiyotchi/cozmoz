<?php $IM->set_main_node_list('a:3:{s:12:".IM_REPEAT_1";a:1:{s:15:"cozmoz_customer";s:6:"PARENT";}s:12:".IM_REPEAT_2";a:1:{s:16:"cozmoz_customer2";s:6:"PARENT";}s:12:".IM_REPEAT_0";a:2:{s:13:"cozmoz_matter";s:6:"PARENT";s:15:"cozmoz_customer";s:5:"CHILD";}}');?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>無題ドキュメント</title>
</head>
<body>

<table>
<thead><tr>
<th>test</th>
			<th>test</th>
		</tr></thead>
<tbody class="IM_CEHCKER IM_REPEAT_0">
<?php  while( $row = $IM->repeat( '.IM_REPEAT_0' ) ):
$IM->push_current( $row );  ?><tr>
<td>
				<span class="IM[cozmoz_matter@name] class_name"><?php  echo $IM->output('cozmoz_matter' ,'name');  ?></span>
				<div class="_im_enclosure IM_CEHCKER IM_REPEAT_1">
					<?php  while( $row = $IM->repeat( '.IM_REPEAT_1' ) ):
$IM->push_current( $row );  ?><div class="_im_repeater">
						<span class="IM[cozmoz_customer@name1]"><?php  echo $IM->output('cozmoz_customer' ,'name1');  ?></span>
						<span class="IM[cozmoz_customer@name2]"><?php  echo $IM->output('cozmoz_customer' ,'name2');  ?></span>
						
					</div>
<?php  $IM->next_node();
endwhile;  ?>
				</div>
			</td>
		</tr>
<?php  $IM->next_node();
endwhile;  ?>
</tbody>
</table>
<hr>
<hr>
<hr>
<hr>
<div class="_im_enclosure IM_CEHCKER IM_REPEAT_2">
	<?php  while( $row = $IM->repeat( '.IM_REPEAT_2' ) ):
$IM->push_current( $row );  ?><div class="_im_repeater">
		<span class="IM[cozmoz_customer2@name1]"><?php  echo $IM->output('cozmoz_customer2' ,'name1');  ?></span>
		<span class="IM[cozmoz_customer2@name2]"><?php  echo $IM->output('cozmoz_customer2' ,'name2');  ?></span>
	</div>
<?php  $IM->next_node();
endwhile;  ?>
</div>

<hr>
<table><tbody><tr>
<td>日本語だよ</td>
			<td>日本語だねtest</td>
		</tr></tbody></table>
</body>
</html>
