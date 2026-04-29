{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<div class="tabs_content_item">
	<div class="content_list_block notfoundlog_contentlist">
		<div class='filtration_component notfoundlog_filtration'>
			<form class="filtration_form panel_component" action="{$element->getFormActionURL()}" method="post" enctype="multipart/form-data">
				<div class="panel_content filtration_form_items">
					<div class="panel_heading">
						{translations name='notfoundlog.filtration'}
					</div>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<input class="input_component" name="{$formNames.errorUrl}" type="text" value="{$element->errorUrl}" placeholder="{translations name='notfoundlog.errorurl'}" />
						</span>
					</label>
					<label class="filtration_form_item">
						<span class="filtration_form_item_field">
							<select class="dropdown_placeholder" name="{$formNames.ignoreRedirected}" autocomplete='off'>
								<option value='ignore'{if $element->ignoreRedirected == "ignore"} selected="selected"{/if}>
									{translations name='notfoundlog.ignore_redirected'}
								</option>
								<option value='include'{if $element->ignoreRedirected == "include"} selected="selected"{/if}>
									{translations name='notfoundlog.include_redirected'}
								</option>
							</select>
						</span>
					</label>
				</div>
				<div class="panel_controls">
					<input class="button primary_button" type="submit" value="{translations name='notfoundlog.query'}" />
					<input type="hidden" value="{$element->id}" name="id" />
					<input type="hidden" value="show" name="action" />
				</div>
			</form>
		</div>
		<div class="content_list_block">
			{if count($logData)}
				<table class='table_component content_list_notfoundlog'>
					<thead>
					<tr>
						<th>
							{translations name='notfoundlog.errorurl'}
						</th>
						<th>
							{translations name='notfoundlog.requestcount'}
						</th>
						<th>
							{translations name='notfoundlog.httpreferer'}
						</th>
						<th>
							{translations name='notfoundlog.redirect'}
						</th>
						<th>
							{translations name='notfoundlog.date'}
						</th>
						<th>
							{translations name='notfoundlog.hiderecord'}
						</th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$logData item=logLine}
						{if count($logLine.id)}
							<tr class="content_list_item elementid_{$logLine.id}">
								<td class="notfoundlog_item_errorurl">
									{$logLine.errorUrl}
								</td>
								<td>
									{$logLine.count}
								</td>
								<td class="notfoundlog_item_referer">
									{$logLine.httpReferer}
								</td>
								<td>
									<a class="button primary_button" href="{$logLine.newRedirectUrl}">{translations name='notfoundlog.redirect'}</a>
								</td>
								<td class="notfoundlog_table_cell_date">
									{$logLine.date}
								</td>
								<td>
									<a class="button primary_button" href="{$element->URL}action:hideRecord/id:{$element->id}/recordId:{$logLine.id}">{translations name='notfoundlog.hiderecord'}</a>
								</td>
							</tr>
						{/if}
					{/foreach}
					</tbody>
				</table>
				<div class="content_list_bottom">{include file=$theme->template('pager.tpl')}</div>
			{/if}
			</form>
		</div>
	</div>
</div>