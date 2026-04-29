{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<div class="content_list_block">
	<div class='filtration_component'>
		<form class="filtration_form panel_component" action="{$element->getFormActionURL()}" method="get" enctype="multipart/form-data">
			<div class="filtration_sections panel_content">
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='dispatchmentlog.startdate'}</span>
						<span class="filtration_form_item_field">
							<span class="date_container">
								<input class="input_component orders_list_filtration_start input_date" name="start" type="text" value="{$filters.start}" autocomplete="off" />
								<span class="icon icon_calendar"></span>
							</span>
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='visitors.enddate'}</span>
						<span class="filtration_form_item_field">
							<span class="date_container">
								<input class="input_component orders_list_filtration_end input_date" name="end" type="text" value="{$filters.end}" autocomplete="off" />
								<span class="icon icon_calendar"></span>
							</span>
						</span>
					</label>
				</div>
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='visitors.firstName'}</span>
						<span class="filtration_form_item_field">
							<input class="input_component" name="firstName" type="text" value="{$filters.firstName}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='visitors.lastName'}</span>
						<span class="filtration_form_item_field">
							<input class="input_component" name="lastName" type="text" value="{$filters.lastName}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='visitors.email'}</span>
						<span class="filtration_form_item_field">
							<input class="input_component" name="email" type="text" value="{$filters.email}" />
						</span>
					</label>
				</div>
				<div class="filtration_section filtration_form_items">
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='visitors.minOrderSum'}</span>
						<span class="filtration_form_item_field">
							<input class="input_component" name="minOrderSum" type="text" value="{$filters.minOrderSum}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='visitors.category'}</span>
						<span class="filtration_form_item_field">
							<select class="visitor_category_ajaxselect" name="category" data-types="category" >
								{if !empty($filterCategory)}
								<option value="{$filterCategory->id}" selected="selected" name="category">
									{if $filterCategory}{$filterCategory->title}{/if}
								</option>
								{/if}
							</select>
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_label">{translations name='visitors.product'}</span>
						<span class="filtration_form_item_field">
							<select class="visitor_product_ajaxselect" name="product" data-types="product">
								{if !empty($filterProduct)}
								<option value="{$filterProduct->id}" selected="selected" name="product">
									{if $filterProduct}{$filterProduct->title}{/if}
								</option>
								{/if}
							</select>
						</span>
					</label>
				</div>
			</div>
			<div class="panel_controls controls_block filtration_form_controls">
				<a class="button warning_button" href="{$element->URL}">{translations name='visitors.reset'}</a>
				<input class="button success_button" type="submit" value="{translations name='visitors.filter'}" />
			</div>
		</form>
	</div>
</div>

<div class="content_list_block">
	{if count($visitorsList)}
		{stripdomspaces}
			{include file=$theme->template('pager.tpl')}
			<table class='visitors_table table_component'>
				<thead>
				{function printOrderCellContent label='' orderParam=''}
					<a class="content_list_field_orderable" href="{$element->getOrderLinkHref($orderParam)}">{translations name=$label}</a>
					{if $element->getContentListOrder() == $orderParam}
						<span class="content_list_field_order_indicator content_list_field_order_indicator_{$element->getContentListDirection()}"></span>
					{/if}
				{/function}
				<th>
					{translations name='visitor.id'}
				</th>
				<th>
					{call printOrderCellContent name=printOrderCellContent label='visitor.firstname' orderParam='firstName'}
				</th>
				<th>
					{call printOrderCellContent name=printOrderCellContent label='visitor.lastname' orderParam='lastName'}
				</th>
				<th>
					{call printOrderCellContent name=printOrderCellContent label='visitor.email' orderParam='email'}
				</th>
				<th class="visitors_table_orderssum">
					{call printOrderCellContent name=printOrderCellContent label='visitor.orders_sum' orderParam='ordersSum'}
				</th>
				<th class="visitors_table_lastvisittime">
					{call printOrderCellContent name=printOrderCellContent label='visitor.last_visit' orderParam='lastVisitTime'}
				</th>
				<th>
					{call printOrderCellContent name=printOrderCellContent label='visitor.visit_referer' orderParam='lastReferer'}
				</th>
				<th></th>
				</thead>
				<tbody>
				{foreach $visitorsList as $item}
					<tr class="content_list_item">
						<td class="name_column">
							<a href="{$element->URL}visitor:{$item.id}/">{$item.id}</a>
						</td>
						<td class="name_column">
							{$item.firstName}
						</td>
						<td class="name_column">
							{$item.lastName}
						</td>
						<td>
							{$item.email}
						</td>
						<td class="visitors_table_orderssum">
							{if !empty($item.ordersSum)}
								{sprintf('%01.2f', $item.ordersSum)} €
							{/if}
						</td>
						<td class="visitors_table_lastvisittime">
							{if $item.lastVisitTime}
								{date('d.m.Y H:i', $item.lastVisitTime)}
							{/if}
						</td>
						<td class="visitors_table_referer">
							{$item.lastReferer}
						</td>
						<td class="visitors_table_button">
							<a class="button primary_button" href="{$element->URL}visitor:{$item.id}/">{translations name='visitors.view'}</a>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			<div class="content_list_bottom">{include file=$theme->template('pager.tpl')}</div>
		{/stripdomspaces}
	{/if}
	</form>
</div>
</div>