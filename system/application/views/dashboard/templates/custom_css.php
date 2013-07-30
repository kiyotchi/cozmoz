<h4 style="margin : 10px 0;font-size : 20px">テンプレートCSSのカスタマイズ</h4>
<p style="margin : 10px">テンプレートに適用するCSSルールを追加定義できます。</p>
<?php echo form_open('dashboard/templates/set_advance_css');?>
<?php echo form_textarea(array('name' => 'custom_css', 'value' => prep_str($custom_css), 'cols' => 0, 'rows' => 10, 'style' => 'width : 580px;height : 300px;font-size:13px;font-family:Arial;display:block;margin:0 auto;'));?>
<p style="text-align : center;margin-top : 20px;">
	<?php echo form_hidden('template_id', $template_id);?>
	<?php echo form_submit(array('value' => 'cssを追加'));?>
</p>
<?php echo form_close();?>