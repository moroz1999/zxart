{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<div class="content_list_block">
	<div class='filtration_component'>
		<form class='panel_component filtration_form' action="{$element->getFormActionURL()}" method="post" enctype="multipart/form-data">
			<div class="panel_content filtration_sections">
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							 <span class="date_container">
								<input class="input_component orders_list_filtration_start input_date" name="{$formNames.periodStart}" type="text" value="{$element->periodStart}"" placeholder="{translations name='searchlog.startdate'}"/>
								<span class="icon icon_calendar"></span>
							</span>
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							 <span class="date_container">
								<input class="input_component orders_list_filtration_end input_date" name="{$formNames.periodEnd}" type="text" value="{$element->periodEnd}"" placeholder="{translations name='searchlog.enddate'}"/>
								<span class="icon icon_calendar"></span>
							</span>
						</span>
					</label>
				</div>
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.phrase}" type="text" value="{$element->phrase}" placeholder="{translations name='searchlog.phrase'}" />
						</span>
					</label>
				</div>

				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="checkbox_placeholder" type="checkbox" value="1" name="{$formNames.bZeroResultsOnly}" {if $element->bZeroResultsOnly == '1'}checked='checked'{/if} /> {translations name='searchlog.zeroresultsonly'}
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="checkbox_placeholder" type="checkbox" value="1" name="{$formNames.bNotClicked}" {if $element->bNotClicked == '1'}checked='checked'{/if}/> {translations name='searchlog.notclicked'}
						</span>
					</label>
				</div>
			</div>
			<div class="panel_controls">
				<input class="button primary_button" type="submit" value="{translations name='searchlog.filter'}" />
				<input type="hidden" value="{$element->id}" name="id" />
				<input type="hidden" value="show" name="action" />
			</div>
		</form>
	</div>

	<div class="content_list_block">
		<table class='table_component'>
			<thead>
			<tr>
				<th>
					#
				</th>
				<th>
					{translations name='searchlog.phrase'}
				</th>
				<th>
					{translations name='searchlog.resultscount'}
				</th>
				<th>
					{translations name='searchlog.clicked'}
				</th>
				<th>
					{translations name='searchlog.date'}
				</th>
				<th>
					{translations name='searchlog.visitor'}
				</th>
			</tr>
			</thead>
			<tbody>
			{if $logData}
				{foreach from=$logData item=logLine}
					{if count($logLine.id)}
						<tr class="content_list_item elementid_{$logLine.id}">
							<td>
								{$logLine.id}
							</td>
							<td class="searchlog_item_phrase">
								{$logLine.phrase}
							</td>
							<td>
								{$logLine.resultsCount}
							</td>
							<td>
								{if $logLine.bClicked}{translations name='searchlog.yes'}{/if}
							</td>
							<td>
								{$logLine.date}
							</td>
							<td>
								{if $logLine.visitorId != 0}
									<a href="{$logLine.visitorURL}">
										{$logLine.visitorId}
									</a>
								{/if}
							</td>
						</tr>
					{/if}
				{/foreach}
			{else}
				<tr class="content_list_item elementid_{$logLine.id}">
					<td colspan="5">{translations name='searchlog.noitems'}</td>
				</tr>
			{/if}
			</tbody>
		</table>
		<div class="content_list_bottom">{include file=$theme->template('pager.tpl')}</div>

		</form>
	</div>
</div>