<div class="email_content">
	<div class="userdata_top_description">
		<b>{$data.userName|escape:'html'}</b> claims to be the author <b>{$data.author|escape:'html'}</b>.
	</div>
	<br/>
	<table class="form_table">
		<tbody>
		<tr>
			<td class="form_label"><b>Author</b></td>
			<td class="form_value">
				<a href="{$data.authorUrl}" target="_blank">{$data.author|escape:'html'}</a> (id {$data.authorId})
			</td>
		</tr>
		<tr>
			<td class="form_label"><b>Account</b></td>
			<td class="form_value">{$data.userName|escape:'html'} (id {$data.userId})</td>
		</tr>
		<tr>
			<td class="form_label"><b>Email</b></td>
			<td class="form_value">{$data.userEmail|escape:'html'}</td>
		</tr>
		</tbody>
	</table>
	<br/>
	<a href="{$data.approvalUrl}" target="_blank">Review this claim</a>
</div>
