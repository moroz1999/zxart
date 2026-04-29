<div class="content_list_block">
	<div class="filtration_component">
		<form class="panel_component content_list_form filtration_form" action="{$currentElement->getFormActionURL()}" method="GET" enctype="multipart/form-data">
			<div class="panel_content filtration_form_items">
				<label class="filtration_form_item">
					<span class="filtration_form_item_label">
						{translations name='translationsexport.date_start'}
					</span>
					<span class="filtration_form_item_field">
						<span class="date_container">
							<input class='input_component input_date' type="text" name="start" value="{$start}" autocomplete="off"/>
							<span class="icon icon_calendar"></span>
						</span>
					</span>
				</label>
				<label class="filtration_form_item">
					<span class="filtration_form_item_label">
						{translations name='translationsexport.date_end'}
					</span>
					<span class="filtration_form_item_field">
						<span class="date_container">
							<input class='input_component input_date' type="text" name="end" value="{$end}" autocomplete="off" />
							<span class="icon icon_calendar"></span>
						</span>
					</span>
				</label>
				<label class="filtration_form_item">
					<span class="filtration_form_item_label">
						{translations name='translationsexport.admin_translations'}
					</span>
					<span class="filtration_form_item_field">
						<select class="dropdown_placeholder" name="admin_translations">
							<option value="1" {if $admin_translations}selected="selected"{/if}>{translations name='translationsexport.yes'}</option>
							<option value="0" {if !$admin_translations}selected="selected"{/if}>{translations name='translationsexport.no'}</option>
						</select>
					</span>
				</label>
			</div>
			<div class="panel_controls">
				<input type="hidden" class="content_list_form_id" value="{$currentElement->id}" name="id" />
				<input type="hidden" class="content_list_form_action" value="showFullList" name="action" />

				<button type="submit" class="button primary_button">
					{translations name='translationsexport.filter_submit'}
				</button>
			</div>
		</form>
	</div>


	<form class="content_list_form translations_export" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
		{assign var='formNames' value=$currentElement->getFormNames()}
		{if $currentElement->getTranslations()}
			<table class="table_component translations_export_table">
				<thead>
				<tr>
					<th>
						<input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
						{translations name='translationsexport.select_all'}
					</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>
						<div class="table_component translations_export_table_container">
							{foreach from=$currentElement->getTranslations() item=translationGroup}
								{$translationGroupName = $translationGroup.element->getTitle()}
								<table class="table_component translations_export_table">
									<thead>
									<tr>
										<th class="checkbox_column">
											<input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
										</th>
										<th class="table_header"><h3>{$translationGroupName}</h3></th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$translationGroup.translations item=translation}
										<tr class="translations_export_table_hover">
											<td class="checkbox_column">
												<input class='singlebox checkbox_placeholder' type="checkbox"
													   name="{$formNames.elements}[{$translationGroup.element->id}][{$translation->id}]" value="1" />												</td>
											<td class="name_column">
												<span class='icon icon_{$translation->structureType}'></span>
												<span>{$translation->getTitle()}</span>
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
							{/foreach}
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		{/if}

		<div class='controls_block content_list_controls'>
			<input type="hidden" class="content_list_form_id" value="{$currentElement->id}" name="id" />
			<input type="hidden" class="content_list_form_action" value="export" name="action" />
			<input type="hidden" class="" value="{if $admin_translations}1{else}0{/if}" name="admin_translations" />

			<button type="submit" class="button primary_button">
				{translations name='translationsexport.export'}
			</button>
		</div>
	</form>

</div>