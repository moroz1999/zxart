{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<div class="content_list_block">
	<div class='filtration_component actionlog_filtration'>
		<form class='panel_component filtration_form' action="{$element->getFormActionURL()}" method="post" enctype="multipart/form-data">
			<div class="panel_content filtration_sections">
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<span class="date_container">
            					<input class="input_component orders_list_filtration_start input_date" name="{$formNames.periodStart}" type="text" value="{$element->periodStart}" placeholder="{translations name='dispatchmentlog.startdate'}" />
            					<span class="icon icon_calendar"></span>
							</span>
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<span class="date_container">
								<input class="input_component orders_list_filtration_end input_date" name="{$formNames.periodEnd}" type="text" value="{$element->periodEnd}" placeholder="{translations name='dispatchmentlog.enddate'}" />
            					<span class="icon icon_calendar"></span>
							</span>
						</span>
					</label>
				</div>
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.elementId}" type="text" value="{$element->elementId}" placeholder="{translations name='actionslog.elementid'}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.elementType}" type="text" value="{$element->elementType}" placeholder="{translations name='actionslog.elementtype'}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.elementName}" type="text" value="{$element->elementName}" placeholder="{translations name='actionslog.elementname'}" />
						</span>
					</label>
				</div>
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.action}" type="text" value="{$element->action}" placeholder="{translations name='actionslog.action'}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.userId}" type="text" value="{$element->userId}" placeholder="{translations name='actionslog.user'}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.userIP}" type="text" value="{$element->userIP}" placeholder="{translations name='actionslog.ip_address'}" />
						</span>
					</label>
				</div>
			</div>
			<div class="panel_controls">
				<input class="button primary_button" type="submit" value="{translations name='actionslog.filter'}" />
				<input type="hidden" value="{$element->id}" name="id" />
				<input type="hidden" value="show" name="action" />
			</div>
		</form>
	</div>

	{if $logData = $element->getActionLogData()}
		<table class='table_component content_list_actionslog'>
			<thead>
			<tr>
				<th>
					{translations name='actionslog.date'}
				</th>
				<th>
					{translations name='actionslog.element'}
				</th>
				<th>
					{translations name='actionslog.elementname'}
				</th>
				<th>
					{translations name='actionslog.actionname'}
				</th>
				<th>
					{translations name='actionslog.user'}
				</th>
				<th>
					{translations name='actionslog.ipaddress'}
				</th>
			</tr>
			</thead>
			<tbody>
			{foreach from=$logData item=logLine}
				{if count($logLine.id)}
					<tr class="content_list_item elementid_{$logLine.id}">
						<td class="actionslog_table_cell_date">
							{$logLine.date}
						</td>
						<td>
							{$logLine.elementType} ({$logLine.elementId})
						</td>
						<td>
							{$logLine.elementName}
						</td>
						<td class="actionslog_table_cell_action">
							{$logLine.action}
						</td>
						<td>
							{$logLine.userName} ({$logLine.userId})
						</td>
						<td>
							{$logLine.userIP}
						</td>
					</tr>
				{/if}
			{/foreach}
			</tbody>
		</table>
		<div class="content_list_bottom">{include file=$theme->template('pager.tpl') pager=$element->getPager()}</div>
	{/if}
</div>