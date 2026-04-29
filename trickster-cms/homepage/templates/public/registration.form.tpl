{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}

{capture assign="moduleContent"}
	<form action="{$currentElement->getFormActionURL()}" class='registration_form ajax_form' method="post" enctype="multipart/form-data" role="form">
		<div class='registration_form_block'>
			{capture assign='moduleTitle'}
				{$element->title}
			{/capture}
			<div class="registration_form_content html_content">
				{$element->content}
			</div>

			<div class='form_error_message ajax_form_error_message' role="alert">
			</div>

			{if $element->resultMessage == ''}
				{$socialPlugins = $element->getSocialPluginsOptions()}
				{if $socialPlugins}
					{stripdomspaces}
						<div class="registration_socialplugins">
							<div class="registration_socialplugins_title">
								{translations name='registration.connect_with_social_plugins'}:
							</div>
							<div class="registration_socialplugins_list">
								{foreach $socialPlugins as $plugin}
									{if !$plugin.connected}
										<a class="registration_socialplugin registration_socialplugin_{$plugin.code}" href="{$plugin.url}" title="{$plugin.title}">
											{if $plugin.icon}
												<img src="{$plugin.icon}" class="registration_socialplugin_icon" alt="{$plugin.title}" />
											{else}
												<span class="registration_socialplugin_text">{$plugin.title}</span>
											{/if}
										</a>
									{else}
										<span class="registration_socialplugin registration_socialplugin_{$plugin.code}">
									{if $plugin.icon}
										<img src="{$plugin.icon}" class="registration_socialplugin_icon" alt="{$plugin.title}" />

																			{else}

										<span class="registration_socialplugin_text" title="{$plugin.title}">{$plugin.title}</span>
									{/if}
											<a class="registration_socialplugin_cancel" href="{$plugin.url}"><span class="icon icon_delete"></span></a>
								</span>
									{/if}
								{/foreach}

							</div>
						</div>
					{/stripdomspaces}
				{/if}
				<table class='form_table'>
					{foreach $element->getConnectedFields() as $field}
						<tr>
							<td class='form_label'>
								{$field->title}:
							</td>
							<td class='form_star'>{if $field->required}*{/if}</td>
							<td class='form_field'>
								<input class='input_component' type="{$field->getInputType()}" value="{$element->getFieldValue($field->id)}" name="{$formNames.dynamicFieldsData}[{$field->id}]" />
							</td>
							<td class='form_extra'></td>
						</tr>
					{/foreach}

					<tr>
						<td class='form_empty' colspan='3'></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td>
							<div class='form_controls'>
								<span tabindex="0" class="button ajax_form_submit registration_form_submit">
									<span class='button_text'>{if $element->type == 'userdata'}{translations name='registration.form_save'}{else}{translations name='registration.form_register'}{/if}</span>
								</span>
							</div>
						</td>
					</tr>
				</table>
			{/if}
			<input type="hidden" value="{$element->id}" name="id" />
			<input type="hidden" value="submit" name="action" />
		</div>
	</form>
{/capture}

{assign moduleClass "registration_block"}

{include file=$theme->template("component.contentmodule.tpl")}
