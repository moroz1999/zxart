<div class="email_content">
	<h1>{$data.heading}</h1>
	<table class="form_table">
		<tbody>
			<tr>
				<td class="form_label">Name:</td>
				<td class="form_value">{$data.name|escape}</td>
			</tr>
			<tr>
				<td class="form_label">Email:</td>
				<td class="form_value">{$data.email|escape}</td>
			</tr>
			<tr>
				<td class="form_label form_label_vertical_top">Message:</td>
				<td class="form_value">{$data.message|escape|nl2br}</td>
			</tr>
		</tbody>
	</table>
</div>
