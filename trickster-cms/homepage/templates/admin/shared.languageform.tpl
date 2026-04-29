{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component languages_form" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		{foreach $formRelativesInfo as $relative}
			<tr{if $formErrors.formRelativesInput} class="form_error"{/if}>
				<td class="form_label" colspan="1">
					{translations name='languages.foreign_relative'} ({$languagesIndex[$relative@key]->title}):
				</td>
				<td>
					<input class='input_component languages_form_searchinput' type="text" value="" name="{$languagesIndex[$relative@key]->id}" placeholder="{translations name='field.search'}..." />
					<input class="ajaxitemsearch_resultid" type="hidden" value="{if $relative}{$relative->id}{/if}" name="{$formNames.formRelativesInput}[{$relative@key}]" />
					<div class="ajaxitemsearch_result"><span class="ajaxitemsearch_result_text">{if $relative}{$relative->title}{/if}</span><div class="ajaxitemsearch_result_remover"></div>
				</td>
			</tr>
		{/foreach}
	</table>
	{include file=$theme->template('component.controls.tpl') action='receiveLanguageForm'}
</form>
