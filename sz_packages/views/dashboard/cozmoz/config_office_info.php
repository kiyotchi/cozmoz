<?php echo $this->load->view('dashboard/dashboard_header');?>
<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		INTERMediator.construct(true);
	});
</script>



<!-- h2 stays for breadcrumbs -->
<h2>事務所情報</h2>

<div id="main">
  <h3>事務所情報</h3>
  
  <table>
	<tbody>
		<tr>
			<th>事務所名</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@office_name]" />
			</td>
		</tr>
		<tr>
			<th>法人／個人区分</th>
			<td>
				<select class="IM[<?php echo $office_info_table; ?>@division]">
					<?php foreach( $div as $key => $val ): ?>
						<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th>行政書士名</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@name]" />
			</td>
		</tr>
		<tr>
			<th>所属行政書士会</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@belongs]" />
			</td>
		</tr>
		<tr>
			<th>所属支部</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@belong_childre]" />
			</td>
		</tr>
		<tr>
			<th>会員番号</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@belong_number]" />
			</td>
		</tr>
		<tr>
			<th>事務所住所</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@zip]" /><br />
				
				<select class="IM[<?php echo $office_info_table; ?>@pref]">
					<?php foreach( $pref_list as $key => $val ): ?>
						<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select><br />
				<input type="text" class="IM[<?php echo $office_info_table; ?>@address1]" /><br />
				<input type="text" class="IM[<?php echo $office_info_table; ?>@address2]" /><br />
				<input type="text" class="IM[<?php echo $office_info_table; ?>@address3]" />
			</td>
		</tr>
		<tr>
			<th>電話番号</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@tel]" />
			</td>
		</tr>
		<tr>
			<th>FAX番号</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@fax]" />
			</td>
		</tr>
		<tr>
			<th>メールアドレス</th>
			<td>
				<input type="text" class="IM[<?php echo $office_info_table; ?>@mailaddress]" />
			</td>
		</tr>
		<tr>
			<th>振込先</th>
			<td>
				銀行名:<input type="text" class="IM[<?php echo $office_info_table; ?>@bank_com]" /><br />
				支店名:<input type="text" class="IM[<?php echo $office_info_table; ?>@bank_shop]" /><br />
				口座区分:
				<select class="IM[<?php echo $office_info_table; ?>@bank_division]">
					<?php foreach( $bank_div as $key => $val ): ?>
						<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select>
				口座名義:<input type="text" class="IM[<?php echo $office_info_table; ?>@bank_name]" /><br />
				口座番号:<input type="text" class="IM[<?php echo $office_info_table; ?>@bank_id]" />
			</td>
		</tr>
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
