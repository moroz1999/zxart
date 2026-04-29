{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<div class="content_list_block">
	<div class='filtration_component dispatchmentlog_filtration'>
		<form class='panel_component filtration_form' action="{$element->getFormActionURL()}" method="post" enctype="multipart/form-data">
			<div class="panel_content filtration_sections">
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<div class="date_container">
            					<input class="input_component orders_list_filtration_start input_date" name="{$formNames.periodStart}" type="text" value="{$element->periodStart}" placeholder="{translations name='dispatchmentlog.startdate'}" />
            					<span class="icon icon_calendar"></span>
							</div>
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<div class="date_container">
								<input class="input_component orders_list_filtration_end input_date" name="{$formNames.periodEnd}" type="text" value="{$element->periodEnd}" placeholder="{translations name='dispatchmentlog.enddate'}" />
            					<span class="icon icon_calendar"></span>
							</div>
						</span>
					</label>
				</div>
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.dispatchmentId}" type="text" value="{$element->dispatchmentId}" placeholder="{translations name='dispatchmentlog.dispatchmentid'}" />
						</span>
					</label>

					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.email}" type="text" value="{$element->email}" placeholder="{translations name='dispatchmentlog.receiveremail'}" />
						</span>
					</label>
				</div>
			</div>
			<div class="panel_controls">
				<input class="button primary_button" type="submit" value="{translations name='dispatchmentlog.filter'}" />
				<input type="hidden" value="{$element->id}" name="id" />
				<input type="hidden" value="show" name="action" />
			</div>
		</form>
	</div>

	<div class="content_list_block">
		{if $logData && count($logData)}
			<table class='dispatchmentlog table_component'>
				<thead>
				<tr>
					<th>
						ID
					</th>
					<th>
						{translations name='dispatchmentlog.subject'}
					</th>
					<th>
						{translations name='dispatchmentlog.receivername'}
					</th>
					<th>
						{translations name='dispatchmentlog.receiveremail'}
					</th>
					<th>
						{translations name='dispatchmentlog.sendername'}
					</th>
					<th>
						{translations name='dispatchmentlog.senderemail'}
					</th>
					<th>
						{translations name='dispatchmentlog.date'}
					</th>
					<th>
						{translations name='dispatchmentlog.status'}
					</th>
				</tr>
				</thead>
				<tbody>
				{foreach from=$logData item=logLine}
					{if $logLine.dispatchmentId}
						<tr class="content_list_item">
							<td>
								{$logLine.dispatchmentId}
							</td>
							<td>
								{if $logLine.data}
									<a href="{$logLine.link}" target="_blank">{$logLine.subject}</a>
								{else}
									{$logLine.subject}
								{/if}
							</td>
							<td>
								{$logLine.name}
							</td>
							<td>
								{$logLine.email}
							</td>
							<td>
								{$logLine.fromName}
							</td>
							<td>
								{$logLine.fromEmail}
							</td>
							<td class="dispatchmentlog_table_cell_date">
								{$logLine.startTime}
							</td>
							<td{if $logLine.status == "fail"} class="dispatchmentlog_table_cell_status_fail"{/if}>
								{translations name='dispatchmentlog.status_'|cat:{$logLine.status}}
							</td>
						</tr>
					{/if}
				{/foreach}
				</tbody>
			</table>
			<div class="content_list_bottom">{include file=$theme->template('pager.tpl')}</div>
		{/if}
	</div>
</div>