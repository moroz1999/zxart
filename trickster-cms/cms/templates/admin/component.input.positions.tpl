{assign var='element' value= $form->getElementProperty($item.property)}
{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}

{foreach from=$element->getPositionElements() key=type item=positionElements}
	<div class="form_items form_header">
		<div class="form_label">
			{$type}
		</div>
		<div class="form_label">
			{translations name="label.positionnumber"}
		</div>
	</div>
	{foreach from=$positionElements item=child}
		<div class="form_items">
            <span class="form_label">
                {$child->getTitle()}:
            </span>
			<div class="form_field">
				<input class="input_component" type="text" name="{$formNames.$fieldName}[{$child->id}]" value="{$child->position}" />

			</div>
		</div>
	{/foreach}
{/foreach}