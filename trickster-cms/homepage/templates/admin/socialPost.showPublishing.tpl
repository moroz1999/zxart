<table class="socialpost_publish_table">
	{foreach from=$element->getPublishingInfo() item=info}
		<tr>
			{assign var=statusText value=$info.statusText}
			<td class="socialpost_publish_title">{$info.title}</td>
			<td class="socialpost_publish_status">{translations name="socialpost.status_$statusText"}</td>
			<td class="socialpost_publish_controls">
				<input onclick="document.location.href = '{$info.publishURL}'" type="button" value="{translations name="socialpost.publish"}" />
			</td>
		</tr>
	{/foreach}
</table>