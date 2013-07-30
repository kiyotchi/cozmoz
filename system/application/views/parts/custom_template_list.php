<?php if($list):?>
<p>切り替えるテンプレートを選択してください。</p>
<ul class="sz_ct_list">
	<li>
		<a href="javascript:void(0)">default</a>
		<div>デフォルトのテンプレートです。</div>
	</li>
	<?php foreach ($list as $key => $val):?>
	<li handle="<?php echo form_prep($key);?>">
		<a href="javascript:void(0)"><?php echo form_prep($key);?></a>
		<?php if ( !empty($val) ):?>
		<div><?php echo nl2br(form_prep($val));?></div>
		<?php endif;?>
	</li>
	<?php endforeach;?>
</ul>
<?php else:?>
<p class="notfound">
使用できるカスタムテンプレートはありません。
</p>
<p class="sz_button">
	<a href="javascript:winClose();">
		<span>閉じる</span>
	</a>
</p>
<?php endif;?>