<div class="email_content">
	<table class="form_table">
		<tbody>
			<tr>
				<td class="form_label">
					<b>{translations name='userdata.email'}:</b>
				</td>
				<td class="form_value">
				{$data.email}
				</td>
			</tr>
			{if $data.link}
				<tr>
					<td class="form_label">
						<b>{translations name='userdata.password'}:</b>
					</td>
					{* keep password without spaces around *}
					<td class="form_value"><a href="{$data.link}">{translations name='userdata.setnewpassword'}</a></td>
				</tr>
			{/if}
		</tbody>
	</table>
</div>