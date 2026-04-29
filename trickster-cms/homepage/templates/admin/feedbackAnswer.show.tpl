<div class="feedbackanswer_details">
	{$report = $element->getAdminReport()}
	<table class="feedbackanswer_details_table form_table">
		<thead>
		<tr>
			<td class="form_label feedbackanswer_details_table_label">
				{translations name='feedback_answer.date_time'}:
			</td>
			<td class="feedbackanswer_group" colspan="2">
				{$element->dateCreated}
			</td>
		</tr>
		</thead>
	{foreach $report.groups as $group}
		<tbody>
		<tr class="feedbackanswer_group_row">
			<td class="feedbackanswer_group" colspan="2">
				<h1 class="form_inner_title" >
				{$group.title}</h1>
			</td>
		</tr>
		{foreach $group.fields as $field}
			<tr class="feedbackanswer_details_row">
				<td class="form_label feedbackanswer_details_table_label">
					{$field.title}:
				</td>
				<td class="form_value">
					{if $field.type != 'fileinput'}
                        {if $field.value|is_array}
                            {foreach from=$field.value item=valueSingle}
                                {if $valueSingle.checked == true}
                                    <div class="form_value_multiple">{$valueSingle.name}</div>
                                {/if}
                            {/foreach}
                        {else}
                            {$field.value}
                        {/if}
					{elseif $field.fileInput }
						{foreach $field.fileInput  as $file}
							<a target="_blank" href="{$file.link}">{$file.originalName}</a>
						{/foreach}
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	{/foreach}
	</table>
</div>