<div class="email_content">
	<div class="userdata_top_description">
		{translations name='userdata.top_description'}
	</div>
	<a href="{$data.verifyEmail}" target="_blank">{translations name='userdata.verify_email'}</a>
	<br/>
	<br/>
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
</div>