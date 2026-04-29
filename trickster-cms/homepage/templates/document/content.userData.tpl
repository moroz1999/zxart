<div class="email_content">
	<div class="userdata_top_description">
		{translations name='userdata.top_description'}
	</div>
	<table class="form_table">
		<tbody>
		{foreach $data.fields as $field}
			<tr>
				<td class="form_label">
					<b>{$field.title}</b>
				</td>
				<td class="form_value">
					{$field.value}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div class="userdata_bottom_description">
		{translations name='userdata.bottom_description'}
	</div>
</div>