{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component" method="post" enctype="multipart/form-data">
	<table class="form_table">
		{foreach from=$formData.answerText key=languageId item=answerText}
			<tr{if $formErrors.answerText.$languageId} class="form_error"{/if}>
				<td class="form_label">
					{translations name='poll.answer'} ({$languageNames.$languageId})
				</td>
				<td>
					<input class='input_component' type="text" value="{$answerText}" name="{$formNames.answerText.$languageId}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="answerText"}
				</td>
			</tr>
		{/foreach}
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>